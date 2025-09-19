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

namespace MpSoft\MpMassImport\Forms;

use Configuration;
use Context;
use HelperForm;
use Language;
use Module;
use ModuleAdminController;
use MpSoft\MpMassImport\Helpers\GetControllerName;
use Tools;

class HelperFormExport extends HelperForm
{
    protected $name;
    protected $controller_name;
    protected $controller;
    public function __construct(ModuleAdminController $controller)
    {
        $this->name = $this->getControllerName($this);
        $this->controller = $controller;
        $this->controller_name = $this->getControllerName($controller);
        $this->module = $controller->module;
        parent::__construct();
    }

    public function renderForm()
    {
        $controller = $this->controller;
        $link = Context::getContext()->link;
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Select Categories'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'categories',
                        'label' => $this->l('Product Category'),
                        'desc' => $this->l('Product Category.'),
                        'name' => 'selected_categories',
                        'tree' => [
                            'id' => 'category',
                            'selected_categories' => Tools::getValue('selected_categories', []),
                            'use_checkbox' => true,
                            'use_search' => true,
                        ],
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'action',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Search'),
                    'icon' => 'process-icon-preview',
                ],
            ],
        ];

        $model = $this->controller->className;
        $definition = $model::$definition;
        $this->show_toolbar = true;
        $this->toolbar_scroll = true;
        $this->table = $definition['table'];
        $this->identifier = $definition['primary'];
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $languages = $controller->getLanguages();
        $this->default_form_language = $lang->id;
        $this->allow_employee_form_lang =
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->submit_action = 'submitImportExcel';
        $this->currentIndex = $link->getAdminLink($this->controller_name, false);
        $this->token = Tools::getAdminTokenLite($this->controller_name);
        $this->tpl_vars = [
            'fields_value' => [
                'action' => 'exportProducts',
                'selected_categories' => Tools::getValue('selected_categories', []),
            ],
            'languages' => $languages,
            'id_language' => Context::getContext()->language->id,
        ];

        return $this->generateForm([$fields_form]);
    }

    protected function getControllerName($controller)
    {
        return (new GetControllerName($controller))->get();
    }
}
