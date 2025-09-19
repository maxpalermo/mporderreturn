<?php

namespace MpSoft\MpMassImport\Helpers;

class RowsToPrices
{
    protected static $instance = null;
    protected $products;

    public function __construct()
    {
        $this->products = [];
    }

    protected static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new RowsToPrices();
        }

        return self::$instance;
    }

    public static function parse($rows)
    {
        $instance = self::getInstance();

        if (!is_array($rows)) {
            return [];
        }

        foreach ($rows as $row) {
            $instance->products[] = $instance->parseRow($row);
        }
        foreach ($instance->products as $key => $row) {
            $id_product = isset($row['id_product']) ? (int) $row['id_product'] : 0;
            if (!$id_product) {
                $id_product = $instance->getIdProductFromReference($row);
                //\Tools::dieObject(["REFERENCE:".$row['reference'], "ID_PRODUCT:".$id_product], 0);
            }
            if (!$id_product) {
                $id_product = $instance->getIdProductFromEan13($row);
                //\Tools::dieObject(["EAN13:".$row['ean13'], "ID_PRODUCT:".$id_product], 0);
            }
            if (!$id_product) {
                $id_product = $instance->getIdProductFromEan13Combination($row);
                //\Tools::dieObject(["EAN13 COMBINATION:".$row['ean13'], "ID_PRODUCT:".$id_product], 0);
            }
            if (!$id_product) {
                unset($instance->products[$key]);
            } else {
                $instance->products[$key]['id_product'] = $id_product;
            }
        }

        return $instance->products;
    }

    protected function getIdProductFromEan13($row)
    {
        if (isset($row['ean13'])) {
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('id_product')
                ->from('product')
                ->where('ean13 = \'' . pSQL($row['ean13']) . '\'');
            $value = (int) $db->getValue($sql);

            return (int) $value;
        } else {
            return 0;
        }
    }

    protected function getIdProductFromEan13Combination($row)
    {
        if (isset($row['ean13'])) {
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('id_product')
                ->from('product_attribute')
                ->where('ean13 = \'' . pSQL($row['ean13']) . '\'');
            $value = (int) $db->getValue($sql);

            return (int) $value;
        } else {
            return 0;
        }
    }

    protected function getIdProductFromReference($row)
    {
        if (isset($row['reference']) && trim($row['reference'])) {
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('id_product')
                ->from('product')
                ->where('reference = \'' . pSQL($row['reference']) . '\'');

            return (int) $db->getValue($sql);
        } else {
            return 0;
        }
    }

    protected function parseRow($row)
    {
        if (!is_array($row)) {
            return [];
        }
        $product = [];
        $key = 'id_product';
        if (isset($row[$key])) {
            $product[$key] = (int) $row[$key];
        } else {
            $product[$key] = 0;
        }

        $key = 'reference';
        if (isset($row[$key])) {
            $product[$key] = trim($row[$key]);
        } else {
            $product[$key] = '';
        }

        $key = 'ean13';
        if (isset($row[$key])) {
            $product[$key] = trim($row[$key]);
        } else {
            $product[$key] = '';
        }

        $key = 'name';
        if (isset($row[$key])) {
            $product[$key] = trim($row[$key]);
        } else {
            $product[$key] = '';
        }

        $key = 'wholesale_price';
        if (isset($row[$key])) {
            $product[$key] = number_format((float) $row[$key], 6);
        } else {
            $product[$key] = 0;
        }

        $key = 'price';
        if (isset($row[$key])) {
            $product[$key] = number_format((float) $row[$key], 6);
        } else {
            $product[$key] = 0;
        }

        return $product;
    }

    private function getSuppliersByName($suppliers)
    {
        if (!is_array($suppliers)) {
            return [];
        }
        $id_suppliers = [];
        foreach ($suppliers as $supplier) {
            $query = new Queries();
            $id_supplier = $query->getIdSupplierByName($supplier);
            $id_suppliers[] = $id_supplier;
        }

        return $id_suppliers;
    }

    private function getManufacturerByName($name)
    {
        if (!$name) {
            return '';
        }
        $query = new Queries();
        $id_manufacturer = $query->getIdManufacturerByName($name);

        return $id_manufacturer;
    }

    private function getTaxRulesGroupByName($name)
    {
        if (!$name) {
            return '';
        }
        $query = new Queries();
        $id_tax_rules_group = $query->getIdTaxRulesGroupByName($name);

        return $id_tax_rules_group;
    }
}
