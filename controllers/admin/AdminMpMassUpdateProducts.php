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

use MpSoft\MpMassImport\Deprecated\MpUtilities;
use MpSoft\MpMassImport\Excel\XlsxReader;
use MpSoft\MpMassImport\FieldsList\FieldsListProducts;
use MpSoft\MpMassImport\FieldsList\FieldsListUpdateProducts;
use MpSoft\MpMassImport\Forms\HelperFormFileUpload;
use MpSoft\MpMassImport\Helpers\AddProducts;
use MpSoft\MpMassImport\Helpers\CheckExtension;
use MpSoft\MpMassImport\Helpers\Cookies;
use MpSoft\MpMassImport\Helpers\ParseExcel;
use MpSoft\MpMassImport\Helpers\ProductExists;
use MpSoft\MpMassImport\Helpers\RowsToProducts;

class AdminMpMassUpdateProductsController extends ModuleAdminController
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

    protected $tools;

    protected $cookies;
    protected $allowFields = [
        'id_product',
        'id_manufacturer',
        'id_supplier',
        'id_tax_rules_group',
        'id_category_default',
        'reference',
        'ean13',
        'supplier_reference',
        'name',
        'description',
        'description_short',
        'link_rewrite',
        'condition',
        'wholesale_price',
        'price',
    ];
    public $module;

    public function __construct()
    {
        $context = Context::getContext();
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->adminClassName = 'AdminMpMassUpdateProducts';
        $this->className = 'MpModelUpdateProduct';
        $this->import_list = [];
        $this->db = Db::getInstance();
        $this->tools = new MpUtilities();
        $this->id_lang = (int) $context->language->id;
        $this->id_shop = (int) $context->shop->id;
        $this->link = $context->link;
        $this->bootstrap = true;
        $this->adminClassName = 'AdminMpMassUpdateProducts';
        $this->cookies = new Cookies();
        $this->initHelperList();

        parent::__construct();
    }

    private function initHelperList()
    {
        $this->table = 'mp_massimport_update_product';
        $this->identifier = 'id_product';
        $this->fields_list = (new FieldsListUpdateProducts($this))->getFieldsList();
        $this->_select = 'id_product as thumb';

        $this->bulk_actions = [
            'import' => [
                'text' => $this->module->l('Import Products', $this->controller_name),
                'confirm' => $this->module->l('Import selected items?', $this->controller_name),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->module->l('Import all filtered products', $this->controller_name),
                'confirm' => $this->module->l('Import all product from current list?', $this->controller_name),
                'icon' => 'icon-download text-info',
            ],
            'divider000' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->module->l('Delete from list', $this->controller_name),
                'confirm' => $this->module->l('Delete selected items from list?', $this->controller_name),
                'icon' => 'icon-trash text-danger',
            ],
            'divider001' => [
                'text' => 'divider',
            ],
        ];
    }

    public function setMedia()
    {
        $this->addCSS(
            $this->module->getLocalPath() . 'views/css/process-icon.css'
        );

        return parent::setMedia();
    }

    public function renderList()
    {
        try {
            $content = parent::renderList();
            $this->cookies->setValue('listsql', $this->_listsql);

            return $content;
        } catch (\Throwable $th) {
            Tools::dieObject('RenderList ERROR:', 0);
            Tools::dieObject($th->getMessage(), 0);
            Tools::dieObject('Query:', 0);
            Tools::dieObject($this->_listsql);
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
        $this->toolbar_btn = [
            'back' => [
                'href' => $this->link->getAdminLink('AdminMpMassImport'),
                'desc' => $this->module->l('Return to main menu', $this->controller_name),
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

    public function getFieldsList()
    {
        return [
            'thumb' => [
                'title' => $this->module->l('Image', $this->controller_name),
                'type' => 'bool',
                'float' => true,
                'width' => 64,
                'align' => 'text-center',
                'search' => false,
                'callback' => 'getThumb',
            ],
            'id_product' => [
                'title' => $this->module->l('Id', $this->controller_name),
                'type' => 'text',
                'width' => 64,
                'align' => 'text-right',
                'search' => false,
            ],
            'reference' => [
                'title' => $this->module->l('Reference', $this->controller_name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!reference',
            ],
            'name' => [
                'title' => $this->module->l('Name', $this->controller_name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!name',
            ],
            'ean13' => [
                'title' => $this->module->l('Ean13', $this->controller_name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!ean13',
            ],
            'price' => [
                'title' => $this->module->l('Price', $this->controller_name),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!price',
                'callback' => 'setPrice',
            ],
            'json' => [
                'title' => $this->module->l('Fields', $this->controller_name),
                'type' => 'bool',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
                'callback' => 'getJson',
            ],
        ];
    }

    public function postProcess()
    {
        parent::postProcess();
        /**
         * PARSE AND IMPORT EXCEL FILE
         */
        if (Tools::isSubmit('submitImportExcel')) {
            $file = Tools::fileAttachment('uploadfile');
            if (!$file) {
                $this->errors[] = $this->module->l('Please select an Excel file.', $this->controller_name);

                return false;
            }
            $rows = $this->parseExcel($file['content'], 'Products');
            MpModelUpdateProduct::truncate();
            if ($rows) {
                foreach ($rows as $row) {
                    if (isset($row['id_product'])) {
                        $id_product = (int) $row['id_product'];
                        if ($id_product) {
                            $product = new Product($id_product, true, $this->id_lang, $this->id_shop);
                            $model = new MpModelUpdateProduct(0, $this->id_lang, $this->id_shop);
                            $model->force_id = true;
                            $model->id = $id_product;
                            $model->reference = isset($row['reference']) ? $row['reference'] : '';
                            $model->ean13 = isset($row['ean13']) ? $row['ean13'] : '';
                            $product_name = isset($row['product_name']) ? $row['product_name'] : '';
                            $name = isset($row['name']) ? $row['name'] : '';
                            if ($product_name && $name) {
                                $model->name = $name;
                            } elseif ($product_name) {
                                $model->name = $product_name;
                            } elseif ($name) {
                                $model->name = $name;
                            } else {
                                $model->name = '';
                            }
                            if (isset($row['price']) && $row['price']) {
                                $model->price = number_format((float) $row['price'], 6);
                            } else {
                                $model->price = 0;
                            }

                            $json = [];
                            foreach ($this->allowFields as $field) {
                                if (isset($row[$field]) && $row[$field]) {
                                    $json[$field] = $row[$field];
                                }
                                if (isset($json['price']) && $json['price']) {
                                    $json['price'] = number_format((float) $json['price'], 6);
                                }
                            }
                            $model->json = json_encode($json);

                            try {
                                $res = $model->add();
                                if ($res) {
                                    $this->confirmations[] =
                                        '<p>' .
                                        sprintf(
                                            $this->module->l('Product %s %s inserted', $this->controller_name),
                                            $product->reference,
                                            $product->name
                                        ) .
                                        '</p>';
                                } else {
                                    $this->errors[] =
                                        '<p>' .
                                        sprintf(
                                            $this->module->l('Product %s %s not inserted: error %s', $this->controller_name),
                                            $product->reference,
                                            $product->name,
                                            Db::getInstance()->getMsgError()
                                        ) .
                                        '</p>';
                                }
                            } catch (\Throwable $th) {
                                $this->errors[] =
                                    '<p>' .
                                    sprintf(
                                        $this->module->l('Product %s %s not inserted: error %s', $this->controller_name),
                                        $product->reference,
                                        $product->name,
                                        $th->getMessage()
                                    ) .
                                    '</p>';
                            }
                        }
                    }
                }
            }
            $this->confirmations[] = '<h1>' . $this->module->l('Operation done.', $this->controller_name) . '</h1>';
        }
    }

    protected function getIdTaxRulesGroup($name)
    {
        $sql = 'select id_tax_rules_group from ' . _DB_PREFIX_ . 'tax_rules_group '
            . "where name like '" . pSQL($name) . "' and deleted = 0";

        return (int) $this->db->getValue($sql);
    }

    protected function parseExcel($content, $worksheet = '')
    {
        $output = [];
        $xlsx = XlsxReader::parse($content, true);
        $rows = null;
        if ($worksheet) {
            foreach ($xlsx->sheetNames() as $key => $value) {
                if ($value == $worksheet) {
                    $rows = $xlsx->rows($key);
                }
            }
        } else {
            $rows = $xlsx->rows(0);
        }
        if (count($rows) > 1) {
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $output[] = array_combine($header, $row);
            }
        }
        //Tools::dieObject($output);
        return $output;
    }

    public function processBulkImport()
    {
        return $this->importProducts(false);
    }

    public function processBulkImportAll()
    {
        $this->boxes = $this->getAllProducts();

        return $this->importProducts(true);
    }

    private function getAllProducts()
    {
        $sql = $this->tools->getCookie('listsql');
        $sql = substr($sql, 0, strpos($sql, 'LIMIT'));
        $rows = $this->db->executeS($sql);
        $output = [];
        if ($rows) {
            foreach ($rows as $row) {
                $output[] = $row['id_product'];
            }
        }

        return $output;
    }

    private function importProducts()
    {
        if (!is_array($this->boxes)) {
            return false;
        }
        if (count($this->boxes) == 0) {
            $this->warnings[] = $this->module->l('Please select at least one product.', $this->controller_name);

            return false;
        }
        foreach ($this->boxes as $box) {
            $id_product = (int) $box;
            $model = new MpModelUpdateProduct($id_product, $this->id_lang, $this->id_shop);
            $product = new Product($model->id, false, $this->id_lang, $this->id_shop);
            foreach ($model->json as $key => $value) {
                $product->$key = $value;
            }

            try {
                $res = $product->update();
                if ($res) {
                    $this->confirmations[] =
                        '<p>' .
                        sprintf(
                            $this->module->l('Product %s %s updated', $this->controller_name),
                            $product->reference,
                            $product->name
                        ) .
                        '</p>';
                    $model->delete();
                } else {
                    $this->errors[] =
                        '<p>' .
                        sprintf(
                            $this->module->l('Product %s %s not updated: error %s', $this->controller_name),
                            $product->reference,
                            $product->name,
                            Db::getInstance()->getMsgError()
                        ) .
                        '</p>';
                }
            } catch (\Throwable $th) {
                $this->errors[] =
                    '<p>' .
                    sprintf(
                        $this->module->l('Product %s %s not updated: error %s', $this->controller_name),
                        $product->reference,
                        $product->name,
                        $th->getMessage()
                    ) .
                    '</p>';
            }
        }
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new MpModelUpdateProduct($box);
            $item->delete();
        }
        $this->confirmations[] = $this->module->l('Operation done.', $this->controller_name);
    }

    public function getJson($json)
    {
        $output = '<ul>';
        $json = json_decode($json, true);
        foreach ($json as $key => $value) {
            $output .= "<li><i>$key</i> : <span class=\"text-primary\">$value</span></li>";
        }
        $output .= '</ul>';

        return $output;
    }

    public function getThumb($id, $row)
    {
        $id_product = (int) $row['id_product'];
        if (!$id_product) {
            return '--';
        }
        $id_cover = Product::getCover($id_product);
        $product = new Product($id_product, false, $this->id_lang, $this->id_shop);
        $link = Context::getContext()->link;
        $imagePath = $link->getImageLink($product->link_rewrite[$this->id_lang], $id_cover['id_image'], 'home_default');
        $href = Context::getContext()->link->getAdminLink('AdminProducts') . '&updateproduct&id_product=' . $id_product;

        return '<a href="' . $href . '" target="_blank"><img src="' . $imagePath . '" style="width: 64px; height: auto;"></a>';
    }

    public function displayManufacturer($manufacturer)
    {
        return Tools::strToUpper($manufacturer);
    }

    public function referenceExists($reference)
    {
        if ($this->tools->productExists($reference)) {
            return "<span class='badge badge-info'>$reference</span>";
        }

        return $reference;
    }

    public function setPrice($value)
    {
        return number_format($value, 6);
    }
}
