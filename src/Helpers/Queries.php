<?php

namespace MpSoft\MpMassImport\Helpers;

use Context;
use Db;
use DbQuery;

class Queries
{
    protected $db;
    protected $context;
    protected $id_lang;
    protected $id_shop;

    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->getContext()->language->id;
        $this->id_shop = (int) $this->context->getContext()->shop->id;
    }

    public function getIdSupplierByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_supplier')
            ->from('supplier')
            ->where('name = \'' . pSQL($name) . '\'');
        $id_supplier = (int) $this->db->getValue($sql);

        return $id_supplier;
    }

    public function getIdManufacturerByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_manufacturer')
            ->from('manufacturer')
            ->where('name = \'' . pSQL($name) . '\'');
        $id_manufacturer = (int) $this->db->getValue($sql);

        return $id_manufacturer;
    }

    public function getIdTaxRulesGroupByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_tax_rules_group')
            ->from('tax_rules_group')
            ->where('name = \'' . pSQL($name) . '\'')
            ->where('deleted = 0');
        $id_tax_rules_group = (int) $this->db->getValue($sql);

        return $id_tax_rules_group;
    }

    public function getIdAttributesByName(&$attributes)
    {
        if (!is_array($attributes)) {
            return [];
        }
        $output = [];
        foreach ($attributes as $key_attribute_group => $value) {
            $id_attribute_group = $this->getIdAttributeGroupByName($key_attribute_group);
            $key_attribute_group .= (':' . $id_attribute_group);
            foreach ($value as $attribute) {
                $output[$key_attribute_group][] = $this->getIdAttributeByName($id_attribute_group, $attribute, true);
            }
        }
        $attributes = $output;
    }

    public function getIdFeaturesByName(&$features)
    {
        if (!is_array($features)) {
            return [];
        }
        $output = [];
        foreach ($features as $key_feature => $value) {
            $id_feature = $this->getIdFeatureByName($key_feature);
            $key_feature .= (':' . $id_feature);
            foreach ($value as $feature_value) {
                $output[$key_feature][] = $this->getIdFeatureValueByName($id_feature, $feature_value, true);
            }
        }
        $features = $output;
    }

    public function getIdAttributeGroupByName($name, $full = false)
    {
        $sql = new DbQuery();
        $sql->select('id_attribute_group')
            ->from('attribute_group_lang')
            ->where('name = \'' . pSQL($name) . '\'')
            ->where('id_lang = ' . (int) $this->id_lang);
        $id_attribute_group = (int) $this->db->getValue($sql);
        if ($full) {
            return $name . ':' . $id_attribute_group;
        }

        return $id_attribute_group;
    }

    public function getIdAttributeByName($id_attribute_group, $name, $full = false)
    {
        $sql = new DbQuery();
        $sql->select('a.id_attribute')
            ->from('attribute', 'a')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute and al.id_lang=' . (int) $this->id_lang)
            ->where('al.name = \'' . pSQL($name) . '\'')
            ->where('a.id_attribute_group = ' . (int) $id_attribute_group);

        $id_attribute = (int) $this->db->getValue($sql);
        if ($full) {
            return $name . ':' . $id_attribute;
        }

        return $id_attribute;
    }

    public function getIdFeatureByName($name, $full = false)
    {
        $sql = new DbQuery();
        $sql->select('id_feature')
            ->from('feature_lang')
            ->where('name = \'' . pSQL($name) . '\'')
            ->where('id_lang = ' . (int) $this->id_lang);
        $id_feature = (int) $this->db->getValue($sql);
        if ($full) {
            return $name . ':' . $id_feature;
        }

        return $id_feature;
    }

    public function getIdFeatureValueByName($id_feature, $name, $full = false)
    {
        $sql = new DbQuery();
        $sql->select('a.id_feature_value')
            ->from('feature_value', 'a')
            ->innerJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value=a.id_feature_value and fvl.id_lang=' . (int) $this->id_lang)
            ->where('fvl.value = \'' . pSQL($name) . '\'')
            ->where('a.id_feature = ' . (int) $id_feature);
        $id_feature_value = (int) $this->db->getValue($sql);
        if ($full) {
            return $name . ':' . $id_feature_value;
        }

        return $id_feature_value;
    }

    public function getIdProductByIdProductAttribute($id_product_attribute)
    {
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product_attribute')
            ->where('id_product_attribute = ' . (int) $id_product_attribute);

        return (int) $this->db->getValue($sql);
    }

    public function getIdProductByReference($reference)
    {
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product')
            ->where('reference = \'' . pSQL($reference) . '\'');

        return (int) $this->db->getValue($sql);
    }

    public function getIdProductAttributeByAttributes($attributes, $reference)
    {
        //\Tools::dieObject($attributes);
        $id_product = $this->getIdProductByReference($reference);
        $id_attributes = [];
        foreach ($attributes as $attr) {
            $split = explode(':', $attr[0]);
            if (count($split) == 2) {
                $id_attributes[] = (int) $split[1];
            }
        }

        if (!$id_attributes) {
            return 0;
        }

        $sql = new DbQuery();
        $sql->select('a.id_product_attribute')
        ->select('count(b.id_attribute) as attributes')
        ->from('product_attribute', 'a')
        ->innerJoin(
            'product_attribute_combination',
            'b',
            'b.id_product_attribute=a.id_product_attribute and b.id_attribute in(' . implode(',', $id_attributes) . ')'
        )
        ->where('a.id_product=' . (int) $id_product)
        ->groupBy('b.id_product_attribute');

        $res = Db::getInstance()->executeS($sql);
        $max = 0;
        $current_id = 0;
        if ($res) {
            foreach ($res as $row) {
                if ($row['attributes'] > $max) {
                    $max = $row['attributes'];
                    $current_id = $row['id_product_attribute'];
                }
            }
        }

        if ($current_id) {
            return $current_id;
        }

        return 0;
    }
}
