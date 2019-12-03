<?php

declare(strict_types=1);

namespace PcmtProductBundle\Services\Factory\ReferenceData;

use PcmtProductBundle\Entity\ReferenceData\AdditionalTradeItemClassificationCodeListCode;
use PcmtProductBundle\Entity\ReferenceData\AdditionalTradeItemIdentificationTypeCode;
use PcmtProductBundle\Entity\ReferenceData\ColourCodeListCode;
use PcmtProductBundle\Entity\ReferenceData\CountryCode;
use PcmtProductBundle\Entity\ReferenceData\DataCarrierTypeCode;
use PcmtProductBundle\Entity\ReferenceData\GDSNMeasurementUnitCode;
use PcmtProductBundle\Entity\ReferenceData\Gs1TradeItemIdentificationKeyCode;
use PcmtProductBundle\Entity\ReferenceData\ImportClassificationTypeCode;
use PcmtProductBundle\Entity\ReferenceData\NonfoodIngredientOfConcernCode;
use PcmtProductBundle\Entity\ReferenceData\PackageTypeCode;
use PcmtProductBundle\Entity\ReferenceData\PlatformTypeCode;
use PcmtProductBundle\Entity\ReferenceData\ReferencedFileTypeCode;
use PcmtProductBundle\Entity\ReferenceData\RegulationTypeCode;
use PcmtProductBundle\Entity\ReferenceData\ShippingContainerTypeCode;
use PcmtProductBundle\Entity\ReferenceData\SizeCodeListCode;
use PcmtProductBundle\Entity\ReferenceData\TemperatureQualifierCode;
use PcmtProductBundle\Entity\ReferenceData\TradeItemUnitDescriptorCode;

class ReferenceDataFactory
{
    public function getReferenceDataClass(string $className): ?string
    {
        switch ($className) {
            case 'AdditionalTradeItemClassificationCodeListCode':
                return AdditionalTradeItemClassificationCodeListCode::class;
            case 'AdditionalTradeItemIdentificationTypeCode':
                return AdditionalTradeItemIdentificationTypeCode::class;
            case 'ColourCodeListCode':
                return ColourCodeListCode::class;
            case 'CountryCode':
                return CountryCode::class;
            case 'DataCarrierTypeCode':
                return DataCarrierTypeCode::class;
            case 'GS1TradeItemIdentificationKeyTypeCode':
                return Gs1TradeItemIdentificationKeyCode::class;
            case 'ImportClassificationTypeCode':
                return ImportClassificationTypeCode::class;
            case 'MeasurementUnitCode_GDSN':
                return GDSNMeasurementUnitCode::class;
            case 'NonfoodIngredientOfConcernCode':
                return NonfoodIngredientOfConcernCode::class;
            case 'PackageTypeCode':
                return PackageTypeCode::class;
            case 'PlatformTypeCode':
                return PlatformTypeCode::class;
            case 'ReferencedFileTypeCode':
                return ReferencedFileTypeCode::class;
            case 'ShippingContainerTypeCode':
                return ShippingContainerTypeCode::class;
            case 'SizeCodeListCode':
                return SizeCodeListCode::class;
            case 'TemperatureQualifierCode':
                return TemperatureQualifierCode::class;
            case 'TradeItemUnitDescriptorCode':
                return TradeItemUnitDescriptorCode::class;
            case 'RegulationTypeCode':
                return RegulationTypeCode::class;
            default:
                return null;
        }
    }
}