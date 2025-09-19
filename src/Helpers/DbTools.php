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

use Context;
use Db;
use DbQuery;
use Exception;
use Language;
use OrderState;

class DbTools
{
    protected $module;
    protected $name;
    protected $id_lang;
    protected $id_shop;

    public function __construct($module)
    {
        $this->module = $module;
        $this->name = 'DbTools';
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
    }

    /**
     * @param array      $array
     * @param int|string $position
     * @param mixed      $insert
     */
    public function arrayInsert(&$array, $position, $insert)
    {
        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }
    public function isJson($string)
    {
        $dummy = json_decode($string);
        $error = json_last_error();

        return  $error === JSON_ERROR_NONE;
    }

    public function getLanguagesArray()
    {
        $languages = Language::getLanguages();
        $arr_lang = [];
        foreach ($languages as $lang) {
            $arr_lang[] = $lang['id_lang'];
        }

        return $arr_lang;
    }

    public function getLanguagesLabel()
    {
        $languages = Language::getLanguages();
        $arr_label = [];
        foreach ($languages as $lang) {
            $arr_label[$lang['id_lang']] = [
                'id_lang' => (int) $lang['id_lang'],
                'name' => $lang['name'],
                'iso_code' => $lang['iso_code'],
            ];
        }

        return $arr_label;
    }

    public function getDefaultFormValues($form_fields)
    {
        $output = [];
        $arr_lang = $this->getLanguagesArray();

        try {
            $fields = $form_fields['form']['input'];
        } catch (\Throwable $th) {
            throw new Exception('Missing Inputs Array', 1);
        }
        foreach ($fields as $field) {
            if (!isset($field['type'])) {
                $field['type'] = 'text';
            }
            $name = $field['name'];
            $values = [];
            if (isset($field['lang']) && $field['lang']) {
                foreach ($arr_lang as $idx) {
                    $values[$idx] = '';
                }
            } else {
                $values = '';
            }

            switch($field['type']) {
                case 'text':
                case 'textarea':
                case 'hidden':
                    $output[$name] = $values;

                    break;
                case 'select':
                    if (isset($field['multiple']) && $field['multiple']) {
                        $output[$name . '[]'] = $values;
                    } else {
                        $output[$name] = $values;
                    }

                    break;
                case 'switch':
                    $output[$name] = $values;

                    break;
                case 'date':
                    $output[$name] = $values;

                    break;
                default:
                    $output[$name] = $values;
            }
        }

        return $output;
    }

    public function getIdOrderStates()
    {
        $orderStates = OrderState::getOrderStates($this->id_lang);
        $output = [];
        foreach ($orderStates as $os) {
            $output[] = [
                'id' => $os['id_order_state'],
                'name' => $os['name'],
            ];
        }

        return $output;
    }
    public function getIdSupplier($id_product)
    {
        $db = Db::getInstance();
        $sql = 'select distinct id_supplier from ' . _DB_PREFIX_ . 'product_supplier where id_product = ' . (int) $id_product;
        $result = $db->executeS($sql);
        $output = [];
        if ($result) {
            foreach ($result as $res) {
                $output[] = (int) $res['id_supplier'];
            }
        }

        return $output;
    }

    public function getProducts($id_lang = null, $id_shop = null)
    {
        if (!$id_lang) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if (!$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('p.id_product')
            ->select('CONCAT(p.reference, " - ", pl.name) as name')
            ->from('product', 'p')
            ->innerJoin('product_lang', 'pl', 'p.id_product=pl.id_product')
            ->where('pl.id_shop=' . (int) $id_shop)
            ->where('pl.id_lang=' . (int) $id_lang)
            ->where('p.active=1')
            ->orderBy('p.reference');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }
        array_unshift(
            $result,
            [
                'id_product' => 0,
                'name' => $this->module->l('Please select a product.', $this->name),
            ]
        );

        return $result;
    }
    public function getSuppliers()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_supplier')
                ->select('name')
                ->from('supplier')
                ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    public function getManufacturers()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_manufacturer')
                ->select('name')
                ->from('manufacturer')
                ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    public function getProductsByIdCategory($but)
    {
        if (!$but->id_categories) {
            return [];
        }

        $db = Db::getInstance();
        $sql = new DbQuery();
        $products = [];
        $id_list = implode(',', $but->id_categories);


        $sql->select('id_product')
            ->from('category_product')
            ->where('id_category in (' . pSQL($id_list) . ')');
        $res = $db->executeS($sql);
        if (!$res) {
            return [];
        }

        foreach ($res as $id) {
            $products[] = $id['id_product'];
        }
        $list = array_unique($products);

        return $list;
    }

    public function getProductsByIdSupplier($but)
    {
        if (!$but->id_suppliers) {
            return [];
        }

        $db = Db::getInstance();
        $sql = new DbQuery();
        $products = [];
        $id_list = implode(',', $but->id_suppliers);


        $sql->select('id_product')
            ->from('product_supplier')
            ->where('id_supplier in (' . pSQL($id_list) . ')');
        $res = $db->executeS($sql);
        if (!$res) {
            return [];
        }

        foreach ($res as $id) {
            $products[] = $id['id_product'];
        }
        $list = array_unique($products);

        return $list;
    }

    public function getIdProducts($button)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('distinct id_product')
            ->from('product_supplier')
            ->where('id_supplier in (' . implode(',', $button->id_suppliers) . ')');
        $result = $db->executeS($sql);
        $output = [];
        if (count($button->id_products) == 0) {
            $button->id_products = [];
        }
        if ($result) {
            foreach ($result as $row) {
                $output[] = $row['id_product'];
            }
            $button->id_products = array_unique(array_merge($button->id_products, $output));
        }

        return $button->id_products;
    }

    public function getCustomerGroups()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_group')
            ->select('name')
            ->from('group_lang')
            ->where('id_lang=' . (int) Context::getContext()->language->id)
            ->orderBy('name');
        $res = $db->executeS($sql);
        if ($res) {
            $res[] = [
                'id_group' => -1,
                'name' => '--',
            ];

            return $res;
        }

        return [];
    }
}
