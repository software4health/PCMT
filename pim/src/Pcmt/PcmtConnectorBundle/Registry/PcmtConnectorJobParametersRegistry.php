<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Registry;

final class PcmtConnectorJobParametersRegistry
{
    public const JOB_REFERENCE_DATA_DOWNLOAD_NAME = 'reference_data_download';
    private const JOB_REFERENCE_DATA_DOWNLOAD_PARAMETERS = [
        'connector'             => 'Pcmt Connector',
        'job'                   => 'reference_data_download_xmls',
        'code'                  => 'reference_data_download_xmls',
        'type'                  => 'data_download',
        'job_execution_handler' => 'pcmt:handler:download_reference_data',
    ];
    public const JOB_REFERENCE_DATA_IMPORT_NAME = 'reference_data_import';
    private const JOB_REFERENCE_DATA_IMPORT_PARAMETERS = [
        'connector'             => 'Pcmt Connector',
        'job'                   => 'reference_data_import_xml',
        'code'                  => 'reference_data_import_xml',
        'type'                  => 'import_hidden',
        'config'                => '{"dirPath": "%s"}',
        'job_execution_handler' => 'pcmt:handler:import_reference_data',
    ];

    public static function getConfig(string $jobName): array
    {
        switch ($jobName) {
            case self::JOB_REFERENCE_DATA_DOWNLOAD_NAME:
                return self::JOB_REFERENCE_DATA_DOWNLOAD_PARAMETERS;
            case self::JOB_REFERENCE_DATA_IMPORT_NAME:
                return self::JOB_REFERENCE_DATA_IMPORT_PARAMETERS;
            default:
                throw new \InvalidArgumentException('The job name is not specified or not allowed.');
        }
    }
}