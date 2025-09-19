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

class FieldsListUpdateProducts extends FieldsList
{
    public function getFieldsList()
    {
        return [
            'thumb' => [
                'title' => $this->module->l('Image', $this->controller_name),
                'type' => 'bool',
                'float' => true,
                'width' => 64,
                'align' => 'text-center',
                'search' => false,
                'callback' => 'getThumb',
                'callback_object' => $this->controller,
            ],
            'id_product' => [
                'title' => $this->module->l('Id', $this->controller_name),
                'type' => 'text',
                'width' => 64,
                'align' => 'text-right',
                'search' => false,
            ],
            'reference' => [
                'title' => $this->module->l('Reference', $this->controller_name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!reference',
            ],
            'name' => [
                'title' => $this->module->l('Name', $this->controller_name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!name',
            ],
            'ean13' => [
                'title' => $this->module->l('Ean13', $this->controller_name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!ean13',
            ],
            'price' => [
                'title' => $this->module->l('Price', $this->controller_name),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!price',
                'callback' => 'setPrice',
                'callback_object' => $this->controller,
            ],
            'json' => [
                'title' => $this->module->l('Fields', $this->controller_name),
                'type' => 'bool',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
                'callback' => 'getJson',
                'callback_object' => $this->controller,
            ],
        ];
    }
}
