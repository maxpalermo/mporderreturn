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

namespace MpSoft\MpMassImport\Helpers;

class HelperForm
{
    public function generate(
        $table,
        $identifier,
        $submitAction,
        $currentIndex,
        $token,
        $fields_form = null,
        $title = '',
        $fields_values = []
    ) {
        if (!$fields_form) {
            return false;
        }

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

        return $helper->generateForm([$fields_form]);
    }
}
