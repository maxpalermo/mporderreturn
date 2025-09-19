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

namespace MpSoft\MpMassImport\Helpers;

use Configuration;
use Context;
use Module;

class ImportSettings
{
    public const MPMASSIMPORT_EAN13 = 'MPMASSIMPORT_EAN13';
    public const MPMASSIMPORT_SUPPLIER_REFERENCE = 'MPMASSIMPORT_SUPPLIER_REFERENCE';
    public const MPMASSIMPORT_PRODUCT_NAME = 'MPMASSIMPORT_PRODUCT_NAME';
    public const MPMASSIMPORT_ID_SUPPLIER = 'MPMASSIMPORT_ID_SUPPLIER';
    public const MPMASSIMPORT_CONDITION = 'MPMASSIMPORT_CONDITION';
    public const MPMASSIMPORT_WS_PRICE = 'MPMASSIMPORT_WS_PRICE';
    public const MPMASSIMPORT_PRICE = 'MPMASSIMPORT_PRICE';
    public const MPMASSIMPORT_IS_VIRTUAL = 'MPMASSIMPORT_IS_VIRTUAL';
    public const MPMASSIMPORT_DESCRIPTION_SHORT = 'MPMASSIMPORT_DESCRIPTION_SHORT';
    public const MPMASSIMPORT_DESCRIPTION_LONG = 'MPMASSIMPORT_DESCRIPTION_LONG';
    public const MPMASSIMPORT_ID_MANUFACTURER = 'MPMASSIMPORT_ID_MANUFACTURER';
    public const MPMASSIMPORT_ID_TAX_RULES_GROUP = 'MPMASSIMPORT_ID_TAX_RULES_GROUP';
    public const MPMASSIMPORT_CATEGORIES = 'MPMASSIMPORT_CATEGORIES';
    public const MPMASSIMPORT_IMAGES = 'MPMASSIMPORT_IMAGES';
    public const MPMASSIMPORT_SUPPLIERS = 'MPMASSIMPORT_SUPPLIERS';
    public const MPMASSIMPORT_QUANTITIES = 'MPMASSIMPORT_QUANTITIES';
    public const MPMASSIMPORT_ATTRIBUTES = 'MPMASSIMPORT_ATTRIBUTES';
    public const MPMASSIMPORT_FEATURES = 'MPMASSIMPORT_FEATURES';
    public const MPMASSIMPORT_COMBINATIONS = 'MPMASSIMPORT_COMBINATIONS';
    public const MPMASSIMPORT_DELETE_COMBINATIONS = 'MPMASSIMPORT_DELETE_COMBINATIONS';

    /** @var Module */
    protected $module;

    /** @var ModuleAdminController */
    protected $controller;
    /** @var string */
    protected $controller_name;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->controller_name = (new GetControllerName($controller))->get();
        $this->module = $controller->module;
    }

    public function getFormSettings()
    {
        $smarty = Context::getContext()->smarty;
        $template = $this->module->getLocalPath() . 'views/templates/admin/form/ProductImportSettings.tpl';
        $switch = $this->module->getLocalPath() . 'views/templates/admin/partials/switch.tpl';
        $switches = [
            [
                'label' => $this->module->l('Import EAN13', $this->controller_name),
                'name' => 'MPMASSIMPORT_EAN13',
                'value' => Configuration::get(self::MPMASSIMPORT_EAN13),
            ],
            [
                'label' => $this->module->l('Import Supplier reference', $this->controller_name),
                'name' => 'MPMASSIMPORT_SUPPLIER_REFERENCE',
                'value' => Configuration::get(self::MPMASSIMPORT_SUPPLIER_REFERENCE),
            ],
            [
                'label' => $this->module->l('Import default supplier', $this->controller_name),
                'name' => 'MPMASSIMPORT_ID_SUPPLIER',
                'value' => Configuration::get(self::MPMASSIMPORT_ID_SUPPLIER),
            ],
            [
                'label' => $this->module->l('Import wholesale price', $this->controller_name),
                'name' => 'MPMASSIMPORT_WS_PRICE',
                'value' => Configuration::get(self::MPMASSIMPORT_WS_PRICE),
            ],
            [
                'label' => $this->module->l('Import price', $this->controller_name),
                'name' => 'MPMASSIMPORT_PRICE',
                'value' => Configuration::get(self::MPMASSIMPORT_PRICE),
            ],
            [
                'label' => $this->module->l('Import description short', $this->controller_name),
                'name' => 'MPMASSIMPORT_DESCRIPTION_SHORT',
                'value' => Configuration::get(self::MPMASSIMPORT_DESCRIPTION_SHORT),
            ],
            [
                'label' => $this->module->l('Import description long', $this->controller_name),
                'name' => 'MPMASSIMPORT_DESCRIPTION_LONG',
                'value' => Configuration::get(self::MPMASSIMPORT_DESCRIPTION_LONG),
            ],
            [
                'label' => $this->module->l('Import manufacturer', $this->controller_name),
                'name' => 'MPMASSIMPORT_ID_MANUFACTURER',
                'value' => Configuration::get(self::MPMASSIMPORT_ID_MANUFACTURER),
            ],
            [
                'label' => $this->module->l('Import tax', $this->controller_name),
                'name' => 'MPMASSIMPORT_ID_TAX_RULES_GROUP',
                'value' => Configuration::get(self::MPMASSIMPORT_ID_TAX_RULES_GROUP),
            ],
            [
                'label' => $this->module->l('Import categories', $this->controller_name),
                'name' => 'MPMASSIMPORT_CATEGORIES',
                'value' => Configuration::get(self::MPMASSIMPORT_CATEGORIES),
            ],
            [
                'label' => $this->module->l('Import images', $this->controller_name),
                'name' => 'MPMASSIMPORT_IMAGES',
                'value' => Configuration::get(self::MPMASSIMPORT_IMAGES),
            ],
            [
                'label' => $this->module->l('Import suppliers', $this->controller_name),
                'name' => 'MPMASSIMPORT_SUPPLIERS',
                'value' => Configuration::get(self::MPMASSIMPORT_SUPPLIERS),
            ],
            [
                'label' => $this->module->l('Import quantities', $this->controller_name),
                'name' => 'MPMASSIMPORT_QUANTITIES',
                'value' => Configuration::get(self::MPMASSIMPORT_QUANTITIES),
            ],
            [
                'label' => $this->module->l('Import features', $this->controller_name),
                'name' => 'MPMASSIMPORT_FEATURES',
                'value' => Configuration::get(self::MPMASSIMPORT_FEATURES),
            ],
            [
                'label' => $this->module->l('Import combinations', $this->controller_name),
                'name' => 'MPMASSIMPORT_COMBINATIONS',
                'value' => Configuration::get(self::MPMASSIMPORT_COMBINATIONS),
            ],
            [
                'label' => $this->module->l('Delete old combinations', $this->controller_name),
                'name' => 'MPMASSIMPORT_DELETE_COMBINATIONS',
                'value' => Configuration::get(self::MPMASSIMPORT_DELETE_COMBINATIONS),
            ],
        ];
        $smarty->assign([
            'switch_path' => $switch,
            'switches' => $switches,
            'class_name' => 'switch_import_settings',
        ]);

        return $smarty->fetch($template);
    }
}
