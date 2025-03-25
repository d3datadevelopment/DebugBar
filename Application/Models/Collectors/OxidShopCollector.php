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

use Composer\InstalledVersions;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Exception;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Eshop\Core\Theme;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridge;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\Facts\Facts;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class OxidShopCollector extends DataCollector implements Renderable
{
    protected Config $config;

    protected array $configVars = [];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __construct()
    {
        $facts = new Facts();
        $theme = new Theme();
        /** @var Theme|null $parent */
        $parent = $theme->getParent();
        $parentThemeId = $parent ? $parent->getId() : '--';

        $moduleList = $this->getInstalledModules();
        $list = [];
        array_walk(
            $moduleList,
            function (ModuleConfiguration $module) use (&$list) {
                $list[] = trim(strip_tags(current($module->getTitle()))).' - '.$module->getVersion();
            }
        );

        $this->configVars = [
            'Shop Edition:' => $facts->getEdition(),
            'Shop Version:' => ShopVersion::getVersion(),
            'CE Version:' => InstalledVersions::getVersion('oxid-esales/oxideshop-ce'),
            'Theme:' => $theme->getActiveThemeId(),
            'Parent Theme:' => $parentThemeId,
            'Modules:'  => $list,
        ];

        $this->useHtmlVarDumper(false);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'oxidshop';
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
            "Shop" => [
                "icon" => "shopping-cart",
                "widget" => $widget,
                "map" => $this->getName().".vars",
                "default" => "{}",
            ],
        ];
    }

    /**
     * @return ModuleConfiguration[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getInstalledModules(): array
    {
        $container = ContainerFactory::getInstance()->getContainer();
        /** @var ShopConfigurationDaoBridge $shopConfigurationDaoBridge */
        $shopConfigurationDaoBridge = $container->get(ShopConfigurationDaoBridgeInterface::class);
        $shopConfiguration = $shopConfigurationDaoBridge->get();

        $modules = [];

        foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
            $modules[] = $moduleConfiguration;
        }

        /** @var $a ModuleConfiguration */
        /** @var $b ModuleConfiguration */
        usort($modules, function ($a, $b) {
            return strcmp(current($a->getTitle()), current($b->getTitle()));
        });

        return $modules;
    }
}
