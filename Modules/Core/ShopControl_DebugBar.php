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

namespace D3\DebugBar\Modules\Core;

use D3\DebugBar\Application\Component\DebugBarComponent;
use D3\DebugBar\Application\Models\DebugBarHandler;
use D3\DebugBar\Core\DebugBarExceptionHandler;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use Throwable;

class ShopControl_DebugBar extends ShopControl_DebugBar_parent
{
    public function __construct()
    {
        $handler = oxNew(DebugBarHandler::class);

        $handler->setErrorHandler();
        $handler->setExceptionHandler();
        $handler->addDebugBarComponent();

        parent::__construct();
    }

    /**
     * @param string|null $controllerKey
     * @param string|null $function
     * @param array $parameters
     * @param array $viewsChain
     */
    public function start($controllerKey = null, $function = null, $parameters = null, $viewsChain = null)
    {
        parent::start();

        global $debugBarSet, $debugBarErrorOccured;

        if (!isAdmin() && $debugBarSet !== 1 && $debugBarErrorOccured !== 1) {
            $activeView =  Registry::getConfig()->getTopActiveView();
            /** @var DebugBarComponent|null $debugBarComponent */
            $debugBarComponent = $activeView->getComponent(DebugBarComponent::class);
            if ($debugBarComponent) {
                $debugBarSet = 1;
                echo $debugBarComponent->getRenderer()->renderHead();
                $debugBarComponent->addTimelineMessures();
                echo $debugBarComponent->getRenderer()->render();
            }
        }
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function debugBarHandleException(Throwable $exception): void
    {
        $exceptionHandler = new DebugBarExceptionHandler();
        $exceptionHandler->handleUncaughtException($exception);
    }

    /**
     * @param StandardException $exception
     */
    protected function _handleSystemException($exception)
    {
        $this->debugBarHandleException($exception);
    }

    /**
     * @param StandardException $exception
     */
    protected function _handleCookieException($exception)
    {
        $this->debugBarHandleException($exception);
    }

    /**
     * @param StandardException $exception
     */
    protected function _handleBaseException($exception)
    {
        $this->debugBarHandleException($exception);
    }
}
