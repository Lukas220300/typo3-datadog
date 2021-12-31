
## Error Handling
When logging TYPO3ErrorHandling, add the following code to the `AdditionalConfiguration`.

``` php
// Error Handling Logging Configuration
if (class_exists(\SCHOENBECK\Logging\Utility\RegisterLoggingUtility::class)) {
    \SCHOENBECK\Logging\Utility\RegisterLoggingUtility::registerLoggingForT3ErrorHandling();
}
