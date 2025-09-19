<?php

namespace MpSoft\MpMassImport\Helpers;

class RowsToProducts
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
            self::$instance = new RowsToProducts();
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
                    case 'id_product':
                    case 'id_supplier':
                    case 'id_manufacturer':
                    case 'id_tax_rules_group':
                    case 'id_category_default':
                    case 'quantity':
                        $product[$key] = (int) $value;

                        break;
                    case 'reference':
                    case 'ean13':
                    case 'product_name':
                    case 'description':
                    case 'description_short':
                    case 'description_long':
                    case 'available_date':
                        $product[$key] = pSQL($value);

                        break;
                    case 'categories':
                        if (!isset($product['categories'])) {
                            $product[$key] = explode('|', $value);
                        }

                        break;
                    case 'suppliers':
                        if (!isset($product['id_suppliers'])) {
                            $product['id_suppliers'] = $this->getSuppliersByName(explode('|', $value));
                        }

                        break;
                    case 'manufacturer':
                        if (!isset($product['id_manufacturer'])) {
                            $product['id_manufacturer'] = $this->getManufacturerByName($value);
                        }

                        break;
                    case 'price':
                    case 'wholesale_price':
                        $product[$key] = number_format($value, 6);

                        break;
                    case 'tax':
                        if (!isset($product['id_tax_rules_group'])) {
                            $product['id_tax_rules_group'] = $this->getTaxRulesGroupByName($value);
                        }
                        if (!$product['id_tax_rules_group']) {
                            $product['id_tax_rules_group'] = $this->getTaxRulesGroupByName($value);
                        }

                        break;
                    case 'img_root':
                    case 'img_folder':
                    case 'images':
                        $product[$key] = explode('|', $value);

                        break;
                    case strtolower(substr($key, 0, 5)) == 'attr:':
                        $product['attributes'][strtolower(substr($key, 5))] = explode('|', $value);

                        break;
                    case strtolower(substr($key, 0, 5)) == 'feat:':
                        $product['features'][strtolower(substr($key, 5))] = explode('|', $value);

                        break;
                    default:
                        $product[$key] = $value;
                }
            }
        }
        $query = new Queries();
        $query->getIdAttributesByName($product['attributes']);
        $query->getIdFeaturesByName($product['features']);

        if (isset($product['img_root']) && isset($product['img_folder']) && isset($product['images'])) {
            $a = count($product['img_root']);
            $b = count($product['img_folder']);
            $c = count($product['images']);
            if (abs($a - $b - $c) == $a) {
                foreach ($product['img_root'] as $key => $value) {
                    $product['images'][$key] =
                        $product['img_root'][$key] .
                        $product['img_folder'][$key] .
                        rawurlencode($product['images'][$key]);
                }
            } else {
                foreach ($product['images'] as $key => $value) {
                    $product['images'][$key] =
                        $product['img_root'][0] .
                        $product['img_folder'][0] .
                        rawurlencode($product['images'][$key]);
                }
            }
            unset($product['img_root'], $product['img_folder']);
        } else {
            $keys = ['img_root', 'img_folder', 'images'];
            foreach ($keys as $key) {
                if (isset($product[$key])) {
                    unset($product[$key]);
                }
            }
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
