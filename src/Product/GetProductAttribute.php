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
use MpSoft\MpMassImport\Helpers\MpTools;

class GetProductAttribute
{
    /** @var int */
    protected $id_lang;
    /** @var array */
    protected $attributes;
    /** @var array */
    protected $id_attributes;
    /** @var ModuleAdminController */
    protected $controller;
    /** @var Db */
    protected $db;
    /** @var array */
    protected $attributeExport;
    /** @var array */
    protected $attributeGroupIndexes;
    /** @var array */
    protected $attributeIndexes;
    /** @var array */
    protected $groups;

    public function __construct(ModuleAdminController $controller)
    {
        $this->controller = $controller;
        $this->db = Db::getInstance();
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->attributes = [];
        $this->attributeGroupIndexes = [];
        $this->attributeIndexes = [];
        $this->groups = [];
    }

    public function get()
    {
        return $this->attributes;
    }
    public function setProductAttributes($id_product)
    {
        $sql = new DbQuery();
        $sql->select('distinct a.id_attribute, al.name as attribute')
            ->select('a.id_attribute_group, agl.name as attribute_group')
            ->from('product_attribute_combination', 'pac')
            ->innerJoin('product_attribute', 'pa', 'pa.id_product_attribute=pac.id_product_attribute and pa.id_product=' . (int) $id_product)
            ->innerJoin('attribute', 'a', 'a.id_attribute=pac.id_attribute')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute and al.id_lang=' . (int) $this->id_lang)
            ->innerJoin('attribute_group_lang', 'agl', 'agl.id_attribute_group=a.id_attribute_group and agl.id_lang=' . (int) $this->id_lang)
            ->orderBy('agl.name,al.name');
        $res = $this->db->executeS($sql);
        if ($res) {
            foreach ($res as $row) {
                $ag = $row['attribute_group'];
                $av = $row['attribute'];
                $this->attributes[$ag][$id_product][] = $av;
            }
        }

        true;
    }

    public function createListAttributes()
    {
        $attributes = $this->attributes;

        if (!$attributes) {
            return false;
        }

        foreach ($attributes as $key => $values) {
            $group = [];
            if (!isset($this->attributeGroupIndexes[$key])) {
                $id_group = (int) $this->getIdGroupByName($key);
                $sql = 'select a.*, b.* from ' .
                    _DB_PREFIX_ . 'attribute_group a ' .
                    'inner join ' . _DB_PREFIX_ . 'attribute_group_lang b on (a.id_attribute_group=b.id_attribute_group) ' .
                    'where a.id_attribute_group=' . (int) $id_group .
                    ' and b.id_lang=' . (int) $this->id_lang;
                $res = $this->db->getRow($sql);
                if ($res) {
                    $group = [
                        'is_color_group' => (int) $res['is_color_group'],
                        'group_type' => $res['group_type'],
                    ];
                } else {
                    continue;
                }
                $this->attributeGroupIndexes[$key] = $id_group;
                $this->groups[$key] = $group;
            } else {
                $id_group = (int) $this->attributeGroupIndexes[$key];
                $group = $this->groups[$key];
            }
            if (!$id_group) {
                continue;
            }
            foreach ($values as $attribute) {
                foreach ($attribute as $attr_value) {
                    if (!isset($this->attributeIndexes[$attr_value])) {
                        $id_attribute = (int) $this->getIdAttributeByName($id_group, $attr_value);
                        if ($id_attribute) {
                            $color = $this->getAttributeColor($id_attribute);
                            $image = $this->getAttributeImage($id_attribute);
                            $row = [
                                'id_attribute_group' => $id_group,
                                'group' => $this->getAttributeGroupName($id_group),
                                'is_color_group' => $group['is_color_group'],
                                'group_type' => $group['group_type'],
                                'id_attribute' => $id_attribute,
                                'attribute' => $this->getAttributeValueName($attr_value),
                                'color' => $color,
                                'img_root' => MpTools::addUrlSlash(Context::getContext()->shop->domain, true),
                                'img_folder' => MpTools::addUrlSlash($image['folder']),
                                'image' => $image['filename'],
                            ];
                            $index = 'attr_' . $id_attribute;
                            $this->attributeExport[$index] = $row;
                        }
                        $this->attributeIndexes[$attr_value] = $id_attribute;
                    } else {
                        $id_attribute = (int) $this->attributeIndexes[$attr_value];
                    }
                }
            }
        }

        return $this->attributeExport;
    }

    public function getIdGroupByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_attribute_group')
            ->from('attribute_group_lang')
            ->where('name=\'' . pSQL($name) . '\'')
            ->where('id_lang=' . (int) $this->id_lang);

        return (int) $this->db->getValue($sql);
    }

    public function getIdAttributeByName($id_attribute_group, $name)
    {
        $sql = new DbQuery();
        $sql->select('a.id_attribute')
            ->from('attribute', 'a')
            ->innerJoin('attribute_lang', 'b', 'a.id_attribute=b.id_attribute and b.id_lang=' . (int) $this->id_lang)
            ->where('b.name=\'' . pSQL($name) . '\'')
            ->where('a.id_attribute_group=' . (int) $id_attribute_group);

        return (int) $this->db->getValue($sql);
    }

    public function getAttributeColor($id_attribute)
    {
        $sql = 'select color from ' . _DB_PREFIX_ . 'attribute where id_attribute=' . (int) $id_attribute;

        return $this->db->getValue($sql);
    }

    public function getAttributeImage($id_attribute)
    {
        $dir = MpTools::addUrlSlash(_PS_COL_IMG_DIR_);
        $folder = 'img/co/';
        $filename = $id_attribute . '.jpg';

        $image = $dir . $filename;

        if (file_exists($image)) {
            return ['folder' => $folder, 'filename' => $filename];
        }

        return ['folder' => '', 'filename' => ''];
    }

    public function getAttributeGroupName($id)
    {
        $sql = 'select name from ' . _DB_PREFIX_ . 'attribute_group_lang '
            . 'where id_attribute_group=' . (int) $id . ' and id_lang=' . (int) $this->id_lang;

        return $this->db->getValue($sql);
    }

    public function getAttributeValueName($values)
    {
        if (!is_array($values)) {
            $values = [(int) $values];
        }
        $value = implode(',', $values);
        $sql = 'select name from ' . _DB_PREFIX_ . 'attribute_lang '
            . 'where id_attribute in (' . $value . ') and id_lang=' . (int) $this->id_lang;
        $res = $this->db->executeS($sql);
        $output = [];
        foreach ($res as $row) {
            $output[] = $row['name'];
        }

        return implode(', ', $output);
    }
}
