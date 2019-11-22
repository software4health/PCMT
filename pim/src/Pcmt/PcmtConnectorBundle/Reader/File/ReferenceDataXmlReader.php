<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Reader\File;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Pcmt\PcmtConnectorBundle\Services\Factory\ReferenceData\ReferenceDataFactory;
use Pcmt\PcmtConnectorBundle\Util\Adapter\FileGetContentsWrapper;
use Sabre\Xml\Service;

abstract class ReferenceDataXmlReader implements FileReaderInterface
{
    /** @var Service */
    protected $xmlReader;

    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var array */
    protected $processed = [];

    /** @var \ArrayIterator */
    protected $arrayIterator;

    protected $fileGetContentsWrapper;

    public function __construct(FileGetContentsWrapper $wrapper)
    {
        $this->xmlReader = new Service();
        $this->fileGetContentsWrapper = $wrapper;
    }

    public function flush(): void
    {
        $this->processed = null;
        $this->arrayIterator = null;
    }

    public function read()
    {
        $filePath = $this->stepExecution->getJobParameters()
            ->get('filePath');

        $xmlMapping = ($this->stepExecution->getJobParameters()
            ->get('xmlMapping')) ?? null;

        $this->xmlReader->elementMap = $xmlMapping ?? [
            '{http://www.w3.org/2001/XMLSchema-instance}urn' => 'Sabre\Xml\Element\XmlElement',
            'code'                                           => 'Sabre\Xml\Element\KeyValue',
        ];

        if (!$this->processed) {
            try {
                $this->validateFileExtension($filePath);
                $input = $this->fileGetContentsWrapper->fileGetContents($filePath);
                $parsed = $this->xmlReader->parse($input);

                if (!$parsed || !is_array($parsed)) {
                    throw new \Exception('File reading failed. File corrupted or wrong data format.');
                }

                $className = null;
                $version = null;

                foreach ($parsed as $value) {
                    if (!$className) {
                        $className = $this->setClassName($value);
                    }

                    if (!$version) {
                        $version = $this->setVersion($value);
                    }

                    if ('code' === ltrim($value['name'], static::DELIMITER)) {
                        $this->processed[] = $this->createReferenceDataArray($value['value'], $className, $version);
                    }
                }
            } catch (\Throwable $exception) {
                $this->stepExecution->addError('Failed to read the input file: ' . $exception->getMessage());
                $this->stepExecution->addFailureException($exception);

                throw $exception;
            }
        }

        if (null === $this->arrayIterator) {
            $arrayObject = new \ArrayObject($this->processed);
            $this->arrayIterator = $arrayObject->getIterator();
            $this->arrayIterator->rewind();
        }
        $item = $this->arrayIterator->current();
        $this->arrayIterator->next();

        return $item;
    }

    public function getProcessed(): ?array
    {
        return $this->processed;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function setClassName(array $item): ?string
    {
        $delimiter = static::DELIMITER;
        if (!array_key_exists('name', $item) || 'urn' !== ltrim($item['name'], $delimiter)) {
            return null;
        }

        $classMatch = [];
        preg_match('/cl:(.*)/', $item['value'], $classMatch);

        $classNameFactory = new ReferenceDataFactory();

        return $classNameFactory->getReferenceDataClass($classMatch[1]);
    }

    private function setVersion(array $item): ?string
    {
        $delimiter = static::DELIMITER;
        if (!array_key_exists('name', $item) || 'version' !== ltrim($item['name'], $delimiter)) {
            return null;
        }

        return $item['value'] ?? null;
    }

    private function createReferenceDataArray(array $value, string $entityType, ?string $entityVersion): array
    {
        $delimiter = static::DELIMITER;

        $valueReindexed = [];

        array_walk(
            $value,
            function ($code, $key) use ($delimiter, &$valueReindexed): void {
                $key = ltrim($key, $delimiter);
                $valueReindexed[$key] = $code;
            }
        );

        $entityModel['class'] = $entityType;
        $entityModel['version'] = $entityVersion ?? null;
        $entityModel['code'] = $valueReindexed['value'];
        $entityModel['name'] = $valueReindexed['name'];
        $entityModel['definition'] = $valueReindexed['description'] ?? null;

        return $entityModel;
    }

    public function validateFileExtension(string $filePath): void
    {
        if (!('xml' === substr($filePath, -3))) {
            throw new \InvalidArgumentException('Invalid file extension');
        }
    }
}