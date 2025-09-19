<?php

namespace MpSoft\MpMassImport\Helpers;

use Combination;
use Context;
use ModelImportCombination;
use Module;
use ModuleAdminController;
use ObjectModel;

class AddCombinations
{
    protected static $controller_name;
    public static function addToTable($items)
    {
        ModelImportCombination::truncate();
        foreach ($items as $item) {
            $res = ModelImportCombination::sanitizeField($item);
            if ($res) {
                self::insertToTable($item);
            }
        }
    }

    private static function insertToTable($item)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var ObjectModel */
        $model = new ModelImportCombination();

        if (!(int) $item['id_product_attribute']) {
            $controller->errors[] = $module->l('Unable to import. Combination has no id.', self::$controller_name);

            return false;
        }
        if (!(int) $item['id_product']) {
            $controller->errors[] = $module->l('Unable to import. Combination has no id product.', self::$controller_name);

            return false;
        }

        $model = new ModelImportCombination($item['id_product_attribute']);
        $model->force_id = true;
        $model->id = $item['id_product_attribute'];
        $definition = ModelImportCombination::$definition;

        foreach ($definition['fields'] as $key => $value) {
            if (isset($item[$key])) {
                $model->$key = $item[$key];
            }
        }
        $model->json = json_encode($item);

        try {
            $res = $model->add();
            if ($res) {
                $controller->confirmations[] = sprintf(
                    $module->l('Combination %s inserted. %s', self::$controller_name),
                    $model->reference ? $model->reference : '',
                    '<br>'
                );
            }
        } catch (\Throwable $th) {
            $controller->errors[] = 'ERROR: ' . $th->getMessage();

            return false;
        }

        return true;
    }

    public static function addToCombinations($id_product_attribute)
    {
        /** @var ModuleAdminController */
        $controller = Context::getContext()->controller;
        /** @var Module */
        $module = Module::getInstanceByName('mpmassimport');
        /** @var string */
        self::$controller_name = (new GetControllerName($controller))->get();
        /** @var ObjectModel */
        $model = new ModelImportCombination();
        /** @var array */
        $fields = Combination::$definition['fields'];
        /** @var int */
        $id_lang = (int) Context::getContext()->language->id;
        /** @var int */
        $id_shop = (int) Context::getContext()->shop->id;
        /** @var ModelImportCombination */
        $item = new ModelImportCombination($id_product_attribute);
        /** @var array */
        $f = $item->json;

        $p = new Combination($id_product_attribute, $id_lang, $id_shop);
        $p->force_id = true;
        $p->id = $id_product_attribute;
        foreach ($fields as $fieldname => $field) {
            if (isset($f[$fieldname])) {
                $value = '';
                switch($field['type']) {
                    case ObjectModel::TYPE_INT:
                    case ObjectModel::TYPE_BOOL:
                        $value = (int) $f[$fieldname];

                        break;
                    case ObjectModel::TYPE_FLOAT:
                        $value = (float) $f[$fieldname];

                        break;
                    default:
                        $value = $f[$fieldname];
                }

                $p->$fieldname = $value;
            }
        }
        $action = '';

        try {
            if ($id_product_attribute) {
                $action = 'update';
                $res = $p->update();
            } else {
                $controller->errors[] = $module->l('Unable to import combination.', self::$controller_name);

                return false;
            }
            if ($res) {
                $item->delete();

                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            $controller->errors[] = sprintf(
                $module->l('Combination %s %s not inserted. Action: %s, Error %s', self::$controller_name),
                $f['reference'],
                $f['ean13'],
                $action,
                $th->getMessage()
            );

            return false;
        }
    }
}
