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
*  @author    Massimiliano Palermo <info@mpsoft.it>
*  @copyright 2007-2018 Digital Solutions®
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class MpModelUpdateCombination extends ObjectModel
{
    public $id_product;
    public $name;
    public $reference;
    public $ean13;
    public $price;
    public $unit_price_impact;
    public $quantity;
    public $json;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'mp_massimport_update_combination',
        'primary' => 'id_product_attribute',
        'multilang' => false,
        'fields' => array(
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 128,
                'required' => false,
            ),
            'reference' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => false,
            ),
            'ean13' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isEan13',
                'size' => 13,
                'required' => false,
            ),
            'price' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isNegativePrice',
                'required' => false,
            ),
            'unit_price_impact' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => false,
            ),
            'quantity' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ),
            'default_on' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ),
            'json' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => true,
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        $context = Context::getContext();
        $this->id_lang = (int)$context->language->id;
        $this->id_shop = (int)$context->shop->id;
        $this->tools = new MpUtilities();

        if ($this->id) {
            $this->json = json_decode($this->json, true);
            if (isset($this->json['price'])) {
                $this->json['price'] = number_format($this->json['price'], 6);
                $this->json['price_impact'] = number_format($this->json['price_impact'], 6);
                $this->json['wholesale_price'] = number_format($this->json['wholesale_price'], 6);
            }
        }
    }

    public static function truncate()
    {
        $db = Db::getInstance();
        return $db->execute("TRUNCATE TABLE "._DB_PREFIX_.self::$definition['table']);
    }

    public static function install()
    {
        $def = self::$definition;
        $sql = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_.$def['table']." (";
        $fields = array(
            "`".$def['primary']."` int not null AUTO_INCREMENT PRIMARY KEY",
        );
        foreach ($def['fields'] as $key => $field) {
            $fields[] = self::getSqlByField($key, $field);
        }
        $sql .= implode(",", $fields) . ") ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

        $res =  (int)Db::getInstance()->execute($sql);
        if (!$res) {
            return false;
        }

        return true;
    }

    public static function getSqlByField($name, $field)
    {
        $name = "`".$name."` ";

        $required = isset($field['required'])?(int)$field['required']:0;
        $datetime = isset($field['datetime'])?(int)$field['datetime']:1;

        switch ($field['type']) {
            case self::TYPE_FLOAT:
                if (isset($field['validate']) && $field['validate'] == 'isPrice') {
                    $name .= " decimal(20,6) ";
                } else {
                    $name .= " float ";
                }
                break;
            case self::TYPE_STRING:
                if (isset($field['size'])) {
                    $name .= " varchar(".(int)$field['size'].") ";
                } else {
                    $name .= " text ";
                }
                break;
            case self::TYPE_INT:
                $name .= " int ";
                break;
            case self::TYPE_DATE:
                $name .= $datetime?" datetime ":" date ";
                break;
            case self::TYPE_BOOL:
                $name .= " tinyint ";
                break;
        }
        $name .= ($required?" NOT ":"")."NULL";

        return $name;
    }

    public static function createTable()
    {
        return self::install();
    }

    public function getValue($array)
    {
        if (is_array($array) && isset($array['value'])) {
            return $array['value'];
        }
        return "";
    }

    public function getCategoryList($list)
    {
        $categories = Tools::jsonDecode($list);
        if (!$categories) {
            return array();
        }
        foreach ($categories as &$cat) {
            $split = explode(":", $cat);
            $cat = (int)$split[0];
        }
        if ($categories) {
            $this->id_category_default = $categories[0];
        }
        return $categories;
    }

    public function parseAttributeCombinations()
    {
        $attributes = $this->attributes;
        if (!$attributes) {
            return array();
        }
        $output = array();
        foreach ($attributes as $row) {
            $attrs = explode(":", $row);
            if (isset($attrs[1])) {
                $ids_attr = explode(";", $attrs[1]);
                $output[] = $ids_attr;
                unset($ids_attr);
            } else {
                continue;
            }
        }
        return $output;
    }

    public function createCombinationList($list)
    {
        if (!$list) {
            return array();
        }
        if (count($list) <= 1) {
            return count($list) ? array_map(array($this, 'arrayComb'), $list[0]) : $list;
        }
        $res = array();
        $first = array_pop($list);
        foreach ($first as $attribute) {
            $tab = $this->createCombinationList($list);
            foreach ($tab as $to_add) {
                $res[] = is_array($to_add) ? array_merge($to_add, array($attribute)) : array($to_add, $attribute);
            }
        }
        return $res;
    }

    private function arrayComb($v)
    {
        return array($v);
    }
}
