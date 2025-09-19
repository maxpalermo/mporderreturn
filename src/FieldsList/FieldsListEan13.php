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

class FieldsListEan13 extends FieldsList
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
            'id_product' => [
                'title' => $this->l('Id Product'),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!id_product',
                'callback_object' => $this->controller,
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
                'search' => true,
                'filter_key' => 'b!name',
            ],
            'ean13' => [
                'title' => $this->l('Ean13'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ],
            'current_ean13' => [
                'title' => $this->l('Current EAN13'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'callback' => 'compareEan13',
            ],
            'json' => [
                'title' => $this->l('Attributes'),
                'type' => 'text',
                'float' >= true,
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'callback' => 'prettifyJson',
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
