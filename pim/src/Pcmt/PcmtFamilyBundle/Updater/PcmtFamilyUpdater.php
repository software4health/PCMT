<?php

declare(strict_types=1);

namespace Pcmt\PcmtFamilyBundle\Updater;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PcmtFamilyUpdater extends FamilyUpdater
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        IdentifiableObjectRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attrRequiFactory,
        AttributeRequirementRepositoryInterface $requirementRepo,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->productRepository = $productRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        parent::__construct(
            $familyRepository,
            $attributeRepository,
            $channelRepository,
            $attrRequiFactory,
            $requirementRepo,
            $translatableUpdater
        );
    }

    protected function addAttributes(FamilyInterface $family, array $data): void
    {
        $currentAttributeCodes = [];
        $wantedAttributeCodes = array_values($data);

        foreach ($family->getAttributes() as $attribute) {
            $currentAttributeCodes[] = $attribute->getCode();
        }

        $attributeCodesToRemove = array_diff($currentAttributeCodes, $wantedAttributeCodes);
        $attributeCodesToAdd = array_diff($wantedAttributeCodes, $currentAttributeCodes);

        foreach ($family->getAttributes() as $attribute) {
            if (in_array($attribute->getCode(), $attributeCodesToRemove)) {
                if (AttributeTypes::IDENTIFIER !== $attribute->getType()) {
                    $family->removeAttribute($attribute);
                }
            }
        }

        foreach ($attributeCodesToAdd as $attributeCode) {
            if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $family->addAttribute($attribute);
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attributes',
                    'code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
        }
    }
}