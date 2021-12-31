<?php

declare(strict_types=1);

namespace SCHOENBECK\Logging\Log\Handler;

use SCHOENBECK\Logging\Log\Formatter\DataDogFormatter;
use SCHOENBECK\Logging\Service\DataDogService;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\Curl\Util;
use Monolog\Logger;

/**
 * Class DataDogHandler.
 */
class DataDogHandler extends AbstractProcessingHandler
{
    protected $dataDogService;

    /**
     * DataDogHandler constructor.
     *
     * @param int $level
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        if (!\extension_loaded('curl')) {
            throw new \LogicException('Curl extension is needed!');
        }
        $this->dataDogService = new DataDogService();

        parent::__construct($level, $bubble);
    }

    /**
     * @param $data
     */
    protected function send($data): void
    {
        $url = $this->dataDogService->getApiUrl();
        $headers = [
            'Content-Type: application/json',
            'DD-API-KEY: ' . $this->dataDogService->getApiKey(),
        ];
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, $url);
        \curl_setopt($ch, \CURLOPT_POST, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $data);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);

        Util::execute($ch);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new DataDogFormatter();
    }

    protected function write(array $record): void
    {
        $this->send($record['formatted'] ?? $this->getDefaultFormatter()->format($record));
    }
}
