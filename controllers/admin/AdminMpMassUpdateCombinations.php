<?php
/**
 * 2017 mpSOFT
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
 *  @copyright 2021 Massimiliano Palermo
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of mpSOFT
 */

require_once _PS_MODULE_DIR_ . 'mpmassimport/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/models/autoload.php';
require_once _PS_MODULE_DIR_.'mpmassimport/classes/MpUtilities.php';
require_once _PS_MODULE_DIR_.'mpmassimport/classes/MpFormImport.php';
require_once _PS_MODULE_DIR_.'mpmassimport/vendor/excel/XlsxReader.php';

use mpmassimport\vendor\excel\XlsxReader;

class AdminMpMassUpdateCombinationsController extends ModuleAdminController
{
    private $db;
    private $tools;
    private $allowFields = [
        'id_product_attribute',
        'id_product',
        'reference',
        'supplier_reference',
        'location',
        'ean13',
        'upc',
        'wholesale_price',
        'price',
        'ecotax',
        'quantity',
        'weight',
        'unit_price_impact',
        'default_on',
        'minimal_quantity',
        'available_date',
    ];
    public $module;

    public function __construct()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {  
            $action = "getTranslator";
    		$this->translator = Context::getContext()->$action();
        }

        $this->module = Module::getInstanceByName('mpmassimport');
        $this->name = 'AdminMpMassUpdateCombinations';
        $context = Context::getContext();
        $this->import_list = array();
        $this->db = Db::getInstance();
        $this->tools = new MpUtilities();
        $this->id_lang = (int)$context->language->id;
        $this->id_shop = (int)$context->shop->id;
        $this->link = $context->link;
        $this->bootstrap = true;
        $this->className = 'MpModelUpdateCombination';
        $this->adminClassName = 'AdminMpMassUpdateCombinations';
        $this->initHelperList();
        
