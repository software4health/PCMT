<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Services\Factory\ReferenceData;

use Pcmt\PcmtProductBundle\Entity\ReferenceData\AdditionalTradeItemClassificationCodeListCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\AdditionalTradeItemIdentificationTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\ColourCodeListCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\CountryCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\DataCarrierTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\GDSNMeasurementUnitCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\Gs1TradeItemIdentificationKeyCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\ImportClassificationTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\NonfoodIngredientOfConcernCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\PackageTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\PlatformTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\ReferencedFileTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\RegulationTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\ShippingContainerTypeCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\SizeCodeListCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\TemperatureQualifierCode;
use Pcmt\PcmtProductBundle\Entity\ReferenceData\TradeItemUnitDescriptorCode;

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