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
require_once _PS_MODULE_DIR_ . 'mpmassimport/src/Models/AutoloadModels.php';

use MpSoft\MpMassImport\FieldsList\FieldsListPrices;
use MpSoft\MpMassImport\Helpers\AddPrices;
use MpSoft\MpMassImport\Helpers\CheckExtension;
use MpSoft\MpMassImport\Helpers\Cookies;
use MpSoft\MpMassImport\Forms\HelperFormFileUpload;
use MpSoft\MpMassImport\Helpers\ImageProduct;
use MpSoft\MpMassImport\Helpers\ParseExcel;
use MpSoft\MpMassImport\Helpers\RowsToPrices;

class AdminMpMassImportPricesController extends ModuleAdminController
{
    /** @var Db */
    protected $db;

    /** @var string */
    public $className;

    /** @var string */
    protected $adminClassName;

    /** @var array */
    protected $import_list;

    /** @var int */
    protected $id_lang;

    /** @var int */
    protected $id_shop;

    /** @var Link */
    protected $link;

    public function __construct()
    {
        $this->import_list = [];
        $this->db = Db::getInstance();
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->link = $this->context->link;
        $this->bootstrap = true;
        $this->className = 'ModelImportPrice';
        $this->adminClassName = 'AdminMpMassImportPrices';
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->initHelperList();

        parent::__construct();
    }

    private function initHelperList()
    {
        $this->table = 'mp_massimport_price';
        $this->identifier = 'id_product';
        $this->fields_list = (new FieldsListPrices($this))->getFieldsList();
        $this->_select = "id_product as thumb, '--' as manufacturer";

        $this->bulk_actions = [
            'import' => [
                'text' => $this->l('Import Selected Prices'),
                'confirm' => $this->l('Import selected items?'),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->l('Import All Prices'),
                'confirm' => $this->l('Import all Prices in table?'),
                'icon' => 'icon-download text-danger',
            ],
            'divider000' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->l('Delete from list'),
                'confirm' => $this->l('Delete selected items from list?'),
                'icon' => 'icon-trash text-warning',
            ],
            'delete_all' => [
                'text' => $this->l('Clear table'),
                'confirm' => $this->l('Clear table?'),
                'icon' => 'icon-trash text-danger',
            ],
            'divider001' => [
                'text' => 'divider',
            ],
        ];
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/process-icon.css');

