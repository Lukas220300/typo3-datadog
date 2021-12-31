<?php

declare(strict_types=1);

namespace SCHOENBECK\Logging\Log\Writer;

use SCHOENBECK\Logging\Log\Handler\DataDogHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataDogWriter extends AbstractWriter
{
    protected $options = [
        'fingersCrossed' => true,
        'fingersCrossedLevel' => LogLevel::WARNING,
    ];

    protected $bufferSize = 150;

    protected static $logger = [];

    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    public function getBufferSize(): int
    {
        return $this->bufferSize;
    }

    public function setFingersCrossed(bool $enabled): void
    {
        $this->options['fingersCrossed'] = $enabled;
    }

    public function setFingersCrossedLevel(int $level): void
    {
        $this->options['fingersCrossedLevel'] = $level;
    }

    public function writeLog(LogRecord $record): void
    {
        $logger = $this->getMonologLogger($this->options);
        $data = $record->getData();
        $forward = $record->toArray();
        if (isset($data['exception'])) {
            $forward['exception'] = $data['exception'];
        }
        $logLevel = LogLevel::getName($record->getLevel());
        $logger->log(Logger::toMonologLevel($logLevel), (string) $record->getMessage(), $forward);
    }

    protected function getMonologLogger(array $options)
    {
        $ident = GeneralUtility::shortMD5(\serialize($options));
        if (isset(self::$logger[$ident])) {
            return self::$logger[$ident];
        }
        $dataDogHandler = new DataDogHandler();
        $fingersCrossed = new FingersCrossedHandler($dataDogHandler, new ErrorLevelActivationStrategy(Logger::toMonologLevel($options['fingersCrossedLevel'])), $this->getBufferSize());
        if (true === $options['fingersCrossed']) {
            $fingersCrossed->activate();
        }
        self::$logger[$ident] = new Logger('TYPO3', [$fingersCrossed]);

        return self::$logger[$ident];
    }
}
