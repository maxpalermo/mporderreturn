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

namespace MpSoft\MpMassImport\Traits;

trait HelperFormExcelImport
{
    public function getFieldsValuesExportFeatures()
    {
        return [
            'HCA_CATEGORY_TREE' => $this->cookieGetValue('HCA_CATEGORY_TREE'),
            'HCA_SELECT_IN_DEFAULT_CATEGORY' => $this->cookieGetValue('HCA_SELECT_IN_DEFAULT_CATEGORY'),
            'HCA_SELECT_IN_ASSOCIATED_CATEGORIES' => $this->cookieGetValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES'),
        ];
    }

    public function getFieldsExportFeatures(
        $selected_categories = [],
        $disabled_categories = [],
        $root_category = null,
        $use_search = true,
        $use_checkbox = true,
        $set_data = null
    ) {
        $name = 'HelperFormExcelExportProductFeatures';
        $module = \Module::getInstanceByName('mpmassimport');
        $link = \Context::getContext()->link;
        $title = $module->l('Esporta su File Excel', $name);

        if (!$root_category) {
            $root_category = (int) \Category::getRootCategory()->id;
        }

        $fields_form = [
            'form' => [
                // 'tinymce' => true,
                'legend' => [
                    'title' => $title,
                    'icon' => 'icon-upload',
                ],
                'input' => [
                    [
                        'type' => 'categories',
                        'label' => $this->l('Root category'),
                        'desc' => $this->l('Root category of the first column.'),
                        'name' => 'HCA_CATEGORIES',
                        'tree' => [
                            'id' => 'CategoryTree',
                            'name' => 'helperTreeCategories',
                            'title' => $this->l('Export Features'),
                            'selected_categories' => $selected_categories,
                            'disabled_categories' => $disabled_categories,
                            'root_category' => $root_category,
                            'use_search' => $use_search,
                            'use_checkbox' => $use_checkbox,
                            'set_data' => $set_data,
                        ],
                    ],
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
                    'back' => [
                        'title' => $module->l('Back to main menu', $name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-back',

                        'href' => $link->getAdminLink('adminMpMassImportFeatures', true),
                    ],
                    'exportToExcel' => [
                        'title' => $module->l('Export To Excel', $name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-upload text-red',

                        'href' => $link->getAdminLink('adminMpMassImportFeatures', true) . '&action=exportToExcel',
                    ],
                ],
                'submit' => [
                    'title' => $module->l('Find Products', $name),
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-search',
                ],
            ],
        ];

        return $fields_form;
    }

    protected function getFormFields()
    {
        $name = 'HelperFormExcelImport';
        $module = \Module::getInstanceByName('mpmassimport');
        $link = \Context::getContext()->link;
        $title = $module->l('Importa da File Excel', $name);
        $fields_form = [
            'form' => [
                // 'tinymce' => true,
                'legend' => [
                    'title' => $title,
                    'icon' => 'icon-download',
                ],
                'input' => [
                    [
                        'type' => 'file',
                        'label' => $module->l('Choose file', $name),
                        'name' => 'uploadfile',
                        'class' => 'fixed-width-xl',
                        'lang' => false,
                        'hint' => $module->l('Import excel file (extensions .xls, .xlsx).', $name),
                    ],
                ],
                'buttons' => [
                    'back' => [
                        'title' => $module->l('Back to main menu', $name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-back',

                        'href' => $link->getAdminLink('adminMpMassImport', false),
                    ],
                ],
                'submit' => [
                    'title' => $module->l('Import', $name),
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-download',
                ],
            ],
        ];

        return $fields_form;
    }

    public function generateForm($table, $identifier, $submitAction, $currentIndex, $token, $fields_form = null, $title = '', $fields_values = [])
    {
        $languages = \Context::getContext()->language->getLanguages();
        $id_lang = (int) \Context::getContext()->language->id;
        $lang = new \Language((int) \Configuration::get('PS_LANG_DEFAULT'));
        $allowForm = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        foreach ($languages as &$language) {
            if ($language['id_lang'] == $lang->id) {
                $language['is_default'] = 1;
            } else {
                $language['is_default'] = 0;
            }
        }

        $helper = new \HelperForm();
        $helper->show_toolbar = true;
        $helper->title = $title;
        $helper->table = $table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = $allowForm ? $allowForm : 0;
        $helper->identifier = $identifier;
        $helper->submit_action = $submitAction;
        $helper->currentIndex = $currentIndex;
        $helper->token = $token;
        $helper->tpl_vars = [
            'fields_value' => $fields_values,
            'languages' => $languages,
            'id_language' => $id_lang,
        ];

        if (!$fields_form) {
            $fields_form = $this->getFields();
        }

        return $helper->generateForm([$fields_form]);
    }
}
