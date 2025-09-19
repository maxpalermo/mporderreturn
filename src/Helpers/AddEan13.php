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
use Validate;

class AddEan13
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
        ModelImportEan13::truncate();

        //Tools::dieObject($products);
        foreach ($rows as &$row) {
            $this->insertToTable($row);
        }
    }

    protected function getIdFromReference($reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product_attribute')
            ->from('product_attribute')
            ->where('reference = \'' . pSQL($reference) . '\'');

        return (int) $db->getValue($sql);
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

    protected function insertToTable($row)
    {
        $module = $this->module;
        $controller = $this->controller;

        try {
            $info = [];
            foreach ($row as $key => $value) {
                $info[] = "{$key}:{$value}";
            }
            unset($key, $value);
            $info = implode('<br>', $info);

            if (!isset($row['ean13']) || !$row['ean13']) {
                $controller->warnings[] = $this->module->l('Found product without ean13', $this->controller_name)
                    . '<br>' . $info . '<hr>';
            }

            $id = 0;

            if (isset($row['id_product_attribute'])) {
                $id = (int) $row['id_product_attribute'];
            } else {
                if (!isset($row['reference']) || !trim($row['reference'])) {
                    $controller->warnings[] = $this->module->l('Found product without reference', $this->controller_name);

                    return false;
                } else {
                    $id = (int) $this->getIdFromReference($row['reference']);
                }
            }

            $model = new ModelImportEan13($id);
            if ($id) {
                $model->force_id = true;
                $model->id = $id;
            }
            if (!$model->id_product && $model->id) {
                $model->id_product = (int) $this->getIdProduct($model->id);
            }
            $model->reference = $row['reference'];
            $model->ean13 = $row['ean13'];
            $model->json = $row;

            $res = $model->add();
            if ($res) {
                $controller->confirmations[] = sprintf(
                    $module->l('Product %s inserted. %s', $this->controller_name),
                    $model->reference,
                    '<br>'
                );
            } else {
                $controller->confirmations[] = sprintf(
                    $module->l('Product not inserted. %s %s %s', $this->controller_name),
                    $info,
                    '<br>',
                    Db::getInstance()->getMsgError()
                );
            }
        } catch (\Throwable $th) {
            $err = $th->getMessage();
            $controller->errors[] = $err;
        }

        return true;
    }
}
