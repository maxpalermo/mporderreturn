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

namespace MpSoft\MpMassImport\Deprecated;

use Configuration;
use Context;
use Db;
use Hook;
use Image;
use ImageManager;
use ImageType;
use Module;
use MpSoft\MpMassImport\Deprecated\MpUtilities;
use Shop;
use Tools;

abstract class MpMassImporter
{
    protected $tools;
    protected $controller;
    protected $context;
    protected $id_lang;
    protected $id_shop;
    protected $module;
    protected $db;
    protected $smarty;
    protected $link;
    protected $name;
    protected $table;

    public function __construct($controller)
    {
        $this->tools = new MpUtilities();
        $this->context = Context::getContext();
        $this->controller = $controller;
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->module = Module::getInstanceByName('mpmassimport');
        $this->db = Db::getInstance();
        $this->smarty = Context::getContext()->smarty;
        $this->link = Context::getContext()->link;
        $this->name = 'MpMassImporter';
        $this->table = '';
    }

    protected function l($text)
    {
        return $this->module->l($text, $this->name);
    }

    public function truncate()
    {
        if (!$this->table) {
            return false;
        }

        return Db::getInstance()->execute('truncate table ' . _DB_PREFIX_ . $this->table);
    }

    /**
     * Import Excel data into temporary table
     *
     * @param array $filename Filename array from Tools::fileAttachment()
     * @return void
     */
    abstract public function ImportExcel($filename);

    /**
     * Import temporary data in Prestashop tables
     *
     * @param [array] $boxes Array of id
     * @param [boolean] force import without check
     * @return void
     */
    abstract public function import($boxes, $force = false);

    /**
     * Get Id supplier from Supplier Name
     *
     * @param string $suppliers Array of Suppliers names
     * @return array Array of Id Suppliers
     */
    protected function getSuppliers(?string $suppliers)
    {
        $list = explode(';', $suppliers);
        $output = [];
        foreach ($list as $supplier) {
            $output[] = $this->tools->getIdSupplier($supplier);
        }

        return $output;
    }

    /**
     * Split string cell by # separator
     *
     * @param string $cell Cell value
     * @return array Array of data in cell
     */
    protected function splitCell($cell)
    {
        $split = explode('#', $cell);
        $output = [];
        foreach ($split as $value) {
            $output[] = $this->stringToArray($value);
        }

        return $output;
    }

    /**
     * Split Cell value into its array components
     *
     * @param string $input_string Cell value
     * @return array Associated array ARRAY('group', 'values')
     *
     */
    protected function createArray($input_string)
    {
        $values = [];
        $parts = explode(':', $input_string);
        $group = (int) $parts[0];
        if (!$group) {
            return false;
        }
        if (isset($parts[1])) {
            $values = explode(';', $parts[1]);
        } else {
            $values = [];
        }
        $row = [
            'group' => $group,
            'values' => $values,
        ];

        return $row;
    }

    /**
     * Undocumented function
     *
     * @param [type] $attribute
     * @return void
     */
    protected function stringToArray(?string $attribute)
    {
        $parts = explode(':', $attribute);
        $out_values = [];
        if (count($parts) == 2) {
            $values = explode(';', $parts[1]);
            foreach ($values as $value) {
                $out_values[] = $value;
            }

            return $parts[0] . ':' . implode(';', $out_values);
        }

        return '';
    }

    protected function addArrayList($array, $type)
    {
        if (!$array) {
            return false;
        }
        $array_row = [];
        foreach ($array as $item) {
            $array_list = $this->createArray($item);
            $item_list = $this->createArrayList($array_list, $type);
            $array_row = array_merge($array_row, $item_list);
            unset($array_list);
        }
        if (!$array_row) {
            return false;
        }

        return $array_row;
    }

    protected function createArrayList($array, $type)
    {
        if (!$array || !$type) {
            return false;
        }
        if ($type == 'attributes') {
            $group = $this->tools->getAttributeGroupName((int) $array['group']);
        } elseif ($type == 'features') {
            $group = $this->tools->getFeatureGroupName((int) $array['group']);
        } else {
            return false;
        }

        $values = $array['values'];
        $output = [];
        foreach ($values as $value) {
            if ($type == 'attributes') {
                $output[] = $this->tools->getAttributeValueName((int) $value);
            } elseif ($type == 'features') {
                $output[] = $this->tools->getFeatureValueName((int) $value);
            } else {
                return false;
            }
        }
        $list = [];
        $list[$group] = implode(', ', $output);

        return $list;
    }

    protected function addManufacturer($id_product, $id_manufacturer)
    {
        $this->db->update(
            'product',
            [
                'id_manufacturer' => (int) $id_manufacturer,
            ],
            'id_product=' . (int) $id_product
        );
    }

    protected function addImages($id_product, $row)
    {
        if (count($row->image_root) == 1) {
            $root = $row->image_root[0];
            $row->image_root = [];
            foreach ($row->images as $image) {
                $row->image_root[] = $root;
            }
        }
        if (count($row->image_folder) == 1) {
            $folder = $row->image_folder[0];
            $row->image_folder = [];
            foreach ($row->images as $image) {
                $row->image_folder[] = $folder;
            }
        }
        foreach ($row->images as $key => $image) {
            if (isset($row->image_root[$key]) && isset($row->image_folder[$key])) {
                $source = $row->image_root[$key] . $row->image_folder[$key] . $image;
                $mime = '';
                $url_exists = $this->tools->urlExists($source, $mime);
                if ($url_exists) {
                    $file = [
                        'save_path' => $source,
                        'name' => $image,
                        'mime' => $mime,
                        'error' => 0,
                        'size' => getimagesize($source),
                    ];
                    $res = (int) $this->addImageProduct($id_product, $file);
                    if (!$res) {
                        $this->controller->errors[] = $file['error'];
                    }
                } else {
                    $this->controller->errors[] = sprintf(
                        $this->l('URL incorrect: %s'),
                        $source
                    );
                }
            }
        }
    }

