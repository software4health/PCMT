<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Helper;

final class GsCodesHelper
{
    public static function getGsCodes(): array
    {
        return [
            'AdditionalTradeItemClassificationCodeListCode',
            'AdditionalTradeItemIdentificationTypeCode',
            'ColourCodeListCode',
            'CountryCode',
            'DataCarrierTypeCode',
            'GDSNMeasurementUnitCode',
            'Gs1TradeItemIdentificationKeyCode',
            'ImportClassificationTypeCode',
            'LanguageCode',
            'NonfoodIngredientOfConcernCode',
            'PackageTypeCode',
            'PlatformTypeCode',
            'ReferencedFileTypeCode',
            'RegulationTypeCode',
            'RouteAdministration',
            'ShippingContainerTypeCode',
            'SizeCodeListCode',
            'TemperatureQualifierCode',
            'TradeItemUnitDescriptorCode',
        ];
    }
}