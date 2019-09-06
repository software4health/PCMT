<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\Validator\Directory;

use Doctrine\Common\Collections\ArrayCollection;
use Pcmt\Bundle\PcmtConnectorBundle\Exception\InvalidJobConfigurationException;
use Pcmt\Bundle\PcmtConnectorBundle\Validator\Directory\Provider\ValidPathProvider;
use function GuzzleHttp\Psr7\str;

class DirectoryPathValidator
{
    /** @var array */
    protected $configProviders;

    public function __construct()
    {
        $this->configProviders = [new ValidPathProvider()];
    }

    public function validate(string $key, $value): bool
    {
        $configuration = [];
        foreach ($this->configProviders as $configProvider){
            $configuration = array_merge($configuration, $configProvider->getConfig());
        }

        if(!array_key_exists($key, $configuration)){
            throw new \InvalidArgumentException(sprintf('Key %s either not valid or not registered', $key));
        }

        if(!is_array($value)){
            return strpos($configuration[$key], $value);
        }

        if(is_array($value)){
            foreach ($configuration[$key] as $configValue) {
                if(strpos($configValue, $value)){
                    return  true;
                }
            }
        }

        return false;
    }
}