<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class XmlReferenceDataDownload implements DefaultValuesProviderInterface
{
    /** @var string  */
    protected $fileDirectory;

    /** @var string $refDirName */
    protected $refDirName;

    /** @var array $supportedJobNames */
    protected $supportedJobNames;

    public function __construct(
        array $supportedJobNames,
        string $refDirName,
        string $fileDirectory = null
    )
    {
       $this->supportedJobNames = $supportedJobNames;
       $this->refDirName = $refDirName;
       $this->fileDirectory = ($fileDirectory) ?? 'reference_data/';
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
                    'http://apps.gs1.org/GDD/Pages/CLXMLReport.aspx?semanticURN=urn:gs1:gdd:cl:TradeItemUnitDescriptorCode&release=1'
                ],
                'dirPath'         => 'src/Pcmt/PcmtConnectorBundle/Resources/config/' . $this->fileDirectory . $this->refDirName . '/',
                'filePath'        => null
        ];
    }

    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}