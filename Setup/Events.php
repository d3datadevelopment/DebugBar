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

namespace D3\DebugBar\Setup;

use OxidEsales\Eshop\Core\Registry;

class Events
{
    /**
     * @return void
     */
    public static function onActivate(): void
    {
        /** @var string $shopDir */
        $shopDir = Registry::getConfig()->getConfigParam('sShopDir');
        if (false === file_exists(
            rtrim($shopDir, '/').'/out/debugbar/debugbar.js'
        )) {
            Registry::getUtilsView()->addErrorToDisplay(
                'The asset files cannot be found. Have you forgotten an installation step described in <a href="https://git.d3data.de/D3Public/DebugBar/src/branch/main/README.en.md">README</a>? Then please run the installation again.'.
                nl2br(PHP_EOL.PHP_EOL).
                'Die Assetdateien können nicht gefunden werden. Hast Du einen Installationsschritt vergessen, der in <a href="https://git.d3data.de/D3Public/DebugBar/src/branch/main/README.md">README</a> beschrieben ist? Dann führe die Installation bitte noch einmal aus.'
            );
        }
    }

    /**
     * @return void
     */
    public static function onDeactivate(): void
    {
    }
}
