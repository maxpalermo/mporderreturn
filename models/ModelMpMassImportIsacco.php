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
use MpSoft\MpMassImport\Helpers\ImageProduct;
use MpSoft\MpMassiveDescription\Helpers\GetControllerName;

class ModelMpMassImportIsacco extends ObjectModel
{
    public $reference;
    public $name;
    public $price;
    public $wholesale_price;
    public $img;
    public $date_add;
    public $date_upd;

    /************************
     * !Protected variables
     ************************/
    /** @var Context */
    protected $context;
    /** @var int */
    protected $id_lang;
    /** @var int */
    protected $id_shop;
    /** @var ModuleAdminController */
    protected $controller;
    /** @var string */
    protected $ctlName;
    /** @var array */
    protected $combinations;
    /** @var Module */
    protected $module;
    /** @var int */
    protected $id_supplier;
    /** @var int */
    protected $id_manufacturer;
    /** @var int */
    protected $id_tax_rules_group;

    public static $definition = [
        'table' => 'mp_massimport_isacco',
        'primary' => 'id_product',
        'multilang' => false,
        'fields' => [
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 64,
                'required' => false,
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isCatalogName',
                'size' => 255,
                'required' => false,
            ],
            'price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'wholesale_price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ],
            'img' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
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
        $this->controller = Context::getContext()->controller;
        $this->ctlName = (new GetControllerName($this->controller))->get();
        $this->id_supplier = self::getIdSupplier('ISACCO');
        $this->id_manufacturer = self::getIdManufacturer('ISACCO');
        $this->id_tax_rules_group = self::getTaxRuleGroup('IT Standard Rate (22%)');

