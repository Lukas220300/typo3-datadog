<?php
defined('TYPO3_MODE') or die();

$boot = function () {
    // Autoloader
    \SCHOENBECK\Autoloader\Loader::extLocalconf('SCHOENBECK', 'logging');
};

$boot();
unset($boot);
