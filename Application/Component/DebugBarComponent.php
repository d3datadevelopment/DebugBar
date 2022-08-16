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

use D3\DebugBar\Application\Models\Collectors\OxidConfigCollector;
use D3\DebugBar\Application\Models\Collectors\OxidShopCollector;
use D3\DebugBar\Application\Models\Collectors\OxidVersionCollector;
use D3\DebugBar\Application\Models\Collectors\SmartyCollector;
use D3\DebugBar\Application\Models\TimeDataCollectorHandler;
use DebugBar\Bridge\DoctrineCollector;
use DebugBar\Bridge\MonologCollector;
use DebugBar\Bridge\NamespacedTwigProfileCollector;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\Bridge\TwigProfileCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use DebugBar\JavascriptRenderer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\Twig\Loader\ContentTemplateLoader;
use ReflectionClass;
use ReflectionException;
use Twig\Environment;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

class DebugBarComponent extends BaseController
{
    /** @var DebugBar */
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
            $debugbar = new DebugBar();

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
        /** @var Connection $connection */
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
     * @return OxidShopCollector
     */
    public function getOxidShopCollector(): OxidShopCollector
    {
        return oxNew(OxidShopCollector::class);
    }

    /**
     * @return OxidConfigCollector
     */
    public function getOxidConfigCollector(): OxidConfigCollector
    {
        return oxNew(OxidConfigCollector::class, Registry::getConfig());
    }

    /**
     * @return OxidVersionCollector
     */
    public function getOxidVersionCollector(): OxidVersionCollector
    {
        return oxNew(OxidVersionCollector::class);
    }

    /**
     * @param object $object
     * @param string $propName
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function getNonPublicProperty(object $object, string $propName)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @param DebugBar $debugbar
     * @return void
     * @throws DatabaseConnectionException
     * @throws DebugBarException
     * @throws ReflectionException
     */
    public function addCollectors(DebugBar $debugbar): void
    {
        // add all default collectors except the useless ExceptionCollector
        $debugbar->addCollector(new PhpInfoCollector());
        $debugbar->addCollector(new MessagesCollector());
        $debugbar->addCollector(new RequestDataCollector());
        $debugbar->addCollector(new TimeDataCollector());
        $debugbar->addCollector(new MemoryCollector());
        $debugbar->addCollector(new ExceptionsCollector());

        /*
        $container = ContainerFactory::getInstance()->getContainer();
        /** @var ContentTemplateLoader $contentTemplateLoader */
        /*
        $contentTemplateLoader = $container->get(ContentTemplateLoader::class);

        $twigEnv = new Environment($contentTemplateLoader);
        $twigProfile = new Profile();
        $twigEnv->addExtension(new ProfilerExtension($twigProfile));
        */

        // add custom collectors
        $debugbar->addCollector($this->getOxidShopCollector());
        $debugbar->addCollector($this->getOxidConfigCollector());
        $debugbar->addCollector($this->getSmartyCollector());
        //$debugbar->addCollector(new NamespacedTwigProfileCollector($twigProfile, $contentTemplateLoader));
        $debugbar->addCollector($this->getMonologCollector());
        $debugbar->addCollector($this->getDoctrineCollector());
        $debugbar->addCollector($this->getOxidVersionCollector());
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
     * @return DebugBar
     */
    public function getDebugBar(): DebugBar
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
