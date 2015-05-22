<?php

/**
 *  webSAT Library autoloader
 */

spl_autoload_register(function($className) {
    $nameSpaces = explode('\\', $className);
    $nameSpace = array_shift($nameSpaces);
    if (count($nameSpaces) > 1) {
        $nameSpace2 = array_shift($nameSpaces);
    }
    $className = implode($nameSpaces);
    if ($nameSpace === 'webSAT' && !isset($nameSpace2)) {
        include __DIR__ . "/lib/$className.php";
    } elseif ($nameSpace === 'webSAT' && isset($nameSpace2) && $nameSpace2 === 'Workers') {
        include __DIR__ . "/bin/Workers/$className.php";
    }
});
