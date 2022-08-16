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

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class AvailabilityCheck
{
    /**
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return !isAdmin() && (
            Registry::getConfig()->getShopConfVar('d3debugbar_showForAdminUsersOnly') != true ||
            self::userIsMallAdmin()
        );
    }

    /**
     * @return bool
     */
    public static function userIsMallAdmin(): bool
    {
        $user = Registry::getConfig()->getUser();
        return $user != null &&
            $user->isMallAdmin();
    }

    /**
     * @return bool
     */
    public static function ifDebugBarNotSet(): bool
    {
        global $debugBarSet;

        return $debugBarSet !== 1;
    }

    /**
     * @return void
     */
    public static function markDebugBarAsSet(): void
    {
        global $debugBarSet;

        $debugBarSet = 1;
    }

    /**
     * @return bool
     */
    public static function ifNoErrorOccured(): bool
    {
        global $debugBarErrorOccured;

        return $debugBarErrorOccured !== 1;
    }

    /**
     * @return void
     */
    public static function markErrorOccured(): void
    {
        global $debugBarErrorOccured;

        $debugBarErrorOccured = 1;
    }
}
