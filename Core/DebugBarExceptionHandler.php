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

namespace D3\DebugBar\Core;

use D3\DebugBar\Application\Component\DebugBarComponent;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DebugBarException;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Eshop\Core\Exception\ExceptionHandler;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\LoggerServiceFactory;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\Context;
use Throwable;

class DebugBarExceptionHandler
{
    /**
     * Handler for uncaught exceptions.
     *
     * @param Throwable $exception exception object
     * @return void
     */
    public function handleUncaughtException(Throwable $exception): void
    {
        try {
            /** @var int $debugMode */
            $debugMode = Registry::get(ConfigFile::class)->getVar('iDebug');
            $defaultExceptionHandler = new ExceptionHandler($debugMode);
            $defaultExceptionHandler->writeExceptionToLog($exception);
        } catch (Throwable $loggerException) {
            /**
             * Its not possible to get the logger from the DI container.
             * Try again to log original exception (without DI container) in order to show the root cause of a problem.
             */
            try {
                $loggerServiceFactory = new LoggerServiceFactory(new Context());
                $logger = $loggerServiceFactory->getLogger();
                $logger->error($exception->getTraceAsString());
            } catch (Throwable $throwableWithoutPossibilityToWriteToLogFile) {
                // It is not possible to log because e.g. the log file is not writable.
            }
        }

        global $debugBarSet;

        if ($debugBarSet !== 1 && false === isAdmin()) {
            try {
                /** @var DebugBarComponent $debugBarComponent */
                $debugBarComponent = oxNew(DebugBarComponent::class);

                /** @var ExceptionsCollector $excCollector */
                $excCollector = $debugBarComponent->getDebugBar()->getCollector('exceptions');
                $excCollector->addThrowable($exception);

                echo <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
        <title></title>
HTML;
                echo $debugBarComponent->getRenderer()->renderHead();
                $debugBarComponent->addTimelineMessures();
                echo <<<HTML
    </head>
    <body>
HTML;
                $debugBarSet = 1;
                echo $debugBarComponent->getRenderer()->render();
                echo <<<HTML
    </body>
</html>
HTML;
            } catch (DebugBarException $e) {
                Registry::getLogger()->error($e->getMessage());
                Registry::getUtilsView()->addErrorToDisplay($e);
            }
        }
    }
}
