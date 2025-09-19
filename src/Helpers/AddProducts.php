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

namespace MpSoft\MpMassImport\Helpers;

use Combination;
use Configuration;
use Context;
use Db;
use DbQuery;
use ModelImportProduct;
use Module;
use ModuleAdminController;
use MpSoft\MpMassImport\Helpers\Combinations;
use MpSoft\MpMassImport\Helpers\ImageProduct;
use MpSoft\MpMassImport\Helpers\ImportSettings;
use MpSoft\MpMassImport\Helpers\Queries;
use ObjectModel;
use Product;
use ProductSupplier;
use Tools;

class AddProducts
{
    /** @var string */
    protected static $controller_name;

    public static function addToTable($products)
    {
        ModelImportProduct::truncate();
        $products = self::sanitizeFields($products);
        //Tools::dieObject($products);
        $combinations = new Combinations();
        foreach ($products as &$product) {
            $list = $combinations->cleanAttributes($product['attributes']);
            $product['combinations'] = $combinations->createCombinationList($list);
            self::insertToTable($product);
        }
    }

    private static function sanitizeFields($products)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();

        $fields = [
            'reference' => 'mandatory',
            'supplier_reference' => 'string',
            'ean13' => 'string',
            'product_name' => 'mandatory',
            'condition' => 'string',
            'wholesale_price' => 'price',
            'is_virtual' => 'bool',
            'description' => 'string',
            'description_short' => 'string',
            'price' => 'price',
            'available_date' => 'date',
            'prefix' => 'string',
            'quantity' => 'int',
        ];

        foreach ($products as &$product) {
            $keys = [];
            foreach ($product as $k => $v) {
                $keys[] = $k;
            }
            //Tools::dieObject(["BEFORE", $product], 0);
            foreach ($fields as $key => $value) {
                if (!in_array($key, $keys)) {
                    //Tools::dieObject(["NOT IN ARRAY", $key], 0);
                    if ($value == 'mandatory') {
                        $controller->errors[] = sprintf(
                            $module->l('Product has no %s. Removed %s %s', self::$controller_name),
                            $key,
                            '<br>',
                            print_r($product, 1)
                        );
                        unset($product);
                    } elseif ($value == 'int') {
                        $product[$key] = 0;
                    } elseif ($value == 'string') {
                        $product[$key] = '';
                    } elseif ($value == 'bool') {
                        $product[$key] = false;
                    } elseif ($value == 'price') {
                        $product[$key] = '0.000000';
                    } elseif ($value == 'date') {
                        $product[$key] = '';
                    }
                }
            }
            //Tools::dieObject(["AFTER", $product], 0);
        }