        parent::__construct($id);
    }

    /**
     * Get all ids in table
     *
     * @return array
     */
    public static function getAllIds()
    {
        $table = self::$definition['table'];
        $primary = self::$definition['primary'];
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select($primary)
            ->from($table)
            ->orderBy($primary);
        $rows = $db->executeS($sql);
        $out = [];
        if ($rows) {
            foreach ($rows as $row) {
                $out[] = (int) $row[$primary];
            }
        }

        return $out;
    }

    public function importDatabase($items, $only_new = false)
    {
        $updated = 0;

        foreach ($items as $item) {
            $image = '';
            $reference = '';
            $name = $item['name'];

            foreach ($item['custom_attributes'] as $attribute) {
                $key = $attribute['attribute_code'];
                $value = $attribute['value'];
                switch($key) {
                    case 'image':
                        $image = $value;

                        break;
                    case  'isacco_catalog_code':
                        $reference = $value;

                        break;
                }
            }

            if ($reference) {
                $id_product = (int) self::existsImport($reference);

                if (!$id_product) {
                    continue;
                }

                $product = new ModelMpMassImportIsacco($id_product);
                $wholesale_price = self::twoDec(self::noVat($item['price']) / 2);
                $price_vat = self::twoDec(self::roundUp($wholesale_price * 2, 7, 0.5));
                $price = self::noVat($price_vat);

                if (!$product->name) {
                    $product->name = $name;
                }
                if (!$product->reference) {
                    $product->reference = $reference;
                }
                if (!$product->img) {
                    $product->img = $image;
                }
                if (!$product->wholesale_price) {
                    $product->wholesale_price = self::toPrice($wholesale_price);
                }
                if (!$product->price) {
                    $product->price = self::toPrice($price);
                }
            } else {
                continue;
            }

            try {
                if ($product->id) {
                    $result = $product->update();
                } else {
                    $result = $product->add();
                }
            } catch (\Throwable $th) {
                $this->controller->errors[] = $th->getMessage();
            }

            if ($result === true) {
                $updated++;
            }
        }

        return $updated;
    }

    public static function hasRecords()
    {
        return (bool) Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . self::$definition['table']);
    }

    public static function truncate()
    {
        Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . self::$definition['table']);
    }

    public function importExcel($rows, $new)
    {
        /**
         * !Structure:
         * - REFERENCE
         * - NEW
         * - NAME
         * - WHOLESALE_PRICE
         * - PRICE
         */
        self::truncate();

        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = $controller->module;
        /** @var int */
        $founds = 0;

        foreach ($rows as $row) {
            if ($new && $row['NEW'] == 'NEW') {
                $product = new ModelMpMassImportIsacco();
                $product->reference = $row['REFERENCE'];
                $product->name = $row['NAME'];
                $product->wholesale_price = $row['WHOLESALE_PRICE'];
                $product->price = $row['PRICE'];

                try {
                    $add = (int) $product->add();
                    $message = sprintf(
                        $module->l('Prodotto %s (%s). Prezzi: Acq %s, Ven %s.', $this->ctlName),
                        $product->name,
                        $product->reference,
                        Tools::displayPrice($product->wholesale_price),
                        Tools::displayPrice($product->price)
                    );
                    if ($add) {
                        $founds++;
                        $controller->confirmations[] = $message;
                    } else {
                        $error = Db::getInstance()->getMsgError();
                        $controller->warnings[] = $message . '-' . $error;
                    }
                } catch (\Throwable $th) {
                    $controller->errors[] = sprintf(
                        $module->l('Prodotto %s (%s). Prezzi: Acq %s, Ven %s - %s', $this->ctlName),
                        $product->name,
                        $product->reference,
                        Tools::displayPrice($product->wholesale_price),
                        Tools::displayPrice($product->price),
                        $th->getMessage()
                    );
                }
            }
        }

        return $founds;
    }

    public function importExcelEan13($rows)
    {
        /**
         * !Structure:
         * - CODICE
         * - DESCRIZIONE
         * - EAN13
         * - ... ELENCO ATTRIBUTI
         */

        $products = [];
        $errors = [];
        $founds = 0;
        $combinations = [];
        $id_size_group = self::getIdAttributeGroup('taglia');
        $attributes = [
            'taglia' => [
                'id' => $id_size_group,
            ],
        ];
        foreach ($rows as $row) {
            $references = [];
            $regex = preg_match('/(.*) \((.*) - \)/i', $row['codice'], $references);
            if (!$regex) {
                continue;
            }
            $reference = trim($references[1]);
            if (!self::existsImport($reference)) {
                continue;
            }
            if (!self::existsProduct($reference)) {
                continue;
            }
            $id_product = self::getIdProductFromReference($reference);
            if (!$id_product) {
                continue;
            }

            $current_ean13 = trim($row['ean13']);

            $size = trim($references[2]);
            if (!isset($attributes['taglia'][$size])) {
                $id_size = self::getIdAttribute($id_size_group, $size);
                if (!$id_size) {
                    continue;
                }

                $attributes['taglia'][$size] = $id_size;
            } else {
                $id_size = (int) $attributes['taglia'][$size];
            }

            if (!isset($combinations[$id_product])) {
                $combinations[$id_product]['combinations'] = [];
                $combinations[$id_product]['combinations'][$id_size_group] = [$id_size];
                $combinations[$id_product]['ean13'] = [$id_size => $current_ean13];
            } else {
                $combinations[$id_product]['combinations'][$id_size_group][] = $id_size;
                $combinations[$id_product]['ean13'][$id_size] = $current_ean13;
            }

            foreach ($row as $cell => $column) {
                switch ($cell) {
                    case 'codice':
                    case 'descrizione':
                    case 'ean13':
                        break;
                    default:
                        $attribute_group = trim($cell);
                        $attribute = trim($column);
                        /**
                         * !ATTRIBUTE GROUP
                         */
                        if (!isset($attributes[$attribute_group])) {
                            $id_attribute_group = self::getIdAttributeGroup($attribute_group);
                            if (!$id_attribute_group) {
                                continue;
                            }
                            $attributes[$attribute_group] = [
                                'id' => $id_attribute_group,
                            ];
                        } else {
                            $id_attribute_group = (int) $attributes[$attribute_group]['id'];
                        }
                        /**
                         * !ATTRIBUTE
                         */
                        if (!isset($attributes[$attribute_group][$attribute])) {
                            $id_attribute = self::getIdAttribute($id_attribute_group, $attribute);
                            if (!$id_attribute) {
                                continue;
                            }
                            $attributes[$attribute_group][$attribute] = $id_attribute;
                        } else {
                            $id_attribute = (int) $attributes[$attribute_group][$attribute];
                        }

                        if (isset($combinations[$id_product][$id_attribute_group])) {
                            $combinations[$id_product]['combinations'][$id_attribute_group][] = $id_attribute;
                        } else {
                            $combinations[$id_product]['combinations'][$id_attribute_group] = [$id_attribute];
                        }
                }
            }
        }

        $helperCombination = new Combinations();
        foreach ($combinations as $id_product => $comb) {
            /** @var ModuleAdminController */
            $controller = Context::getContext()->controller;
            /** @var Module */
            $module = $controller->module;
            /** @var Product */
            $product = new Product($id_product);

            if (!$product->id) {
                continue;
            }

            $combination = $comb['combinations'];
            $ean13 = $comb['ean13'];
            $out = [];
            foreach ($combination as &$groups) {
                $groups = array_values(array_unique($groups));
                $out[] = $groups;
            }

            $current_combinations = $helperCombination->createCombinationList($out);
            foreach ($current_combinations as $combination_list) {
                if (!self::insertIfExists($id_product, $combination_list, $ean13[$combination_list[0]])) {
                    $controller->errors[] = sprintf(
                        $module->l('Combinazione %s per il prodotto %d non trovata. Ean13 %s ignorato'),
                        self::getAttributesLabel($combination_list, $attributes),
                        $id_product,
                        $ean13[$combination_list[0]]
                    );
                } else {
                    $controller->confirmations[] = sprintf(
                        $module->l('Ean13 %s inserito'),
                        $ean13[$combination_list[0]]
                    );
                }
            }
            //$helperCombination->addCombinations($id_product, $current_combinations, $ean13);
        }

        return [
            'founds' => $founds,
            'errors' => $errors,
            'products' => $products,
            'combinations' => $combinations,
        ];
    }

    protected function getAttributesLabel($combination_list, $attributes)
    {
        $labels = [];
        foreach ($combination_list as $id_attribute) {
            foreach ($attributes as $group => $list) {
                foreach ($list as $key => $attribute) {
                    if ($attribute == $id_attribute && $key != 'id') {
                        $labels[] = $group . ': ' . $key;
                    }
                }
            }
        }

        return implode(',', $labels);
    }

    protected static function insertIfExists($id_product, $combination, $ean13)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('a.id_product_attribute')
            ->from('product_attribute_combination', 'pac')
            ->innerJoin('product_attribute', 'a', 'a.id_product=' . (int) $id_product)
            ->where('pac.id_attribute in (' . implode(',', $combination) . ')');
        $id_product_attribute = (int) $db->getValue($sql);

        if (!$id_product_attribute) {
            return false;
        }

        return $db->update(
            'product_attribute',
            [
                'ean13' => pSQL($ean13),
            ],
            'id_product_attribute=' . (int) $id_product_attribute
        );
    }

    protected static function getIdProductFromReference($reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product')
            ->where('supplier_reference=\'' . pSQL($reference) . '\'');

        return (int) $db->getValue($sql);
    }

    protected static function getIdAttributeGroup($group)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_attribute_group')
            ->from('attribute_group_lang')
            ->where('name = \'' . pSQL($group) . '\' and id_lang=' . (int) $id_lang);
        $id_group = (int) $db->getValue($sql);
        if (!$id_group) {
            return 0;
        }

        return $id_group;
    }

    protected static function getIdAttribute($id_group, $attribute)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('a.id_attribute')
            ->from('attribute', 'a')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute and al.id_lang=' . (int) $id_lang)
            ->where('al.name = \'' . pSQL($attribute) . '\'')
            ->where('a.id_attribute_group = ' . (int) $id_group);

        return (int) $db->getValue($sql);
    }

    public static function existsProduct($reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product')
            ->where('reference = \'ISA' . pSQL($reference) . '\'');

        return (int) $db->getValue($sql);
    }

    protected static function existsImport($reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from(self::$definition['table'])
            ->where('reference = \'' . pSQL($reference) . '\'');

        return (int) $db->getValue($sql);
    }

    protected static function noVat($value)
    {
        return $value / 1.22;
    }
    protected function addVat($value)
    {
        return round($value * (100 + 22) / 100, 2);
    }

    protected static function twoDec($value)
    {
        return floor($value * 100) / 100;
    }

    protected static function toPrice($value)
    {
        return round($value, 6);
    }

    protected static function roundUp($value, $percent, $ceil)
    {
        $value = $value * (100 + $percent) / 100;
        $up = ceil($value / $ceil) * $ceil;

        return $up;
    }

    protected static function roundDown($value, $percent, $floor)
    {
        $value = $value * (100 + $percent) / 100;
        $down = floor($value / $floor) * $floor;

        return $down;
    }

    public function insertRecord()
    {
        /** @var int */
        $id_supplier = $this->id_supplier;
        /** @var int */
        $id_manufacturer = $this->id_manufacturer;
        /** @var int */
        $id_tax_rules_group = $this->id_tax_rules_group;
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = $controller->module;
        /** @var array */
        $languages = Language::getLanguages();
        /** @var int */
        $root_category = (int) Category::getRootCategory()->id;
        /** @var string */
        $root_isacco = 'https://www.isacco.it/pub/media/catalog/product';

        if (self::existsProduct($this->reference)) {
            $this->controller->warnings[] = sprintf(
                $this->module->l('Il prodotto %s esiste già ed è stato ignorato.', $this->name),
                $this->reference
            );

            return true;
        }

        $product = new Product();
        $product->id_supplier = $id_supplier;
        $product->id_manufacturer = $id_manufacturer;
        $product->id_category_default = $root_category;
        $product->id_shop_default = Context::getContext()->shop->id;
        $product->on_sale = 0;
        $product->online_only = 0;
        $product->ean13 = '';
        $product->quantity = 0;
        $product->price = $this->price;
        $product->wholesale_price = $this->wholesale_price;
        $product->reference = 'ISA' . $this->reference;
        $product->supplier_reference = $this->reference;
        $product->location = '';
        $product->out_of_stock = 2;
        $product->active = 0;
        $product->available_for_order = 1;
        $product->condition = 'new';
        $product->show_price = 1;
        $product->visibility = 'both';
        $product->date_add = date('Y-m-d H:i:s');
        $product->id_tax_rules_group = $id_tax_rules_group;
        $product->pack_stock_type = 3;
        foreach ($languages as $language) {
            $this->name = $this->name . ' ISACCO ' . $this->reference;
            $id_lang = (int) $language['id_lang'];
            $product->description[$id_lang] = '';
            $product->description_short[$id_lang] = '';
            $product->name[$id_lang] = $this->name;
            $product->link_rewrite[$id_lang] = Tools::link_rewrite($this->name);
            $product->meta_description[$id_lang] = '';
            $product->meta_keywords[$id_lang] = '';
            $product->meta_title[$id_lang] = '';
            $product->available_now[$id_lang] = '';
            $product->available_later[$id_lang] = '';
        }

        try {
            $result = $product->add();
        } catch (\Throwable $th) {
            $controller->errors[] = $th->getMessage();
            $result = false;
        }

        if ($result) {
            $product->addToCategories($root_category);
            $id_product = (int) $product->id;
            $image = $root_isacco . $this->img;
            $imageProduct = new ImageProduct($this->module, $this->context->controller);
            $imageProduct->addImages($id_product, $image);
            $this->createCombinations($id_product);
        }

        return true;
    }

    protected static function getTaxRuleGroup($name)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_tax_rules_group')
            ->from('tax_rules_group')
            ->where('name = \'' . pSQL($name) . '\'')
            ->where('deleted = 0')
            ->where('active = 1')
            ->orderBy('id_tax_rules_group DESC');
        $id_tax_rule_group = (int) $db->getValue($sql);

        return $id_tax_rule_group;
    }

    protected static function getIdSupplier($name)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_supplier')
            ->from('supplier')
            ->where('name = \'' . pSQL($name) . '\'')
            ->where('active = 1')
            ->orderBy('id_supplier DESC');
        $id_supplier = (int) $db->getValue($sql);

        return $id_supplier;
    }

    protected static function getIdManufacturer($name)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_manufacturer')
            ->from('manufacturer')
            ->where('name = \'' . pSQL($name) . '\'')
            ->where('active = 1')
            ->orderBy('id_manufacturer DESC');
        $id_manufacturer = (int) $db->getValue($sql);

        return $id_manufacturer;
    }

    protected function createCombinations($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(ModelMpMassImportIsaccoEan13::$definition['table'])
            ->where('id_product=' . (int) $this->id);
        $sql = $sql->build();
        $rows = $db->executeS($sql);

        if (!$rows) {
            return false;
        }

        $combinations = [];
        $ean13 = [];
        $classCombinations = new Combinations();
        foreach ($rows as $row) {
            $combinations[] = explode('|', $row['combination']);
            $ean13[] = $row['ean13'];
        }
        $classCombinations->addCombinations($id_product, $combinations, $ean13);
    }
}
