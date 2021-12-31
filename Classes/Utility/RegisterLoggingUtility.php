<?php

namespace SCHOENBECK\Logging\Utility;

use SCHOENBECK\Logging\Log\Writer\DataDogWriter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class RegisterLoggingUtility.
 */
class RegisterLoggingUtility
{
    public static function registerExtensionForLogging(string $vendorName, string $extensionKey): void
    {
        if (ExtensionManagementUtility::isLoaded('logging')) {
            $level = self::buildLogLevelConfiguration(['vendorName' => $vendorName, 'extensionKey' => $extensionKey]);

            if (\count($level)) {
                $GLOBALS['TYPO3_CONF_VARS']['LOG'][\mb_strtoupper($vendorName)][\ucfirst($extensionKey)]['writerConfiguration'] = $level;
            }
        }
    }

    public static function registerNamespaceForLogging(string $namespace = '', bool $useClassName = true): void
    {
        if (ExtensionManagementUtility::isLoaded('logging')) {
            $namespace = \explode('\\', $namespace);
            if (!$useClassName) {
                $namespace = \array_slice($namespace, 0, \count($namespace) - 1);
            }

            $level = self::buildLogLevelConfiguration($namespace);

            if (\count($level)) {
                $writerConfigurationArray = self::buildWriterConfigurationArray($namespace, $level);
                $GLOBALS['TYPO3_CONF_VARS']['LOG'] = \SCHOENBECK\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TYPO3_CONF_VARS']['LOG'], $writerConfigurationArray);
            }
        }
    }

    public static function registerLoggingForT3ErrorHandling(): void
    {
        $level = self::buildLogLevelConfiguration(['TYPO3', 'CMS_Error']);
        if (\count($level)) {
            $writerConfigurationArray = self::buildWriterConfigurationArray(['TYPO3', 'CMS', 'Core', 'Error'], $level);
            $GLOBALS['TYPO3_CONF_VARS']['LOG'] = \SCHOENBECK\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TYPO3_CONF_VARS']['LOG'], $writerConfigurationArray);
        }
    }

    public static function registerLoggingForT3Deprecations(): void
    {
        $level = self::buildLogLevelConfiguration(['TYPO3', 'CMS_Deprecations']);
        if (\count($level)) {
            $writerConfigurationArray = self::buildWriterConfigurationArray(['TYPO3', 'CMS', 'deprecations'], $level);
            $GLOBALS['TYPO3_CONF_VARS']['LOG'] = \SCHOENBECK\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TYPO3_CONF_VARS']['LOG'], $writerConfigurationArray);
        }
    }

    public static function registerLoggingForExceptionHandler(string $namespace): void
    {
        $namespace = \explode('\\', $namespace);
        $level = self::buildLogLevelConfiguration(['TYPO3', 'CMS_Error']);
        if (\count($level)) {
            $writerConfigurationArray = self::buildWriterConfigurationArray($namespace, $level);
            $GLOBALS['TYPO3_CONF_VARS']['LOG'] = \SCHOENBECK\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TYPO3_CONF_VARS']['LOG'], $writerConfigurationArray);
        }
    }

    /**
     * @param array $options
     */
    public static function buildLogLevelConfiguration($options = []): array
    {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['logging']['configuration'])) {
            return [];
        }

        $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['logging']['configuration'];
        $logLevel = (int) $extensionConfiguration['loglevel'];
        $level = [];
        $writerOptions = [];

        if ('FileWriter' === $extensionConfiguration['writer']) {
            if (\count($options) >= 2) {
                $writerOptions = ['logFile' => Environment::getVarPath() . '/log/' . \mb_strtoupper(\array_values($options)[0]) . '_' . \ucfirst(\array_values($options)[1]) . '.log'];
            } else {
                $writerOptions = [
                    'logFile' => Environment::getVarPath() . '/log/' . \mb_strtoupper('SCHOENBECK') . '_' . \ucfirst('Logging') . '_' . \ucfirst('Default') . '.log',
                ];
            }
            if (TYPO3_version >= 10) {
                $level = [
                    LogLevel::getName($logLevel) => [
                        FileWriter::class => $writerOptions,
                    ],
                ];
            } else {
                $level = [
                    $logLevel => [
                        FileWriter::class => $writerOptions,
                    ],
                ];
            }
        }
        if ('DataDogWriter' === $extensionConfiguration['writer']) {
            if (TYPO3_version >= 10) {
                $level = [
                    LogLevel::getName($logLevel) => [
                        DataDogWriter::class => [
                            'fingersCrossed' => (bool) $extensionConfiguration['fingersCrossed'],
                            'fingersCrossedLevel' => (int) $extensionConfiguration['fingersCrossedLevel'],
                        ],
                    ],
                ];
            } else {
                $level = [
                    $logLevel => [
                        DataDogWriter::class => [
                            'fingersCrossed' => (bool) $extensionConfiguration['fingersCrossed'],
                            'fingersCrossedLevel' => (int) $extensionConfiguration['fingersCrossedLevel'],
                        ],
                    ],
                ];
            }

        }
        return $level;
    }

    public static function buildWriterConfigurationArray(array $namespace, array $level): array
    {
        if (!empty($namespace)) {
            $namespaceSegment[$namespace[0]] = self::buildWriterConfigurationArray(\array_slice($namespace, 1), $level);
        } else {
            $namespaceSegment['writerConfiguration'] = $level;
        }

        return $namespaceSegment;
    }
}