        return $products;
    }

    private static function insertToTable($product)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var ObjectModel */
        $model = new ModelImportProduct();

        if (!self::addWarnings($product)) {
            $controller->errors[] = sprintf(
                $module->l('Product %s %s not imported.', self::$controller_name),
                '<strong>' . $product['reference'] . '</strong>',
                '<strong>' . $product['product_name'] . '</strong>'
            );

            return false;
        }

        try {
            $model->reference = $product['reference'];
            $model->supplier_reference = $product['supplier_reference'];
            $model->ean13 = $product['ean13'];
            $model->product_name = $product['product_name'];
            $model->condition = $product['condition'];
            $model->wholesale_price = $product['wholesale_price'];
            $model->is_virtual = $product['is_virtual'];
            $model->description = $product['description'];
            $model->description_short = $product['description_short'];
            $model->link_rewrite = Tools::str2url($product['product_name']);
            $model->price = $product['price'];
            $model->available_date = $product['available_date'];
            $model->prefix = $product['prefix'];
            $model->quantity = $product['quantity'];
            $model->json = json_encode($product);
            $res = $model->add();
            if ($res) {
                $controller->confirmations[] = sprintf(
                    $module->l('Product %s inserted. %s', self::$controller_name),
                    $model->product_name,
                    '<br>'
                );
            }
        } catch (\Throwable $th) {
            $controller->errors[] = $th->getMessage();
        }

        return true;
    }

    private static function addWarnings($product)
    {
        $check = true;
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();

        if (isset($product['categories'])) {
            foreach ($product['categories'] as $key => $category) {
                if (!(int) $category) {
                    unset($product['categories'][$key]);
                }
            }
        }
        /**
         * ATTRIBUTES
         */
        if (isset($product['attributes'])) {
            foreach ($product['attributes'] as $id_attribute_group => $attributes) {
                /**
                 * CHECK ATTRIBUTE GROUP
                 */
                $chk = self::checkItem($id_attribute_group, $module->l('Attribute Group', self::$controller_name), '');
                if (!$chk) {
                    $check = false;
                }
                /**
                 * CHECK ATTRIBUTES
                 */
                foreach ($attributes as $attribute) {
                    $chk = self::checkItem($attribute, $module->l('Attribute', self::$controller_name), $id_attribute_group);
                    if (!$chk) {
                        $check = false;
                    }
                }
            }
        }
        /**
         * FEATURES
         */
        if (isset($product['features'])) {
            foreach ($product['features'] as $id_feature => $features) {
                /**
                 * CHECK ATTRIBUTE GROUP
                 */
                $chk = self::checkItem($id_feature, $module->l('Feature', self::$controller_name), '');
                if (!$chk) {
                    $check = false;
                }
                /**
                 * CHECK ATTRIBUTES
                 */
                foreach ($features as $feature) {
                    $chk = self::checkItem($feature, $module->l('Feature Value', self::$controller_name), $id_feature);
                    if (!$chk) {
                        $check = false;
                    }
                }
            }
        }
        //Tools::dieObject([$product['product_name'], "CHECK", (int)$check], 0);
        return $check;
    }

    private static function checkItem($item, $itemName, $parent = '')
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();

        $split = explode(':', $item);

        if (count($split) == 2) {
            $id = (int) trim($split[1]);
            if (!$id) {
                $controller->warnings[] = sprintf(
                    $module->l('%s %s%s not found', self::$controller_name),
                    $itemName,
                    "<strong>$parent</strong>=>",
                    '<strong>' . trim($split[0]) . '</strong>'
                );

                return false;
            }
        } else {
            $controller->warnings[] = sprintf(
                $module->l('%s %s%s not found', self::$controller_name),
                $itemName,
                "<strong>$parent</strong>=>",
                "<strong>$item</strong>"
            );

            return false;
        }

        return true;
    }

    public static function addToProducts($product)
    {
        $fields = Product::$definition['fields'];
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var int */
        $id_lang = (int) Context::getContext()->language->id;
        /** @var int */
        $id_shop = (int) Context::getContext()->shop->id;
        /** @var Queries */
        $queries = new Queries();
        /** @var array */
        $f = $product->json;

        $f['name'] = $f['product_name'];

        $id_product = $queries->getIdProductByReference($f['reference']);
        $p = new Product($id_product, false, $id_lang, $id_shop);
        foreach ($fields as $fieldname => $field) {
            if (isset($f[$fieldname])) {
                if (self::importField($fieldname)) {
                    $value = '';
                    switch($field['type']) {
                        case ObjectModel::TYPE_INT:
                        case ObjectModel::TYPE_BOOL:
                            $value = (int) $f[$fieldname];

                            break;
                        case ObjectModel::TYPE_FLOAT:
                            $value = (float) $f[$fieldname];

                            break;
                        default:
                            $value = $f[$fieldname];
                    }

                    $p->$fieldname = $value;
                }
            }
        }
        $action = '';
        $p->link_rewrite = Tools::str2url($p->name);

        try {
            if ($id_product) {
                $action = 'update';
                $res = $p->update();
            } else {
                $action = 'add';
                $res = $p->add();
            }
            if ($res) {
                $id_product = $queries->getIdProductByReference($p->reference);
            } else {
                return false;
            }
            /**
             * ADD SUPPLIERS
             */
            if (Configuration::get(ImportSettings::MPMASSIMPORT_SUPPLIERS)) {
                self::addSuppliers($id_product, $f['id_suppliers'], $f['supplier_reference']);
            }
            /**
             * ADD CATEGORIES
             */
            if (Configuration::get(ImportSettings::MPMASSIMPORT_CATEGORIES)) {
                self::addCategories($id_product, $f['categories']);
            }
            /**
             * ADD IMAGES
             */
            if (Configuration::get(ImportSettings::MPMASSIMPORT_IMAGES)) {
                self::addImages($id_product, $f['images']);
            }
            /**
             * ADD FEATURES
             */
            if (Configuration::get(ImportSettings::MPMASSIMPORT_FEATURES)) {
                self::addFeatures($id_product, $f['features']);
            }
            /**
             * ADD COMBINATIONS
             */
            if (Configuration::get(ImportSettings::MPMASSIMPORT_COMBINATIONS)) {
                self::addCombinations($id_product, $f['combinations']);
            }

            return true;
        } catch (\Throwable $th) {
            $controller->errors[] = sprintf(
                $module->l('Product %s %s not inserted. Action: %s, Error %s', self::$controller_name),
                $f['reference'],
                $f['product_name'],
                $action,
                $th->getMessage()
            );

            return false;
        }
    }

    private static function importField($field)
    {
        $conf = '';
        switch ($field) {
            case 'ean13':
                $conf = ImportSettings::MPMASSIMPORT_EAN13;

                break;
            case 'supplier_reference':
                $conf = ImportSettings::MPMASSIMPORT_SUPPLIER_REFERENCE;

                break;
            case 'id_supplier':
                $conf = ImportSettings::MPMASSIMPORT_ID_SUPPLIER;

                break;
            case 'wholesale_price':
                $conf = ImportSettings::MPMASSIMPORT_WS_PRICE;

                break;
            case 'price':
                $conf = ImportSettings::MPMASSIMPORT_PRICE;

                break;
            case 'description_short':
                $conf = ImportSettings::MPMASSIMPORT_DESCRIPTION_SHORT;

                break;
            case 'description_long':
            case 'description':
                $conf = ImportSettings::MPMASSIMPORT_DESCRIPTION_LONG;

                break;
            case 'id_manufacturer':
            case 'manufacturer':
                $conf = ImportSettings::MPMASSIMPORT_ID_MANUFACTURER;

                break;
            case 'id_tax_rules_group':
                $conf = ImportSettings::MPMASSIMPORT_ID_TAX_RULES_GROUP;

                break;
            case 'id_category_default':
            case 'categories':
                $conf = ImportSettings::MPMASSIMPORT_CATEGORIES;

                break;
            case 'img_root':
            case 'img_folder':
            case 'images':
                $conf = ImportSettings::MPMASSIMPORT_IMAGES;

                break;
            case 'suppliers':
                $conf = ImportSettings::MPMASSIMPORT_SUPPLIERS;

                break;
            case 'quantity':
                $conf = ImportSettings::MPMASSIMPORT_QUANTITIES;

                break;
            default:
                return true;
        }

        return (int) Configuration::get($conf);
    }

    private static function addSuppliers($id_product, $suppliers, $supplier_reference)
    {
        $db = Db::getInstance();
        $context = Context::getContext();
        $db->execute('delete from ' . _DB_PREFIX_ . 'product_supplier where id_product=' . (int) $id_product);
        foreach ($suppliers as $id_supplier) {
            $db->insert(
                'product_supplier',
                [
                    'id_product' => $id_product,
                    'id_product_attribute' => 0,
                    'id_supplier' => $id_supplier,
                    'product_supplier_reference' => $supplier_reference,
                    'product_supplier_price_te' => 0,
                    'id_currency' => $context->currency->id,
                ]
            );
        }
    }

    private static function addCategories($id_product, $categories)
    {
        $controller = Context::getContext()->controller;
        $module = Module::getInstanceByName('mpmassimport');
        $db = Db::getInstance();
        $pos = 0;
        $categories = array_unique($categories);
        $db->execute('delete from ' . _DB_PREFIX_ . 'category_product where id_product=' . (int) $id_product);
        foreach ($categories as $id_category) {
            $res = $db->insert(
                'category_product',
                [
                    'id_product' => $id_product,
                    'id_category' => $id_category,
                    'position' => $pos,
                ]
            );
            $pos++;
            if (!$res) {
                $controller->errors[] = sprintf(
                    $module->l('Error inserting category id %d for product %d', self::$controller_name),
                    $id_category,
                    $id_product
                );
            }
        }
    }

    private static function addImages($id_product, $images)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();

        foreach ($images as $image) {
            $source = $image;
            $url_exists = ImageProduct::urlExists($source, $mime);
            if ($url_exists) {
                $file = [
                    'save_path' => $source,
                    'name' => basename($source),
                    'mime' => $mime,
                    'error' => 0,
                    'size' => getimagesize($source),
                ];
                $obj_img = new ImageProduct($module, $controller);
                $res = (int) $obj_img->addImageProduct($id_product, $file);
                if (!$res) {
                    $controller->errors[] = sprintf(
                        $module->l('Images for product %d not inserted.', self::$controller_name),
                        $id_product
                    );
                }
            } else {
                $controller->errors[] = sprintf(
                    $module->l('URL incorrect: %s'),
                    $source
                );
            }
        }
    }

    private static function addFeatures($id_product, $features)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var Product */
        $product = new Product($id_product);

        $product->deleteProductFeatures();
        foreach ($features as $key_feature => $feature) {
            $name_feature = '';
            $id_feature = self::getIdFromKey($key_feature, $name_feature);
            foreach ($feature as $key_feature_value) {
                $name_feature_value = '';
                $id_feature_value = self::getIdFromKey($key_feature_value, $name_feature_value);
                $res = Product::addFeatureProductImport($id_product, $id_feature, $id_feature_value);
                if (!$res) {
                    $controller->errors[] = sprintf(
                        $module->l('Error inserting feature %s %s for product %d', self::$controller_name),
                        $name_feature,
                        $name_feature_value,
                        $id_product
                    );
                }
            }
        }
    }

    private static function addCombinations($id_product, $combinations)
    {
        $product = new Product($id_product);
        if (Configuration::get(ImportSettings::MPMASSIMPORT_DELETE_COMBINATIONS)) {
            $product->deleteProductAttributes();
        }
        foreach ($combinations as $combination) {
            $pa = new Combination();
            $fields = [
                'id_product' => $id_product,
                'price' => 0,
                'weight' => 0,
                'ecotax' => 0,
                'quantity' => 0,
                'reference' => $product->reference,
                'supplier_reference' => $product->supplier_reference,
                'default_on' => 0,
                'available_date' => date('Y-m-d'),
            ];
            foreach ($fields as $key => $value) {
                $pa->$key = $value;
            }
            $add = $pa->add();
            if ($add) {
                $pa->setAttributes($combination);
            }
        }
    }

    private static function getIdFromKey($key, &$name = null)
    {
        $split = explode(':', $key);
        if (count($split) == 2) {
            $name = trim($split[0]);

            return (int) $split[1];
        }

        return 0;
    }
}
