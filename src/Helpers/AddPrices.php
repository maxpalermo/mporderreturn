<?php

namespace MpSoft\MpMassImport\Helpers;

use Context;
use Db;
use DbQuery;
use ModelImportPrice;
use Module;
use ModuleAdminController;
use MpSoft\MpMassImport\Library\Queries;
use ObjectModel;
use Product;

class AddPrices
{
    protected static $controller_name;

    public static function addToTable($products)
    {
        ModelImportPrice::truncate();
        $products = self::sanitizeFields($products);
        //Tools::dieObject($products);
        foreach ($products as &$product) {
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
        /** @var ObjectModel */
        $model = new ModelImportPrice();

        $fields = [
            'reference' => 'mandatory',
            'name' => 'string',
            'wholesale_price' => 'price',
            'price' => 'price',
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

    private static function setProductName($product)
    {
        if (isset($product['name']) && $product['name']) {
            return $product['name'];
        }

        return self::getProductName($product['id_product']);
    }

    private static function getProductName($reference)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('pl.name')
            ->from('product', 'p')
            ->innerJoin('product_lang', 'pl', 'p.id_product=pl.id_product and pl.id_lang=' . $id_lang . ' and pl.id_shop=' . $id_shop)
            ->where('p.reference = \'' . pSQL($reference) . '\'');

        return $db->getValue($sql);
    }

    private static function getIdProduct($reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product')
            ->where('reference = \'' . pSQL($reference) . '\'');

        return (int) $db->getValue($sql);
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
        $model = new ModelImportPrice();
        if (!self::addWarnings($product)) {
            $controller->errors[] = sprintf(
                $module->l('Product %s %s not imported.', self::$controller_name),
                '<strong>' . $product['reference'] . '</strong>',
                '<strong>' . $product['product_name'] . '</strong>'
            );

            return false;
        }

        try {
            $model->force_id = true;
            $model->id = $product['id_product'];
            $model->reference = $product['reference'];
            $model->name = self::setProductName($product);
            $model->wholesale_price = $product['wholesale_price'];
            $model->price = $product['price'];
            $model->json = json_encode($product);
            $res = $model->add();
            if ($res) {
                $controller->confirmations[] = sprintf(
                    $module->l('Product %s inserted. %s', self::$controller_name),
                    $model->name,
                    '<br>'
                );
            }
        } catch (\Throwable $th) {
            if (strpos($th->getMessage(), 'Duplicate entry' === false)) {
                $controller->errors[] = $th->getMessage();
            }
        }

        return true;
    }

    private static function addWarnings($product)
    {
        /** @var bool */
        $check = true;
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var ObjectModel */
        $model = new ModelImportPrice();
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
        /** @var ObjectModel */
        $model = new ModelImportPrice();
        /** @var array */
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

    public static function addToPrices($product)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var ObjectModel */
        $model = new ModelImportPrice();
        /** @var int */
        $id_lang = (int) Context::getContext()->language->id;
        /** @var int */
        $id_shop = (int) Context::getContext()->shop->id;
        /** @var int */
        $id_product = (int) $product->id;
        /** @var array */
        $f = $product->json;

        if (!$id_product) {
            return false;
        }
        $p = new Product($id_product, true, $id_lang, $id_shop);
        $p->wholesale_price = $product->wholesale_price;
        $p->price = $product->price;

        try {
            $res = $p->update();

            return (int) $res;
        } catch (\Throwable $th) {
            $controller->errors[] = sprintf(
                $module->l('Product price %s %s not inserted. Error %s', self::$controller_name),
                $f['reference'],
                $f['product_name'],
                $th->getMessage()
            );

            return false;
        }
    }
}
