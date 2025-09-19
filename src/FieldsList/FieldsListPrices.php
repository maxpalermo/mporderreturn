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

class FieldsListPrices extends FieldsList
{
    public function getFieldsList()
    {
        return [
            'id_product' => [
                'title' => $this->l('Id'),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => false,
            ],
            'thumb' => [
                'title' => $this->l('Image'),
                'type' => 'bool',
                'float' => true,
                'size' => 64,
                'align' => 'text-center',
                'search' => false,
                'callback' => 'getThumb',
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
            'manufacturer' => [
                'title' => $this->l('Manufacturer'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!manufacturer',
                'callback' => 'getManufacturer',
                'callback_object' => $this->controller,
            ],
            'name' => [
                'title' => $this->l('Name'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!name',
            ],
            'old_wholesale_price' => [
                'title' => $this->l('Acquisto corrente'),
                'type' => 'price',
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!wholesale_price',
                'callback' => 'addVat',
                'callback_object' => $this->controller,
            ],
            'wholesale_price' => [
                'title' => $this->l('Acquisto nuovo'),
                'type' => 'price',
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!wholesale_price',
                'callback' => 'addVatCompareWholesalePrice',
                'callback_object' => $this->controller,
            ],
            'old_price' => [
                'title' => $this->l('Prezzo corrente'),
                'type' => 'price',
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!price',
                'callback' => 'addVat',
                'callback_object' => $this->controller,
            ],
            'price' => [
                'title' => $this->l('Prezzo nuovo'),
                'type' => 'price',
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!price',
                'callback' => 'addVatComparePrice',
                'callback_object' => $this->controller,
            ],
            'available_date' => [
                'title' => $this->l('Available Date'),
                'type' => 'date',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!available_date',
            ],
        ];
    }
}
