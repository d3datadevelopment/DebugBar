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

use D3\DebugBar\Application\Models\Exceptions\CompileErrorException;
use D3\DebugBar\Application\Models\Exceptions\CoreErrorException;
use D3\DebugBar\Application\Models\Exceptions\ParseException;
use D3\DebugBar\Application\Models\Exceptions\UserErrorException;
use ErrorException;
use OxidEsales\Eshop\Core\Registry;

class DebugBarErrorHandler
{
    /**
     * @param $severity
     * @param $message
     * @param $file
     * @param $line
     *
     * @return void|false
     * @throws CompileErrorException
     * @throws CoreErrorException
     * @throws ErrorException
     * @throws ParseException
     * @throws UserErrorException
     */
    public function callback( $severity, $message, $file, $line )
    {
        global $debugBarErrorOccured;
        $debugBarErrorOccured = 1;

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
                throw new ErrorException($message, 0, $severity, $file, $line);
            default:
                $this->handleUnregisteredErrorTypes($message, $severity, $file, $line);
        }
    }

    /**
     * @param $messsage
     * @return array|null
     */
    protected function getSmartyTemplateLocationFromError($messsage): ?array
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
     * @param string      $message
     * @param int|null    $severity
     * @param string|null $file
     * @param int|null    $line
     *
     * @throws ErrorException
     */
    protected function handleUnregisteredErrorTypes(string $message = '', int $severity = null, string $file = null, int $line = null)
    {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
}