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

namespace MpSoft\MpMassImport\Forms;

require_once _PS_MODULE_DIR_ . 'mpmassimport/traits/autoload.php';

class HelperFormImportExcel extends HelperForm
{
    public function getFieldsForm()
    {
        $name = $this->extractClassName($this);

        /** @var \ModuleAdminController */
        $controller = \Context::getContext()->controller;
        /** @var \Module */
        $module = $controller->module;
        /** @var string */
        $controller_name = $this->extractClassName($controller);
        /** @var \Link */
        $link = \Context::getContext()->link;

        $fields_form = [
            'form' => [
                // 'tinymce' => true,
                'legend' => [
                    'title' => $module->l('Import Excel file', $name),
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

                        'href' => $link->getAdminLink($controller_name, true),
                    ],
                    'settings' => [
                        'title' => $module->l('import settings', $name),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-cogs',
                        'href' => 'javascript:showImportOptions();',
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
}