        return parent::setMedia();
    }

    public function renderList()
    {
        try {
            $content = parent::renderList();
            $cookies = new Cookies();
            $cookies->setValue('listsql', $this->_listsql);

            return $content;
        } catch (\Throwable $th) {
            print '<pre>';
            print "\n<h1>RenderList</h1>";
            print "ERROR:<p style='color: red;'>" . $th->getMessage() . '</p>';
            $query = str_replace("\t", '', $this->_listsql);
            $query = str_replace("\n", ' ', $query);
            $query = str_replace("\r", ' ', $query);
            $query = str_replace('  ', ' ', $query);
            print "QUERY:<p style='color: blue;'>$query</p>";
            print '<hr>';
            print '<p>' . print_r(debug_print_backtrace(), 1) . '</p>';
            print '</pre>';
            die();
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
        $this->toolbar_btn = [
            'back' => [
                'href' => $this->link->getAdminLink('AdminMpMassImport'),
                'desc' => $this->l('Return to main menu'),
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

    /* POST PROCESS ACTIONS */
    public function postProcess()
    {
        parent::postProcess();
        /**
         * PARSE AND IMPORT EXCEL FILE
         */
        if (Tools::isSubmit('submitImportExcel')) {
            $file = Tools::fileAttachment('uploadfile');
            if (!$file) {
                $this->errors[] = $this->l('Please select an Excel file.');

                return false;
            }
            if (!CheckExtension::check($file['name'], 'xlsx')) {
                $this->errors[] = $this->l('File format not valid.');

                return false;
            }

            $rows = ParseExcel::parse($file['content'], 'Prices');
            $products = RowsToPrices::parse($rows);
            //Tools::dieObject($products);
            AddPrices::addToTable($products);
        }
    }

    public function processBulkImport()
    {
        return $this->importProducts();
    }

    public function processBulkImportAll()
    {
        $this->boxes = $this->getAllRecords();

        return $this->processBulkImport();
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new MpMassImportProduct($box);
            $item->delete();
        }
        $this->confirmations[] = sprintf(
            '%s%s%s',
            '<h1>',
            sprintf($this->l('Operation done. Removed %d items.'), count($this->boxes)),
            '</h1>'
        );
    }

    public function processBulkDeleteAll()
    {
        $this->boxes = $this->getAllRecords();

        return $this->processBulkDelete();
    }

    private function getAllRecords()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(ModelImportPrice::$definition['primary'])
            ->from(ModelImportPrice::$definition['table']);
        $rows = $db->executeS($sql);
        $output = [];
        if ($rows) {
            foreach ($rows as $row) {
                $output[] = $row[ModelImportPrice::$definition['primary']];
            }
        }

        return $output;
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return $this->module->l($string, $this->adminClassName);
    }

    private function importProducts($force = false)
    {
        if (!is_array($this->boxes)) {
            $this->errors[] = $this->l('Selection not valid.');

            return false;
        }
        if (count($this->boxes) == 0) {
            $this->warnings[] = $this->l('Please select at least one product.');

            return false;
        }

        foreach ($this->boxes as $box) {
            $item = new ModelImportPrice($box);
            if ($item->id) {
                $result = AddPrices::addToPrices($item);
                if ($result) {
                    $this->confirmations[] = sprintf(
                        $this->l('Product %s %s imported.'),
                        '<strong>' . $item->reference . '</strong>',
                        '<strong>' . $item->name . '</strong>'
                    );
                    $item->delete();
                } else {
                    $this->errors[] = sprintf(
                        $this->l('Error %s import product with id %d'),
                        $this->db->getMsgError(),
                        "<strong>$box</strong>"
                    );
                }
            }
        }
    }

    public function getThumb($id)
    {
        if (!(int) $id) {
            return '<img src="/img/404.gif" style="width: 96px; height: 96px; object-fit: contain;">';
        }
        $path = ImageProduct::getImageCoverProduct($id);

        return $path;
    }

    public function displayManufacturer($manufacturer, $row)
    {
        $reference = $row['reference'];
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('m.name')
            ->from('manufacturer', 'm')
            ->leftJoin('product', 'p', 'p.id_manufacturer=m.id_manufacturer');
        $manufacturer = $db->getValue($sql);

        return Tools::strToUpper($manufacturer);
    }

    public function getManufacturer($value, $row)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('m.name')
            ->from('manufacturer', 'm')
            ->innerJoin('product', 'p', 'p.id_manufacturer=m.id_manufacturer')
            ->where('p.id_product=' . $row['id_product']);

        return $db->getValue($sql);
    }

    public function addVat($value, $row)
    {
        //Tools::dieObject($row);
        $tax_rate = 22;
        /*
        if (isset($row['reference']) && $row['reference']) {
            $reference = pSQL($row['reference']);
            $db = Db::getInstance();
            $sql = new DbQuery();
            $sql->select('id_product')
                ->from('product')
                ->where('reference = \''.$reference.'\'');
            $id_product = (int)$db->getValue($sql);
            if ($id_product) {
                $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct($id_product);
                $sql = new DbQuery();
                $sql->select('c.rate')
                    ->from('tax_rules_group', 'a')
                    ->innerJoin('tax_rule', 'b', 'b.id_tax_rules_group=a.id_tax_rules_group')
                    ->innerJoin('tax', 'c', 'c.id_tax=b.id_tax')
                    ->where('a.id_tax_rules_group='.(int)$id_tax_rules_group);
                $tax_rate = (float)$db->getValue($sql);
            }
        }
        */
        $price_ti = $value * (100 + $tax_rate) / 100;

        return Tools::displayPrice($price_ti);
        //return '<span class="badge badge-warning">'.Tools::displayPrice($value).'</span>';
    }
}
