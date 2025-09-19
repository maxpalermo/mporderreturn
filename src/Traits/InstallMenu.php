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
trait InstallMenu
{
    protected $HIDDEN = -1;
    protected $ROOT = 0;
    protected $ADMINDASHBOARD = 'AdminDashboard';
    protected $SELL = 'SELL';
    protected $ADMINPARENTORDERS = 'AdminParentOrders';
    protected $ADMINORDERS = 'AdminOrders';
    protected $ADMININVOICES = 'AdminInvoices';
    protected $ADMINSLIP = 'AdminSlip';
    protected $ADMINDELIVERYSLIP = 'AdminDeliverySlip';
    protected $ADMINCARTS = 'AdminCarts';
    protected $ADMINCATALOG = 'AdminCatalog';
    protected $ADMINPRODUCTS = 'AdminProducts';
    protected $ADMINCATEGORIES = 'AdminCategories';
    protected $ADMINTRACKING = 'AdminTracking';
    protected $ADMINPARENTATTRIBUTESGROUPS = 'AdminParentAttributesGroups';
    protected $ADMINATTRIBUTESGROUPS = 'AdminAttributesGroups';
    protected $ADMINFEATURES = 'AdminFeatures';
    protected $ADMINPARENTMANUFACTURERS = 'AdminParentManufacturers';
    protected $ADMINMANUFACTURERS = 'AdminManufacturers';
    protected $ADMINSUPPLIERS = 'AdminSuppliers';
    protected $ADMINATTACHMENTS = 'AdminAttachments';
    protected $ADMINPARENTCARTRULES = 'AdminParentCartRules';
    protected $ADMINCARTRULES = 'AdminCartRules';
    protected $ADMINSPECIFICPRICERULE = 'AdminSpecificPriceRule';
    protected $ADMINSTOCKMANAGEMENT = 'AdminStockManagement';
    protected $ADMINPARENTCUSTOMER = 'AdminParentCustomer';
    protected $ADMINCUSTOMERS = 'AdminCustomers';
    protected $ADMINADDRESSES = 'AdminAddresses';
    protected $ADMINOUTSTANDING = 'AdminOutstanding';
    protected $ADMINPARENTCUSTOMERTHREADS = 'AdminParentCustomerThreads';
    protected $ADMINCUSTOMERTHREADS = 'AdminCustomerThreads';
    protected $ADMINORDERMESSAGE = 'AdminOrderMessage';
    protected $ADMINRETURN = 'AdminReturn';
    protected $ADMINSTATS = 'AdminStats';
    protected $ADMINSTOCK = 'AdminStock';
    protected $ADMINWAREHOUSES = 'AdminWarehouses';
    protected $ADMINPARENTSTOCKMANAGEMENT = 'AdminParentStockManagement';
    protected $ADMINSTOCKMVT = 'AdminStockMvt';
    protected $ADMINSTOCKINSTANTSTATE = 'AdminStockInstantState';
    protected $ADMINSTOCKCOVER = 'AdminStockCover';
    protected $ADMINSUPPLYORDERS = 'AdminSupplyOrders';
    protected $ADMINSTOCKCONFIGURATION = 'AdminStockConfiguration';
    protected $IMPROVE = 'IMPROVE';
    protected $ADMINPARENTMODULESSF = 'AdminParentModulesSf';
    protected $ADMINMODULESSF = 'AdminModulesSf';
    protected $ADMINMODULESMANAGE = 'AdminModulesManage';
    protected $ADMINMODULESNOTIFICATIONS = 'AdminModulesNotifications';
    protected $ADMINMODULESUPDATES = 'AdminModulesUpdates';
    protected $ADMINPARENTMODULESCATALOG = 'AdminParentModulesCatalog';
    protected $ADMINMODULESCATALOG = 'AdminModulesCatalog';
    protected $ADMINADDONSCATALOG = 'AdminAddonsCatalog';
    protected $ADMINMODULES = 'AdminModules';
    protected $ADMINPARENTTHEMES = 'AdminParentThemes';
    protected $ADMINTHEMES = 'AdminThemes';
    protected $ADMINTHEMESCATALOG = 'AdminThemesCatalog';
    protected $ADMINPARENTMAILTHEME = 'AdminParentMailTheme';
    protected $ADMINMAILTHEME = 'AdminMailTheme';
    protected $ADMINCMSCONTENT = 'AdminCmsContent';
    protected $ADMINMODULESPOSITIONS = 'AdminModulesPositions';
    protected $ADMINIMAGES = 'AdminImages';
    protected $ADMINPARENTSHIPPING = 'AdminParentShipping';
    protected $ADMINCARRIERS = 'AdminCarriers';
    protected $ADMINSHIPPING = 'AdminShipping';
    protected $ADMINPARENTPAYMENT = 'AdminParentPayment';
    protected $ADMINPAYMENT = 'AdminPayment';
    protected $ADMINPAYMENTPREFERENCES = 'AdminPaymentPreferences';
    protected $ADMININTERNATIONAL = 'AdminInternational';
    protected $ADMINPARENTLOCALIZATION = 'AdminParentLocalization';
    protected $ADMINLOCALIZATION = 'AdminLocalization';
    protected $ADMINLANGUAGES = 'AdminLanguages';
    protected $ADMINCURRENCIES = 'AdminCurrencies';
    protected $ADMINGEOLOCATION = 'AdminGeolocation';
    protected $ADMINPARENTCOUNTRIES = 'AdminParentCountries';
    protected $ADMINZONES = 'AdminZones';
    protected $ADMINCOUNTRIES = 'AdminCountries';
    protected $ADMINSTATES = 'AdminStates';
    protected $ADMINPARENTTAXES = 'AdminParentTaxes';
    protected $ADMINTAXES = 'AdminTaxes';
    protected $ADMINTAXRULESGROUP = 'AdminTaxRulesGroup';
    protected $ADMINTRANSLATIONS = 'AdminTranslations';
    protected $CONFIGURE = 'CONFIGURE';
    protected $SHOPPARAMETERS = 'ShopParameters';
    protected $ADMINPARENTPREFERENCES = 'AdminParentPreferences';
    protected $ADMINPREFERENCES = 'AdminPreferences';
    protected $ADMINMAINTENANCE = 'AdminMaintenance';
    protected $ADMINPARENTORDERPREFERENCES = 'AdminParentOrderPreferences';
    protected $ADMINORDERPREFERENCES = 'AdminOrderPreferences';
    protected $ADMINSTATUSES = 'AdminStatuses';
    protected $ADMINPPREFERENCES = 'AdminPPreferences';
    protected $ADMINPARENTCUSTOMERPREFERENCES = 'AdminParentCustomerPreferences';
    protected $ADMINCUSTOMERPREFERENCES = 'AdminCustomerPreferences';
    protected $ADMINGROUPS = 'AdminGroups';
    protected $ADMINGENDERS = 'AdminGenders';
    protected $ADMINPARENTSTORES = 'AdminParentStores';
    protected $ADMINCONTACTS = 'AdminContacts';
    protected $ADMINSTORES = 'AdminStores';
    protected $ADMINPARENTMETA = 'AdminParentMeta';
    protected $ADMINMETA = 'AdminMeta';
    protected $ADMINSEARCHENGINES = 'AdminSearchEngines';
    protected $ADMINREFERRERS = 'AdminReferrers';
    protected $ADMINPARENTSEARCHCONF = 'AdminParentSearchConf';
    protected $ADMINSEARCHCONF = 'AdminSearchConf';
    protected $ADMINTAGS = 'AdminTags';
    protected $ADMINADVANCEDPARAMETERS = 'AdminAdvancedParameters';
    protected $ADMININFORMATION = 'AdminInformation';
    protected $ADMINPERFORMANCE = 'AdminPerformance';
    protected $ADMINADMINPREFERENCES = 'AdminAdminPreferences';
    protected $ADMINEMAILS = 'AdminEmails';
    protected $ADMINIMPORT = 'AdminImport';
    protected $ADMINPARENTEMPLOYEES = 'AdminParentEmployees';
    protected $ADMINEMPLOYEES = 'AdminEmployees';
    protected $ADMINPROFILES = 'AdminProfiles';
    protected $ADMINACCESS = 'AdminAccess';
    protected $ADMINPARENTREQUESTSQL = 'AdminParentRequestSql';
    protected $ADMINREQUESTSQL = 'AdminRequestSql';
    protected $ADMINBACKUP = 'AdminBackup';
    protected $ADMINLOGS = 'AdminLogs';
    protected $ADMINWEBSERVICE = 'AdminWebservice';
    protected $ADMINSHOPGROUP = 'AdminShopGroup';
    protected $ADMINSHOPURL = 'AdminShopUrl';
    protected $ADMINFEATUREFLAG = 'AdminFeatureFlag';
    protected $ADMINQUICKACCESSES = 'AdminQuickAccesses';
    protected $DEFAULT = 'DEFAULT';
    protected $ADMINPATTERNS = 'AdminPatterns';
    protected $WISHLISTCONFIGURATIONADMINPARENTCONTROLLER = 'WishlistConfigurationAdminParentController';
    protected $WISHLISTCONFIGURATIONADMINCONTROLLER = 'WishlistConfigurationAdminController';
    protected $WISHLISTSTATISTICSADMINCONTROLLER = 'WishlistStatisticsAdminController';
    protected $ADMINDASHGOALS = 'AdminDashgoals';
    protected $ADMINCONFIGUREFAVICONBO = 'AdminConfigureFaviconBo';
    protected $ADMINLINKWIDGET = 'AdminLinkWidget';
    protected $ADMINTHEMESPARENT = 'AdminThemesParent';
    protected $ADMINPSTHEMECUSTOCONFIGURATION = 'AdminPsThemeCustoConfiguration';
    protected $ADMINPSTHEMECUSTOADVANCED = 'AdminPsThemeCustoAdvanced';
    protected $ADMINWELCOME = 'AdminWelcome';
    protected $ADMINGAMIFICATION = 'AdminGamification';
    protected $ADMINAJAXPSGDPR = 'AdminAjaxPsgdpr';
    protected $ADMINDOWNLOADINVOICESPSGDPR = 'AdminDownloadInvoicesPsgdpr';
    protected $ADMINPSMBOMODULE = 'AdminPsMboModule';
    protected $ADMINPSMBOADDONS = 'AdminPsMboAddons';
    protected $ADMINPSMBORECOMMENDED = 'AdminPsMboRecommended';
    protected $ADMINPSMBOTHEME = 'AdminPsMboTheme';
    protected $ADMINAJAXPS_BUYBUTTONLITE = 'AdminAjaxPs_buybuttonlite';
    protected $ADMINMETRICSLEGACYSTATSCONTROLLER = 'AdminMetricsLegacyStatsController';
    protected $ADMINMETRICSCONTROLLER = 'AdminMetricsController';
    protected $MARKETING = 'Marketing';
    protected $ADMINPSFACEBOOKMODULE = 'AdminPsfacebookModule';
    protected $ADMINAJAXPSFACEBOOK = 'AdminAjaxPsfacebook';
    protected $ADMINPSXMKTGWITHGOOGLEMODULE = 'AdminPsxMktgWithGoogleModule';
    protected $ADMINAJAXPSXMKTGWITHGOOGLE = 'AdminAjaxPsxMktgWithGoogle';
    protected $ADMINBLOCKLISTING = 'AdminBlockListing';
    protected $ADMINSELFUPGRADE = 'AdminSelfUpgrade';
    protected $ADMINETSEMMIGRATE = 'AdminETSEMMigrate';
    protected $ADMINETSEMDOWNLOAD = 'AdminETSEMDownload';

