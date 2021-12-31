<?php

declare(strict_types=1);

namespace SCHOENBECK\Logging\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DataDogService.
 */
class DataDogService
{
    public function getApiKey(): string
    {
        $extensionConfiguration = $this->getExtensionConfigurationByName('logging');

        return $extensionConfiguration['datadog'] ? $extensionConfiguration['datadog']['apiKey'] : '';
    }

    public function getApiUrl(): string
    {
        $extensionConfiguration = $this->getExtensionConfigurationByName('logging');

        return $extensionConfiguration['datadog'] ? $extensionConfiguration['datadog']['apiUrl'] : '';
    }

    public function getHostName(): string
    {
        $extensionConfiguration = $this->getExtensionConfigurationByName('logging');

        return $extensionConfiguration['configuration'] ? $extensionConfiguration['configuration']['hostName'] : '';
    }

    public function getExtensionConfigurationByName(string $name): array
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extensionConfigurationForLogging = [];

        try {
            $extensionConfigurationForLogging = $extensionConfiguration->get($name);
        } catch (\Exception $exception) {
        }

        return $extensionConfigurationForLogging;
    }
}
