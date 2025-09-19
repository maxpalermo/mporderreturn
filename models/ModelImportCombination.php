<?php
/**
* 2007-2018 PrestaShop
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
*  @copyright 2021 Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ModelImportCombination extends ObjectModel
{
    public $id_product;
    public $reference;
    public $supplier_reference;
    public $product_name;
    public $ean13;
    public $upc;
    public $wholesale_price;
    public $price;
    public $ecotax;
    public $quantity;
    public $weight;
    public $unit_price_impact;
    public $default_on;
    public $minimal_quantity;
    public $available_date;
    public $json;
    public $date_add;
    public $date_upd;

    public $context;
    public $id_lang;
    public $id_shop;
    public $controller;
    public $combinations;

    public static $definition = [
        'table' => 'mp_massimport_combination',
        'primary' => 'id_product_attribute',
        'multilang' => false,
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => true,
            ],
            'supplier_reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => false,
            ],
            'product_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => false,
            ],
            'ean13' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isEan13',
                'size' => 13,
                'required' => false,
            ],
            'upc' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isEan13',
                'size' => 12,
                'required' => false,
            ],
            'wholesale_price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'ecotax' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'quantity' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ],
            'weight' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'unit_price_impact' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'default_on' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'minimal_quantity' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'available_date' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'json' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
                'datetime' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
                'datetime' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->context = Context::getContext();
        $this->controller = $this->context->controller;
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->module = Module::getInstanceByName('mpmassimport');

        parent::__construct($id);

        if ($this->id) {
            $this->json = json_decode($this->json, true);
        }
    }

    public static function truncate()
    {
        return ModelBase::truncate(self::$definition);
    }

    public static function install()
    {
        return ModelBase::install(self::$definition);
    }

    public static function uninstall()
    {
        return ModelBase::uninstall(self::$definition);
    }

    public static function sanitizeFields(&$rows)
    {
        return ModelBase::sanitizeFields($rows, self::$definition);
    }

    public static function sanitizeField(&$row)
    {
        return ModelBase::sanitizeField($row, self::$definition);
    }
}
