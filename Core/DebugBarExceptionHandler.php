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
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseException;
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
     * @throws DebugBarException
     */
    public function handleUncaughtException(Throwable $exception)
    {
        //dumpvar(__METHOD__.__LINE__);
        try {
            $debugMode = (bool) \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class)->getVar('iDebug');
            $defaultExceptionHandler = new ExceptionHandler($debugMode);
            $defaultExceptionHandler->writeExceptionToLog($exception);
        } catch (Throwable $loggerException) {
            /**
             * Its not possible to get the logger from the DI container.
             * Try again to log original exception (without DI container) in order to show the root cause of a problem.
             */
            try {
                $loggerServiceFactory = new LoggerServiceFactory(new Context(Registry::getConfig()));
                $logger = $loggerServiceFactory->getLogger();
                $logger->error($exception->getTraceAsString());
            } catch (Throwable $throwableWithoutPossibilityToWriteToLogFile) {
                // It is not possible to log because e.g. the log file is not writable.
            }
        }

        global $debugBarSet;
        if ($debugBarSet !== 1) {
            /** @var FrontendController $activeView */
            $activeView = Registry::getConfig()->getTopActiveView();
            /** @var DebugBarComponent|null $debugBarComponent */
            $debugBarComponent = $activeView->getComponent(DebugBarComponent::class) ?: oxNew(DebugBarComponent::class);

            /** @var ExceptionsCollector $excCollector */
            $excCollector = $debugBarComponent->getDebugBar()->getCollector('exceptions');
            $excCollector->addThrowable($exception);

            echo <<<HTML
    <!DOCTYPE html>
<html>
    <head>
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
        }
    }

    /**
     * @param DatabaseException $exception
     * @return void
     * @throws DebugBarException
     */
    public function handleDatabaseException(DatabaseException $exception)
    {
        $this->handleUncaughtException($exception);
    }
}