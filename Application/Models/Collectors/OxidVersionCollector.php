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
use Exception;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;

/**
 * Collects info about OXID shop
 */
class OxidVersionCollector extends DataCollector implements Renderable
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'oxidversion';
    }

    /**
     * @return array
     * @throws Exception
     */
    public function collect(): array
    {
        $facts = new Facts();

        return [
            'version' => $facts->getEdition().' '.ShopVersion::getVersion(),
        ];
    }

    /**
     * @return string[][]
     */
    public function getWidgets(): array
    {
        return [
            "oxidversion" => [
                "icon" => "shopping-cart",
                "tooltip" => 'OXID Version',
                "map" => $this->getName().".version",
                "default" => "",
            ],
        ];
    }
}
