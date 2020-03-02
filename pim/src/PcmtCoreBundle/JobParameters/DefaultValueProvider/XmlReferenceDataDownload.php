<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PcmtCoreBundle\JobParameters\SupportedJobsTrait;

class XmlReferenceDataDownload implements DefaultValuesProviderInterface
{
    use SupportedJobsTrait;

    /** @var string */
    protected $fileDirectory;

    /** @var string */
    protected $refDirName;

    public function __construct(
        array $supportedJobNames,
        string $refDirName,
        ?string $fileDirectory = null
    ) {
        $this->supportedJobNames = $supportedJobNames;
        $this->refDirName = $refDirName;
        $this->fileDirectory = $fileDirectory ?? 'reference_data/';
    }

    public function getDefaultValues(): array
    {
        return [
            'xml_data_pick_urls' => [
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:AdditionalTradeItemClassificationCodeListCode&release=9',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:AdditionalTradeItemIdentificationTypeCode&release=6',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:ColourCodeListCode&release=4',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:CountryCode&release=4',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:DataCarrierTypeCode&release=2',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:MeasurementUnitCode_GDSN&release=7',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:GS1TradeItemIdentificationKeyTypeCode&release=1',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:ImportClassificationTypeCode&release=2',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:NonfoodIngredientOfConcernCode&release=1',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:PackageTypeCode&release=2',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:PlatformTypeCode&release=1',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:ReferencedFileTypeCode&release=5',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:RegulationTypeCode&release=6',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:ShippingContainerTypeCode&release=1',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:SizeCodeListCode&release=2',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:TemperatureQualifierCode&release=2',
                'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:TradeItemUnitDescriptorCode&release=1',
            ],
            'dirPath'  => 'src/PcmtCoreBundle/Resources/' . $this->fileDirectory . $this->refDirName . '/',
            'filePath' => null,
        ];
    }
}