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
require_once _PS_MODULE_DIR_ . 'mpmassimport/traits/FieldsList/autoload.php';

use MpSoft\MpMassImport\Excel\XlsxWriter;
use MpSoft\MpMassImport\Features\ExportProductFeatures;
use MpSoft\MpMassImport\Forms\HelperFormImportExcel;
use MpSoft\MpMassImport\Helpers\HelperForm;
use MpSoft\MpMassImport\Helpers\HelperTree;

class AdminMpMassImportFeaturesController extends ModuleAdminController
{
    use \MpSoft\MpMassImport\Traits\Cookies;
    use \MpSoft\MpMassImport\Traits\HelperFormExcelImport;
    use \MpSoft\MpMassImport\Traits\SmartyTpl;
    use \MpSoft\MpMassImport\Traits\Tools;
    use \MpSoft\MpMassImport\Traits\Excel;
    use \MpSoft\MpMassImport\Traits\FieldsList\ImportFeatureProduct;
    use MpSoft\MpMassImport\Traits\CommonDbTools;
    use MpSoft\MpMassImport\Traits\InstallTable;

    public const MAIN = 'Main';
    public const EXPORT_FEATURES = 'ExportFeatures';
    public const EXPORT_FEATURES_PRODUCT = 'ExportFeaturesProduct';
    public const IMPORT_FEATURES = 'ImportFeatures';
    public const IMPORT_FEATURES_PRODUCT = 'ImportFeaturesProduct';
    public const SETTINGS = 'Settings';
    public const BACK = 'Back';

    private $db;
    public $name;
    public $id_lang;
    private $currentExportList = [];
    private $doExport = false;
    private $submitAction = 'submitDefaultAction';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'Product';
        $this->name = $this->extractClassName($this);

