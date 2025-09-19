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

use MpSoft\MpMassImport\Excel\XlsxReader;
use MpSoft\MpMassImport\FieldsList\FieldsListIsacco;
use MpSoft\MpMassImport\Forms\HelperFormFileUpload;
use MpSoft\MpMassImport\Forms\HelperFormIsacco;
use MpSoft\MpMassImport\Helpers\Cookies;
use MpSoft\MpMassImport\Helpers\GetControllerName;
use MpSoft\MpMassImport\Helpers\GetThumb;

class AdminMpMassImportIsaccoController extends ModuleAdminController
{
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

    protected $isacco_token;

    public $name;

    public function __construct()
    {
        $this->name = 'AdminMpMassImportIsacco';
        $this->import_list = [];
        $this->db = Db::getInstance();
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->link = $this->context->link;
        $this->bootstrap = true;
        $this->className = 'ModelImportProduct';
        $this->controllerName = (new GetControllerName($this))->get();
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->initHelperList();

        parent::__construct();

        $this->errors = [];
    }

    private function initHelperList()
    {
        $this->table = ModelMpMassImportIsacco::$definition['table'];
        $this->identifier = ModelMpMassImportIsacco::$definition['primary'];
        $this->className = 'ModelMpMassImportIsacco';
        $this->fields_list = (new FieldsListIsacco($this))->getFieldsList();
        $this->_select = '\'\' as combinations';
        $this->addRowAction('import');
        $this->addRowAction('delete');

        $this->bulk_actions = [
            'import' => [
                'text' => $this->l('Importa i prodotti selezionati'),
                'confirm' => $this->l('Importare i prodotti selezionati?'),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->l('Importa TUTTI i prodotti'),
                'confirm' => $this->l('Importare TUTTI i prodotti presenti nella tabella?'),
                'icon' => 'icon-download text-danger',
            ],
            'divider000' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->l('Elimina dall\'elenco'),
                'confirm' => $this->l('Eliminare i prodotti selezionati dall\'elenco?'),
                'icon' => 'icon-trash text-warning',
            ],
            'delete_all' => [
                'text' => $this->l('Svuota tabella'),
                'confirm' => $this->l('Svuotare la tabella?'),
                'icon' => 'icon-trash text-danger',
            ],
            'divider001' => [
                'text' => 'divider',
            ],
        ];
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
        //unset($this->toolbar_btn['new']);
    }

    public function initContent()
    {
        $this->content =
            $this->getFormResult() .
            $this->getFormStep1() .
            $this->getFormStep2() .
            $this->getFormStep3() .
            $this->getScriptProgressBar() .
            $this->getModalProgressBar() .
            $this->replaceBulkActions();

        parent::initContent();
    }

    protected function getTemplate($template)
    {
        return $this->module->getLocalPath() . 'views/templates/admin/' . $template . '.tpl';
    }

    protected function getSmartyTemplate($template, $params)
    {
        $smarty = Context::getContext()->smarty;
        if ($params) {
            $smarty->assign($params);
        }

        return $smarty->fetch($template);
    }

    protected function getModalProgressBar()
    {
        $template = $this->getTemplate('ImportIsacco/ModalProgress');
        $params = [
            'root_url' => $this->module->getPathUri(),
        ];

        return $this->getSmartyTemplate($template, $params);
    }

    protected function getScriptProgressBar()
    {
        $template = $this->getTemplate('ImportIsacco/ProgressBar');

        return $this->getSmartyTemplate($template, []);
    }
    protected function getResultHtml($params)
    {
        $template = $this->getTemplate('ImportIsacco/ModalBody');

        return $this->getSmartyTemplate($template, $params);
    }
    protected function getFormResult()
    {
        $template = $this->getTemplate('ImportIsacco/Results');
        $params = [
            'ajax_controller' => $this->context->link->getAdminLink($this->name),
        ];

        return $this->getSmartyTemplate($template, $params);
    }
    protected function getFormStep1()
    {
        $template = $this->getTemplate('ImportIsacco/Step1');
        $params = [
            'ajax_controller' => $this->context->link->getAdminLink($this->name),
        ];

        return $this->getSmartyTemplate($template, $params);
    }

    protected function getFormStep2()
    {
        $template = $this->getTemplate('ImportIsacco/Step2');
        $params = [
            'ajax_controller' => $this->context->link->getAdminLink($this->name),
        ];

        return $this->getSmartyTemplate($template, $params);
    }

    protected function getFormStep3()
    {
        $template = $this->getTemplate('ImportIsacco/Step3');
        $params = [
            'ajax_controller' => $this->context->link->getAdminLink($this->name),
            'attributeGroups' => AttributeGroup::getAttributesGroups($this->id_lang),
        ];

        return $this->getSmartyTemplate($template, $params);
    }

    protected function replaceBulkActions()
    {
        $template = $this->getTemplate('ImportIsacco/ReplaceBulkActions');
        $params = [
            'admin_controller' => $this->context->link->getAdminLink($this->name),
        ];

        return $this->getSmartyTemplate($template, $params);
    }

    public function getDatabase($page = 50)
    {
        $username = 'catalogo_api';
        $password = 'ctg12#25p';

        if (!$this->isacco_token) {
            $this->isacco_token = $this->getAuthToken('www.isacco.it', $username, $password);
        }

        if ($this->isacco_token) {
            $response = $this->curlGet(
                'https://www.isacco.it/rest/V1/products?searchCriteria[pageSize]=200&searchCriteria[currentPage]=' . $page,
                $this->isacco_token
            );
            if ($response) {
                return $response;
            }
        } else {
            $this->errors[] = sprintf(
                $this->module->l('Token non prelevato. %s', $this->controllerName),
                implode('<br>', $this->errors)
            );
        }
    }
    protected function getAuthToken($hostname, $username, $password)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://$hostname/index.php/rest/V1/integration/admin/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['username' => $username, 'password' => $password]),
            CURLOPT_HTTPHEADER => [
                'cache-control: no-cache',
                'content-type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $this->errors[] = $err;

            return false;
        } else {
            return json_decode($response, true);
        }
    }

    protected function curlGet($endpoint, $token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'cache-control: no-cache',
                'authorization: Bearer ' . $token,
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $this->errors[] = $err;

            return false;
        } else {
            return json_decode($response, true);
        }
    }

    public function generateFormImport()
    {
        $form = new HelperFormIsacco($this);

        return $form->renderForm();
    }

    /* POST PROCESS ACTIONS */

    protected function ajaxOut($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($params));
    }
    public function ajaxProcessImportExcelProducts()
    {
        $model = new ModelMpMassImportIsacco();
        $file = Tools::fileAttachment();
        $only_new = Tools::getValue('switch');

        $content = $file['content'];
        $reader = XlsxReader::parseData($content);
        $rows = $reader->rows(0);

        if (count($rows) > 1) {
            $header = array_shift($rows);
        } else {
            exit(
                json_encode(
                    ['result' => false]
                )
            );
        }

        foreach ($rows as &$row) {
            $row = array_combine($header, $row);
        }

        $inserted = $model->importExcel($rows, $only_new);
        $resultHtml = $this->getResultHtml([
            'processed' => count($rows),
            'inserted' => $inserted,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'confirmations' => $this->confirmations,
        ]);
        $this->ajaxOut([
            'resultHtml' => $resultHtml,
        ]);
    }

    public function ajaxProcessImportExcelEan13()
    {
        $products = Tools::getValue('products', []);
        $file = Tools::fileAttachment();
        $id_group_size = (int) Tools::getValue('id_group_size');
        $id_no_size = trim(Tools::getValue('id_no_size', ''));
        $model = new ModelMpMassImportIsaccoEan13();
        $content = $file['content'];
        $reader = XlsxReader::parseData($content);
        $rows = $reader->rows(0);

        if (count($rows) > 1) {
            $header = array_shift($rows);
            if ($header) {
                foreach ($header as &$title) {
                    $title = trim(strtolower($title));
                }
            }
        } else {
            exit(
                json_encode(
                    ['result' => false]
                )
            );
        }

        foreach ($rows as &$row) {
            $row = array_combine($header, $row);
        }
        $inserted = $model->importExcelEan13($rows, $products, $id_group_size, $id_no_size);
        $resultHtml = $this->getResultHtml([
            'processed' => count($rows),
            'inserted' => $inserted,
            'errors' => array_unique($this->errors),
            'warnings' => array_unique($this->warnings),
            'confirmations' => array_unique($this->confirmations),
        ]);
        $this->ajaxOut([
            'resultHtml' => $resultHtml,
        ]);
    }

    public function ajaxProcessBulkImport()
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->boxes = Tools::getValue('boxes');
        $chunk = 20;
        $ids = array_splice($this->boxes, 0, $chunk);

        foreach ($ids as $id_product) {
            $record = new ModelMpMassImportIsacco($id_product);
            $result = $record->insertRecord();
            if ($result !== true) {
                $this->errors[] = $result;
            }
        }
        if ($this->errors) {
            $this->warnings[] = $this->module->l('Operazione eseguita con errori', $this->name);
        } else {
            $this->confirmations[] = $this->module->l('Operazione eseguita', $this->name);
        }

        exit(
            json_encode(
                [
                    'confirmations' => $this->confirmations,
                    'warnings' => $this->warnings,
                    'errors' => $this->errors,
                    'boxes' => $this->boxes,
                    'html' => $this->showAlerts(),
                ]
            )
        );
    }

    public function ajaxProcessGetSizeAttributes()
    {
        $id_group = (int) Tools::getValue('id_group');
        $id_lang = (int) Context::getContext()->language->id;
        $attributes = AttributeGroup::getAttributes($id_lang, $id_group);
        $this->ajaxOut([
            'options' => $attributes,
        ]);
    }
    protected function showAlerts()
    {
        $template = $this->getTemplate('form/Alerts');
        $params = [
            'confirmations' => $this->confirmations,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];

        return $this->getSmartyTemplate($template, $params);
    }

    public function ajaxProcessBulkImportAll()
    {
        $this->boxes = ModelMpMassImportIsacco::getAllIds();

        exit(
            json_encode(
                [
                    'boxes' => $this->boxes,
                ]
            )
        );
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
            sprintf($this->l('Operazione eseguita. Rimossi %d elementi.'), count($this->boxes)),
            '</h1>'
        );
    }

    public function processBulkDeleteAll()
    {
        $this->boxes = ModelMpMassImportIsacco::getAllIds();

        return $this->processBulkDelete();
    }

    public function ajaxProcessImportDatabase()
    {
        $page = (int) Tools::getValue('page');
        $switch = (int) Tools::getValue('switch');

        if ($switch == 0 && $page == 0) {
            ModelMpMassImportIsacco::truncate();
        }
        $rows = $this->getDatabase($page);
        if (is_array($rows) && isset($rows['items']) && count($rows['items'])) {
            $model = new ModelMpMassImportIsacco();
            $updated = $model->importDatabase($rows['items'], $switch);
            $pages = $page;
            $processed = (int) count($rows['items']);
            $this->ajaxOut([
                'pages' => $pages,
                'processed' => $processed,
                'updated' => $updated,
            ]);
        } else {
            $this->ajaxOut(false);
        }
    }

    protected function importExcelEan13(&$excel)
    {
        $model = new ModelMpMassImportIsacco();
        $content = $excel['content'];
        $reader = XlsxReader::parseData($content);
        $rows = $reader->rows(0);

        if (count($rows) > 1) {
            $header = array_shift($rows);
            foreach ($header as &$head) {
                $head = strtolower(trim($head));
            }
        } else {
            exit(
                json_encode(
                    ['result' => false]
                )
            );
        }

        foreach ($rows as &$row) {
            $row = array_combine($header, $row);
        }

        $result = $model->importExcelEan13($rows);
        $template = $this->module->getLocalPath() . 'views/templates/admin/form/Alerts.tpl';
        $smarty = Context::getContext()->smarty;
        $smarty->assign([
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'confirmations' => $this->confirmations,
        ]);
        $alerts = $smarty->fetch($template);
        exit(
            json_encode(
                [
                    'result' => $result,
                    'processed' => count($rows),
                    'errors' => $this->errors,
                    'warnings' => $this->warnings,
                    'confirmations' => $this->confirmations,
                    'alerts' => $alerts,
                ]
            )
        );
    }

    public function getThumb($value)
    {
        return (new GetThumb())->getThumbIsacco($value);
    }

    public function addVat($value)
    {
        $vat = 22;
        $value = $value * (100 + $vat) / 100;

        return Tools::displayPrice($value);
    }

    public function getCombinations($value, $row)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('count(*)')
            ->from(ModelMpMassImportIsaccoEan13::$definition['table'])
            ->where('id_product=' . (int) $row['id_product']);
        $value = (int) $db->getValue($sql);

        return "<span class=\"badge badge-info\">{$value}</span>";
    }

    public function existsProduct($value)
    {
        if (ModelMpMassImportIsacco::existsProduct($value)) {
            return "<span class=\"badge badge-warning\">{$value}</span>";
        }

        return $value;
    }
}
