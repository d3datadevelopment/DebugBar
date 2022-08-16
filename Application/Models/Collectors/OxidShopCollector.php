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

namespace D3\DebugBar\Application\Models\Collectors;

use Composer\InstalledVersions;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Exception;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Eshop\Core\Theme;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridge;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\Facts\Facts;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class OxidShopCollector extends DataCollector implements Renderable
{
    /** @var Config */
    protected $config;

    /** @var array  */
    protected $configVars = [];

    /**
     * @var bool
     */
    protected $useHtmlVarDumper = true;

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
        array_walk(
            $moduleList,
            function (Module &$module) {
                $str = trim(strip_tags($module->getTitle())).' - '.$module->getInfo('version').' ';
                $module = $str;
            }
        );

        $this->configVars = [
            'Shop Edition:' => $facts->getEdition(),
            'Shop Version:' => ShopVersion::getVersion(),
            'CE Version:' => InstalledVersions::getVersion('oxid-esales/oxideshop-ce'),
            'Theme:' => $theme->getActiveThemeId(),
            'Parent Theme:' => $parentThemeId,
            'Modules:'  => implode(chr(10), $moduleList),
        ];
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
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return bool
     */
    public function isHtmlVarDumperUsed(): bool
    {
        return $this->useHtmlVarDumper;
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
     * @return array
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
            $module = oxNew(Module::class);
            $module->load($moduleConfiguration->getId());
            $modules[] = $module;
        }

        usort($modules, function ($a, $b) {
            return strcmp($a->getTitle(), $b->getTitle());
        });

        return $modules;
    }
}
