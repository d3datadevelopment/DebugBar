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

use D3\DebugBar\Application\Models\AvailabilityCheck;
use D3\DebugBar\Core\DebugBarExceptionHandler;
use OxidEsales\Eshop\Core\Exception\ExceptionHandler;

class Config_DebugBar extends Config_DebugBar_parent
{
    /**
     * @return DebugBarExceptionHandler|ExceptionHandler
     */
    protected function getExceptionHandler()
    {
        if (AvailabilityCheck::isAvailable()) {
            return new DebugBarExceptionHandler();
        }

        return parent::getExceptionHandler();
    }
}
