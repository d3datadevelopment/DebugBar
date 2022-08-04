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
use OxidEsales\Eshop\Core\Registry;
use Smarty;

class SmartyCollector extends DataCollector implements Renderable
{
    /** @var Smarty */
    protected $smarty;

    /**
     * @var bool
     */
    protected $useHtmlVarDumper = false;

    /**
     * Sets a flag indicating whether the Symfony HtmlDumper will be used to dump variables for
     * rich variable rendering.
     *
     * @param bool $value
     * @return $this
     */
    public function useHtmlVarDumper($value = true)
    {
        $this->useHtmlVarDumper = $value;

        return $this;
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
     * @param Smarty $smarty
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        $data = ['current view template' => Registry::getConfig()->getTopActiveView()->getTemplateName()];

        $vars = $this->smarty->get_template_vars();

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
     * @return string
     */
    public function getName(): string
    {
        return 'smarty';
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
            "smarty" => [
                "icon" => "file-text",
                "widget" => $widget,
                "map" => "smarty.vars",
                "default" => "{}",
            ],
            "smarty:badge" => [
                "map" => "smarty.count",
                "default" => 0,
            ],
        ];
    }
}
