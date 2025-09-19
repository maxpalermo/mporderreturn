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

use MpSoft\MpMassImport\Excel\XlsxWriter;
use MpSoft\MpMassImport\Forms\HelperFormFileUpload;
use MpSoft\MpMassImport\Helpers\AddProducts;
use MpSoft\MpMassImport\Helpers\CheckExtension;
use MpSoft\MpMassImport\Helpers\HelperForm;
use MpSoft\MpMassImport\Helpers\HelperTree;
use MpSoft\MpMassImport\Helpers\ImportSettings;
use MpSoft\MpMassImport\Helpers\ParseExcel;
use MpSoft\MpMassImport\Helpers\ProductExists;
use MpSoft\MpMassImport\Helpers\RowsToProducts;

class AdminMpMassImportProductsController extends ModuleAdminController
{
    use \MpSoft\MpMassImport\Traits\Cookies;
    use \MpSoft\MpMassImport\Traits\HelperFormExcelImport;
    use \MpSoft\MpMassImport\Traits\SmartyTpl;
    use MpSoft\MpMassImport\Traits\Tools;

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

    public $name;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->import_list = [];
        $this->controllerName = $this->extractClassName($this);
        $this->name = $this->extractClassName($this);

        parent::__construct();

        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;

        $a = new XlsxWriter();
        $b = new HelperForm();
        $c = new HelperTree();
    }

    public function init()
    {
        parent::init();
        $this->initHelperList();
    }

    private function initHelperList()
    {
    }

    public function renderList()
    {
        try {
            $content = parent::renderList();
            $this->cookieSetValue($this->prefix('listsql'), $this->_listsql);

            return $content;
        } catch (\Throwable $th) {
            $this->displayException(__FUNCTION__, $th->getMessage());
        }
    }

    public function initPageHeaderToolbar()
    {
        $back = $this->context->link->getAdminLink('AdminMpMassImport');
        $settings = $this->context->link->getAdminLink('AdminMpMassImportSettings');
        $update = $this->context->link->getAdminLink($this->name) . '&action=setPageImport';
        $refresh = $this->context->link->getAdminLink($this->name) . '&action=setPageExport';
        $mailReply = $this->context->link->getAdminLink($this->name) . '&action=setPageUpdate';
        $reset = $this->context->link->getAdminLink($this->name) . '&action=setPageDefault';

        $toolbar_buttons = [
            'main' => [
                'href' => $reset,
                'desc' => $this->l('Main page'),
                'icon' => 'process-icon-menu text-black',
            ],
            'importProducts' => [
                'href' => $update,
                'desc' => $this->l('Import'),
                'icon' => 'process-icon-download',
            ],
            'exportProducts' => [
                'href' => $refresh,
                'desc' => $this->l('Export'),
                'icon' => 'process-icon-upload',
            ],
            'updateProducts' => [
                'href' => $mailReply,
                'desc' => $this->l('Update'),
                'icon' => 'process-icon-refresh',
            ],
            'cogs' => [
                'href' => $settings,
                'desc' => $this->l('Settings'),
            ],
            'back' => [
                'href' => $back,
                'desc' => $this->l('Back to list'),
            ],
        ];

        switch ($this->getCurrentPage()) {
            case 'ExportFeatures':
                unset($toolbar_buttons['exportFeatures']);

                break;
            case 'ExportProductFeatures':
                unset($toolbar_buttons['exportProductFeatures']);

                break;
            case 'ImportFeatures':
                unset($toolbar_buttons['importFeatures']);

                break;
            case 'ImportProductFeatures':
                unset($toolbar_buttons['importProductFeatures']);

                break;
            default:
                // Nothing
                break;
        }

        $this->page_header_toolbar_btn = $toolbar_buttons;

        parent::initPageHeaderToolbar();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
        $this->toolbar_btn = [
            'back' => [
                'href' => $this->context->link->getAdminLink('AdminMpMassImport'),
                'desc' => $this->l('Return to main menu'),
            ],
        ];
    }

    public function initContent()
    {
        $page = Tools::strtolower($this->getCurrentPage());
        switch ($page) {
            case 'import':
                $this->table = 'mp_massimport_product';
                $this->identifier = 'id_product';
                $this->className = 'ModelImportProduct';
                $this->list_id = $this->table;
                $this->_defaultOrderBy = $this->identifier;
                $this->_defaultOrderWay = 'ASC';
                $this->_select = 'id_product as thumb';
                $this->addRowAction('import');
                $this->addRowAction('delete');

                $this->bulk_actions = [
                    'import' => [
                        'text' => $this->l('Import Selected Products'),
                        'confirm' => $this->l('Import selected items?'),
                        'icon' => 'icon-download',
                    ],
                    'import_all' => [
                        'text' => $this->l('Import All Products'),
                        'confirm' => $this->l('Import all Products?'),
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
                $this->fields_list = $this->getFieldsListImport();
                $this->content = $this->generateFormImport();

                break;
            case 'export':
                break;
            case 'update':
                break;
            default:
                $this->fields_form = [];
                $this->fields_list = [];
                $this->content = '';
        }

        parent::initContent();
    }

    protected function getFieldsListImport()
    {
        return [
            'id_product' => [
                'title' => $this->l('Id'),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => false,
            ],
            'thumb' => [
                'title' => $this->l('Image'),
                'type' => 'bool',
                'float' => true,
                'size' => 64,
                'align' => 'text-center',
                'search' => false,
                'callback' => 'getThumb',
                'callback_object' => $this->name,
            ],
            'reference' => [
                'title' => $this->l('Reference'),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!reference',
                'callback' => 'referenceExists',
                'callback_object' => $this->name,
            ],
            'manufacturer' => [
                'title' => $this->l('Manufacturer'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!manufacturer',
                'callback' => 'displayManufacturer',
                'callback_object' => $this->name,
            ],
            'product_name' => [
                'title' => $this->l('Name'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!product_name',
            ],
            'wholesale_price' => [
                'title' => $this->l('Buy Price'),
                'type' => 'price',
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!wholesale_price',
            ],
            'price' => [
                'title' => $this->l('Price'),
                'type' => 'price',
                'float' => true,
                'size' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!price',
                'callback' => 'addVat',
                'callback_object' => $this->name,
            ],
            'available_date' => [
                'title' => $this->l('Available Date'),
                'type' => 'date',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!available_date',
            ],
        ];
    }

    public function generateFormImport()
    {
        $settingForm = new ImportSettings($this);
        $form = new HelperFormFileUpload($this);
        $modalSettings = $settingForm->getFormSettings();

        return $form->renderForm() . $modalSettings;
    }

    /* POST PROCESS ACTIONS */
    public function postProcess()
    {
        parent::postProcess();

        $matches = [];
        if (preg_match('/^setPage(.*)/i', Tools::getValue('action'), $matches)) {
            $currentPage = $matches[1];
            $this->setCurrentPage($currentPage);
            Tools::redirectAdmin($this->context->link->getAdminLink($this->name));
            exit();
        }

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

            $rows = ParseExcel::parse($file['content'], 'Products');
            $products = RowsToProducts::parse($rows);
            AddProducts::addToTable($products);
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
        $sql->select(ModelImportProduct::$definition['primary'])
            ->from(ModelImportProduct::$definition['table']);
        $rows = $db->executeS($sql);
        $output = [];
        if ($rows) {
            foreach ($rows as $row) {
                $output[] = $row[ModelImportProduct::$definition['primary']];
            }
        }

        return $output;
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
            $item = new ModelImportProduct($box);
            if ($item->product_name) {
                $result = AddProducts::addToProducts($item);
                if ($result) {
                    $this->confirmations[] = sprintf(
                        $this->l('Product %s %s imported.'),
                        '<strong>' . $item->reference . '</strong>',
                        '<strong>' . $item->product_name . '</strong>'
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
            return;
        }
        $obj = new ModelImportProduct((int) $id);
        if (isset($obj->json['images']) && count($obj->json['images'])) {
            $path = $obj->json['images'][0];

            return '<img src="' . $path . '" style="width: 96px; height: 96px; object-fit: contain;">';
        }

        return '<img src="/img/404.gif" style="width: 96px; height: 96px; object-fit: contain;">';
    }

    public function displayManufacturer($manufacturer)
    {
        return Tools::strToUpper($manufacturer);
    }

    public function referenceExists($reference)
    {
        if (ProductExists::productExistsByReference($reference)) {
            return "<span class='badge badge-info'>$reference</span>";
        }

        return $reference;
    }

    public function addVat($value, $row)
    {
        // Tools::dieObject($row);
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
        // return '<span class="badge badge-warning">'.Tools::displayPrice($value).'</span>';
    }

    public function ajaxProcessUpdateImportSettings()
    {
        $values = Tools::getValue('values');
        foreach ($values as $value) {
            Configuration::updateValue($value['name'], $value['value']);
        }
        die($this->module->l('Settings updated', $this->controllerName));
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
            'confirm' => $this->l('Confirm Import?'),
        ]);

        return $tpl->fetch();
    }

    public function processImportMpMassimportProduct()
    {
        $id = (int) Tools::getValue($this->identifier);
        $product = new ModelImportProduct($id);
        if ($product->id) {
            $this->boxes = [$id];
            $this->processBulkImport();
        }
    }
}
