<?php
/**
 * 2017 mpSOFT
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
 *  @author    Massimiliano Palermo <info@mpsoft.it>
 *  @copyright 2021 Digital Solutions®
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of mpSOFT
 */

require_once 'MpMassImporter.php';
require_once 'MpUtilities.php';
require_once 'object_model/MpMassImportEan13.php';

class DeprecatedMpMassImporterExcelEan13
{
    public function __construct($controller)
    {
        $this->name = 'MpMassImporterExcelEan13';
    }

    public function PrepareRow($row)
    {
    }

    public function ParseRow($row)
    {
    }

    public function import($boxes)
    {
        Tools::dieObject($boxes);
        $controller = $this->controller;
        if (!$boxes) {
            $this->controller->errors[] = $controller->module->l('Please select at least one product.');

            return false;
        }
        foreach ($boxes as $box) {
            $row = new MpMassImportEan13($box);
            $res = $this->db->update(
                'product_attribute',
                [
                    'ean13' => $row->ean13,
                ],
                'id_product_attribute = ' . (int) $row->id_product_attribute
            );
            if (!$res) {
                $this->controller->warnings[] = sprintf(
                    $controller->module->l('Unable to update Ean13 to product %s %s'),
                    $row->reference,
                    $row->attributes
                );
            }
        }
    }

    public function importExcel($filename)
    {
        $reader = new MpExcelReader();
        $data = $reader->read($filename, 'Ean13');
        MpMassImportEan13::truncate();
        $output = [];
        foreach ($data as &$row) {
            $output_row = [
                'reference' => '',
                'id_product' => 0,
                'ean13' => '',
                'product' => '',
                'id_attributes' => [],
                'attributes' => [],
            ];
            foreach ($row as $key => $value) {
                $key = Tools::strtolower($key);
                switch ($key) {
                    case 'reference':
                        $output_row['reference'] = trim($value);
                        $output_row['id_product'] = $this->getIdProductByReference(trim($value));
                        if ($output_row['id_product'] == 0) {
                            continue;
                        }

                        break;
                    case 'ean13':
                        $output_row[$key] = trim($value);

                        break;
                    case 'product':
                        $output_row[$key] = trim($value);

                        break;
                    case $this->startsWith('attr:', $key):
                        $attributeGroupName = $this->getAttributeFromKey($key);
                        $attributeName = trim($value);
                        $id_attribute = $this->getAttributeId($attributeGroupName, $attributeName);
                        if ($id_attribute) {
                            $output_row['attributes'][] = $attributeName;
                            $output_row['id_attributes'][] = $id_attribute;
                        }

                        break;
                }
            }
            $output[] = $output_row;
        }

        $controller = $this->controller;
        MpMassImportEan13::truncate();
        foreach ($output as $row) {
            $row['id_product_attribute'] = $this->getIdProductAttribute($row['id_product'], $row['id_attributes']);
            $row['attributes'] = implode(', ', $row['attributes']);
            if (!trim($row['attributes'])) {
                $controller->warnings[] = sprintf(
                    $controller->module->l('Unable to insert product %s %s. Combination not found'),
                    $row['reference'],
                    $row['attributes']
                );

                continue;
            }
            $ean = new MpMassImportEan13();
            $ean->id_product_attribute = $row['id_product_attribute'];
            $ean->id_product = $row['id_product'];
            $ean->reference = $row['reference'];
            $ean->ean13 = $row['ean13'];
            $ean->description = $row['product'];
            $ean->attributes = $row['attributes'];
            $res = $ean->add();
            if (!$res) {
                $controller->errors[] = sprintf(
                    $controller->module->l('Unable to insert product %s %s. Error %s'),
                    $row['reference'],
                    $row['attributes'],
                    $this->db->getMsgError()
                );
            }
        }

        $this->controller->confirmations[] = $this->l('Operation done.');
    }

    private function getIdProductByReference($reference)
    {
        $sql = 'select id_product from ' . _DB_PREFIX_ . "product where reference = '" . pSQL($reference) . "'";

        return (int) $this->db->getValue($sql);
    }

    private function getIdProductAttribute($id_product, $attributes)
    {
        $ids = [];
        $id_product_attributes = [];
        $id_attributes = [];
        foreach ($attributes as $attr) {
            $id_attributes[] = $attr;
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

        $res = $this->db->executeS($sql);
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

    private function getAttributeId($group, $value)
    {
        $prefix = _DB_PREFIX_;
        $id_lang = (int) Context::getContext()->language->id;

        $sql = 'select a.id_attribute from '
        . $prefix . 'attribute a '
        . 'inner join ' . $prefix . "attribute_lang al on (al.id_lang = $id_lang and al.name='" . pSQL($value) . "') "
        . 'inner join ' . $prefix . "attribute_group_lang agl on (agl.id_lang = $id_lang and agl.name='" . pSQL($group) . "') "
        . 'where agl.id_attribute_group=a.id_attribute_group '
        . 'and a.id_attribute = al.id_attribute';

        return $this->db->getValue($sql);
    }

    private function startsWith($needle, $haystack)
    {
        return strpos($haystack, $needle) === 0;
    }

    private function getAttributeFromKey($key)
    {
        $attributeName = trim(substr($key, 5));

        return $attributeName;
    }

    private function getIdByReference($reference)
    {
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product')
            ->where('reference like \'' . pSQL($reference) . '\'');

        return (int) $this->conn->getValue($sql);
    }

    private function addEan13($row)
    {
        if (!$row) {
            return false;
        }
        $p = new MpMassImportEan13();
        $p->id_product = $row['id_product'];
        $p->reference = $row['reference'];
        $p->ean13 = $row['ean13'];
        $p->attributes = Tools::jsonEncode($row['attributes']);
        $res = $p->add();
        if ($res) {
            $id = $this->conn->Insert_ID();

            return $id;
        }
        $this->controller->errors[] = sprintf(
            $this->l('Error importing ean13 (%s) %s. Error %s.'),
            $row['reference'],
            $row['ean13'],
            $this->conn->getMsgError()
        );

        return false;
    }
}
