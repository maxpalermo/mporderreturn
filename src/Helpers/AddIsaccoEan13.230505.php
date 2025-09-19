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

use Context;
use Db;
use DbQuery;
use ModelImportEan13;
use Module;
use ModuleAdminController;
use ObjectModel;

class AddIsaccoEan13
{
    /** @var Module */
    protected $module;
    /** @var ModuleAdminController */
    protected $controller;
    /** @var string */
    protected $controller_name;

    public function __construct($controller)
    {
        /** @var ModuleAdminController */
        $this->controller = $controller;

        /** @var string */
        $this->controller_name = (new GetControllerName($controller))->get();

        /** @var Module */
        $this->module = Module::getInstanceByName('mpmassimport');
    }

    public function addToTable($rows)
    {
        $db = Db::getInstance();
        $db->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . ModelImportEan13::$definition['table']);

        //Tools::dieObject($products);
        foreach ($rows as &$row) {
            $this->insertToTable($row);
        }
    }

    protected function insertToTable($row)
    {
        $module = $this->module;
        $controller = $this->controller;

        try {
            $id = (int) $row['id_product_attribute'];
            if (!$id || !isset($row['ean13'])) {
                return false;
            }
            $model = new ModelImportEan13($id);
            $model->force_id = true;
            $model->id = $id;
            $model->id_product = (int) $row['id_product'];
            $model->reference = $row['reference'];
            $model->ean13 = $row['ean13'];
            $model->json = $row;

            if (!$model->reference) {
                $model->reference = '--';
            }

            $res = $model->add();
            if ($res) {
                $controller->confirmations[] = sprintf(
                    $module->l('Product %s inserted. %s', $this->controller_name),
                    $model->reference,
                    '<br>'
                );
            }
        } catch (\Throwable $th) {
            $err = $th->getMessage();
            $controller->errors[] = $err;
        }

        return true;
    }
}
