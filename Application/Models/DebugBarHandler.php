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

namespace D3\DebugBar\Application\Models;

use D3\DebugBar\Application\Component\DebugBarComponent;
use D3\DebugBar\Core\DebugBarErrorHandler;
use D3\DebugBar\Core\DebugBarExceptionHandler;
use OxidEsales\Eshop\Core\Registry;

class DebugBarHandler
{
    /**
     * @return void
     */
    public function setErrorHandler(): void
    {
        if ($this->d3CanActivateDebugBar()) {
            /** @var callable $callable */
            $callable = [
                new DebugBarErrorHandler(),
                'callback',
            ];
            set_error_handler($callable, $this->getHandledErrorTypes());
        }
    }

    /**
     * @return int
     */
    protected function getHandledErrorTypes(): int
    {
        return E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_PARSE;
    }

    /**
     * @return void
     */
    public function setExceptionHandler(): void
    {
        if ($this->d3CanActivateDebugBar()) {
            set_exception_handler([
               new DebugBarExceptionHandler(),
               'handleUncaughtException',
           ]);
        }
    }

    /**
     * @return void
     */
    public function addDebugBarComponent(): void
    {
        if ($this->d3CanActivateDebugBar()) {
            $userComponentNames = Registry::getConfig()->getConfigParam('aUserComponentNames');
            $d3CmpName          = DebugBarComponent::class;
            $blDontUseCache     = 1;

            if (! is_array($userComponentNames)) {
                $userComponentNames = [];
            }

            if (! in_array($d3CmpName, array_keys($userComponentNames))) {
                $userComponentNames[ $d3CmpName ] = $blDontUseCache;
                Registry::getConfig()->setConfigParam('aUserComponentNames', $userComponentNames);
            }
        }
    }

    /**
     * @return bool
     */
    protected function d3CanActivateDebugBar(): bool
    {
        return false === isAdmin();
    }
}
