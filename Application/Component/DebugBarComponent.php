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
use DebugBar\Bridge\DoctrineCollector;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBarException;
use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;
use Doctrine\DBAL\Logging\DebugStack;
use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\StandardException;
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
     * @throws ReflectionException
     */
    public function getMonologCollector(): MonologCollector
    {
        $loggerWrapper = Registry::getLogger();
        $monolog = $this->getNonPublicProperty($loggerWrapper, 'logger');
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
        return new SmartyCollector(Registry::getUtilsView()->getSmarty());
    }

    /**
     * @return string|null
     */
    public function render()
    {
        $this->getParent()->addTplParam('debugBarRenderer', $this->debugBarRenderer);
        $this->getParent()->addTplParam('debugBarComponent', $this);
        return parent::render();
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

    public function addTimelineMessures()
    {
        /** @var TimeDataCollector $tCollector */
        $tCollector = $this->debugBar['time'];

        global $aStartTimes;
        global $aProfileTimes;
        global $executionCounts;
        foreach ($aProfileTimes as $label => $recordedTime) {
            for ($i = 0; $i < $executionCounts[$label]; $i++) {
                $tCollector->addMeasure(
                    $label,
                    $aStartTimes[$label],
                    $aStartTimes[$label] + $aProfileTimes[$label] / $executionCounts[$label]
                );
            }
        }
    }
}