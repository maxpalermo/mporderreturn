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

class ModelImportPrice_old extends ObjectModel
{
    public $reference;
    public $name;
    public $wholesale_price;
    public $price;
    public $json;
    public $date_add;
    public $date_upd;

    protected $context;
    protected $id_lang;
    protected $id_shop;
    protected $controller;
    protected $module;

    public static $definition = [
        'table' => 'mp_massimport_price',
        'primary' => 'id_product',
        'multilang' => false,
        'fields' => [
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => false,
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => true,
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
        }
    }

    public static function truncate()
    {
        return ModelBase::truncate(self::$definition);
    }
}
