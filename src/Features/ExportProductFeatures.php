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

namespace MpSoft\MpMassImport\Features;

class ExportProductFeatures
{
    use \MpSoft\MpMassImport\Traits\Cookies;

    protected $name = 'ExportProductFeatures';

    public function getFieldsList(\ModuleAdminController $controller)
    {
        $categories = $this->cookieGetValue('HCA_CATEGORY_TREE');
        $search_default = $this->cookieGetValue('HCA_SELECT_IN_DEFAULT_CATEGORY');
        $search_assoc = $this->cookieGetValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES');
        $id_lang = (int) \Context::getContext()->language->id;
        $module = $controller->module;
        $name = $controller->name;

        return [
            'id_product' => [
                'title' => $module->l('Id', $name),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!id_product',
            ],
            'image' => [
                'title' => $module->l('Image', $name),
                'align' => 'center',
                'image' => 'p',
                'orderby' => false,
                'filter' => false,
                'search' => false,
            ],
            'reference' => [
                'title' => $module->l('Reference', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'a!reference',
            ],
            'category_default' => [
                'title' => $module->l('Default Category', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'filter_key' => 'cat!name',
            ],
            'name' => [
                'title' => $module->l('Name', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'b!name',
            ],
        ];
    }

    public function export()
    {
        $categories = $this->cookieGetValue('HCA_CATEGORY_TREE');
        $search_default = $this->cookieGetValue('HCA_SELECT_IN_DEFAULT_CATEGORY');
        $search_assoc = $this->cookieGetValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES');
        $id_lang = (int) \Context::getContext()->language->id;

        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('a.id_product, a.reference')
            ->select('cat_def.name as category_default')
            ->select('b.name')
            ->from('product', 'a')
            ->innerJoin('category_lang', 'cat_def', 'a.id_category_default=cat_def.id_category and cat_def.id_lang=' . (int) $id_lang)
            ->innerJoin('product_lang', 'b', 'a.id_product=b.id_product and b.id_lang=' . (int) $id_lang)
            ->orderBy('a.reference, b.name');
        if ($categories) {
            $categories = implode(',', $categories);
            if ($search_default && !$search_assoc) {
                $sql->where('a.id_category_default in (' . $categories . ')');
            } elseif ($search_default && $search_assoc) {
                $sql->where('a.id_category_default in (' . $categories . ')');
                $sql->innerJoin('category_product', 'cp', 'cp.id_product=a.id_product and cp.id_category in (' . $categories . ')');
            } elseif (!$search_default && $search_assoc) {
                $sql->innerJoin('category_product', 'cp', 'cp.id_product=a.id_product and cp.id_category in (' . $categories . ')');
            }
        }
        $sql = $sql->build();
        $rows = $db->executeS($sql);

        if ($rows) {
            $result = $this->parseRows($rows);
            if ($result) {
                $this->exportExcel($result);
            }
        } else {
            /** @var \Context */
            $context = \Context::getContext();
            /** @var \ModuleAdminController */
            $controller = $context->controller;
            /** @var \Module */
            $module = $controller->module;
            $controller->errors[] = $module->l('No Products to show', $this->name);
        }
    }

    protected function parseRows($rows)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $featuresFound = [];
        foreach ($rows as &$row) {
            $sql = new \DbQuery();
            $sql->select('fl.id_feature, fl.name as feature_group, fvl.id_feature_value, fvl.value as feature_value')
                ->from('feature_product', 'a')
                ->innerJoin('feature_lang', 'fl', 'fl.id_feature=a.id_feature and fl.id_lang=' . (int) $id_lang)
                ->innerJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value=a.id_feature_value and fvl.id_lang=' . (int) $id_lang)
                ->where('a.id_product=' . (int) $row['id_product'])
                ->orderBy('fl.id_feature, fvl.id_feature_value');
            $features = $db->executeS($sql);
            if ($features) {
                foreach ($features as $feature) {
                    $currentFeature = [
                        'id_feature_group' => $feature['id_feature'],
                        'feature_group' => $feature['feature_group'],
                        'id_feature_value' => $feature['id_feature_value'],
                        'feature_value' => $feature['feature_value'],
                    ];
                    $id_feature = (int) $feature['id_feature'];
                    $id_value = (int) $feature['id_feature_value'];
                    $row['features'][$id_feature][$id_value] = $currentFeature;
                    $featuresFound[$id_feature][$id_value] = $currentFeature;
                }
            }
        }

        return [
            'products' => $rows,
            'features' => $featuresFound,
        ];
    }

    protected function exportExcel($result)
    {
        $excel = [];
        $header = $this->generateHeader($result['features']);
        $excel[] = $header;
        $features = $result['features'];
        foreach ($result['products'] as $product) {
            $row = [];
            foreach ($header as $col) {
                $matches = [];
                if (preg_match('/^(\d+)\:\:.*/i', $col, $matches)) {
                    $id_feature = $matches[1];
                    $row[] = $this->getFeatureValues($product['features'], $id_feature);
                } else {
                    $row[] = $product[$col];
                }
            }
            $excel[] = $row;
            unset($row);
        }

        $sheet = new \MpSoft\MpMassImport\Excel\XlsxWriter();
        $sheet->addSheet($excel, 'product_features');
        $sheet->setDefaultFont('courier new');
        $sheet->setDefaultFontSize('12');
        exit($sheet->downloadAs('product_features.xlsx'));
    }

    protected function generateHeader($features)
    {
        $header = [
            'id_product',
            'reference',
            'name',
            'category_default',
        ];

        foreach ($features as $feature) {
            $elem = reset($feature);
            $header[] = $elem['id_feature_group'] . '::' . $elem['feature_group'];
        }

        return $header;
    }

    protected function getFeatureValues($product_features, $id_feature)
    {
        $values = [];
        foreach ($product_features as $key => $value) {
            if ($key == $id_feature) {
                foreach ($value as $feature_value) {
                    $values[] = $feature_value['feature_value'];
                }
            }
        }

        return implode(',', $values);
    }
}