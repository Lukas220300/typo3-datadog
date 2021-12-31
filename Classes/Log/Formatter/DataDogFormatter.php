<?php

declare(strict_types=1);

namespace SCHOENBECK\Logging\Log\Formatter;

use SCHOENBECK\Logging\Service\DataDogFormatterService;
use SCHOENBECK\Logging\Service\DataDogService;
use Monolog\Formatter\JsonFormatter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataDogFormatter extends JsonFormatter
{
    /**
     * @var DataDogFormatterService
     */
    protected $dataDogFormatterService;

    /**
     * @var DataDogService
     */
    protected $dataDogService;

    /**
     * DataDogFormatter constructor.
     */
    public function __construct(int $batchMode = self::BATCH_MODE_JSON, bool $appendNewline = true)
    {
        $this->dataDogFormatterService = GeneralUtility::makeInstance(DataDogFormatterService::class);
        parent::__construct($batchMode, $appendNewline);
        $this->dataDogService = new DataDogService();
    }

    public function format(array $record): string
    {
        $data = [
            'hostname' => $this->dataDogService->getHostName(),
            'message' => $record['message'],
            'status' => $record['level_name'],
            'service' => 'TYPO3',
            'ddsource' => $this->dataDogFormatterService->getSource($record['context']),
            'ddtags' => 'app:typo3,env:' . $this->dataDogFormatterService->getEnv() . ',user:' . $this->dataDogFormatterService->getUser() . ',context:' . (string) Environment::getContext(),
            'mode' => $this->dataDogFormatterService->getTypo3Mode(),
            'typo3' => $record['context'],
            'context' => (string) Environment::getContext(),
        ];

        return parent::format($data);
    }
}
