<?php

namespace SCHOENBECK\Logging\Service;

use Monolog\Formatter\JsonFormatter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

/**
 * DataDogFormatter Service.
 */
class DataDogFormatterService extends JsonFormatter
{
    /**
     * Get environment for tagging in DataDog.
     */
    public function getEnv(): string
    {
        $environment = Environment::getContext();
        if ($environment->isDevelopment()) {
            return 'development';
        }
        if ($environment->isProduction()) {
            return 'production';
        }

        return 'unknown';
    }

    /**
     * Get user.
     */
    public function getUser(): string
    {
        if (\is_object($GLOBALS['BE_USER'])) {
            return 'BE-' . $GLOBALS['BE_USER']->user['username'];
        }
        if (\is_object($GLOBALS['TSFE']->fe_user) && isset($GLOBALS['TSFE']->fe_user->user['username'])) {
            return 'FE-' . $GLOBALS['TSFE']->fe_user->user['username'];
        }

        return 'unknown';
    }

    /**
     * Get TYPO3 mode.
     *
     * @return string
     */
    public function getTypo3Mode()
    {
        $environment = GeneralUtility::makeInstance(EnvironmentService::class);

        if ($environment->isEnvironmentInFrontendMode()) {
            return 'FE';
        }
        if ($environment->isEnvironmentInBackendMode()) {
            return 'BE';
        }
        if (Environment::isCli()) {
            return 'CLI';
        }

        return 'Environment could not be detected.';
    }

    public function getSource(array $context): string
    {
        if (isset($context['component'])) {
            return \implode(' ', \array_slice(\explode('.', $context['component']), 0, 2));
        }

        return 'No source available.';
    }
}
