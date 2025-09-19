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

trait CommonDbTools
{
    public static function getAllIds()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $primary = self::$definition['primary'];

        $sql = "SELECT $primary FROM $table ORDER BY $primary";

        /** @var array */
        $rows = \Db::getInstance()->executeS($sql);

        if (!$rows) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $out[] = reset($row);
        }

        return $out;
    }

    public static function deleteRow($id)
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $primary = self::$definition['primary'];

        $sql = "DELETE FROM $table WHERE $primary = " . (int) $id;

        return \Db::getInstance()->execute($sql);
    }

    public function getEmployees($asList = true)
    {
        $employees = \Employee::getEmployees();
        $name = '';
        $out = [];
        foreach ($employees as &$e) {
            $name = \Tools::strtoupper($e['firstname'] . ' ' . $e['lastname']);
            if ($asList) {
                $out[(int) $e['id_employee']] = $name;
            }
            $e['name'] = $name;
        }
        if ($asList) {
            return $out;
        }

        return $employees;
    }

    public function getEmployeeName($id_employee)
    {
        $employee = new \Employee($id_employee);
        if (\Validate::isLoadedObject($employee)) {
            return \Tools::strtoupper($employee->firstname . ' ' . $employee->lastname);
        }

        return '--';
    }

    public function addSelect(&$select, $field)
    {
        if (!preg_match('/,$/i', $select)) {
            $select .= ',';
        }

        $select .= $field;
    }

    public function addJoin(&$join, $item)
    {
        $item = str_replace('{PFX}', _DB_PREFIX_, $item);
        $join .= ' ' . $item;
    }

    public function addWhere($where, $item)
    {
        $where .= ' AND ' . $item;
    }

    public static function truncate()
    {
        $sql = 'TRUNCATE TABLE ' . _DB_PREFIX_ . self::$definition['table'];

        return \Db::getInstance()->execute($sql);
    }

    public function existsTable($table, &$error = null)
    {
        if (!preg_match('/^' . _DB_PREFIX_ . '/i', $table)) {
            $table = _DB_PREFIX_ . $table;
        }

        try {
            \Db::getInstance()->getValue("SELECT COUNT(*) FROM {$table}");

            return true;
        } catch (\Throwable $th) {
            $error = $th->getMessage();

            return false;
        }
    }
}
