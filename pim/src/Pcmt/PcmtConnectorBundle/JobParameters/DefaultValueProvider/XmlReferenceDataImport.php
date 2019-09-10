<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class XmlReferenceDataImport implements  DefaultValuesProviderInterface
{
   protected $supportedJobNames;

    protected $fileDirectory = 'Xml';

    public function __construct(
        array $supportedJobNames
    )
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues(): array
    {
        return  [
            'filePath' => null,
            'dirPath' => 'src/Pcmt/Bundle/PcmtConnectorBundle/Resources/config/reference_data',
            'uploadAllowed'     => [
                new Type('bool'),
                new IsTrue(['groups' => 'UploadExecution'])
            ],
            'decimalSeparator'  => new NotBlank(),
            'xmlMapping' => [
                '{http://www.w3.org/2001/XMLSchema-instance}urn' => 'Sabre\Xml\Element\XmlElement',
                'code' => 'Sabre\Xml\Element\KeyValue'
            ]
        ];


    }

    public function supports(JobInterface $job): bool
    {
        return (in_array($job->getName(), $this->supportedJobNames));
    }
}