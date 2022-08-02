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

use D3\DebugBar\Application\Models\TimeDataCollectorHandler;

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