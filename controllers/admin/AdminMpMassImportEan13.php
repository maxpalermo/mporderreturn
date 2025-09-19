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

require_once _PS_MODULE_DIR_ . 'mpmassimport/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/models/autoload.php';

use MpSoft\MpMassImport\FieldsList\FieldsListEan13;
use MpSoft\MpMassImport\Forms\HelperFormFileUpload;
use MpSoft\MpMassImport\Helpers\AddEan13;
use MpSoft\MpMassImport\Helpers\AddIsaccoEan13;
use MpSoft\MpMassImport\Helpers\CheckExtension;
use MpSoft\MpMassImport\Helpers\Cookies;
use MpSoft\MpMassImport\Helpers\GetControllerName;
use MpSoft\MpMassImport\Helpers\ParseExcel;
use MpSoft\MpMassImport\Helpers\RowsToEan13;
use MpSoft\MpMassImport\Helpers\RowsToIsaccoEan13;

class AdminMpMassImportEan13Controller extends ModuleAdminController
{
    public const IMPORT_ISACCO = 'IMPORT_ISACCO';
    public const IMPORT_EAN13 = 'IMPORT_EAN13';

    public const CURRENT_DISPLAY = 'MPMASSIMPORT_DISPLAY';

    /** @var Db */
    protected $db;

    /** @var string */
    public $className;

    /** @var array */
    protected $import_list;

    /** @var int */
    protected $id_lang;

    /** @var int */
    protected $id_shop;

    /** @var Link */
    protected $link;

