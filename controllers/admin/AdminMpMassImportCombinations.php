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

use MpSoft\MpMassImport\FieldsList\FieldsListCombinations;
use MpSoft\MpMassImport\Helpers\AddCombinations;
use MpSoft\MpMassImport\Helpers\CheckExtension;
use MpSoft\MpMassImport\Helpers\Cookies;
use MpSoft\MpMassImport\Forms\HelperFormFileUpload;
use MpSoft\MpMassImport\Helpers\ImageProduct;
use MpSoft\MpMassImport\Helpers\ParseExcel;
use MpSoft\MpMassImport\Helpers\RowsToCombinations;

class AdminMpMassImportCombinationsController extends ModuleAdminController
{/** @var Db */
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
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->link = $this->context->link;
        $this->db = Db::getInstance();
        $this->bootstrap = true;
        $this->className = 'MpMassImportCombination';
        $this->table = 'mp_massimport_combination';
        $this->identifier = 'id_product_attribute';
        $this->adminClassName = 'AdminMpMassImportCombinations';
        $this->bulk_actions = [];
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->initHelperList();

        parent::__construct();
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

    private function initHelperList()
    {
        $this->fields_list = (new FieldsListCombinations($this))->getFieldsList();

        $this->bulk_actions = [
            'import' => [
                'text' => $this->l('Import Combination'),
                'confirm' => $this->l('Import selected items?'),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->l('Import all'),
                'confirm' => $this->l('Import all combinations?'),
                'icon' => 'icon-download text-danger',
            ],
            'divider000' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items from list?'),
                'icon' => 'icon-trash text-danger',
            ],
            'delete_all' => [
                'text' => $this->l('Delete from list'),
                'confirm' => $this->l('Delete selected items from list?'),
                'icon' => 'icon-trash text-danger',
            ],
            'divider001' => [
                'text' => 'divider',
            ],
        ];
    }

    public function getProductName($value, $row)
    {
        $id_product = (int) $row['id_product'];
        $product = new Product($id_product, false, $this->id_lang, $this->id_shop);

        return $product->name;
    }

    public function getAttributes($value, $row)
    {
        $json = json_decode($row['json'], true);
        $attributes = $json['attributes'];
        $output = [];
        foreach ($attributes as $key => $value) {
            $group = explode(':', $key)[0];
            $items = [];
            foreach ($value as $item) {
                $attribute = explode(':', $item)[0];
                $items[] = $attribute;
            }
            $output[] = strtoupper($group) . ': <strong>' . implode(',', $items) . '</strong>';
            unset($items);
        }

        return implode('<br>', $output);
    }

    public function displayDefaultOn($value)
    {
        if ((int) $value) {
            return '<i class="icon icon-check text-success"></i>';
        }

        return '<span></span>';
    }

    public function setMedia()
    {
        $this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/process-icon.css');

        return parent::setMedia();
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

            $rows = ParseExcel::parse($file['content'], 'Combinations');
            $combinations = RowsToCombinations::parse($rows);
            AddCombinations::addToTable($combinations);
        }
    }

    /**
     *
     * Import Combinations into Prestashop Database
     *
     * @return void
     *
     **/
    public function processBulkImport()
    {
        $this->importCombinations();
    }

    public function processBulkImportAll()
    {
        $this->boxes = $this->getAllRecords();
        $this->processBulkImport();
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new MpMassImportCombination($box);
            $item->delete();
        }
        $this->confirmations[] = $this->l('Operation done.');
    }

    public function processBulkDeleteAll()
    {
        foreach ($this->boxes as $box) {
            $item = new MpMassImportCombination($box);
            $item->delete();
        }
        $this->confirmations[] = $this->l('Operation done.');
    }

    private function getAllRecords()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(ModelImportCombination::$definition['primary'])
            ->from(ModelImportCombination::$definition['table']);
        $rows = $db->executeS($sql);
        $output = [];
        if ($rows) {
            foreach ($rows as $row) {
                $output[] = $row[ModelImportCombination::$definition['primary']];
            }
        }

        return $output;
    }

    private function importCombinations()
    {
        foreach ($this->boxes as $box) {
            AddCombinations::addToCombinations($box);
        }
        $this->confirmations = sprintf(
            '%s%s%s',
            '<h2>',
            $this->module->l('Operation done', $this->adminClassName),
            '</h2>'
        );
    }

    /**
     *
     * Parse Excel file to prepare import combinations
     *
     * @param  array $file The file parameters from Tools::fileAttachment()
     * @return void
     *
     **/
    protected function importExcel($file)
    {
        $reader = new MpMassImporterExcelCombination($this);
        $reader->importExcel($file);
    }
}