        parent::__construct();

        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->installTable();
    }

    private function installTable()
    {
        $table = ModelImportFeatureProduct::$definition['table'];
        $error = '';
        if (!$this->existsTable($table, $error)) {
            if ($this->createTable(ModelImportFeatureProduct::$definition)) {
                $this->confirmations = $this->paragraph(sprintf(
                    $this->module->l('Tabella %s creata.', $this->name),
                    $this->strong($table)
                ));
            } else {
                $this->errors[] = $error;
            }
        }
    }

    /**
     * Manage GET and POST data
     *
     * @return void
     */
    public function initProcess()
    {
        $this->setDefaultCurrentPage();

        $matches = [];
        if (preg_match('/^setPage(.*)/i', Tools::getValue('action'), $matches)) {
            $currentPage = Tools::ucfirst($matches[1]);
            $this->setCurrentPage($currentPage);
            Tools::redirectAdmin($this->context->link->getAdminLink($this->name));
            exit();
        }

        if (Tools::isSubmit('submitFindProductFeatures')) {
            $categories = Tools::getValue('HCA_CATEGORY_TREE');
            $default_category = (int) Tools::getValue('HCA_SELECT_IN_DEFAULT_CATEGORY');
            $associated_categories = (int) Tools::getValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES');
            $this->cookieSetValue('HCA_CATEGORY_TREE', $categories);
            $this->cookieSetValue('HCA_SELECT_IN_DEFAULT_CATEGORY', $default_category);
            $this->cookieSetValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES', $associated_categories);
        }

        switch ($this->getCurrentPage()) {
            case self::EXPORT_FEATURES:
                // code...
                break;
            case self::EXPORT_FEATURES_PRODUCT:
                $export = new ExportProductFeatures();
                $pl = _DB_PREFIX_ . 'product_lang';
                $pi = _DB_PREFIX_ . 'image';
                $cat = _DB_PREFIX_ . 'category_lang';
                $cp = _DB_PREFIX_ . 'category_product';

                $this->fields_list = $export->getFieldsList($this);
                $this->table = 'product';
                $this->identifier = 'id_product';
                $this->className = 'Product';
                $this->list_id = $this->table;
                $this->_defaultOrderBy = $this->identifier;
                $this->_defaultOrderWay = 'ASC';
                $this->_select = 'b.name, c.id_image, cat.name as `category_default`';
                $this->_join = "INNER JOIN {$pl} b ON (a.id_product=b.id_product AND b.id_lang=" . (int) $this->id_lang . ')'
                    . " LEFT JOIN {$pi} c ON (a.id_product=c.id_product AND c.cover=1)"
                    . " LEFT JOIN {$cat} cat ON (a.id_category_default=cat.id_category AND cat.id_lang = " . (int) $this->id_lang . ')';
                $categories = $this->cookieGetValue('HCA_CATEGORY_TREE');
                $default_category = (int) $this->cookieGetValue('HCA_SELECT_IN_DEFAULT_CATEGORY');
                $associated_categories = (int) $this->cookieGetValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES');
                if ($categories) {
                    if (!is_array($categories)) {
                        $categories = [$categories];
                    }
                    $str_categories = implode(',', $categories);
                    if ($default_category) {
                        $this->_where .= " AND a.id_category_default in ($str_categories)";
                    }
                    if ($associated_categories) {
                        $this->_join .= " INNER JOIN {$cp} cp ON (a.id_product=cp.id_product AND cp.id_category IN ({$str_categories}))";
                    }
                }

                $this->toolbar_title = $this->l('Export Product Features to Excel');

                break;
            case self::IMPORT_FEATURES:
                // code...
                break;
            case self::IMPORT_FEATURES_PRODUCT:
                $this->toolbar_title = $this->l('Import Product Features from Excel');
                $export = new ExportProductFeatures();
                $p = _DB_PREFIX_ . 'product';
                $pi = _DB_PREFIX_ . 'image';
                $cat = _DB_PREFIX_ . 'category_lang';

                $this->fields_list = $this->getFieldsList();
                $this->table = ModelImportFeatureProduct::$definition['table'];
                $this->identifier = ModelImportFeatureProduct::$definition['primary'];
                $this->className = 'ModelImportFeatureProduct';
                $this->list_id = $this->table;
                $this->_defaultOrderBy = $this->identifier;
                $this->_defaultOrderWay = 'ASC';
                $this->_select = 'b.active, c.id_image, cat.name as `category_default`';
                $this->_join =
                    "INNER JOIN {$p} b ON (a.id_product=b.id_product)"
                    . " LEFT JOIN {$pi} c ON (a.id_product=c.id_product AND c.cover=1)"
                    . " LEFT JOIN {$cat} cat ON (b.id_category_default=cat.id_category AND cat.id_lang = " . (int) $this->id_lang . ')';

                $this->toolbar_title = $this->module->l('Import Product Features', $this->name);
                $this->fields_list = $this->getFieldsListImportFeatureProduct();

                break;
            default:
                // code...
                break;
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
            switch ($this->getCurrentPage()) {
                case self::MAIN:
                    break;
                case self::BACK:
                    break;
                case self::SETTINGS:
                    break;
                case self::IMPORT_FEATURES:
                    break;
                case self::IMPORT_FEATURES_PRODUCT:
                    $rows = $this->excelParseAssoc($file['content']);
                    ModelImportFeatureProduct::insertRows($rows, $this);

                    break;
                case self::EXPORT_FEATURES:
                    break;
                case self::EXPORT_FEATURES_PRODUCT:
                    break;
                default:
            }
        }

        parent::initProcess();
    }

    public function initContent()
    {
        $this->content .= $this->getTopMenu();
        $currentPage = $this->getCurrentPage();

        switch ($currentPage) {
            case self::EXPORT_FEATURES:
                $this->toolbar_title = $this->l('Export Features to Excel');

                break;
            case self::EXPORT_FEATURES_PRODUCT:
                $this->content .= $this->generateFormExportProductFeatures();

                break;
            case self::IMPORT_FEATURES:
                $this->toolbar_title = $this->l('Import Features from Excel');

                break;
            case self::IMPORT_FEATURES_PRODUCT:
                $form = new HelperFormImportExcel(
                    $this->module->l('Importa File Excel', $this->name),
                    'product',
                    'id_product',
                    'submitImportExcel'
                );
                $this->content .= $form->renderForm();

                break;
            case self::SETTINGS:
            default:
                $this->fields_list = [];
                $this->table = 'product';
                $this->identifier = 'id_product';
                $this->className = 'Product';

                $this->content .= $this->displaySettings();

                break;
        }

        parent::initContent();
    }

    protected function getTopMenu()
    {
        $adminFeaturesExportFeatures = $this->context->link->getAdminLink($this->name) . $this->setPageAction(self::EXPORT_FEATURES);
        $adminFeaturesExportProductsFeatures = $this->context->link->getAdminLink($this->name) . $this->setPageAction(self::EXPORT_FEATURES_PRODUCT);
        $adminFeaturesImportFeatures = $this->context->link->getAdminLink($this->name) . $this->setPageAction(self::IMPORT_FEATURES);
        $adminFeaturesImportProductsFeatures = $this->context->link->getAdminLink($this->name) . $this->setPageAction(self::IMPORT_FEATURES_PRODUCT);
        $settings = $this->context->link->getAdminLink('AdminMpMassImportSettings');

        $params = [
            'menu' => [
                'AdminFeaturesImportFeatures' => [
                    'label' => $this->module->l('Importa Caratteristiche', $this->name),
                    'href' => $adminFeaturesImportFeatures,
                    'icon' => 'icon-download text-info',
                ],
                'AdminFeaturesExportFeatures' => [
                    'label' => $this->module->l('Esporta Caratteristiche', $this->name),
                    'href' => $adminFeaturesExportFeatures,
                    'icon' => 'icon-upload text-info',
                ],
                'divider_001' => '',
                'AdminFeaturesImportProductsFeatures' => [
                    'label' => $this->module->l('Importa Caratteristiche Prodotti', $this->name),
                    'href' => $adminFeaturesImportProductsFeatures,
                    'icon' => 'icon-download text-success',
                ],
                'AdminFeaturesExportProductsFeatures' => [
                    'label' => $this->module->l('Esporta Caratteristiche Prodotti', $this->name),
                    'href' => $adminFeaturesExportProductsFeatures,
                    'icon' => 'icon-upload text-success',
                ],
                'divider_002' => '',
                'AdminFeaturesSettings' => [
                    'label' => $this->module->l('Impostazioni', $this->name),
                    'href' => $settings,
                    'icon' => 'icon-cogs',
                ],
            ],
        ];

        return $this->renderTplAdmin('menu/AdminMenuFeatures', $params);
    }

    protected function displaySettings()
    {
        return '';
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        $currentPage = $this->getCurrentPage();
        switch ($currentPage) {
            case self::MAIN:
            case self::BACK:
            case self::SETTINGS:
            case self::IMPORT_FEATURES:
            case self::IMPORT_FEATURES_PRODUCT:
                parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop);

                return;
        }
        if ($this->doExport) {
            parent::getList($id_lang, $orderBy, $orderWay, 0, 999999999, $id_lang_shop);
        } else {
            parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop);
        }

        if (!$this->_list) {
            return;
        }

        $db = Db::getInstance();
        $rows = $this->_list;
        $id_product_list = [];

        if ($rows) {
            foreach ($rows as $row) {
                $id_product_list[] = $row['id_product'];
            }
        }

        $qry = new DbQuery();
        $qry->select('distinct fl.id_feature, fl.name')
            ->from('feature_lang', 'fl')
            ->innerJoin(
                'feature_product',
                'fp',
                'fp.id_feature=fl.id_feature AND fp.id_product IN (' . implode(',', $id_product_list) . ')'
            )->where('fl.id_lang=' . (int) $this->id_lang)
            ->orderBy('fl.id_feature');

        $features = $db->executeS($qry);
        if ($features) {
            $name_features = [];
            foreach ($features as $feature) {
                $name_feature = $feature['id_feature'] . '::' . $feature['name'];
                $name_features[] = $name_feature;
                if (!$this->doExport) {
                    $this->fields_list[$name_feature] = [
                        'title' => $name_feature,
                        'type' => 'text',
                        'float' => true,
                        'size' => 'auto',
                        'search' => false,
                    ];
                }
            }

            foreach ($rows as &$row) {
                $qry = new DbQuery();
                $qry->select('fl.id_feature, fl.name as feature, fvl.id_feature_value, fvl.value as feature_value')
                    ->from('feature_product', 'fp')
                    ->innerJoin('feature_lang', 'fl', 'fl.id_feature=fp.id_feature and fl.id_lang=' . (int) $this->id_lang)
                    ->innerJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value=fp.id_feature_value and fvl.id_lang=' . (int) $this->id_lang)
                    ->where('fp.id_product=' . (int) $row['id_product'])
                    ->orderBy('fp.id_feature, fp.id_feature_value');
                $qry = $qry->build();
                foreach ($name_features as $name_feature) {
                    $row[$name_feature] = '--';
                }
                $associated_product_features = $db->executeS($qry);
                foreach ($row as $key => &$col) {
                    $matches = [];
                    if (preg_match('/^(\d+)\:\:(.*)/i', $key, $matches)) {
                        foreach ($associated_product_features as $item) {
                            if ($item['id_feature'] == $matches[1]) {
                                $val = $item['id_feature_value'] . '::' . $item['feature_value'];
                                $col .= $val . ',';
                                $col = ltrim($col, '--');
                            }
                        }
                        $col = rtrim($col, ',');
                    }
                }
                unset($matches);
            }
        }

        if ($this->doExport) {
            $exportFields = [
                'id_product',
                'reference',
                'name',
            ];
            if ($rows) {
                $header = [];
                $excel_data = [];

                $first = reset($rows);
                foreach ($first as $key => $col) {
                    if (in_array($key, $exportFields)) {
                        $header[] = $key;
                    }
                    if (preg_match('/^\d+\:\:/i', $key)) {
                        $header[] = $key;
                    }
                }
                $excel_data[] = $header;
                unset($col, $key);
                foreach ($rows as $row) {
                    $excel_row = [];
                    foreach ($row as $key => $col) {
                        if (in_array($key, $exportFields)) {
                            $excel_row[] = $col;
                        }
                        if (preg_match('/^\d+\:\:/i', $key)) {
                            $excel_row[] = $col;
                        }
                    }
                    $excel_data[] = $excel_row;
                    unset($excel_row, $col);
                }

                $excel = new XlsxWriter();
                $excel->addSheet($excel_data, $this->module->l('Product Features'));
                exit($excel->downloadAs('ProductFeatures_' . date('YmdHis') . '.xlsx'));
            }
        }

        $this->fields_list = array_merge(
            $this->fields_list,
            [
                'date_add' => [
                    'title' => $this->module->l('Date add', $this->name),
                    'type' => 'date',
                    'size' => 'auto',
                    'align' => 'text-center',
                    'search' => true,
                    'filter_key' => 'a!date_add',
                ],
                'date_upd' => [
                    'title' => $this->module->l('Date upd', $this->name),
                    'type' => 'date',
                    'size' => 'auto',
                    'align' => 'text-center',
                    'search' => true,
                    'filter_key' => 'a!date_upd',
                ],
            ]
        );

        $this->_list = $rows;
    }

    public function setHelperDisplay(Helper $helper)
    {
        // $helper->force_show_bulk_actions = true;
        $this->list_no_link = true;
        parent::setHelperDisplay($helper);
    }

    public function renderList()
    {
        try {
            $content = parent::renderList();
            $this->cookieSetValue('listsql', $this->_listsql);

            return $content;
        } catch (\Throwable $th) {
            Tools::dieObject([$th->getMessage(), $this->_listsql]);
        }
    }

    public function initHelperList()
    {
        $this->table = 'mp_massimport_feature';
        $this->identifier = 'id_' . $this->table;
        $this->_select = 'id_feature_value as `exists`';
        $this->fields_list = $this->getFieldsList();
        $this->addRowAction('toProduct');
        $this->bulk_actions = [
            'import' => [
                'text' => $this->l('Import Features'),
                'confirm' => $this->l('Import selected features?'),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->l('Import all'),
                'confirm' => $this->l('Import all features?'),
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

    public function displayToProductLink($token = null, $id, $name = null)
    {
        $params = [
            'adminProductsController' => Context::getContext()->link->getAdminLink('AdminProducts'),
            'id_product' => $id,
        ];

        return $this->renderTplAdmin('RowsAction/ToProduct', $params);
    }

    private function getFieldsList()
    {
        return [
            'id_mp_massimport_feature' => [
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
                'callback' => 'existsFeature',
                'size' => 64,
                'align' => 'text-center',
                'search' => false,
                'class' => 'fixed-width-xs',
            ],
            'feature' => [
                'title' => $this->l('Feature Group'),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
            ],
            'feature_value' => [
                'title' => $this->l('Feature Value'),
                'type' => 'text',
                'size' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ],
            'custom' => [
                'title' => $this->l('Is Custom'),
                'type' => 'text',
                'float' => true,
                'size' => 64,
                'class' => 'fixed-width-sm',
                'align' => 'text-center',
                'search' => true,
                'callback' => 'displayCustom',
            ],
            'date_add' => [
                'title' => $this->l('Date add'),
                'type' => 'datetime',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => $this->l('Date upd'),
                'type' => 'datetime',
                'size' => 'auto',
                'align' => 'text-center',
                'search' => true,
                'filter_key' => 'a!date_upd',
            ],
        ];
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/icons.css');

        parent::setMedia();
    }

    protected function setPageAction($action)
    {
        return '&action=setPage' . \Tools::ucfirst($action);
    }

    public function initPageHeaderToolbar()
    {
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

    protected function generateFormImportFeatures()
    {
        $form = $this->generateForm(
            $this->table,
            $this->identifier,
            'submitImportExcelFeatures',
            $this->context->link->getAdminLink($this->name, false),
            \Tools::getAdminTokenLite($this->name)
        );

        return $form;
    }

    protected function generateFormImportProductFeatures()
    {
        $form = $this->generateForm(
            $this->table,
            $this->identifier,
            'submitImportExcelProductFeatures',
            $this->context->link->getAdminLink($this->name, false),
            \Tools::getAdminTokenLite($this->name)
        );

        return $form;
    }

    protected function generateFormExportFeatures()
    {
        $form = $this->generateForm(
            $this->table,
            $this->identifier,
            'submitExportExcelFeatures',
            $this->context->link->getAdminLink($this->name, false),
            \Tools::getAdminTokenLite($this->name),
            $this->getFieldsExportFeatures($this->cookieGetValue('HCA_CATEGORY_TREE')),
            $this->l('Export Features'),
            $this->getFieldsValuesExportFeatures()
        );

        return $form;
    }

    protected function generateFormExportProductFeatures()
    {
        $categoryTree = (new HelperTree())->getTreeField(
            $this->module->l('Categories tree', $this->module->name),
            $this->module->l('Categories', $this->module->name),
            'HCA_CATEGORY_TREE',
            $this->cookieGetValue('HCA_CATEGORY_TREE')
        );

        $fields_form = [
            'form' => [
                // 'tinymce' => true,
                'legend' => [
                    'title' => $this->module->l('Export Product Features'),
                    'icon' => 'icon-upload',
                ],
                'input' => [
                    $categoryTree,
                    [
                        'type' => 'switch',
                        'name' => 'HCA_SELECT_IN_DEFAULT_CATEGORY',
                        'label' => $this->module->l('Cerca nella categoria di default', $this->name),
                        'desc' => $this->module->l('Se impostato, cerca nella categoria di default', $this->name),
                        'values' => [
                            [
                                'id' => 'default_category_on',
                                'value' => 1,
                                'label' => $this->module->l('SI', $this->name),
                            ],
                            [
                                'id' => 'default_category_off',
                                'value' => 0,
                                'label' => $this->module->l('NO', $this->name),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'HCA_SELECT_IN_ASSOCIATED_CATEGORIES',
                        'label' => $this->module->l('Cerca nelle categorie associate', $this->name),
                        'desc' => $this->module->l('Se impostato, cerca nelle categorie associate', $this->name),
                        'values' => [
                            [
                                'id' => 'associated_category_on',
                                'value' => 1,
                                'label' => $this->module->l('SI', $this->name),
                            ],
                            [
                                'id' => 'associated_category_off',
                                'value' => 0,
                                'label' => $this->module->l('NO', $this->name),
                            ],
                        ],
                    ],
                ],
                'buttons' => [
                    'exportToExcel' => [
                        'title' => $this->module->l('Export To Excel', $this->name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-upload text-red',

                        'href' => $this->context->link->getAdminLink($this->name, true) . '&action=exportToExcel',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Find Products', $this->name),
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-search',
                ],
            ],
        ];

        $this->submitAction = 'submitFindProductFeatures';

        $form = (new HelperForm())->generate(
            $this->table,
            $this->identifier,
            $this->submitAction,
            $this->context->link->getAdminLink($this->name, false),
            \Tools::getAdminTokenLite($this->name),
            $fields_form,
            $this->l('Export Product Features'),
            $this->getFieldsValuesExportFeatures()
        );

        return $form;
    }

    /* POST PROCESS ACTIONS */
    public function postProcess()
    {
        return parent::postProcess();
    }

    public function processExportToExcel()
    {
        $this->doExport = true;
    }

    /**
     *
     * Parse Excel file to prepare import combinations
     *
     * @param array $file The file parameters from Tools::fileAttachment()
     *
     * @return void
     *
     **/
    protected function importExcel($file)
    {
    }

    public function processBulkImport()
    {
        $currentPage = $this->getCurrentPage();

        if ($currentPage == self::IMPORT_FEATURES) {
            return $this->import(false);
        } elseif ($currentPage == self::IMPORT_FEATURES_PRODUCT) {
            return $this->importFeatureProducts();
        }
    }

    public function processBulkImportAll()
    {
        $this->boxes = $this->getAllFeatures();

        return $this->processBulkImport();
    }

    private function getAllFeatures()
    {
        $boxes = ModelImportFeatureProduct::getAllIds();

        return $boxes;
    }

    private function import($force = false)
    {
    }

    public function processBulkDelete()
    {
        foreach ($this->boxes as $box) {
            $item = new MpMassImportFeature($box);
            $item->delete();
        }
        $this->confirmations[] = $this->l('Operation done.');
    }

    public function ajaxProcessCustomMpMassImportFeature()
    {
        die();
    }

    public function displayCustom($value)
    {
        if ((int) $value) {
            return '<i class="icon icon-check text-success"></i>';
        }

        return '<span></span>';
    }

    public function existsFeature($id)
    {
        if ((int) $id) {
            return '<i class="icon icon-check text-success"></i>';
        }

        return '<i class="icon icon-times text-danger"></i>';
    }
}
