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

namespace MpSoft\MpMassImport\FieldsList;

class FieldsListCombinations extends FieldsList
{
    public function getFieldsList()
    {
        return [
            'id_product_attribute' => [
                'title' => $this->l('Id'),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => false,
            ],
            'reference' => [
                'title' => $this->l('Reference'),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!reference',
            ],
            'product_name' => [
                'title' => $this->l('Product'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => false,
                'orderBy' => false,
                'callback' => 'getProductName',
                'callback_object' => $this->controller,
            ],
            'json' => [
                'title' => $this->l('Attributes'),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'align' => 'text-left',
                'search' => false,
                'orderBy' => false,
                'callback' => 'getAttributes',
                'callback_object' => $this->controller,
            ],
            'ean13' => [
                'title' => $this->l('Ean13'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ],
            'upc' => [
                'title' => $this->l('Upc'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ],
            'wholesale_price' => [
                'title' => $this->l('Wholesale price'),
                'type' => 'price',
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
            ],
            'price' => [
                'title' => $this->l('Price'),
                'type' => 'price',
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-right',
                'search' => true,
            ],
            'ecotax' => [
                'title' => $this->l('Ecotax'),
                'type' => 'text',
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-right',
                'search' => true,
            ],
            'quantity' => [
                'title' => $this->l('Quantity'),
                'type' => 'text',
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-right',
                'search' => true,
            ],
            'weight' => [
                'title' => $this->l('Weight'),
                'type' => 'text',
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-right',
                'search' => true,
            ],
            'unit_price_impact' => [
                'title' => $this->l('Unit price impact'),
                'type' => 'price',
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-right',
                'search' => true,
            ],
            'default_on' => [
                'title' => $this->l('Def on'),
                'type' => 'bool',
                'float' => true,
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-center',
                'search' => true,
                'callback' => 'displayDefaultOn',
                'callback_object' => $this->controller,
            ],
            'minimal_quantity' => [
                'title' => $this->l('Min'),
                'type' => 'text',
                'size' => 96,
                'class' => 'fixed-width-sm',
                'align' => 'text-right',
                'search' => true,
            ],
            'available_date' => [
                'title' => $this->l('Available date'),
                'type' => 'date',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
            ],
            'date_add' => [
                'title' => $this->l('Date add'),
                'type' => 'date',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
            ],
            'date_upd' => [
                'title' => $this->l('Date upd'),
                'type' => 'date',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
            ],
        ];
    }
}