    /** @var string */
    protected $controllerName;
    /** @var Cookies */
    protected $cookies;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->import_list = [];
        $this->db = Db::getInstance();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->bootstrap = true;
        $this->className = 'ModelImportEan13';
        $this->controllerName = (new GetControllerName($this))->get();
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->cookies = new Cookies();
        $this->initHelperList();

        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if ($this->cookies->getValue(self::CURRENT_DISPLAY) == self::IMPORT_ISACCO) {
            $params = [
                'action' => 'importEan13',
            ];
            $params = http_build_query($params);
            $href = $this->context->link->getAdminLink($this->controllerName) . '&' . $params;
            $this->page_header_toolbar_btn['refresh'] = [
                'href' => $href,
                'desc' => $this->l('Torna alla pagina principale'),
            ];
        } else {
            $params = [
                'action' => 'importEan13Isacco',
            ];
            $params = http_build_query($params);
            $href = $this->context->link->getAdminLink($this->controllerName) . '&' . $params;
            $this->page_header_toolbar_btn['barcode'] = [
                'href' => $href,
                'desc' => $this->l('Importa EAN13 Isacco'),
            ];
        }
    }

    protected function initHelperList()
    {
        if ($this->cookies->getValue(self::CURRENT_DISPLAY) == self::IMPORT_ISACCO) {
            $this->toolbar_title = $this->l('Import ISACCO Ean13');
        } else {
            $this->toolbar_title = $this->l('Import Ean13');
        }
        $pre = _DB_PREFIX_;
        $this->table = ModelImportEan13::$definition['table'];
        $this->identifier = ModelImportEan13::$definition['primary'];
        $this->fields_list = (new FieldsListEan13($this))->getFieldsList();
        $this->addRowAction('import');
        $this->addRowAction('delete');
        $this->bulk_actions = [];
        $this->_select = "\nb.name as product_name, c.ean13 as current_ean13, a.id_product_attribute as `values` ";
        $this->_join .=
            ' left join ' . $pre . 'product_lang b on ('
            . "b.id_product=a.id_product and b.id_lang=$this->id_lang and b.id_shop=$this->id_shop)"
            . ' left join ' . $pre . 'product_attribute c on ('
            . 'c.id_product_attribute=a.id_product_attribute)';

        $this->bulk_actions = [
            'import' => [
                'text' => $this->l('Import Ean13'),
                'confirm' => $this->l('Import selected items?'),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->l('Import all'),
                'confirm' => $this->l('Import all items?'),
                'icon' => 'icon-download text-danger',
            ],
            'divider000' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->l('Delete from list'),
                'confirm' => $this->l('Delete selected items from list?'),
                'icon' => 'icon-trash text-danger',
            ],
            'divider001' => [
                'text' => 'divider',
            ],
        ];
    }

    public function initContent()
    {
        $this->content = $this->generateFormImport();
        parent::initContent();
    }

    public function generateFormImport()
    {
        $form = new HelperFormFileUpload($this);

        return $form->renderForm();
    }

    public function _postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('submitImportExcel')) {
            $this->importUploadFile();
        }
    }

    public function processUploadFile()
    {
        $this->importUploadFile();
    }

    public function importUploadFile()
    {
        $file = Tools::fileAttachment('uploadfile');
        if (!$file) {
            $this->errors[] = $this->l('Please select an Excel file.');

            return false;
        }

        if (!CheckExtension::check($file['name'], 'xlsx')) {
            $this->errors[] = $this->l('File format not valid.');

            return false;
        }

        if ($this->cookies->getValue(self::CURRENT_DISPLAY) == self::IMPORT_ISACCO) {
            $rows = ParseExcel::parse($file['content']);
            $parsed = (new RowsToIsaccoEan13($rows))->parse();

            (new AddIsaccoEan13($this))->addToTable($parsed);
        } else {
            $rows = ParseExcel::parse($file['content'], 'Ean13');
            $parsed = (new RowsToEan13($rows))->parse();

            (new AddEan13($this))->addToTable($parsed);
        }

        return true;
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new ModelImportEan13($box);
            $item->delete();
        }
        $this->confirmations[] = $this->l('Operation done.');
    }

    public function processBulkImport()
    {
        if ($this->cookies->getValue(self::CURRENT_DISPLAY) == self::IMPORT_ISACCO) {
            $this->processImportEan13Isacco($this->boxes);
        } else {
            foreach ($this->boxes as $id_product_attribute) {
                $this->processImportMpMassImportEan13($id_product_attribute);
            }
        }
    }

    public function processBulkImportAll()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product_attribute')
            ->from($this->table)
            ->orderBy($this->identifier);
        $boxes = $db->executeS($sql);
        $this->boxes = [];
        if ($boxes) {
            foreach ($boxes as $row) {
                $this->boxes[] = (int) $row['id_product_attribute'];
            }

            return $this->processBulkImport();
        }

        return false;
    }

    private function getAllIds()
    {
        $res = $this->db->ExecuteS('select id_mp_massimport_ean13 from ' . _DB_PREFIX_ . 'mp_massimport_ean13');
        if ($res) {
            $output = [];
            foreach ($res as $row) {
                $output[] = $row['id_mp_massimport_ean13'];
            }

            return $output;
        }

        return [];
    }

    public function displayImportLink($token, $id)
    {
        $tpl = $this->createTemplate('list_action_import.tpl');
        $tpl->assign([
            'href' => sprintf(
                '%s&token=%s&%s=%d&action=Import%s',
                self::$currentIndex,
                $this->token,
                $this->identifier,
                (int) $id,
                Tools::ucfirst(Tools::toCamelCase($this->table))
            ),
            'action' => $this->l('Import'),
            'confirm' => $this->l('Confirm import?'),
        ]);

        return $tpl->fetch();
    }

    public function processImportMpMassImportEan13($id = 0)
    {
        if (!$id) {
            $id = (int) Tools::getValue($this->identifier);
        }

        if ($this->cookies->getValue(self::CURRENT_DISPLAY) == self::IMPORT_ISACCO) {
            return $this->processImportEan13Isacco([$id]);
        }

        $db = Db::getInstance();
        $model = new ModelImportEan13($id);
        if ($model->id) {
            $json = $model->json;
            $export = new Combination($id);
            if (isset($json['reference'])) {
                $export->reference = $json['reference'];
            }
            if (isset($json['supplier_reference'])) {
                $export->supplier_reference = $json['supplier_reference'];
            }
            if (isset($json['location'])) {
                $export->location = $json['location'];
            }
            if (isset($json['wholesale_price'])) {
                $export->wholesale_price = $json['wholesale_price'];
            }
            if (isset($json['price'])) {
                $export->price = $json['price'];
            }
            if (isset($json['quantity'])) {
                $export->quantity = $json['quantity'];
            }
            if (isset($json['default_on']) && $json['default_on']) {
                $db->update(
                    'product_attribute',
                    [
                        'default_on' => false,
                    ],
                    'id_product=' . (int) $model->id_product
                );
                $export->default_on = $json['default_on'];
            }
            if (isset($json['available_date']) && $json['available_date']) {
                $export->available_date = $json['available_date'];
            }
            $res = $export->update();
            if ($res) {
                if (isset($json['quantity'])) {
                    $db->update(
                        'stock_available',
                        [
                            'quantity' => (int) $json['quantity'],
                        ],
                        'id_product=' . (int) $model->id_product . ' and id_product_attribute=' . (int) $model->id
                    );
                    $sql = new DbQuery();
                    $sql->select('sum(quantity)')
                        ->from('stock_available')
                        ->where('id_product=' . (int) $model->id_product)
                        ->where('id_product_attribute <> 0');
                    $quantity = (int) $db->getValue($sql);
                    $db->update(
                        'stock_available',
                        [
                            'quantity' => $quantity,
                        ],
                        'id_product=' . (int) $model->id_product . ' and id_product_attribute=0'
                    );
                }
                $this->confirmations[] = sprintf(
                    $this->l('Product %s %s imported. %s'),
                    $model->ean13,
                    $model->reference,
                    '<br>'
                );
                $db->delete(
                    $this->table,
                    $this->identifier . '=' . (int) $id
                );
            } else {
                $this->errors[] = sprintf(
                    $this->l('Product %s %s not imported: %s %s'),
                    $model->ean13,
                    $model->reference,
                    $db->getMsgError(),
                    '<br>'
                );
            }
        } else {
            $this->warnings[] = sprintf(
                $this->l('Can\'t read Id attribute %d'),
                $id
            );
        }
    }

    public function prettifyJson($value)
    {
        $json = json_decode($value, true);
        if (!$json) {
            return '--';
        }

        $fields = $json;
        $out = '<ul>';

        foreach ($fields as $key => $value) {
            $out .= "<li><strong>{$key}</strong>: <span class=\"text-info\">{$value}</span></li>";
        }
        $out .= '</ul>';

        return $out;
    }

    public function processImportEan13Isacco($boxes = null)
    {
        if (!$boxes) {
            $this->cookies->setValue(self::CURRENT_DISPLAY, self::IMPORT_ISACCO);
            $this->redirect_after = $this->context->link->getAdminLink($this->controllerName);
            $this->redirect();
        } else {
            foreach ($boxes as $id_product_attribute) {
                $model = new ModelImportEan13((int) $id_product_attribute);
                $ean13 = $model->ean13;
                $db = Db::getInstance();
                $res = $db->update(
                    'product_attribute',
                    [
                        'ean13' => $ean13,
                    ],
                    'id_product_attribute=' . (int) $id_product_attribute
                );
                if ($res) {
                    $this->confirmations[] = sprintf(
                        $this->l('EAN 13 %s Imported.%s'),
                        $ean13,
                        '<br>'
                    );
                    $model->delete();
                } else {
                    $this->errors[] = sprintf(
                        $this->l('EAN 13 %s NOT Imported: %s%s'),
                        $ean13,
                        $db->getMsgError(),
                        '<br>'
                    );
                }
            }
        }
    }

    public function processImportEan13()
    {
        $this->cookies->setValue(self::CURRENT_DISPLAY, self::IMPORT_EAN13);
        $this->redirect_after = $this->context->link->getAdminLink($this->controllerName);
        $this->redirect();
    }

    public function compareEan13($value, $row)
    {
        $current_ean13 = $value;
        $import_ean13 = $row['ean13'];

        if ($current_ean13 == $import_ean13) {
            $class = 'badge badge-success';
        } else {
            $class = 'badge badge-warning';
        }

        return "<span class=\"{$class}\">{$value}</span>";
    }
}
