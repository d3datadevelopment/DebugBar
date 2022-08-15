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
use D3\DebugBar\Application\Models\Exceptions\CompileErrorException;
use D3\DebugBar\Application\Models\Exceptions\CoreErrorException;
use D3\DebugBar\Application\Models\Exceptions\ParseException;
use D3\DebugBar\Application\Models\Exceptions\UserErrorException;
use D3\DebugBar\Core\DebugBarExceptionHandler;
use DebugBar\DataCollector\ExceptionsCollector;
use ErrorException;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class ShopControl_DebugBar extends ShopControl_DebugBar_parent
{
    /**
     * @throws ErrorException
     */
    public function __construct()
    {
        $this->d3DebugBarSetErrorHandler();;
        $this->d3DebugBarSetExceptionHandler();
        $this->d3AddDebugBarComponent();

        parent::__construct();
    }

    /**
     * @return void
     * @throws ErrorException
     */
    public function d3DebugBarSetErrorHandler()
    {
        if ($this->d3CanActivateDebugBar()) {
            set_error_handler(
                function( $severity, $message, $file, $line ) {
                    if ( 0 === error_reporting() || !( error_reporting() & $severity ) ) {
                        // This error code is not included in error_reporting.
                        return false;
                    }

                    $smartyTemplate = $this->getSmartyTemplateLocationFromError( $message );
                    if ( is_array( $smartyTemplate ) ) {
                        [ $file, $line ] = $smartyTemplate;
                    }

                    switch($severity) {
                        case E_CORE_ERROR:
                            throw new CoreErrorException($message, 0, $severity, $file, $line);
                        case E_COMPILE_ERROR:
                            throw new CompileErrorException($message, 0, $severity, $file, $line);
                        case E_USER_ERROR:
                            throw new UserErrorException($message, 0, $severity, $file, $line);
                        case E_PARSE:
                            throw new ParseException($message, 0, $severity, $file, $line);
                        case E_ERROR:
                        default:
                            throw new ErrorException($message, 0, $severity, $file, $line);
                    }
                },
                E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_PARSE
            );
        }
    }

    /**
     * @return void
     */
    protected function d3DebugBarSetExceptionHandler(): void
    {
        if ($this->d3CanActivateDebugBar()) {
            set_exception_handler( [
                new DebugBarExceptionHandler(),
                'handleUncaughtException'
            ] );
        }
    }

    /**
     * @param $messsage
     * @return array|null
     */
    protected function getSmartyTemplateLocationFromError($messsage)
    {
        if (stristr($messsage, 'Smarty error: [in ')) {
            $start = strpos($messsage, '[')+1;
            $end = strpos($messsage, ']');
            $parts = explode(' ', substr($messsage, $start, $end - $start));
            return [Registry::getConfig()->getTemplateDir(isAdmin()).$parts[1], (int) $parts[3]];
        }

        return null;
    }

    /**
     * @return void
     */
    protected function d3AddDebugBarComponent(): void
    {
        if ($this->d3CanActivateDebugBar()) {
            $userComponentNames = Registry::getConfig()->getConfigParam( 'aUserComponentNames' );
            $d3CmpName          = DebugBarComponent::class;
            $blDontUseCache     = 1;

            if ( ! is_array( $userComponentNames ) ) {
                $userComponentNames = [];
            }

            if ( ! in_array( $d3CmpName, array_keys( $userComponentNames ) ) ) {
                $userComponentNames[ $d3CmpName ] = $blDontUseCache;
                Registry::getConfig()->setConfigParam( 'aUserComponentNames', $userComponentNames );
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

    public function __destruct()
    {
        global $debugBarSet;
        if (!isAdmin() && $debugBarSet !== 1) {
            $activeView =  Registry::getConfig()->getTopActiveView();
            /** @var DebugBarComponent|null $debugBarComponent */
            $debugBarComponent = $activeView->getComponent(DebugBarComponent::class);
            if ($debugBarComponent) {
                echo $debugBarComponent->getRenderer()->renderHead();
                $debugBarComponent->addTimelineMessures();
                echo $debugBarComponent->getRenderer()->render();
            }
        }
    }
}
