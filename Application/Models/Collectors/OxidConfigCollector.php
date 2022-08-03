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

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Eshop\Core\Registry;
use ReflectionClass;
use ReflectionException;

class OxidConfigCollector extends DataCollector implements Renderable
{
    /** @var Config */
    protected $config;

    /** @var array  */
    protected $configVars = [];

    /**
     * @var bool
     */
    protected $useHtmlVarDumper = false;

    public function __construct(Config $config)
    {
        $config->init();
        $this->config = $config;
        $this->configVars = array_merge(
            (array) $this->getNonPublicProperty($this->config, '_aConfigParams'),
            Registry::get(ConfigFile::class)->getVars()
        );

        $this->sanitizeCriticalProperties();
    }

    /**
     * @return void
     */
    protected function sanitizeCriticalProperties(): void
    {
        $generic = (array) preg_grep('/Password/', array_keys($this->configVars));
        $specific = ['sSerialNr', 'aSerials', 'dbPwd'];
        $search = array_merge($generic, $specific);

        array_walk($this->configVars, function ($item, $key) use ($search) {
            if (in_array($key, $search)) {
                $this->configVars[$key] = '[hidden]';
            }
        });
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
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return mixed
     */
    public function isHtmlVarDumperUsed()
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
            "Configuration" => [
                "icon" => "tags",
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
