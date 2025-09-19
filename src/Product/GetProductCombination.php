<?php
/*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Massimiliano Palermo <maxx.palermo@gmail.com>
*  @copyright Since 2016 Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace MpSoft\MpMassImport\Product;

use MpSoft\MpMassImport\Helpers\MpTools;

class GetProductCombination
{
    public function prepareCombinations($rows)
    {
        $current_pa = 0;
        $comb = [];
        $output = [];
        foreach ($rows as $row) {
            if ($current_pa == 0) {
                $current_pa = $row['id_product_attribute'];
                $comb = [
                    'id_product_attribute' => $row['id_product_attribute'],
                    'id_product' => $row['id_product'],
                    'reference' => $row['reference'],
                    'ean13' => $row['ean13'],
                    'wholesale_price' => $row['wholesale_price'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'unit_price_impact' => $row['unit_price_impact'],
                    'default_on' => $row['default_on'],
                    'attributes' => [
                        [
                            'attribute_group' => $row['attribute_group'],
                            'attribute_value' => $row['attribute_value'],
                        ],
                    ],
                    'images' => GetProductImage::getProductAttributeImages($row['id_product_attribute']),
                ];

                continue;
            }
            if ($current_pa != $row['id_product_attribute']) {
                $output[] = $comb;
                $current_pa = $row['id_product_attribute'];
                unset($comb);
                $comb = [
                    'id_product_attribute' => $row['id_product_attribute'],
                    'id_product' => $row['id_product'],
                    'reference' => $row['reference'],
                    'ean13' => $row['ean13'],
                    'wholesale_price' => $row['wholesale_price'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'unit_price_impact' => $row['unit_price_impact'],
                    'default_on' => $row['default_on'],
                    'attributes' => [
                        [
                            'attribute_group' => $row['attribute_group'],
                            'attribute_value' => $row['attribute_value'],
                        ],
                    ],
                    'images' => GetProductImage::getProductAttributeImages($row['id_product_attribute']),
                ];

                continue;
            }
            $comb['attributes'][] = [
                'attribute_group' => $row['attribute_group'],
                'attribute_value' => $row['attribute_value'],
            ];
        }
        $output[] = $comb;
        unset($row, $comb);

        $attributes = [];
        foreach ($output as $row) {
            foreach ($row['attributes'] as $attr) {
                $id_pa = (int) $row['id_product_attribute'];
                $ag = $attr['attribute_group'];
                $av = $attr['attribute_value'];
                if (!isset($attributes[$ag])) {
                    $attributes[$ag] = [];
                    $attributes[$ag][$id_pa][] = $av;
                } else {
                    $attributes[$ag][$id_pa][] = $av;
                }
            }
        }
        foreach ($output as &$row) {
            foreach ($attributes as $key => $value) {
                $row['attr:' . $key] = MpTools::setExcelValue($value, $row['id_product_attribute']);
            }
            unset($row['attributes']);
        }

        return $output;
    }
}
