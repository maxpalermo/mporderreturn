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
if (!defined('_PS_VERSION_')) {
    exit;
}

$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $path . 'vendor/autoload.php';

use MpSoft\MpOrderReturn\Traits\InstallHooks;

class MpOrderReturn extends Module
{
    use InstallHooks;

    protected $id_lang;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->name = 'mporderreturn';
        $this->tab = 'administration';
        $this->version = '0.1.0';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('MP Stampa modulo reso');
        $this->description = $this->l('Sostituisce la stampa del modulo di reso');
        $this->id_lang = (int) Context::getContext()->language->id;
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(ShopCore::CONTEXT_ALL);
        }

        $hooks = [
            'actionAdminControllerSetMedia',
            'displayFooter',
            'displayBackOfficeFooter',
        ];

        $res =
            parent::install() &&
            $this->installHooks($this, $hooks);

        return $res;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $css_icons = $this->getLocalPath() . 'views/css/icons.css';
        $controller = Context::getContext()->controller;
        $controller->addCSS($css_icons);
    }

    public function hookDisplayBackOfficeFooter($params)
    {
        $controller = Tools::getValue('controller');
        $moduleFrontController = $this->context->link->getModuleLink($this->name, 'OrderReturn');
        if (preg_match('/AdminReturn/i', $controller) && $id_order_return = (int) Tools::getValue('id_order_return')) {
            $tplPath = $this->getLocalPath() . 'views/templates/admin/order_return.tpl';
            $tpl = $this->context->smarty->createTemplate($tplPath);
            $tpl->assign([
                'id_employee' => $this->context->employee->id,
                'id_order_return' => $id_order_return,
                'moduleFrontController' => $moduleFrontController,
            ]);

            return $tpl->fetch();
        }
    }

    public function hookDisplayFooter($params)
    {
        $controller = Tools::getValue('controller');
        $moduleFrontController = $this->context->link->getModuleLink($this->name, 'OrderReturn');
        if (preg_match('/orderfollow/i', $controller)) {
            $tplPath = $this->getLocalPath() . 'views/templates/front/order-follow.tpl';
            $tpl = $this->context->smarty->createTemplate($tplPath);
            $tpl->assign([
                'moduleFrontController' => $moduleFrontController,
            ]);

            return $tpl->fetch();
        }
    }
}
