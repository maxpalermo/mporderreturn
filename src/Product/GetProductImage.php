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

use Db;
use DbQuery;

class GetProductImage
{
    public static function getProductImages($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_image')
            ->from('image')
            ->where('id_product=' . (int) $id_product)
            ->orderBy('cover desc')
            ->orderBy('id_image');
        $rows = $db->executeS($sql);
        $image_folders = [];
        $image_paths = [];
        if ($rows) {
            foreach ($rows as $row) {
                $folder_split = str_split($row['id_image']);
                $folder = implode('/', $folder_split);
                $image_folder = 'p/' . $folder . '/';
                $image_path = $row['id_image'];
                $file = _PS_IMG_DIR_ . $image_folder . $image_path;
                if (file_exists($file . '.jpg')) {
                    $image_path .= '.jpg';
                } elseif (file_exists($file . '.png')) {
                    $image_path .= '.png';
                } else {
                    $image_path = '';
                }
                if (!$image_path) {
                    continue;
                }
                $image_folders[] = 'img/' . $image_folder;
                $image_paths[] = $image_path;
            }
        }

        return [
            'folders' => implode('|', $image_folders),
            'images' => implode('|', $image_paths),
        ];
    }

    public static function getProductAttributeImages($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = 'select id_image from ' . _DB_PREFIX_ . 'product_attribute_image '
            . 'where id_product_attribute=' . (int) $id_product_attribute;
        $images = $db->executeS($sql);
        $output = [];
        if ($images) {
            foreach ($images as $img) {
                $output[] = $img['id_image'];
            }

            return implode('|', $output);
        }

        return '';
    }
}
