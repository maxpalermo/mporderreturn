<?php
/*
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpMassImport\Install;

use Language;
use Tab;

class InstallMenu
{
    public const HIDDEN = -1;
    public const ROOT = 0;
    public const ADMINDASHBOARD = 'AdminDashboard';
    public const SELL = 'SELL';
    public const ADMINPARENTORDERS = 'AdminParentOrders';
    public const ADMINORDERS = 'AdminOrders';
    public const ADMININVOICES = 'AdminInvoices';
    public const ADMINSLIP = 'AdminSlip';
    public const ADMINDELIVERYSLIP = 'AdminDeliverySlip';
    public const ADMINCARTS = 'AdminCarts';
    public const ADMINCATALOG = 'AdminCatalog';
    public const ADMINPRODUCTS = 'AdminProducts';
    public const ADMINCATEGORIES = 'AdminCategories';
    public const ADMINTRACKING = 'AdminTracking';
    public const ADMINPARENTATTRIBUTESGROUPS = 'AdminParentAttributesGroups';
    public const ADMINATTRIBUTESGROUPS = 'AdminAttributesGroups';
    public const ADMINFEATURES = 'AdminFeatures';
    public const ADMINPARENTMANUFACTURERS = 'AdminParentManufacturers';
    public const ADMINMANUFACTURERS = 'AdminManufacturers';
    public const ADMINSUPPLIERS = 'AdminSuppliers';
    public const ADMINATTACHMENTS = 'AdminAttachments';
    public const ADMINPARENTCARTRULES = 'AdminParentCartRules';
    public const ADMINCARTRULES = 'AdminCartRules';
    public const ADMINSPECIFICPRICERULE = 'AdminSpecificPriceRule';
    public const ADMINSTOCKMANAGEMENT = 'AdminStockManagement';
    public const ADMINPARENTCUSTOMER = 'AdminParentCustomer';
    public const ADMINCUSTOMERS = 'AdminCustomers';
    public const ADMINADDRESSES = 'AdminAddresses';
    public const ADMINOUTSTANDING = 'AdminOutstanding';
    public const ADMINPARENTCUSTOMERTHREADS = 'AdminParentCustomerThreads';
    public const ADMINCUSTOMERTHREADS = 'AdminCustomerThreads';
    public const ADMINORDERMESSAGE = 'AdminOrderMessage';
    public const ADMINRETURN = 'AdminReturn';
    public const ADMINSTATS = 'AdminStats';
    public const ADMINSTOCK = 'AdminStock';
    public const ADMINWAREHOUSES = 'AdminWarehouses';
    public const ADMINPARENTSTOCKMANAGEMENT = 'AdminParentStockManagement';
    public const ADMINSTOCKMVT = 'AdminStockMvt';
    public const ADMINSTOCKINSTANTSTATE = 'AdminStockInstantState';
    public const ADMINSTOCKCOVER = 'AdminStockCover';
    public const ADMINSUPPLYORDERS = 'AdminSupplyOrders';
    public const ADMINSTOCKCONFIGURATION = 'AdminStockConfiguration';
    public const IMPROVE = 'IMPROVE';
    public const ADMINPARENTMODULESSF = 'AdminParentModulesSf';
    public const ADMINMODULESSF = 'AdminModulesSf';
    public const ADMINMODULESMANAGE = 'AdminModulesManage';
    public const ADMINMODULESNOTIFICATIONS = 'AdminModulesNotifications';
    public const ADMINMODULESUPDATES = 'AdminModulesUpdates';
    public const ADMINPARENTMODULESCATALOG = 'AdminParentModulesCatalog';
    public const ADMINMODULESCATALOG = 'AdminModulesCatalog';
    public const ADMINADDONSCATALOG = 'AdminAddonsCatalog';
    public const ADMINMODULES = 'AdminModules';
    public const ADMINPARENTTHEMES = 'AdminParentThemes';
    public const ADMINTHEMES = 'AdminThemes';
    public const ADMINTHEMESCATALOG = 'AdminThemesCatalog';
    public const ADMINPARENTMAILTHEME = 'AdminParentMailTheme';
    public const ADMINMAILTHEME = 'AdminMailTheme';
    public const ADMINCMSCONTENT = 'AdminCmsContent';
    public const ADMINMODULESPOSITIONS = 'AdminModulesPositions';
    public const ADMINIMAGES = 'AdminImages';
    public const ADMINPARENTSHIPPING = 'AdminParentShipping';
    public const ADMINCARRIERS = 'AdminCarriers';
    public const ADMINSHIPPING = 'AdminShipping';
    public const ADMINPARENTPAYMENT = 'AdminParentPayment';
    public const ADMINPAYMENT = 'AdminPayment';
    public const ADMINPAYMENTPREFERENCES = 'AdminPaymentPreferences';
    public const ADMININTERNATIONAL = 'AdminInternational';
    public const ADMINPARENTLOCALIZATION = 'AdminParentLocalization';
    public const ADMINLOCALIZATION = 'AdminLocalization';
    public const ADMINLANGUAGES = 'AdminLanguages';
    public const ADMINCURRENCIES = 'AdminCurrencies';
    public const ADMINGEOLOCATION = 'AdminGeolocation';
    public const ADMINPARENTCOUNTRIES = 'AdminParentCountries';
    public const ADMINZONES = 'AdminZones';
    public const ADMINCOUNTRIES = 'AdminCountries';
    public const ADMINSTATES = 'AdminStates';
    public const ADMINPARENTTAXES = 'AdminParentTaxes';
    public const ADMINTAXES = 'AdminTaxes';
    public const ADMINTAXRULESGROUP = 'AdminTaxRulesGroup';
    public const ADMINTRANSLATIONS = 'AdminTranslations';
    public const CONFIGURE = 'CONFIGURE';
    public const SHOPPARAMETERS = 'ShopParameters';
    public const ADMINPARENTPREFERENCES = 'AdminParentPreferences';
    public const ADMINPREFERENCES = 'AdminPreferences';
    public const ADMINMAINTENANCE = 'AdminMaintenance';
    public const ADMINPARENTORDERPREFERENCES = 'AdminParentOrderPreferences';
    public const ADMINORDERPREFERENCES = 'AdminOrderPreferences';
    public const ADMINSTATUSES = 'AdminStatuses';
    public const ADMINPPREFERENCES = 'AdminPPreferences';
    public const ADMINPARENTCUSTOMERPREFERENCES = 'AdminParentCustomerPreferences';
    public const ADMINCUSTOMERPREFERENCES = 'AdminCustomerPreferences';
    public const ADMINGROUPS = 'AdminGroups';
    public const ADMINGENDERS = 'AdminGenders';
    public const ADMINPARENTSTORES = 'AdminParentStores';
    public const ADMINCONTACTS = 'AdminContacts';
    public const ADMINSTORES = 'AdminStores';
    public const ADMINPARENTMETA = 'AdminParentMeta';
    public const ADMINMETA = 'AdminMeta';
    public const ADMINSEARCHENGINES = 'AdminSearchEngines';
    public const ADMINREFERRERS = 'AdminReferrers';
    public const ADMINPARENTSEARCHCONF = 'AdminParentSearchConf';
    public const ADMINSEARCHCONF = 'AdminSearchConf';
    public const ADMINTAGS = 'AdminTags';
    public const ADMINADVANCEDPARAMETERS = 'AdminAdvancedParameters';
    public const ADMININFORMATION = 'AdminInformation';
    public const ADMINPERFORMANCE = 'AdminPerformance';
    public const ADMINADMINPREFERENCES = 'AdminAdminPreferences';
    public const ADMINEMAILS = 'AdminEmails';
    public const ADMINIMPORT = 'AdminImport';
    public const ADMINPARENTEMPLOYEES = 'AdminParentEmployees';
    public const ADMINEMPLOYEES = 'AdminEmployees';
    public const ADMINPROFILES = 'AdminProfiles';
    public const ADMINACCESS = 'AdminAccess';
    public const ADMINPARENTREQUESTSQL = 'AdminParentRequestSql';
    public const ADMINREQUESTSQL = 'AdminRequestSql';
    public const ADMINBACKUP = 'AdminBackup';
    public const ADMINLOGS = 'AdminLogs';
    public const ADMINWEBSERVICE = 'AdminWebservice';
    public const ADMINSHOPGROUP = 'AdminShopGroup';
    public const ADMINSHOPURL = 'AdminShopUrl';
    public const ADMINFEATUREFLAG = 'AdminFeatureFlag';
    public const ADMINQUICKACCESSES = 'AdminQuickAccesses';
    public const DEFAULT = 'DEFAULT';
    public const ADMINPATTERNS = 'AdminPatterns';
    public const WISHLISTCONFIGURATIONADMINPARENTCONTROLLER = 'WishlistConfigurationAdminParentController';
    public const WISHLISTCONFIGURATIONADMINCONTROLLER = 'WishlistConfigurationAdminController';
    public const WISHLISTSTATISTICSADMINCONTROLLER = 'WishlistStatisticsAdminController';
    public const ADMINDASHGOALS = 'AdminDashgoals';
    public const ADMINCONFIGUREFAVICONBO = 'AdminConfigureFaviconBo';
    public const ADMINLINKWIDGET = 'AdminLinkWidget';
    public const ADMINTHEMESPARENT = 'AdminThemesParent';
    public const ADMINPSTHEMECUSTOCONFIGURATION = 'AdminPsThemeCustoConfiguration';
    public const ADMINPSTHEMECUSTOADVANCED = 'AdminPsThemeCustoAdvanced';
    public const ADMINWELCOME = 'AdminWelcome';
    public const ADMINGAMIFICATION = 'AdminGamification';
    public const ADMINAJAXPSGDPR = 'AdminAjaxPsgdpr';
    public const ADMINDOWNLOADINVOICESPSGDPR = 'AdminDownloadInvoicesPsgdpr';
    public const ADMINPSMBOMODULE = 'AdminPsMboModule';
    public const ADMINPSMBOADDONS = 'AdminPsMboAddons';
    public const ADMINPSMBORECOMMENDED = 'AdminPsMboRecommended';
    public const ADMINPSMBOTHEME = 'AdminPsMboTheme';
    public const ADMINAJAXPS_BUYBUTTONLITE = 'AdminAjaxPs_buybuttonlite';
    public const ADMINMETRICSLEGACYSTATSCONTROLLER = 'AdminMetricsLegacyStatsController';
    public const ADMINMETRICSCONTROLLER = 'AdminMetricsController';
    public const MARKETING = 'Marketing';
    public const ADMINPSFACEBOOKMODULE = 'AdminPsfacebookModule';
    public const ADMINAJAXPSFACEBOOK = 'AdminAjaxPsfacebook';
    public const ADMINPSXMKTGWITHGOOGLEMODULE = 'AdminPsxMktgWithGoogleModule';
    public const ADMINAJAXPSXMKTGWITHGOOGLE = 'AdminAjaxPsxMktgWithGoogle';
    public const ADMINBLOCKLISTING = 'AdminBlockListing';
    public const ADMINSELFUPGRADE = 'AdminSelfUpgrade';
    public const ADMINETSEMMIGRATE = 'AdminETSEMMigrate';
    public const ADMINETSEMDOWNLOAD = 'AdminETSEMDownload';

    /**
     * Install a new menu
     * @param string $name Tab name
     * @param string $module_name Module name
     * @param string $parent Parent tab name
     * @param string $controller Controller class name
     * @param string $icon Material Icon name
     * @param string $wording Wording type
     * @param string $wording_domain Wording domain
     * @param bool $active If true, Tab menu will be shown
     * @param bool $enabled If true Tab menu is enabled
     * @return bool True if successful, False otherwise
     */
    public static function install(
        string $name,
        string $module_name,
        string $parent,
        string $controller,
        bool $active = true
    ) {
        // Create new admin tab
        $tab = new Tab();

        if ($parent != self::HIDDEN) {
            $id_parent = Tab::getIdFromClassName($parent);
            $tab->id_parent = (int) $id_parent;
        } else {
            $tab->id_parent = -1;
        }
        $tab->name = [];

        if (!is_array($name)) {
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $name;
            }
        } else {
            foreach ($name as $name_lang) {
                $tab->name[$name_lang['id_lang']] = $name_lang['name'];
            }
        }

        $tab->class_name = $controller;
        $tab->module = $module_name;
        $tab->active = $active;
        $result = $tab->add();

        return $result;
    }

    /**
     * Uninstall a menu
     * @param string $className Class name of the controller
     * @return bool True if successful, False otherwise
     */
    public static function uninstall($className)
    {
        $id_tab = Tab::getIdFromClassName($className);
        if ($id_tab) {
            $tab = new Tab((int) $id_tab);

            return $tab->delete();
        }

        return true;
    }
}
