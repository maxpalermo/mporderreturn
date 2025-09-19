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

class ModelMpMassImportIsaccoEan13 extends ObjectModel
{
    public $id_product;
    public $combination;
    public $ean13;
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
    /** @var array */
    protected $combinations;
    /** @var Module */
    protected $module;
    /** @var string */
    public $name;

    public static $definition = [
        'table' => 'mp_massimport_isacco_ean13',
        'primary' => 'id_product',
        'multilang' => false,
        'fields' => [
            'combination' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => false,
            ],
            'ean13' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isEan13',
                'size' => 64,
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
        $this->context = Context::getContext();
        $this->controller = $this->context->controller;
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->name = 'ModelMpMassImportEan13';
        $this->controller = Context::getContext()->controller;
        $this->combinations = [];

        parent::__construct($id);

        if ($id) {
            $this->combination = explode('|', $this->combination);
        }
    }

    public static function truncate()
    {
        Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . self::$definition['table']);
    }

    public function update($null_values = false)
    {
        if (is_array($this->combination)) {
            $this->combination = implode('|', $this->combination);
        }

        return parent::update($null_values);
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (is_array($this->combination)) {
            $this->combination = implode('|', $this->combination);
        }

        return parent::add($auto_date = true, $null_values = false);
    }

    public function importExcelEan13($rows, $products, $id_size_group, $id_no_size)
    {
        /**
         * !Structure:
         * - CODICE
         * - DESCRIZIONE
         * - EAN13
         * - ... ELENCO ATTRIBUTI
         */

        self::truncate();

        if (!$products) {
            $products = self::getImportedProducts();
        }
        if (!$products) {
            $this->controller->warnings[] = $this->module->l('Nessun prodotto da importare', $this->name);

            return false;
        }
        $updated = 0;
        $combinations = [];
        $attributes = [
            'taglia' => [
                'id' => $id_size_group,
            ],
        ];
        foreach ($rows as $row) {
            $references = [];
            $regex = preg_match('/(.*) \((.*) - \)/i', $row['codice'], $references);
            if (!$regex) {
                $references = [
                    $row['codice'],
                    trim($row['codice']),
                    $id_no_size,
                ];

                $this->controller->warnings[] = sprintf(
                    $this->module->l('Il Prodotto %s non ha taglia. sarà usata la taglia %s', $this->name),
                    $row['codice'],
                    $id_no_size
                );
            }

            $reference = trim($references[1]);
            $size = trim($references[2]);

            if (!($id_product = isset($products[$reference]) ? (int) $products[$reference] : 0)) {
                $this->controller->warnings[] = sprintf(
                    $this->module->l('Prodotto non trovato con il riferimento %s', $this->name),
                    $reference
                );

                continue;
            }

            $ean13 = [];
            $preg_ean13 = preg_match('/\d{13}/im', $row['ean13'], $ean13);
            if ($preg_ean13) {
                $current_ean13 = $ean13[0];
            } else {
                $this->controller->warnings[] = sprintf(
                    $this->module->l('EAN13 non valido %s'),
                    $row['ean13']
                );
                $current_ean13 = '';
            }

            if (!isset($attributes['taglia'][$size])) {
                $id_size = self::getIdAttribute($id_size_group, $size);
                if (!$id_size) {
                    $this->controller->warnings[] = sprintf(
                        $this->module->l('Attributo taglia non valido: %s'),
                        $size
                    );

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
                                $this->controller->warnings[] = sprintf(
                                    $this->module->l('Gruppo Attributo non valido: %s'),
                                    $attribute_group
                                );

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
                                $this->controller->warnings[] = sprintf(
                                    $this->module->l('Attributo non valido: %s'),
                                    $attribute
                                );

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
            /** @var ModelMpMassImportIsaccoEan13 */
            $model = new ModelMpMassImportIsaccoEan13($id_product);

            $combination = $comb['combinations'];
            $ean13 = $comb['ean13'];
            $out = [];
            foreach ($combination as &$groups) {
                $groups = array_values(array_unique($groups));
                $out[] = $groups;
            }

            $current_combinations = $helperCombination->createCombinationList($out);
            foreach ($current_combinations as $combination_list) {
                $model->combination = $combination_list;
                $model->ean13 = $ean13[$combination_list[0]];

                try {
                    if (!$model->id) {
                        $model->id = $id_product;
                        $model->force_id = true;
                        $result = $model->add();
                    } else {
                        $result = $model->update();
                    }
                    if (!$result) {
                        $this->controller->warnings[] = sprintf(
                            $this->module->l('Errore nel salvataggio EAN13: %s %s %s'),
                            $reference,
                            $model->ean13,
                            Db::getInstance()->getMsgError()
                        );
                    } else {
                        $updated++;
                    }
                } catch (\Throwable $th) {
                    $this->controller->errors[] = $th->getMessage();
                }
            }
        }

        if (!$this->controller->errors && !$this->controller->warnings) {
            $this->controller->confirmations[] = $this->module->l('Operazione eseguita senza errori.', $this->name);
        } else {
            $this->controller->confirmations[] = $this->module->l('Operazione eseguita con errori.', $this->name);
        }

        return $updated;
    }

    protected static function getImportedProducts()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product, reference')
            ->from(ModelMpMassImportIsacco::$definition['table'])
            ->orderBy('reference');
        $rows = $db->executeS($sql);
        if (!$rows) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $reference = $row['reference'];
            $out[$reference] = (int) $row['id_product'];
        }

        return $out;
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

    protected static function existsProduct($reference)
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
}
