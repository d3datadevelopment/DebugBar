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
use OxidEsales\Eshop\Core\Registry;

class TemplateVariablesCollector extends DataCollector implements Renderable
{
    public function __construct(protected array $templateVariables)
    {
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        $data = ['current view template' => Registry::getConfig()->getTopActiveView()->getTemplateName()];

        $vars = $this->templateVariables;

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
        return 'template_variables';
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
            "template_variables" => [
                "icon" => "file-text",
                "widget" => $widget,
                "map" => "template_variables.vars",
                "default" => "{}",
            ],
            "template_variables:badge" => [
                "map" => "template_variables.count",
                "default" => 0,
            ],
        ];
    }
}
