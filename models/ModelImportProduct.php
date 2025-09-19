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

use MpSoft\MpMassImport\Helpers\Combinations;

class ModelImportProduct extends ObjectModel
{
    public $reference;
    public $supplier_reference;
    public $ean13;
    public $product_name;
    public $condition;
    public $wholesale_price;
    public $is_virtual;
    public $description;
    public $description_short;
    public $link_rewrite;
    public $price;
    public $id_manufacturer;
    public $id_supplier;
    public $id_tax_rules_group;
    public $available_date;
    public $prefix;
    public $id_category_default;
    public $json;
    public $quantity;
    public $date_add;
    public $date_upd;

    protected $context;
    protected $id_lang;
    protected $id_shop;
    protected $controller;
    protected $combinations;
    protected $module;

    public static $definition = [
        'table' => 'mp_massimport_product',
        'primary' => 'id_product',
        'multilang' => false,
        'fields' => [
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => false,
            ],
            'supplier_reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => false,
            ],
            'ean13' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isEan13',
                'size' => 13,
                'required' => false,
            ],
            'product_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => true,
            ],
            'condition' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 32,
                'required' => false,
            ],
            'wholesale_price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'is_virtual' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'description' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'description_short' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'link_rewrite' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => false,
            ],
            'price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'available_date' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'prefix' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
            ],
            'quantity' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
            ],
            'json' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
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
            $attributes = $this->json['attributes'];

            /**
             * Create combinations
             */
            $combinations = new Combinations();
            $list = $combinations->cleanAttributes($attributes);
            $this->combinations = $combinations->createCombinationList($list);
        }
    }

    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . self::$definition['table']);
    }
}
