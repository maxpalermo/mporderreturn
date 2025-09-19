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
require_once 'object_model/MpMassImportFeature.php';

class DeprecatedMpMassImporterExcelFeature
{
    public function __construct($controller)
    {
        $this->name = 'MpMassImporterExcelFeature';
    }

    public function importExcel($filename)
    {
        $reader = new MpExcelReader();
        $data = $reader->read($filename, 'Features');
        MpMassImportFeature::truncate();
        $output = [];
        foreach ($data as &$row) {
            $output_row = [
                'id_mp_massimport_feature' => 0,
                'id_feature' => 0,
                'id_feature_value' => 0,
                'feature' => '',
                'feature_value' => '',
                'custom' => 0,
            ];
            foreach ($row as $key => $value) {
                $key = Tools::strtolower($key);
                switch ($key) {
                    case 'feature_group':
                        $output_row['feature'] = $value;
                        $output_row['id_feature'] = $this->tools->getIdFeatureGroup($value);

                        break;
                    case 'feature_value':
                        $output_row['feature_value'] = $value;
                        if ((int) $output_row['id_feature']) {
                            $output_row['id_feature_value'] = $this->tools->getIdFeatureValue(
                                (int) $output_row['id_feature'],
                                $value
                            );
                        } else {
                            $output_row['id_feature_value'] = 0;
                        }

                        break;
                    case 'custom':
                        $output_row[$key] = trim($value);

                        break;
                }
            }
            $output[] = $output_row;
        }
        $controller = $this->controller;
        MpMassImportFeature::truncate();
        foreach ($output as $row) {
            $feature = new MpMassImportFeature();
            foreach ($row as $key => $field) {
                $feature->$key = $field;
            }

            try {
                $res = $feature->add();
            } catch (\Throwable $th) {
                $res = false;
                $controller->errors[] = sprintf(
                    $this->l('Throwable error inserting feature. Error %s. dump row: %s'),
                    $th->getMessage(),
                    print_r($row, 1)
                );

                continue;
            }

            if (!$res) {
                $controller->errors[] = sprintf(
                    $this->l('Unable to insert feature %s %s. Error %s'),
                    $row['$feature'],
                    $row['$feature_value'],
                    $this->db->getMsgError()
                );
            }
        }

        $this->controller->confirmations[] = $this->l('Operation done.');
    }

    /**
     * Import from file with this header:
     * - id_feature_value (optional)
     * - feature_group (feature group name)
     * - feature_value (feature value name)
     * - custom (0/1 is custom feature)
     */
    public function import($boxes, $force = false)
    {
        if (!is_array($boxes)) {
            return false;
        }
        if (count($boxes) == 0) {
            $this->warnings[] = $this->l('Please select at least one Feature.');

            return false;
        }
        foreach ($boxes as $box) {
            $features = new MpMassImportFeature((int) $box);
            $feature = new Feature($features->id_feature, $this->id_lang, $this->id_shop);
            $feature->position = Feature::getHigherPosition() + 1;
            $feature->name = $features->feature;
            $feature->custom = (int) $features->custom;
            if ($feature->id) {
                $res = $feature->update();
            } else {
                $res = $feature->add();
            }
            if ($res) {
                $featureValue = new FeatureValue($features->id_feature_value, $this->id_lang, $this->id_shop);
                $featureValue->id_feature = $feature->id;
                $featureValue->custom = (int) $features->custom;
                $featureValue->value = $features->feature_value;
                if ($featureValue->id) {
                    $res = $featureValue->update();
                } else {
                    $res = $featureValue->add();
                }
                if (!$res) {
                    $this->errors[] = sprintf(
                        $this->l('Update feature value %s %s. error %s.'),
                        $features->feature,
                        $features->feature_value,
                        $this->db->getMsgError()
                    );
                } else {
                    $features->delete();
                }
            } else {
                $this->errors[] = sprintf(
                    $this->l('Update feature %s %s. error %s.'),
                    $features->feature,
                    $features->feature_value,
                    $this->db->getMsgError()
                );
            }
        }

        $this->confirmations[] = $this->l('Operation done.');
    }
}
