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

namespace MpSoft\MpMassImport\Helpers;

use Context;
use Db;
use DbQuery;
use Module;
use ModuleAdminController;
use MpSoft\MpMassImport\Excel\XlsxWriter;
use MpSoft\MpMassImport\Product\GetProductAttribute;
use MpSoft\MpMassImport\Product\GetProductCategory;
use MpSoft\MpMassImport\Product\GetProductCombination;
use MpSoft\MpMassImport\Product\GetProductFeature;
use MpSoft\MpMassImport\Product\GetProductImage;
use MpSoft\MpMassImport\Product\GetProductSupplier;
use MpSoft\MpMassImport\Product\GetStock;

class ExportProducts
{
    /** @var ModuleAdminController */
    protected $controller;
    /** @var Module */
    protected $module;
    /** @var array */
    protected $features;
    /** @var array */
    protected $attributes;
    /** @var array */
    protected $categories;
    /** @var array */
    protected $id_features;
    /** @var array */
    protected $id_attributes;
    /** @var array */
    protected $id_categories;
    /** @var int */
    protected $id_lang;
    /** @var array */
    protected $attributeExport;
    /** @var array */
    protected $featureExport;

    public function __construct(ModuleAdminController $controller)
    {
        $this->controller = $controller;
        $this->id_lang = (int) Context::getContext()->language->id;
    }
    public function export($boxes)
    {
        $ctlName = (new GetControllerName($this->controller))->get();
        $db = Db::getInstance();

        if (!is_array($boxes)) {
            return false;
        }
        if (count($boxes) == 0) {
            $this->controller->warnings[] = $this->module->l('Please select at least one Product.', $ctlName);

            return false;
        }

        if (!$boxes) {
            $this->controller->errors[] = $this->module->l('Product list not valid.', $ctlName);

            return false;
        }

        $list_boxes = implode(',', $boxes);
        $sql = new DbQuery();
        $sql->select('p.id_product, p.reference, p.ean13, p.supplier_reference, pl.name as product_name')
            ->select('p.id_supplier, p.condition, p.wholesale_price, p.is_virtual')
            ->select('pl.description_short, pl.description as description_long, pl.link_rewrite')
            ->select('p.price, p.id_manufacturer, m.name as manufacturer, p.id_tax_rules_group, trg.name as tax')
            ->select('p.id_category_default, p.date_add as product_date, p.available_date')
            ->from('product', 'p')
            ->leftJoin('product_lang', 'pl', 'pl.id_product=p.id_product and pl.id_lang=' . (int) $this->id_lang)
            ->leftJoin('manufacturer', 'm', 'm.id_manufacturer=p.id_manufacturer')
            ->leftJoin('tax_rules_group', 'trg', 'trg.id_tax_rules_group=p.id_tax_rules_group')
            ->where('p.id_product in (' . $list_boxes . ')')
            ->orderBy('p.id_product');
        $result = $db->executeS($sql);
        if ($result) {
            $products = [];
            foreach ($result as $row) {
                $products[$row['id_product']] = $row;
            }
            $excelProducts = $this->addFields($products);
        } else {
            return false;
        }

        $filename = 'Export_Products_' . date('YmdHis') . '.xlsx';
        $excel = new XlsxWriter();

        if ($excelProducts) {
            $excelProducts = $this->rows2ToSheet($excelProducts);
            $excel->addSheet($excelProducts, 'Products');
        }

        if ($this->attributeExport) {
            $this->attributeExport = $this->rows2ToSheet($this->attributeExport);
            $excel->addSheet($this->attributeExport, 'Attributes');
        }

        if ($this->featureExport) {
            $this->featureExport = $this->rows2ToSheet($this->featureExport);
            $excel->addSheet($this->featureExport, 'Features');
        }

        $sql = 'select pa.id_product_attribute, pa.id_product, pa.reference, pa.ean13, pa.wholesale_price, '
            . 'pa.price, pa.quantity, pa.unit_price_impact, pa.default_on, pac.id_attribute, '
            . 'agl.name as attribute_group, al.name as attribute_value '
            . 'from ' . _DB_PREFIX_ . 'product_attribute pa inner join ' . _DB_PREFIX_ . 'product_attribute_combination pac '
            . 'on (pac.id_product_attribute=pa.id_product_attribute) inner join ' . _DB_PREFIX_ . 'attribute a '
            . 'on (a.id_attribute=pac.id_attribute) inner join ' . _DB_PREFIX_ . 'attribute_lang al '
            . 'on (al.id_attribute=a.id_attribute and al.id_lang=' . (int) $this->id_lang . ') '
            . 'inner join ' . _DB_PREFIX_ . 'attribute_group_lang agl on (agl.id_attribute_group=a.id_attribute_group '
            . 'and agl.id_lang=' . (int) $this->id_lang . ') '
            . 'where pa.id_product in (' . $list_boxes . ') '
            . 'order by pa.id_product_attribute, attribute_group, attribute_value';
        $combination_list = $db->executeS($sql);
        $combinations = (new GetProductCombination())->prepareCombinations($combination_list);
        if ($combinations) {
            $combinations = $this->rows2ToSheet($combinations);
            $excel->addSheet($combinations, 'Combinations');
        }

        $this->forceDownload($excel, $filename);
    }

    protected function rows2ToSheet($rows)
    {
        $rows = array_values($rows);
        $header = [];
        foreach ($rows[0] as $key => $value) {
            $header[] = strtolower($key);
        }
        foreach ($rows as &$row) {
            $row = array_values($row);
        }
        $rows = array_merge([$header], $rows);

        return $rows;
    }

    protected function forceDownload($excel, $filename)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $excel->downloadAs($filename);
        exit();
    }

    protected function addFields(&$result)
    {
        $getProductAttribute = new GetProductAttribute($this->controller);
        $getProductFeatures = new GetProductFeature($this->controller);
        $this->attributes = [];
        $this->features = [];
        $this->id_attributes = [];
        $this->id_features = [];
        $this->attributeExport = [];
        $this->featureExport = [];
        foreach ($result as &$row) {
            $images = GetProductImage::getProductImages($row['id_product']);
            $row['prefix'] = '';
            $row['categories'] = GetProductCategory::getProductCategories($row['id_product'], $row['id_category_default']);
            $row['img_root'] = MpTools::addUrlSlash(Context::getContext()->shop->domain, true);
            $row['img_folder'] = $images['folders'];
            $row['images'] = $images['images'];
            $row['suppliers'] = GetProductSupplier::getProductSuppliers($row['id_product']);
            $row['quantity'] = GetStock::getStockQuantity($row['id_product']);
            $getProductAttribute->setProductAttributes($row['id_product']);
            $getProductFeatures->setProductFeatures($row['id_product']);
        }
        $this->attributes = $getProductAttribute->get();
        $this->features = $getProductFeatures->get();

        foreach ($result as &$row) {
            foreach ($this->attributes as $key => $value) {
                $row['attr:' . $key] = MpTools::setExcelValue($value, $row['id_product']);
            }
            foreach ($this->features as $key => $value) {
                $row['feat:' . $key] = MpTools::setExcelValue($value, $row['id_product']);
            }
        }

        $this->attributeExport = $getProductAttribute->createListAttributes();
        $this->featureExport = $getProductFeatures->createListFeatures();

        return $result;
    }
}
