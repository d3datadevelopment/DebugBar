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

use D3\DebugBar\Modules\Core\Config_DebugBar;
use D3\DebugBar\Modules\Core\ShopControl_DebugBar;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ShopControl;

$sMetadataVersion = '2.1';

$sModuleId = 'd3debugbar';
$logo = '<img src="https://logos.oxidmodule.com/d3logo.svg" alt="(D3)" style="height:1em;width:1em">';

/**
 * Module information
 */
$aModule = [
    'id'          => $sModuleId,
    'title'       => $logo.' DebugBar',
    'description' => [
        'de' => '',
        'en' => '',
    ],
    'version'     => '1.0.0.0',
    'author'      => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'       => 'support@shopmodule.com',
    'url'         => 'https://www.oxidmodule.com/',
    'controllers' => [],
    'extend'      => [
        Config::class      => Config_DebugBar::class,
        ShopControl::class => ShopControl_DebugBar::class,
    ],
    'events'      => [
        'onActivate'    => '\D3\DebugBar\Setup\Events::onActivate',
    ],
    'templates'   => [],
    'settings'    => [],
    'blocks'      => [],
];
