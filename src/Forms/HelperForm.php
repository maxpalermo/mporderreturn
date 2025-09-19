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
class HelperForm extends \HelperForm
{
    use \MpSoft\MpMassImport\Traits\Tools;

    public function __construct($title, $table, $identifier, $submitAction)
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

        /** @var \ModuleAdminController */
        $controller = \Context::getContext()->controller;
        $controller_name = $this->extractClassName($controller);
        $currentIndex = \Context::getContext()->link->getAdminLink($controller_name, false);
        $token = \Tools::getAdminTokenLite($controller_name);

        $this->show_toolbar = true;
        $this->title = $title;
        $this->table = $table;
        $this->default_form_language = $lang->id;
        $this->allow_employee_form_lang = $allowForm ? $allowForm : 0;
        $this->identifier = $identifier;
        $this->submit_action = $submitAction;
        $this->currentIndex = $currentIndex;
        $this->token = $token;
        $this->tpl_vars = [
            'fields_value' => [],
            'languages' => $languages,
            'id_language' => $id_lang,
        ];

        parent::__construct();
    }

    public function renderForm()
    {
        $fields_form = $this->getFieldsForm();

        return $this->generateForm([$fields_form]);
    }

    public function getFieldsForm()
    {
        return [];
    }
}
