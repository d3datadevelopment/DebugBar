<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

use D3\DebugBar\Application\Component\DebugBarComponent;
use D3\DebugBar\Application\Models\TimeDataCollectorHandler;
use DebugBar\DataCollector\MessagesCollector;
use OxidEsales\Eshop\Core\Registry;

function startProfile($sProfileName)
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace[0] = $sProfileName;
    $hash = md5(serialize($trace)).'-'.$sProfileName;

    $timeDataCollector = TimeDataCollectorHandler::getInstance();
    $timeDataCollector->startMeasure($hash, $sProfileName);

    global $aStartTimes;
    global $executionCounts;
    if (!isset($executionCounts[$sProfileName])) {
        $executionCounts[$sProfileName] = 0;
    }
    if (!isset($aStartTimes[$sProfileName])) {
        $aStartTimes[$sProfileName] = 0;
    }
    $executionCounts[$sProfileName]++;
    $aStartTimes[$sProfileName] = microtime(true);
}

function stopProfile($sProfileName)
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace[0] = $sProfileName;
    $hash = md5(serialize($trace)).'-'.$sProfileName;

    $timeDataCollector = TimeDataCollectorHandler::getInstance();
    $timeDataCollector->stopMeasure($hash);


    global $aStartTimes;
    global $executionCounts;
    if (!isset($executionCounts[$sProfileName])) {
        $executionCounts[$sProfileName] = 0;
    }
    if (!isset($aStartTimes[$sProfileName])) {
        $aStartTimes[$sProfileName] = 0;
    }
    $executionCounts[$sProfileName]++;
    $aStartTimes[$sProfileName] = microtime(true);
}

function debugVar($mVar, $blToFile = false)
{
    if ($blToFile) {
        $out = var_export($mVar, true);
        $f = fopen(Registry::getConfig()->getConfigParam('sCompileDir') . "/vardump.txt", "a");
        fwrite($f, $out);
        fclose($f);
    } else {
        if (!isAdmin()) {
            $activeView = Registry::getConfig()->getTopActiveView();
            /** @var DebugBarComponent $debugBarComponent */
            $debugBarComponent = $activeView->getComponent(DebugBarComponent::class);
            /** @var MessagesCollector $messages */
            $messages = $debugBarComponent->getDebugBar()->getCollector('messages');
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            //$location = $trace[1]['class'] . '::' . $trace[1]['function']. '(' . $trace[0]['line'] . ')';
            $location = $trace[1]['class'] . '::' . $trace[1]['function'];
            $messages->addMessage($mVar, $location);
        } else {
            dumpVar($mVar, $blToFile);
        }
    }
}