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

namespace MpSoft\MpMassImport\Install;

use Db;
use ObjectModel;

class CreateSql
{
    protected $definition;

    public function __construct($definition)
    {
        $this->definition = $definition;
    }


    /**
     * Create Sql Table from Prestashop ObjectModel Definition
     * add 'datetime' to definition field to set as DATETIME,
     * add 'price' to definition field to set as DECIMAL(20,6),
     * add 'enum' to definition field to set as ENUM, the enum must contains all enum values,
     * add 'text' to definition field to set as TEXT, it can depend by 'size' property,
     * add 'fixed' to definition field to set as CHAR, it can depend by 'size' property,
     * add 'default_value' to definition field to set DEFAULT in NULL fields,
     * add 'comment' to definition field to set COMMENT, 'comment' must contains the field comment
     *
     * @return string Sql create table
     */
    public function run()
    {
        $definition = $this->definition;

        $tablename = _DB_PREFIX_ . $definition['table'];
        $primary = "`{$definition['primary']}`";
        $multilang = (int) isset($definition['multilang']) ? $definition['multilang'] : 0;
        $multishop = (int) isset($definition['multishop']) ? $definition['multishop'] : 0;

        $engine = ") ENGINE=InnoDB;\n";
        $fields = [];
        $fields_ml = [];
        $fields_ms = [];

        $sql_table = "CREATE TABLE IF NOT EXISTS $tablename (\n" .
            "\t{$primary} INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,\n";

        $sql_lang = "CREATE TABLE IF NOT EXISTS {$tablename}_lang (\n" .
            "\t{$primary} INT(11) NOT NULL,\n" .
            "\t`id_lang` INT(11) NOT NULL DEFAULT 1,\n";
        if ($multishop) {
            $sql_lang .= "\t`id_shop` INT(11) NOT NULL DEFAULT 1,\n";
        }

        $sql_shop = "CREATE TABLE IF NOT EXISTS {$tablename}_shop (\n" .
            "\t{$primary} INT(11) NOT NULL,\n" .
            "\t`id_shop` INT(11) NOT NULL DEFAULT 1,\n" .
            "\tPRIMARY KEY ($primary,`id_shop`)\n" . $engine;

        foreach ($definition['fields'] as $key => $value) {
            $field = $this->addField($key, $value);
            if (isset($value['lang']) && $value['lang']) {
                $fields_ml[] = "\t$field";
            } else {
                $fields[] = "\t$field";
            }
        }

        $sql_table .= implode(",\n", $fields) . "\n" . $engine;

        if ($multilang) {
            $sql_lang .= implode(",\n", $fields_ml) .
                ",\n\tPRIMARY KEY ($primary,`id_lang`)\n" .
                $engine;
        } else {
            $sql_lang = '';
        }
        if (!$multishop) {
            $sql_shop = '';
        }

        return $sql_table . $sql_lang . $sql_shop;
    }

    protected function addField($key, $prop)
    {
        if (in_array($key, ['date_add', 'date_upd'])) {
            return "`$key` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP";
        }

        $values = [];
        $values[] = "`$key`";
        switch ($prop['type']) {
            case ObjectModel::TYPE_BOOL:
                $values[] = 'TINYINT(1)';

                break;
            case ObjectModel::TYPE_DATE:
                if (isset($prop['datetime']) && (int) $prop['datetime']) {
                    $values[] = 'DATETIME';
                } else {
                    $values[] = 'DATE';
                }

                break;
            case ObjectModel::TYPE_FLOAT:
                if (isset($prop['price']) && (int) $prop['price']) {
                    $values[] = 'DECIMAL(20,6)';
                } else {
                    $values[] = 'FLOAT';
                }

                break;
            case ObjectModel::TYPE_HTML:
                if (isset($prop['size']) && (int) $prop['size']) {
                    $values[] = 'TEXT(' . (int) $prop['size'] . ')';
                } else {
                    $values[] = 'TEXT(16777216)';
                }

                break;
            case ObjectModel::TYPE_INT:
                $values[] = 'INT(11)';

                break;
            case ObjectModel::TYPE_STRING:
                if (isset($prop['enum']) && $prop['enum']) {
                    $values[] = "ENUM ({$prop['enum']})";

                    break;
                } elseif (isset($prop['text']) && (int) $prop['text']) {
                    $value = 'TEXT';
                } else {
                    if (isset($prop['fixed']) && (int) $prop['fixed']) {
                        $value = 'CHAR';
                    } else {
                        $value = 'VARCHAR';
                    }
                }
                if (isset($prop['size']) && (int) $prop['size']) {
                    $value .= '(' . (int) $prop['size'] . ')';
                } elseif (!isset($prop['text'])) {
                    $value .= '(255)';
                } elseif (isset($prop['text']) && $prop['text']) {
                    $value .= '(16777216)';
                }

                $values[] = $value;

                break;
            default:
                $values = 'VARCHAR(255)';
        }

        if (isset($prop['required']) && (int) $prop['required']) {
            $values[] = 'NOT NULL';
        } else {
            $values[] = 'NULL';
        }
        if (isset($prop['default_value']) && $prop['default_value']) {
            $values[] = 'DEFAULT ' . $prop['default_value'];
        }
        if (isset($prop['comment']) && $prop['comment']) {
            $values[] = "COMMENT '{$prop['comment']}'";
        }

        return implode(' ', $values);
    }

    /**
     * Write to a specified file
     * @param mixed $path The path of the file
     * @param mixed $content The content of the file
     * @return bool|int
     */
    public function writeFile($path, $content)
    {
        $res = file_put_contents($path, $content);
        chmod($path, 0775);

        return $res;
    }

    public function truncate($table)
    {
        $table = _DB_PREFIX_ . $table;

        return Db::getInstance()->execute("TRUNCATE TABLE $table");
    }

    public function drop($table)
    {
        $table = _DB_PREFIX_ . $table;

        return Db::getInstance()->execute("DROP TABLE $table");
    }
}
