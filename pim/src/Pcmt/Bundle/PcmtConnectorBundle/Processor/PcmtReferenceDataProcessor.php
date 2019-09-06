<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\Processor;

use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CustomEntityBundle\Configuration\Registry;
use Pim\Bundle\CustomEntityBundle\Connector\Processor\Denormalization\ReferenceDataProcessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PcmtReferenceDataProcessor extends ReferenceDataProcessor
{
    protected $className;

    public function __construct(
        Registry $confRegistry,
        EntityManagerInterface $em,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher
    )
    {
        parent::__construct($confRegistry, $em, $updater, $validator, $detacher);
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (!isset($item['code'])) {
            throw new \RuntimeException(sprintf('Column "%s" is mandatory', 'code'));
        }

        $entity = $this->findOrCreateObject($item);
        try {
            unset($item['class']);
            $this->updater->update($entity, $item);
        } catch (\Exception $e) {
            $this->skipItemWithMessage($item, $e->getMessage(), $e);
        }

        $violations = $this->validator->validate($entity);
        if ($violations->count() > 0) {
            $this->detacher->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $entity;
    }

    protected function findOrCreateObject(array $item)
    {
        $this->className = $item['class'];
        $entity = $this->findObject($item);

        if (null === $entity) {
            $className = $this->className;
            $entity = new $className();
        }
        return $entity;
    }

    protected function getClassName(): ?string
    {
        return $this->className;
    }
}