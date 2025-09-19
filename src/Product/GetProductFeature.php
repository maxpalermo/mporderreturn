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

namespace MpSoft\MpMassImport\Product;

use Context;
use Db;
use DbQuery;
use ModuleAdminController;

class GetProductFeature
{
    /** @var int */
    protected $id_lang;
    /** @var array */
    protected $features;
    /** @var array */
    protected $id_features;
    /** @var ModuleAdminController */
    protected $controller;
    /** @var Db */
    protected $db;
    /** @var array */
    protected $featureExport;
    /** @var array */
    protected $featureIndexes;
    /** @var array */
    protected $featureValueIndexes;

    public function __construct(ModuleAdminController $controller)
    {
        $this->controller = $controller;
        $this->db = Db::getInstance();
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->features = [];
        $this->featureIndexes = [];
        $this->featureValueIndexes = [];
    }

    public function get()
    {
        return $this->features;
    }

    public function setProductFeatures($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('distinct fv.id_feature_value, fvl.value as feature_value')
            ->select('fv.id_feature as id_feature_group, fl.name as feature_group')
            ->from('feature_product', 'fp')
            ->innerJoin('feature_value', 'fv', 'fv.id_feature_value=fp.id_feature_value')
            ->innerJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value=fv.id_feature_value and fvl.id_lang=' . (int) $this->id_lang)
            ->innerJoin('feature_lang', 'fl', 'fl.id_feature=fv.id_feature and fl.id_lang=' . (int) $this->id_lang)
            ->where('fp.id_product=' . (int) $id_product)
            ->orderBy('fl.name,fvl.value');
        $res = $db->executeS($sql);
        if ($res) {
            foreach ($res as $row) {
                $fg = $row['feature_group'];
                $fv = $row['feature_value'];
                $id_fg = (int) $row['id_feature_group'];
                $id_fv = (int) $row['id_feature_value'];
                $this->features[$fg][$id_product][] = $fv;
                if (!isset($this->id_features[$id_fg])) {
                    $this->id_features[$id_fg][] = $id_fv;
                } elseif (!in_array($id_fv, $this->id_features[$id_fg])) {
                    $this->id_features[$id_fg][] = $id_fv;
                }
            }
        }

        return [
            'id_features' => $this->id_features,
            'features' => $this->features,
        ];
    }

    public function createListFeatures()
    {
        $features = $this->features;
        if (!$features) {
            return false;
        }

        foreach ($features as $key => $values) {
            if (!isset($this->featureIndexes[$key])) {
                $id_feature_group = (int) $this->getIdGroupByName($key);
                $this->featureIndexes[$key] = $id_feature_group;
            } else {
                $id_feature_group = (int) $this->featureIndexes[$key];
            }
            if (!$id_feature_group) {
                continue;
            }

            foreach ($values as $feature_value) {
                foreach ($feature_value as $feature_name) {
                    if (!isset($this->featureValueIndexes[$feature_name])) {
                        $id_feature_value = (int) $this->getIdFeatureValueByName($id_feature_group, $feature_name);
                        if ($id_feature_value) {
                            $custom = $this->getCustomFeature($id_feature_value);
                            $row = [
                                'id_feature_group' => $id_feature_group,
                                'feature_group' => $this->getFeatureGroupName($id_feature_group),
                                'id_feature_value' => $id_feature_value,
                                'feature_value' => $this->getFeatureValueName($id_feature_value),
                                'custom' => $custom,
                            ];
                            $index = 'feat_' . $id_feature_value;
                            $this->featureExport[$index] = $row;
                        }
                        $this->featureValueIndexes[$feature_name] = $id_feature_value;
                    } else {
                        $id_feature_value = (int) $this->featureValueIndexes[$feature_name];
                    }
                }
            }
        }

        return $this->featureExport;
    }

    public function getIdGroupByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_feature')
            ->from('feature_lang')
            ->where('name=\'' . pSQL($name) . '\'')
            ->where('id_lang=' . (int) $this->id_lang);

        return (int) $this->db->getValue($sql);
    }

    public function getIdFeatureValueByName($id_feature_group, $name)
    {
        $sql = new DbQuery();
        $sql->select('a.id_feature_value')
            ->from('feature_value', 'a')
            ->innerJoin('feature_value_lang', 'b', 'a.id_feature_value=b.id_feature_value and b.id_lang=' . (int) $this->id_lang)
            ->where('b.value=\'' . pSQL($name) . '\'')
            ->where('a.id_feature=' . (int) $id_feature_group);

        return (int) $this->db->getValue($sql);
    }

    public function getCustomFeature($id_feature_value)
    {
        $sql = 'select custom from ' . _DB_PREFIX_ . 'feature_value where id_feature_value=' . (int) $id_feature_value;

        return $this->db->getValue($sql);
    }

    public function getFeatureGroupName($id)
    {
        $sql = 'select name from ' . _DB_PREFIX_ . 'feature_lang '
            . 'where id_feature=' . (int) $id . ' and id_lang=' . (int) $this->id_lang;

        return $this->db->getValue($sql);
    }

    public function getFeatureValueName($values)
    {
        if (!is_array($values)) {
            $values = [(int) $values];
        }
        $value = implode(',', $values);
        $sql = 'select value from ' . _DB_PREFIX_ . 'feature_value_lang '
            . 'where id_feature_value in (' . $value . ') and id_lang=' . (int) $this->id_lang;
        $res = $this->db->executeS($sql);
        $output = [];
        foreach ($res as $row) {
            $output[] = $row['value'];
        }

        return implode(', ', $output);
    }
}
