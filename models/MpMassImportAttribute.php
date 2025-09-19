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
*  @copyright 2021 Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class MpMassImportAttribute extends ObjectModel
{
    public $id_attribute_group;
    public $id_attribute;
    public $attribute_group;
    public $attribute;
    public $is_color_group;
    public $group_type;
    public $color;
    public $img_root;
    public $img_folder;
    public $image;
    public $date_add;
    public $date_upd;

    public $context;
    public $id_lang;
    public $id_shop;
    public $controller;
    private $tools;

    public static $definition = [
        'table' => 'mp_massimport_attribute',
        'primary' => 'id_mp_massimport_attribute',
        'multilang' => false,
        'fields' => [
            'id_attribute_group' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_attribute' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'attribute_group' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 64,
                'required' => false,
            ],
            'attribute' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 64,
                'required' => false,
            ],
            'is_color_group' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'group_type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 32,
                'required' => false,
            ],
            'color' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => false,
            ],
            'img_root' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => false,
            ],
            'img_folder' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => false,
            ],
            'image' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => false,
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
        parent::__construct($id);

        $this->context = Context::getContext();
        $this->controller = $this->context->controller;
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->tools = new MpUtilities();
    }

    public static function truncate()
    {
        $db = Db::getInstance();

        return $db->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . self::$definition['table']);
    }

    public static function install()
    {
        $def = self::$definition;
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $def['table'] . ' (';
        $fields = [
            '`' . $def['primary'] . '` int not null AUTO_INCREMENT PRIMARY KEY',
        ];
        foreach ($def['fields'] as $key => $field) {
            $fields[] = self::getSqlByField($key, $field);
        }
        $sql .= implode(',', $fields) . ') ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $res = (int) Db::getInstance()->execute($sql);
        if (!$res) {
            return false;
        }

        return true;
    }

    public static function getSqlByField($name, $field)
    {
        $name = '`' . $name . '` ';

        $required = isset($field['required']) ? (int) $field['required'] : 0;
        $datetime = isset($field['datetime']) ? (int) $field['datetime'] : 1;

        switch ($field['type']) {
            case self::TYPE_FLOAT:
                if (isset($field['validate']) && $field['validate'] == 'isPrice') {
                    $name .= ' decimal(20,6) ';
                } else {
                    $name .= ' float ';
                }

                break;
            case self::TYPE_STRING:
                if (isset($field['size'])) {
                    $name .= ' varchar(' . (int) $field['size'] . ') ';
                } else {
                    $name .= ' text ';
                }

                break;
            case self::TYPE_INT:
                $name .= ' int ';

                break;
            case self::TYPE_DATE:
                $name .= $datetime ? ' datetime ' : ' date ';

                break;
            case self::TYPE_BOOL:
                $name .= ' tinyint ';

                break;
        }
        $name .= ($required ? ' NOT ' : '') . 'NULL';

        return $name;
    }

    public static function createTable()
    {
        return self::install();
    }
}
