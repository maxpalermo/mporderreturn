<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpMassImport\Helpers;

class HelperTree
{
    public function getTreeField(
        $title,
        $label,
        $name,
        $selected_categories = [],
        $disabled_categories = [],
        $root_category = null,
        $use_search = true,
        $use_checkbox = true,
        $set_data = null
    ) {
        if (!$root_category) {
            $root_category = (int) \Category::getRootCategory()->id;
        }

        if (!is_array($selected_categories)) {
            $selected_categories = [$selected_categories];
        }

        if (!is_array($disabled_categories)) {
            $selected_categories = [$disabled_categories];
        }

        $field = [
            'type' => 'categories',
            'label' => $label,
            'name' => $name,
            'tree' => [
                'id' => 'CategoryTree',
                'name' => 'helperTreeCategories',
                'title' => $title,
                'selected_categories' => $selected_categories,
                'disabled_categories' => $disabled_categories,
                'root_category' => $root_category,
                'use_search' => $use_search,
                'use_checkbox' => $use_checkbox,
                'set_data' => $set_data,
            ],
        ];

        return $field;
    }
}
