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

exit('UNDER CONSTRUCTION');

if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
    require_once _PS_MODULE_DIR_ . 'mpmassimport/vendor/excel/autoload.php';
}
require_once _PS_MODULE_DIR_ . 'mpmassimport/classes/MpUtilities.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/classes/MpFormImport.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/classes/MpMassImporterExcelAttribute.php';

class AdminMpMassImportAttributesController extends ModuleAdminController
{
    private $tools;

    public function __construct()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $this->translator = Context::getContext()->getTranslator();
        }
        $this->tools = new MpUtilities();
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->link = $this->context->link;
        $this->bootstrap = true;
        $this->className = 'MpMassImportAttribute';
        $this->adminClassName = 'AdminMpMassImportAttributes';
        $this->initHelperList();

        parent::__construct();
    }

    public function setHelperDisplay(Helper $helper)
    {
        $helper->force_show_bulk_actions = true;
        $this->list_no_link = true;
        parent::setHelperDisplay($helper);
    }

    public function initHelperList()
    {
        $this->table = 'mp_massimport_attribute';
        $this->identifier = 'id_' . $this->table;
        $this->fields_list = $this->getFieldsList();
        $this->_select = 'CONCAT(img_root,img_folder,image) as path, id_attribute as `exists`';
        $this->bulk_actions = [
            'import' => [
                'text' => $this->l('Import Attributes'),
                'confirm' => $this->l('Import selected attributes?'),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->l('Import all'),
                'confirm' => $this->l('Import all attributes?'),
                'icon' => 'icon-download text-info',
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

    private function getFieldsList()
    {
        return [
            'id_mp_massimport_attribute' => [
                'title' => $this->l('Id'),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => true,
                'class' => 'fixed-width-xs',
            ],
            'exists' => [
                'title' => $this->l('Exists'),
                'type' => 'bool',
                'float' => true,
                'callback' => 'existsAttribute',
                'size' => 64,
                'align' => 'text-center',
                'search' => false,
                'class' => 'fixed-width-xs',
            ],
            'attribute_group' => [
                'title' => $this->l('Group'),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'a!attribute_group',
            ],
            'attribute' => [
                'title' => $this->l('Attribute'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!attribute',
            ],
            'group_type' => [
                'title' => $this->l('Type'),
                'type' => 'text',
                'size' => 'auto',
                'class' => 'fixed-width-sm',
                'align' => 'text-left',
                'search' => true,
                'filter_key' => 'a!group_type',
                'class' => 'fixed-width-sm',
            ],
            'color' => [
                'title' => $this->l('Color'),
                'type' => 'text',
                'float' => true,
                'color' => 'color',
                'size' => 'auto',
                'class' => 'fixed-width-md',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!color',
                'class' => 'fixed-width-sm',
            ],
            'path' => [
                'title' => $this->l('Image'),
                'type' => 'text',
                'float' => true,
                'size' => 64,
                'align' => 'text-center',
                'search' => false,
                'orderby' => false,
                'callback' => 'getImageThumb',
            ],
            'date_add' => [
                'title' => $this->l('Date add'),
                'type' => 'date',
                'size' => 'auto',
                'class' => 'fixed-width-md',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => $this->l('Date upd'),
                'type' => 'date',
                'size' => 'auto',
                'class' => 'fixed-width-md',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!date_upd',
            ],
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addCSS(
            $this->context->controller->module->getLocalPath() . 'views/css/process-icon.css'
        );

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
        $form = new MpFormImport();

        return $form->renderForm(
            $this->l('Import Excel Attributes'),
            $this->table,
            $this->identifier,
            $this->adminClassName,
            $this->getLanguages()
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
            $file = Tools::fileAttachment('uploadfile', 0);
            if (!$file) {
                $this->errors[] = $this->l('Please select an Excel file.');

                return false;
            }

            return $this->importExcel($file);
        }
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
        $reader = new MpMassImporterExcelAttribute($this);
        $reader->importExcel($file, 'Attributes');
    }

    public function processBulkImport()
    {
        return $this->import(false);
    }

    public function processBulkImportAll()
    {
        $this->boxes = $this->tools->listAll('id_mp_massimport_attribute');

        return $this->import(true);
    }

    private function import($force = false)
    {
        $importer = new MpMassImporterExcelAttribute($this);
        $importer->import($this->boxes);
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new MpMassImportAttribute($box);
            $item->delete();
        }
        $this->confirmations[] = $this->l('Operation done.');
    }

    public function getImageThumb($path)
    {
        if (trim($path)) {
            return '<img src="' . $path . '" style="width: 48px; height: 48px;">';
        }
    }

    public function existsAttribute($id)
    {
        if ((int) $id) {
            return '<i class="icon icon-check text-success"></i>';
        }

        return '<i class="icon icon-times text-danger"></i>';
    }
}
