<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
require_once _PS_MODULE_DIR_ . 'mpmassimport/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/models/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/traits/autoload.php';

class ModelImportFeatureProduct extends ObjectModel
{
    use \MpSoft\MpMassImport\Traits\Cookies;
    use \MpSoft\MpMassImport\Traits\HelperFormExcelImport;
    use \MpSoft\MpMassImport\Traits\SmartyTpl;
    use \MpSoft\MpMassImport\Traits\Tools;
    use \MpSoft\MpMassImport\Traits\CommonDbTools;

    /** @var int */
    public $id_product;

    /** @var string */
    public $reference;

    /** @var string */
    public $name;

    /** @var string|array */
    public $json;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = [
        'table' => 'mp_massimport_feature_product',
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
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => false,
            ],
            'json' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 999999999,
                'required' => true,
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
        parent::__construct($id, $id_lang, $id_shop);

        if ($this->id) {
            $this->json = Tools::jsonDecode($this->json, true);
        }
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (is_array($this->json)) {
            $this->json = Tools::jsonEncode($this->json);
        }

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        if (is_array($this->json)) {
            $this->json = Tools::jsonEncode($this->json);
        }

        return parent::update($null_values);
    }

    public static function insertRows(array $rows, \ModuleAdminController $controller)
    {
        self::truncate();
        $res = false;

        foreach ($rows as $row) {
            $record = new ModelImportFeatureProduct();
            $record->force_id = true;
            $record->id = (int) $row['id_product'];
            $record->reference = $row['reference'];
            $record->name = $row['name'];
            $record->json = $row;

            try {
                $res = $record->add();
                if (!$res) {
                    $controller->errors[] = Db::getInstance()->getMsgError();
                }
            } catch (\Throwable $th) {
                $controller->errors[] = $th->getMessage();
                $res = false;
            }
        }

        if ($res) {
            $controller->confirmations[] = $controller->module->l('Foglio Excel Importato', $controller->name);
        }

        return $res;
    }
}
