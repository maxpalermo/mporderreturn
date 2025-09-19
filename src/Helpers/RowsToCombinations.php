<?php

namespace MpSoft\MpMassImport\Helpers;

class RowsToCombinations
{
    protected static $instance = null;
    protected $attributes;
    protected $features;
    protected $products;

    public function __construct()
    {
        $this->attributes = [];
        $this->features = [];
        $this->products = [];
    }

    protected static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new RowsToCombinations();
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

        return $instance->products;
    }

    protected function parseRow($row)
    {
        if (!is_array($row)) {
            return [];
        }
        $product = [];

        foreach ($row as $key => $value) {
            if (trim($value)) {
                switch ($key) {
                    case 'quantity':
                        $product[$key] = (int) $value;

                        break;
                    case 'reference':
                    case 'ean13':
                        $product[$key] = pSQL($value);

                        break;
                    case 'price':
                    case 'wholesale_price':
                    case 'unit_price_impact':
                        $product[$key] = number_format($value, 6);

                        break;
                    case strtolower(substr($key, 0, 5)) == 'attr:':
                        $product['attributes'][strtolower(substr($key, 5))] = explode(';', $value);

                        break;
                    case 'default_on':
                        $product[$key] = (int) $value;
                        // no break
                    default:
                        $product[$key] = $value;
                }
            }
        }
        $query = new Queries();
        $query->getIdAttributesByName($product['attributes']);
        $id_product_attribute = $query->getIdProductAttributeByAttributes($product['attributes'], $product['reference']);
        $product['id_product_attribute'] = $id_product_attribute;
        $product['id_product'] = $query->getIdProductByIdProductAttribute($id_product_attribute);

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
