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
    /** @var string */
    protected $name;

    public function __construct($controller)
    {
        /** @var ModuleAdminController */
        $this->controller = $controller;

        /** @var string */
        $this->controller_name = (new GetControllerName($controller))->get();

        /** @var string */
        $this->name = (new GetControllerName($this))->get();

        /** @var Module */
        $this->module = Module::getInstanceByName('mpmassimport');
    }

    public function addToTable($rows)
    {
        $db = Db::getInstance();
        ModelImportEan13::truncate();

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
            $id_product = isset($row['id_product']) ? (int) $row['id_product'] : 0;

            if (!$id || !isset($row['ean13'])) {
                $this->controller->warnings[] = $this->module->l('Found Product with no ean13.', $this->name);

                return false;
            }

            if (!isset($row['reference']) || !$row['reference']) {
                $this->controller->warnings[] = $this->module->l('Found Product with no reference.', $this->name);

                return false;
            }

            if ($id && !$id_product) {
                $id_product = $this->getIdProduct($id);
            }

            $model = new ModelImportEan13($id);
            $model->force_id = true;
            $model->id = $id;
            $model->id_product = $id_product;
            $model->reference = $row['reference'];
            $model->ean13 = $row['ean13'];
            $model->json = $row;

            if (!$model->reference) {
                $model->reference = '--';
            }

            $res = $model->add();
            if ($res) {
                $controller->confirmations[] = sprintf(
                    $module->l('Product %s inserted. %s', $this->name),
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

    protected function getIdProduct($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product_attribute')
            ->where('id_product_attribute = \'' . (int) $id_product_attribute . '\'');

        return (int) $db->getValue($sql);
    }
}
