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

namespace D3\DebugBar\Application\Core;

use InvalidArgumentException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Stringable;

class LoggerCascade implements LoggerInterface
{
    public const OXID_LOGGER = 'oxidLogger';
    public const DEBUGBAR_LOGGER = 'debugBarLogger';

    /**
     * List of all loggers in the registry (by named indexes)
     *
     * @var Logger[]
     */
    private $loggers = array();

    /**
     * Adds new logging channel to the registry
     *
     * @param  LoggerInterface           $logger    Instance of the logging channel
     * @param  string                    $name      Name of the logging channel ($logger->getName() by default)
     * @param  bool                      $overwrite Overwrite instance in the registry if the given name already exists?
     * @throws InvalidArgumentException If $overwrite set to false and named Logger instance already exists
     * @return void
     */
    public function addLogger(LoggerInterface $logger, $name, $overwrite = false): void
    {
        if (isset($this->loggers[$name]) && !$overwrite) {
            throw new InvalidArgumentException('Logger with the given name already exists');
        }

        $this->loggers[$name] = $logger;
    }

    /**
     * Checks if such logging channel exists by name or instance
     *
     * @param string|Logger $logger Name or logger instance
     * @return bool
     */
    public function hasLogger($logger): bool
    {
        if ($logger instanceof LoggerInterface) {
            $index = array_search($logger, $this->loggers, true);

            return false !== $index;
        } else {
            return isset($this->loggers[$logger]);
        }
    }

    /**
     * Removes instance from registry by name or instance
     *
     * @param string|Logger $logger Name or logger instance
     * @return void
     */
    public function removeLogger($logger): void
    {
        if ($logger instanceof LoggerInterface) {
            if (false !== ($idx = array_search($logger, $this->loggers, true))) {
                unset($this->loggers[$idx]);
            }
        } else {
            unset($this->loggers[$logger]);
        }
    }

    /**
     * Clears the registry
     * @return void
     */
    public function clear(): void
    {
        $this->loggers = array();
    }

    /**
     *
     * @return Logger[]
     */
    public function getLoggers(): array
    {
        return $this->loggers;
    }

    /**
     * @param $logger
     *
     * @return LoggerInterface
     * @throws LoggerNotSetException
     */
    public function getLogger($logger): LoggerInterface
    {
        if ($this->hasLogger($logger)) {
            if ($logger instanceof LoggerInterface) {
                $index = array_search($logger, $this->loggers, true);

                return $this->getLoggers()[$index];
            } else {
                return $this->getLoggers()[$logger];
            }
        }

        throw oxNew(LoggerNotSetException::class);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function emergency($message, array $context = [] ): bool
    {
        return $this->call('emergency', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function alert($message, array $context = [] ): bool
    {
        return $this->call('alert', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function critical($message, array $context = [] ): bool
    {
        return $this->call('critical', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function error($message, array $context = [] ): bool
    {
        return $this->call('error', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function warning($message, array $context = [] ): bool
    {
        return $this->call('warning', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function notice($message, array $context = [] ): bool
    {
        return $this->call('notice', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function info($message, array $context = [] ): bool
    {
        return $this->call('info', $message, $context);
    }

    /**
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function debug($message, array $context = [] ): bool
    {
        return $this->call('debug', $message, $context);
    }

    /**
     * @param mixed             $level
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return bool
     */
    public function log( $level, $message, array $context = [] ): bool
    {
        return $this->call('log', $level, $message, $context);
    }

    /**
     * @param $method
     * @param ...$arguments
     *
     * @return bool
     */
    protected function call($method, ...$arguments): bool
    {
        $return = [];

        foreach ($this->getLoggers() as $logger) {
            $return[] = call_user_func_array([$logger, $method], $arguments);
        }

        return false === in_array(false, $return);
    }
}