        parent::__construct();
    }

    public function setHelperDisplay(Helper $helper)
    {
        $helper->force_show_bulk_actions = true;
        $this->list_no_link = true;
        parent::setHelperDisplay($helper);
    }

    private function initHelperList()
    {
        $this->table = 'mp_massimport_update_combination';
        $this->identifier = 'id_product_attribute';
        $this->fields_list = $this->getFieldsList();
        $this->_select = "id_product as thumb";

        $this->bulk_actions = array(
            'import' => array(
                'text' => $this->module->l('Import Combinations', $this->name),
                'confirm' => $this->module->l('Import selected items?', $this->name),
                'icon' => 'icon-download'
            ),
            'import_all' => array(
                'text' => $this->module->l('Import all filtered combinations', $this->name),
                'confirm' => $this->module->l('Import all combinations from current list?', $this->name),
                'icon' => 'icon-download text-info'
            ),
            'divider000' => array(
                'text' => 'divider',
            ),
            'delete' => array(
                'text' => $this->module->l('Delete from list', $this->name),
                'confirm' => $this->module->l('Delete selected items from list?', $this->name),
                'icon' => 'icon-trash text-danger'
            ),
            'divider001' => array(
                'text' => 'divider',
            ),
        );
    }

    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addCSS(
            $this->context->controller->module->getLocalPath().'views/css/process-icon.css'
        );
        return parent::setMedia($isNewTheme);
    }

    public function renderList()
    {
        try {
            $content = parent::renderList();
            $this->tools->setCookie('listsql', $this->_listsql);
            return $content;
        } catch (\Throwable $th) {
            Tools::dieObject("RenderList ERROR:", 0);
            Tools::dieObject($th->getMessage(), 0);
            Tools::dieObject("Query:", 0);
            Tools::dieObject($this->_listsql);
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
        $this->toolbar_btn = array(
            'back' => array(
                'href' => $this->link->getAdminLink('AdminMpMassImport'),
                'desc' => $this->module->l('Return to main menu', $this->name),
            ),
        );
    }

    public function initContent()
    {
        $this->content = $this->generateFormImport();
        parent::initContent();
    }

    public function generateFormImport()
    {
        $form = new MpFormImport();
        return $form->renderForm(
            $this->module->l('Import Excel Combinations', $this->name),
            $this->table,
            $this->identifier,
            $this->adminClassName,
            $this->getLanguages()
        );
    }

    public function getFieldsList()
    {
        return array(
            'thumb' => array(
                'title' => $this->module->l('Image', $this->name),
                'type' => 'bool',
                'float' => true,
                'width' => 64,
                'align' => 'text-center',
                'search' => false,
                'callback' => 'getThumb',
            ),
            'id_product_attribute' => array(
                'title' => $this->module->l('Id', $this->name),
                'type' => 'text',
                'width' => 64,
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!id_product_attribute',
            ),
            'reference' => array(
                'title' => $this->module->l('Reference', $this->name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!reference',
            ),
            'name' => array(
                'title' => $this->module->l('Name', $this->name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!name',
            ),
            'ean13' => array(
                'title' => $this->module->l('Ean13', $this->name),
                'type' => 'text',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!ean13',
            ),
            'price' => array(
                'title' => $this->module->l('Price', $this->name),
                'type' => 'price',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!price',
            ),
            'unit_price_impact' => array(
                'title' => $this->module->l('Impact', $this->name),
                'type' => 'price',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!unit_price_impact',
            ),
            'default_on' => array(
                'title' => $this->module->l('Default on', $this->name),
                'type' => 'bool',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!default_on',
                'callback' => 'htmlCheck',
            ),
            'json' => array(
                'title' => $this->module->l('Fields', $this->name),
                'type' => 'bool',
                'float' => true,
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
                'callback' => 'getJson',
            ),
        );
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
                $this->errors[] = $this->module->l('Please select an Excel file.', $this->name);
                return false;
            }
            $rows = $this->parseExcel($file['content'], 'Combinations');
            MpModelUpdateCombination::truncate();
            if ($rows) {
                foreach ($rows as $row) {
                    if (isset($row['id_product_attribute'])) {
                        $id_product_attribute = (int)$row['id_product_attribute'];
                        $id_product = $this->getProductId($id_product_attribute);
                        $name = $this->getProductName($id_product);

                        if ($id_product_attribute) {
                            $model = new MpModelUpdateCombination(0, $this->id_lang, $this->id_shop);
                            $model->force_id = true;
                            $model->id = $id_product_attribute;
                            $model->id_product = $id_product;
                            $model->reference = isset($row['reference'])?$row['reference']:"";
                            $model->ean13 = isset($row['ean13'])?$row['ean13']:"";
                            $model->name = $name;
                            if (isset($row['price']) && $row['price']) {
                                $model->price = number_format((float)$row['price'], 6);
                            } else {
                                $model->price = -1;
                            }
                            if (isset($row['unit_price_impact']) && $row['unit_price_impact']) {
                                $model->unit_price_impact = number_format((float)$row['unit_price_impact'], 6);
                            } else {
                                $model->unit_price_impact = 0;
                            }
                            $model->quantity = isset($row['quantity'])?$row['quantity']:0;
                            $model->default_on = isset($row['default_on'])?(int)$row['default_on']:"";
                            $json = [];
                            foreach ($this->allowFields as $field) {
                                if (isset($row[$field]) && $row[$field]) {
                                    $json[$field] = $row[$field];
                                }
                            }

                            if (isset($json['price'])) {
                                $json['price'] = number_format($json['price'], 6);
                            }
                            if (isset($json['unit_price_impact'])) {
                                $json['unit_price_impact'] = number_format($json['unit_price_impact'], 6);
                            }
                            if (isset($json['wholesale_price'])) {
                                $json['wholesale_price'] = number_format($json['wholesale_price'], 6);
                            }
                            
                            //Tools::dieObject($json);
                            $model->json = json_encode($json);
                            try {
                                $res = $model->add();
                                if ($res) {
                                    $this->confirmations[] = 
                                        "<p>".
                                        sprintf(
                                            $this->module->l('Combination %s %s inserted', $this->name),
                                            $model->reference,
                                            $name
                                        ).
                                        "</p>";
                                } else {
                                    $this->errors[] = 
                                        "<p>".
                                        sprintf(
                                            $this->module->l('Combination %s %s not inserted: error %s', $this->name),
                                            $model->reference,
                                            $name,
                                            Db::getInstance()->getMsgError()
                                        ).
                                        "</p>";
                                }
                            } catch (\Throwable $th) {
                                $this->errors[] = 
                                    "<p>".
                                    sprintf(
                                        $this->module->l('Combination %s %s not inserted: error %s', $this->name),
                                        $model->reference,
                                        $name,
                                        $th->getMessage()
                                    ).
                                    "</p>";
                            }
                        }
                    }
                }
            }
            $this->confirmations[] = "<h1>".$this->module->l('Operation done.', $this->name)."</h1>";
        }
    }

    protected function parseExcel($content, $worksheet = "")
    {
        $xlsx = XlsxReader::parse($content, true);
        $rows = null;
        if ($worksheet) {
            foreach ($xlsx->sheetNames() as $key => $value) {
                if($value == $worksheet) {
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
        return $this->importCombinations(false);
    }

    public function processBulkImportAll()
    {
        $this->boxes = $this->getAllProducts();
        return $this->importCombinations(true);
    }

    private function getAllProducts()
    {
        $sql = $this->tools->getCookie('listsql');
        $sql = substr($sql, 0, strpos($sql, "LIMIT"));
        $rows = $this->db->executeS($sql);
        $output = array();
        if ($rows) {
            foreach ($rows as $row) {
                $output[] = $row['id_product'];
            }
        }
        return $output;
    }

    private function importCombinations()
    {
        if (!is_array($this->boxes)) {
            return false;
        }
        if (count($this->boxes) == 0) {
            $this->warnings[] = $this->module->l('Please select at least one combination.', $this->name);
            return false;
        }
        foreach ($this->boxes as $box) {
            $default_on = 0;
            $id_product_attribute = (int)$box;
            $model = new MpModelUpdateCombination($id_product_attribute, $this->id_lang, $this->id_shop);
            $combination = new Combination($id_product_attribute, $this->id_lang, $this->id_shop);
            
            if (isset($model->json['default_on']) && $model->json['default_on']) {
                $default_on = $model->json['default_on'];
                unset($model->json['default_on']);
            }
            foreach ($model->json as $key => $value) {
                $combination->$key = $value;
            }
            
            try {
                $res = $combination->update();
                if ($res) {
                    if ($default_on) {
                        $product = new Product($model->id_product);
                        $product->deleteDefaultAttributes();
                        $product->setDefaultAttribute($model->id);
                    }
                    
                    $this->confirmations[] = 
                        "<p>".
                        sprintf(
                            $this->module->l('Product %s %s updated', $this->name),
                            $model->reference,
                            $model->name
                        ).
                        "</p>";
                    $model->delete();
                } else {
                    $this->errors[] = 
                        "<p>".
                        sprintf(
                            $this->module->l('Product %s %s not updated: error %s', $this->name),
                            $model->reference,
                            $model->name,
                            Db::getInstance()->getMsgError()
                        ).
                        "</p>";
                }
            } catch (\Throwable $th) {
                $this->errors[] = 
                    "<p>".
                    sprintf(
                        $this->module->l('Product %s %s not updated: error %s', $this->name),
                        $model->reference,
                        $model->name,
                        $th->getMessage()
                    ).
                    "</p>";
            }
        }
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new MpModelUpdateCombination($box);
            $item->delete();
        }
        $this->confirmations[] = $this->module->l('Operation done.', $this->name);
    }

    public function getJson($json)
    {
        $output = "<ul>";
        $json = json_decode($json, true);
        foreach ($json as $key => $value) {
            $output .= "<li><i>$key</i> : <span class=\"text-primary\">$value</span></li>";
        }
        $output .= "</ul>";
        return $output;
    }

    public function getThumb($id, $row)
    {
        $id_product_attribute = (int)$row['id_product_attribute'];
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_image')
            ->from('product_attribute_image')
            ->where('id_product_attribute='.(int)$id_product_attribute);
        $id_cover = ['id_image' => (int)$db->getValue($sql)];

        if (!$id_cover['id_image']) {
            $id_product = (int)$row['id_product'];
            if (!$id_product) {
                return "--";
            }
            $id_cover = Product::getCover($id_product);
        }
        
        $product = new Product($id_product, false, $this->id_lang, $this->id_shop);
        $link = Context::getContext()->link;
        $imagePath = $link->getImageLink($product->link_rewrite[$this->id_lang], $id_cover['id_image'], 'home_default');
        $href = Context::getContext()->link->getAdminLink('AdminProducts').'&updateproduct&id_product='.$id_product;
        return '<a href="'.$href.'" target="_blank"><img src="'.$imagePath.'" style="width: 64px; height: auto;"></a>';
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

    public function htmlCheck($value)
    {
        if ($value) {
            return '<i class="icon icon-check text-success"></i>';
        }
        return '<i class="icon icon-times text-danger"></i>';
    }

    public function getProductName($id_product)
    {
        $db = Db::getInstance();
        $sql = "select name from "._DB_PREFIX_."product_lang where "
            ."id_product = ".(int)$id_product." and id_lang=".(int)$this->id_lang
            ." and id_shop=".(int)$this->id_shop;
        return $db->getValue($sql);
    }

    public function getProductId($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = "select id_product from "._DB_PREFIX_."product_attribute where "
            ."id_product_attribute=".(int)$id_product_attribute;
            
        return $db->getValue($sql);
    }
}
