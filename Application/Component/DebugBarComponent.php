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

namespace D3\DebugBar\Application\Component;

use D3\DebugBar\Application\Models\Collectors\SmartyCollector;
use D3\DebugBar\Application\Models\TimeDataCollectorHandler;
use DebugBar\Bridge\DoctrineCollector;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DebugBarException;
use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;
use Doctrine\DBAL\Logging\DebugStack;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use ReflectionClass;
use ReflectionException;

class DebugBarComponent extends BaseController
{
    /** @var StandardDebugBar */
    protected $debugBar;
    /** @var JavascriptRenderer */
    protected $debugBarRenderer;

    /**
     * Marking object as component
     * @var bool
     */
    protected $_blIsComponent = true;

    /**
     * @throws DebugBarException
     * @throws DatabaseConnectionException
     * @throws ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        if (false === isAdmin()) {
            $debugbar = new StandardDebugBar();

            $this->addCollectors($debugbar);

            $debugbarRenderer = $debugbar->getJavascriptRenderer();
            $debugbarRenderer->setBaseUrl(Registry::getConfig()->getOutUrl() . 'debugbar');
            $this->debugBar = $debugbar;
            $this->debugBarRenderer = $debugbarRenderer;
        }
    }

    /**
     * @return MonologCollector
     */
    public function getMonologCollector(): MonologCollector
    {
        /** @var Logger $monolog */
        $monolog = Registry::getLogger();
        return new MonologCollector($monolog);
    }

    /**
     * @return DoctrineCollector
     * @throws ReflectionException
     * @throws DebugBarException
     * @throws DatabaseConnectionException
     */
    public function getDoctrineCollector(): DoctrineCollector
    {
        $db = DatabaseProvider::getDb();
        $debugStack = new DebugStack();
        $connection = $this->getNonPublicProperty($db, 'connection');
        $connection->getConfiguration()->setSQLLogger($debugStack);
        return new DoctrineCollector($debugStack);
    }

    /**
     * @return SmartyCollector
     */
    public function getSmartyCollector(): SmartyCollector
    {
        $smarty = Registry::getUtilsView()->getSmarty();
        return new SmartyCollector($smarty);
    }

    /**
     * @param  $object
     * @param $propName
     * @return mixed
     * @throws ReflectionException
     */
    protected function getNonPublicProperty($object, $propName)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @param StandardDebugBar $debugbar
     * @return void
     * @throws DatabaseConnectionException
     * @throws DebugBarException
     * @throws ReflectionException
     */
    public function addCollectors(StandardDebugBar $debugbar): void
    {
        $debugbar->addCollector($this->getMonologCollector());
        $debugbar->addCollector($this->getDoctrineCollector());
        $debugbar->addCollector($this->getSmartyCollector());
    }

    /**
     * @return void
     */
    public function addTimelineMessures(): void
    {
        $collectors = $this->debugBar->getCollectors();
        $collectors['time'] = TimeDataCollectorHandler::getInstance();

        $reflection = new ReflectionClass($this->debugBar);
        $property = $reflection->getProperty('collectors');
        $property->setAccessible(true);
        $property->setValue($this->debugBar, $collectors);
    }

    /**
     * @return StandardDebugBar
     */
    public function getDebugBar(): StandardDebugBar
    {
        return $this->debugBar;
    }

    /**
     * @return JavascriptRenderer
     */
    public function getRenderer(): JavascriptRenderer
    {
        return $this->debugBarRenderer;
    }
}
