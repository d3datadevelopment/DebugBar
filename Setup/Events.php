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

use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use D3\ModCfg\Application\Model\Install\d3install;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;

class Events
{
    /**
     * @throws d3ShopCompatibilityAdapterException
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws StandardException
     * @throws SystemComponentException
     */
    public static function onActivate()
    {
        if (false === file_exists(
            rtrim(Registry::getConfig()->getConfigParam('sShopDir'), '/').'/out/debugbar/debugbar.jas'
        )) {
            Registry::getUtilsView()->addErrorToDisplay(
                'The asset files cannot be found. Have you forgotten an installation step described in <a href="https://git.d3data.de/D3Public/DebugBar/src/branch/main/README.en.md">README</a>? Then please run the installation again.'.
                nl2br(PHP_EOL.PHP_EOL).
                'Die Assetdateien können nicht gefunden werden. Hast Du einen Installationsschritt vergessen, der in <a href="https://git.d3data.de/D3Public/DebugBar/src/branch/main/README.md">README</a> beschrieben ist? Dann führe die Installation bitte noch einmal aus.'
            );
        }
    }

    public static function onDeactivate()
    {
    }
}