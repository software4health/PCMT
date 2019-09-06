<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\Services\Factory\ReferenceData;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Pcmt\Bundle\Entity\AdditionalTradeItemClassificationCodeListCode;
use Pcmt\Bundle\Entity\AdditionalTradeItemIdentificationTypeCode;
use Pcmt\Bundle\Entity\ColourCodeListCode;
use Pcmt\Bundle\Entity\CountryCode;
use Pcmt\Bundle\Entity\DataCarrierTypeCode;
use Pcmt\Bundle\Entity\GDSNMeasurementUnitCode;
use Pcmt\Bundle\Entity\Gs1TradeItemIdentificationKeyCode;
use Pcmt\Bundle\Entity\ImportClassificationTypeCode;
use Pcmt\Bundle\Entity\NonfoodIngredientOfConcernCode;
use Pcmt\Bundle\Entity\PackageTypeCode;
use Pcmt\Bundle\Entity\PlatformTypeCode;
use Pcmt\Bundle\Entity\ReferencedFileTypeCode;
use Pcmt\Bundle\Entity\RegulationTypeCode;
use Pcmt\Bundle\Entity\ShippingContainerTypeCode;
use Pcmt\Bundle\Entity\SizeCodeListCode;
use Pcmt\Bundle\Entity\TemperatureQualifierCode;
use Pcmt\Bundle\Entity\TradeItemUnitDescriptorCode;

class ReferenceDataFactory
{
    public function getReferenceDataClass(string $className): ?string
    {
        switch ($className) {

                case 'AdditionalTradeItemClassificationCodeListCode':
                    return  AdditionalTradeItemClassificationCodeListCode::class;
                case 'AdditionalTradeItemIdentificationTypeCode':
                    return  AdditionalTradeItemIdentificationTypeCode::class;
                case 'ColourCodeListCode':
                    return ColourCodeListCode::class;
                case 'CountryCode':
                    return  CountryCode::class;
                case 'DataCarrierTypeCode':
                    return  DataCarrierTypeCode::class;
                case 'GS1TradeItemIdentificationKeyTypeCode':
                    return  Gs1TradeItemIdentificationKeyCode::class;
                case 'ImportClassificationTypeCode':
                    return  ImportClassificationTypeCode::class;
                case 'MeasurementUnitCode_GDSN':
                    return  GDSNMeasurementUnitCode::class;
                case 'NonfoodIngredientOfConcernCode':
                    return  NonfoodIngredientOfConcernCode::class;
                case 'PackageTypeCode':
                    return  PackageTypeCode::class;
                case 'PlatformTypeCode':
                    return  PlatformTypeCode::class;
                case 'ReferencedFileTypeCode':
                    return  ReferencedFileTypeCode::class;
                case 'ShippingContainerTypeCode':
                    return  ShippingContainerTypeCode::class;
                case 'SizeCodeListCode':
                    return  SizeCodeListCode::class;
                case 'TemperatureQualifierCode':
                    return  TemperatureQualifierCode::class;
                case 'TradeItemUnitDescriptorCode':
                    return TradeItemUnitDescriptorCode::class;
                case 'RegulationTypeCode':
                    return RegulationTypeCode::class;
                default:
                    return null;
            }
    }

}