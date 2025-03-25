<?php

/**
 * Copyright (c) D3 Data Development (Inh. Thomas Dartsch)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\DebugBar\Application\Models\Collectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridge;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;

class OxidConfigCollector extends DataCollector implements Renderable
{
    public const HIDDEN_TEXT = '[hidden]';

    protected array $configVars = [];

    /**
     * @param Config $config
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(Config $config)
    {
        $config->init();

        $this->configVars['config table'] = (array) $this->getNonPublicProperty($config, '_aConfigParams');
        $this->configVars['config file'] = Registry::get(ConfigFile::class)->getVars();
        $this->configVars = array_merge($this->configVars, $this->getModuleSettings());

        $this->sanitizeCriticalProperties();

        $this->useHtmlVarDumper(false);
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getModuleSettings(): array
    {
        $container = ContainerFactory::getInstance()->getContainer();
        /** @var ShopConfigurationDaoBridge $shopConfigurationDaoBridge */
        $shopConfigurationDaoBridge = $container->get(ShopConfigurationDaoBridgeInterface::class);
        $shopConfiguration = $shopConfigurationDaoBridge->get();

        $moduleSettings = [];

        foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
            foreach ($moduleConfiguration->getModuleSettings() as $setting) {
                $moduleSettings[$moduleConfiguration->getId()][$setting->getName()] = $setting->getType() == 'hidden' ?
                    self::HIDDEN_TEXT :
                    $setting->getValue();
            }
        }

        return $moduleSettings;
    }

    /**
     * @return void
     */
    protected function sanitizeCriticalProperties(): void
    {
        $generic = (array) preg_grep('/Password/', array_keys($this->configVars));
        $specific = ['sSerialNr', 'aSerials', 'dbPwd'];
        $search = array_merge($generic, $specific);

        foreach ($this->configVars as $group => $values) {
            array_walk($this->configVars[$group], function ($item, $key) use ($group, $search) {
                if (in_array($key, $search)) {
                    $this->configVars[$group][ $key ] = self::HIDDEN_TEXT;
                }
            });
        }
    }

    /**
     * @param object $object
     * @param string $propName
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function getNonPublicProperty(object $object, string $propName): mixed
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'oxidconfig';
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        $data = [];

        $vars = $this->configVars;

        foreach ($vars as $idx => $var) {
            if ($this->isHtmlVarDumperUsed()) {
                $data[$idx] = $this->getVarDumper()->renderVar($var);
            } else {
                $data[$idx] = $this->getDataFormatter()->formatVar($var);
            }
        }

        return ['vars' => $data, 'count' => count($data)];
    }

    /**
     * @return array
     */
    public function getWidgets(): array
    {
        $widget = $this->isHtmlVarDumperUsed()
            ? "PhpDebugBar.Widgets.HtmlVariableListWidget"
            : "PhpDebugBar.Widgets.VariableListWidget";
        return [
            "Configuration" => [
                "icon" => "database",
                "widget" => $widget,
                "map" => "oxidconfig.vars",
                "default" => "{}",
            ],
            "Configuration:badge" => [
                "map" => "oxidconfig.count",
                "default" => 0,
            ],
        ];
    }
}
