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
use OxidEsales\Eshop\Core\Registry;

class ShopControl_DebugBar extends ShopControl_DebugBar_parent
{
    /**
     * @param null $controllerKey
     * @param null $function
     * @param null $parameters
     * @param null $viewsChain
     */
    public function start ($controllerKey = null, $function = null, $parameters = null, $viewsChain = null)
    {
        $this->_d3AddDebugBarComponent();

        parent::start( $controllerKey, $function, $parameters, $viewsChain);
    }

    /**
     * @return void
     */
    protected function _d3AddDebugBarComponent(): void
    {
        $userComponentNames = Registry::getConfig()->getConfigParam('aUserComponentNames');
        $d3CmpName = DebugBarComponent::class;
        $blDontUseCache = 1;

        if (!is_array($userComponentNames)) {
            $userComponentNames = [];
        }

        if (!in_array($d3CmpName, array_keys($userComponentNames))) {
            $userComponentNames[$d3CmpName] = $blDontUseCache;
            Registry::getConfig()->setConfigParam('aUserComponentNames', $userComponentNames);
        }
    }
}