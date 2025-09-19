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

use MpSoft\MpMassImport\Deprecated\MpMassExporterExcel;
use MpSoft\MpMassImport\FieldsList\FieldsListExport;
use MpSoft\MpMassImport\Forms\HelperFormExport;
use MpSoft\MpMassImport\Helpers\AddProducts;
use MpSoft\MpMassImport\Helpers\CheckExtension;
use MpSoft\MpMassImport\Helpers\Cookies;
use MpSoft\MpMassImport\Helpers\ExportProducts;
use MpSoft\MpMassImport\Helpers\ParseExcel;
use MpSoft\MpMassImport\Helpers\ProductExists;
use MpSoft\MpMassImport\Helpers\RowsToProducts;

class AdminMpMassExportController extends ModuleAdminController
{
    /** @var Db */
    protected $db;

    /** @var array */
    protected $import_list;

    /** @var int */
    protected $id_lang;

    /** @var int */
    protected $id_shop;

    /** @var Link */
    protected $link;
    /** @var string */
    public $name;

    public function __construct()
    {
        $this->name = 'AdminMpMassExport';
        $this->db = Db::getInstance();
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->link = $this->context->link;
        $this->bootstrap = true;
        $this->className = 'Product';
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->initHelperList();

        parent::__construct();
    }

    public function initHelperList()
    {
        $cookies = new Cookies();
        $this->toolbar_title = $this->l('Export products');
        $this->table = 'product';
        $this->identifier = 'id_' . $this->table;
        $this->fields_form = [];
        $this->fields_list = $this->fields_list = (new FieldsListExport($this))->getFieldsList();

        $this->bulk_actions['export'] = [
            'text' => $this->l('Export Products'),
            'confirm' => $this->l('Export selected items?'),
            'icon' => 'icon-upload',
        ];
        $this->bulk_actions['export_all'] = [
            'text' => $this->l('Export All Products'),
            'confirm' => $this->l('Export all items?'),
            'icon' => 'icon-upload text-danger',
        ];

        if (Tools::isSubmit('submitCategories')) {
            $cookies->setValue('selected_categories', Tools::getValue('selected_categories'));
        }

        $this->_join .=
            ' LEFT JOIN ' . _DB_PREFIX_ . 'product_lang b ' .
                'ON (a.id_product=b.id_product' .
                ' AND b.id_lang=' . (int) $this->id_lang .
                ' AND b.id_shop=' . (int) $this->id_shop . ') ' .
            ' LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ' .
                'ON (m.id_manufacturer=a.id_manufacturer)' .
            ' LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ' .
                'ON (cl.id_category=a.id_category_default and cl.id_lang=' . $this->id_lang . ')';

        $categories = $cookies->getValue('selected_categories');
        if ($categories) {
            $this->_join .= ' inner join ' . _DB_PREFIX_ . 'category_product cp ' .
                ' on (cp.id_product=a.id_product and cp.id_category in (' . implode(',', $categories) . '))';
        }

        $this->_select .= 'b.name as product_name, m.name as manufacturer, cl.name as category_name';
    }

    public function renderList()
    {
        try {
            $cookies = new Cookies();
            $content = parent::renderList();
            $cookies->setValue('listsql', $this->_listsql);

            return $content;
        } catch (\Throwable $th) {
            Tools::dieObject($this->_listsql);
        }
    }

    public function generateFormExport()
    {
        $form = new HelperFormExport($this);

        return $form->renderForm();
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/process-icon.css');

        return parent::setMedia();
    }

    public function initContent()
    {
        $this->content = $this->generateFormExport();
        parent::initContent();
    }

    public function processBulkExport()
    {
        $exporter = new ExportProducts($this);
        $exporter->export($this->boxes);
        $this->confirmations[] = $this->l('Operation done.');
    }

    public function processBulkExportAll()
    {
        $exporter = new ExportProducts($this);
        $exporter->export($this->getAllProducts());
        $this->confirmations[] = $this->l('Operation done.');
    }

    private function getAllProducts()
    {
        $cookies = new Cookies();
        $sql = $cookies->getValue('listsql');
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
}