    protected function addImageProduct($id_product, &$file)
    {
        $image = new Image();
        $image->id_product = (int) ($id_product);
        $image->position = Image::getHighestPosition($id_product) + 1;

        if (!Image::getCover($image->id_product)) {
            $image->cover = 1;
        } else {
            $image->cover = 0;
        }

        if (($validate = $image->validateFieldsLang(false, true)) !== true) {
            $this->context->controller->errors[] = $validate;
            $file['error'] = Tools::displayError($validate);
        }

        if (!$image->add()) {
            $file['error'] = Tools::displayError('Error while creating additional image');
        } else {
            if (!$new_path = $image->getPathForCreation()) {
                $file['error'] = Tools::displayError('An error occurred during new folder creation');

                return false;
            }
            //copy image
            $url = $file['save_path'];
            $img = $this->tools->shuffleChars(8) . '.' . $image->image_format;

            try {
                $copy = copy($url, $img);
                $this->context->controller->confirmations[] = sprintf(
                    $this->l('Copied image from %s to %s') . '<br>',
                    $url,
                    $img
                );
            } catch (\Exception $e) {
                $this->context->controller->errors[] = sprintf(
                    $this->l('Unable to copy image from %s to %s'),
                    $url,
                    $img
                );
            }

            $error = 0;

            if (!ImageManager::resize($img, $new_path . '.' . $image->image_format, null, null, 'jpg', false, $error)) {
                switch ($error) {
                    case ImageManager::ERROR_FILE_NOT_EXIST :
                        $file['error'] = Tools::displayError('An error occurred while copying image, the file does not exist anymore.');

                        break;

                    case ImageManager::ERROR_FILE_WIDTH :
                        $file['error'] = Tools::displayError('An error occurred while copying image, the file width is 0px.');

                        break;

                    case ImageManager::ERROR_MEMORY_LIMIT :
                        $file['error'] = Tools::displayError('An error occurred while copying image, check your memory limit.');

                        break;
                    default:
                        $file['error'] = Tools::displayError('An error occurred while copying image.');

                        break;
                }
                $this->context->controller->errors[] = $file['error'];

                return false;
            } else {
                $imagesTypes = ImageType::getImagesTypes('products');
                $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');

                foreach ($imagesTypes as $imageType) {
                    if (!ImageManager::resize($img, $new_path . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format)) {
                        $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);

                        return false;
                    }

                    if ($generate_hight_dpi_images) {
                        if (!ImageManager::resize($img, $new_path . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format)) {
                            $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);

                            return false;
                        }
                    }
                }
            }

            unlink($img);
            //Necessary to prevent hacking
            unset($file['save_path'], $img);

            //Hook::exec('actionWatermark', array('id_image' => $image->id, 'id_product' => $product->id));

            if (!$image->update()) {
                $file['error'] = Tools::displayError('Error while updating status');

                return false;
            }

            // Associate image to shop from context
            $shops = Shop::getContextListShopID();
            $image->associateTo($shops);
            $json_shops = [];

            foreach ($shops as $id_shop) {
                $json_shops[$id_shop] = true;
            }

            $file['status'] = 'ok';
            $file['id'] = $image->id;
            $file['position'] = $image->position;
            $file['cover'] = $image->cover;
            $file['legend'] = $image->legend;
            $file['path'] = $image->getExistingImgPath();
            $file['shops'] = $json_shops;

            @unlink(_PS_TMP_IMG_DIR_ . 'product_' . (int) $id_product . '.jpg');
            @unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '_' . $this->context->shop->id . '.jpg');
        }

        return true;
    }

    protected function addCategories($id_product, $categories)
    {
        $pos = 0;
        $categories = array_unique($categories);
        $this->db->execute('delete from ' . _DB_PREFIX_ . 'category_product where id_product=' . (int) $id_product);
        foreach ($categories as $id_category) {
            $this->db->insert(
                'category_product',
                [
                    'id_product' => $id_product,
                    'id_category' => $id_category,
                    'position' => $pos,
                ]
            );
            $pos++;
        }
    }

    protected function addSuppliers($id_product, $suppliers, $reference)
    {
        $this->db->execute('delete from ' . _DB_PREFIX_ . 'product_supplier where id_product=' . (int) $id_product);
        foreach ($suppliers as $id_supplier) {
            $this->db->insert(
                'product_supplier',
                [
                    'id_product' => $id_product,
                    'id_product_attribute' => 0,
                    'id_supplier' => $id_supplier,
                    'product_supplier_reference' => $reference,
                    'product_supplier_price_te' => 0,
                    'id_currency' => $this->context->currency->id,
                ]
            );
        }
    }

    protected function addFeatures($id_product, $features)
    {
        $this->db->execute('delete from ' . _DB_PREFIX_ . 'feature_product where id_product=' . (int) $id_product);
        foreach ($features as $feature) {
            $split = explode(':', $feature);
            if (isset($split[1])) {
                $feature_group = (int) $split[0];
                $feature_values = explode(';', $split[1]);
                foreach ($feature_values as $fv) {
                    if (!$fv) {
                        continue;
                    }
                    $this->db->insert(
                        'feature_product',
                        [
                            'id_feature' => (int) $feature_group,
                            'id_product' => (int) $id_product,
                            'id_feature_value' => (int) $fv,
                        ]
                    );
                }
            }
        }
    }
}
