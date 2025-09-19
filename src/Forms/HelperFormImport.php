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
 *  @author    Massimiliano Palermo <info@mpsoft.it>
 *  @copyright 2021 Massimiliano Palermo - MpSoft.it
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of mpSOFT
 */

namespace MpSoft\MpMassImport\Forms;

require_once _PS_MODULE_DIR_ . 'mpmassimport/traits/autoload.php';

class HelperFormImport
{
    use \MpSoft\MpMassImport\Traits\GetControllerName;
    private static $helper;
    private static $name;

    public static function renderForm($title, $table, $identifier, $submitAction)
    {
        self::$name = 'HelperFormImport';
        /** @var \ModuleAdminController */
        $controller = \Context::getContext()->controller;
        $controller_name = $controller->name;
        $module = $controller->module;
        $link = \Context::getContext()->link;
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
                        'label' => $module->l('Choose file', self::$name),
                        'name' => 'uploadfile',
                        'class' => 'fixed-width-xl',
                        'lang' => false,
                        'hint' => $module->l('Import excel file (extensions .xls, .xlsx).', self::$name),
                    ],
                ],
                'buttons' => [
                    'back' => [
                        'title' => $module->l('Back to main menu', self::$name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-back',

                        'href' => $link->getAdminLink('adminMpMassImport', false),
                    ],
                    'settings' => [
                        'title' => $module->l('import settings', self::$name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-cogs',
                        'href' => 'javascript:showImportOptions();',
                    ],
                ],
                'submit' => [
                    'title' => $module->l('Import', self::$name),
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-download',
                ],
            ],
        ];
        $languages = 'getLanguages';
        $allowForm = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        self::$helper = new \HelperForm();
        self::$helper->show_toolbar = true;
        self::$helper->table = $table;
        $lang = new \Language((int) \Configuration::get('PS_LANG_DEFAULT'));
        self::$helper->default_form_language = $lang->id;
        self::$helper->allow_employee_form_lang = $allowForm ? $allowForm : 0;
        self::$helper->identifier = $identifier;
        self::$helper->submit_action = $submitAction;
        self::$helper->currentIndex = $link->getAdminLink($controller_name, false);
        self::$helper->token = \Tools::getAdminTokenLite($controller_name);
        self::$helper->tpl_vars = [
            'fields_value' => [],
            'languages' => $controller->$languages(),
            'id_language' => \Context::getContext()->language->id,
        ];

        return self::$helper->generateForm([$fields_form]);
    }
}