    /**
     * Install a new menu
     *
     * @param string $name Tab name
     * @param string $module_name Module name
     * @param string $parent Parent tab name
     * @param string $controller Controller class name
     * @param string $icon Material Icon name
     * @param string $wording Wording type
     * @param string $wording_domain Wording domain
     * @param bool $active If true, Tab menu will be shown
     * @param bool $enabled If true Tab menu is enabled
     *
     * @return bool True if successful, False otherwise
     */
    protected function installMenu(
        string $name,
        string $module_name,
        string $parent,
        string $controller,
        bool $active = true
    ) {
        // Create new admin tab
        $tab = new \Tab();

        if ($parent != $this->HIDDEN) {
            $id_parent = \Tab::getIdFromClassName($parent);
            $tab->id_parent = (int) $id_parent;
        } else {
            $tab->id_parent = -1;
        }
        $tab->name = [];

        if (!is_array($name)) {
            foreach (\Language::getLanguages(true) as $lang) {
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
     *
     * @param string|array $className Class name of the controller
     *
     * @return bool True if successful, False otherwise
     */
    protected function uninstallMenu($className)
    {
        $result = true;
        if (is_array($className)) {
            foreach ($className as $menu) {
                $result = $result && $this->uninstallTab($menu);
            }
        } else {
            $result = $this->uninstallTab($className);
        }

        return $result;
    }

    private function uninstallTab($className)
    {
        $id_tab = \Tab::getIdFromClassName($className);
        if ($id_tab) {
            $tab = new \Tab((int) $id_tab);

            return $tab->delete();
        }

        return true;
    }

    public static function insertValueAtPosition($arr, $insertedArray, $position)
    {
        $i = 0;
        $new_array = [];
        foreach ($arr as $key => $value) {
            if ($i == $position) {
                foreach ($insertedArray as $ikey => $ivalue) {
                    $new_array[$ikey] = $ivalue;
                }
            }
            $new_array[$key] = $value;
            ++$i;
        }

        return $new_array;
    }

    public function installHooks(\Module $module, Array $hooks)
    {
        foreach ($hooks as $hook) {
            if (!$module->registerHook($hook)) {
                return false;
            };
        }

        return true;
    }
}
