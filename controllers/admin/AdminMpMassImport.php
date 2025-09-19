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
class AdminMpMassImportController extends ModuleAdminController
{
    protected $translator;

    public function __construct()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $action = 'getTranslator';
            $this->translator = Context::getContext()->$action();
        }
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->link = $this->context->link;
        $this->db = Db::getInstance();
        $this->smarty = $this->context->smarty;
        $this->bootstrap = true;
        $this->className = 'ModelImportProduct';
        $this->identifier = 'id_product';
        $this->adminClassName = 'AdminMpMassImport';
        $this->bulk_actions = [];
        $this->fields_list = [];
        $this->fields_form = [];

        parent::__construct();
    }

    public function setHelperDisplay(Helper $helper)
    {
        $helper->force_show_bulk_actions = true;
        $this->list_no_link = true;
        parent::setHelperDisplay($helper);
    }

    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addCSS($this->context->controller->module->getLocalPath() . 'views/css/process-icon.css');

        return parent::setMedia();
    }

    /**
     *
     * Get file extension
     *
     * @param array $file The file params from Tools::fileAttachment()
     *
     * @return string $extension The extension file
     *
     **/
    protected function getExtension(?array $file)
    {
        $pathinfo = pathinfo($file['tmp_name']);
        $extension = $pathinfo['extension'];

        return $extension;
    }

    public function initContent()
    {
        $template = $this->module->getLocalPath() . 'views/templates/admin/mainmenu.tpl';
        $icons = [
            [
                'label' => $this->l('Import Products'),
                'icon' => 'icon-cart-arrow-down',
                'url' => $this->link->getAdminLink('AdminMpMassImportProducts'),
            ],
            [
                'label' => $this->l('Import Barcodes'),
                'icon' => 'icon-barcode',
                'url' => $this->link->getAdminLink('AdminMpMassImportEan13'),
                'disabled' => true,
            ],
            [
                'label' => $this->l('Import Prices'),
                'icon' => 'icon-money',
                'url' => $this->link->getAdminLink('AdminMpMassImportPrices'),
            ],
            [
                'label' => $this->l('Import Attributes'),
                'icon' => 'icon-list-ol',
                'url' => $this->link->getAdminLink('AdminMpMassImportAttributes'),
                'disabled' => true,
            ],
            [
                'label' => $this->l('Import Features'),
                'icon' => 'icon-list-ul',
                'url' => $this->link->getAdminLink('AdminMpMassImportFeatures'),
                'disabled' => true,
            ],
            [
                'label' => $this->l('Import Combinations'),
                'icon' => 'icon-refresh',
                'url' => $this->link->getAdminLink('AdminMpMassImportCombinations'),
            ],
            [
                'label' => $this->l('Export Products'),
                'icon' => 'icon-upload',
                'url' => $this->link->getAdminLink('AdminMpMassExport'),
            ],
            [
                'label' => $this->l('Update Products'),
                'icon' => 'icon-refresh',
                'url' => $this->link->getAdminLink('AdminMpMassUpdateProducts'),
            ],
            [
                'label' => $this->l('Update Combinations'),
                'icon' => 'icon-list',
                'url' => $this->link->getAdminLink('AdminMpMassUpdateCombinations'),
            ],
        ];
        $this->smarty->assign('icons', $icons);
        $html = $this->smarty->fetch($template);
        $this->content = $html;
        parent::initContent();
    }
}
