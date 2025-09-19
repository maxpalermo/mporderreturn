<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright 2021 Massimiliano Palermo
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

class DeprecatedMpCommonProductsMethods
{
    protected $childName;
    protected $module;
    protected $db;
    protected $table;
    protected $name;

    protected function l($string)
    {
        $name = $this->childName;

        return $this->module->l($string, $name);
    }

    protected function prepareImages($product)
    {
        $product['img_root'] = json_decode($product['img_root'], true);
        $product['img_folder'] = json_decode($product['img_folder'], true);
        $product['images'] = json_decode($product['images'], true);

        if (!$product['img_root'] && !$product['img_folder'] && !$product['images']) {
            $product['img_root'] = [];
            $product['img_folder'] = [];
            $product['images'] = [];

            return $product;
        }

        foreach ($product['images'] as $key => &$value) {
            if (array_key_exists($key, $product['img_root']) && array_key_exists($key, $product['img_folder'])) {
                $value = $product['img_root'][$key] . $product['img_folder'][$key] . $value;
            } elseif (array_key_exists($key, $product['img_root']) && !array_key_exists($key, $product['img_folder'])) {
                $arrFolder = $product['img_folder'];
                $k1 = array_shift($arrFolder);
                $value = $product['img_root'][$key] . $k1 . $value;
            } elseif (!array_key_exists($key, $product['img_root']) && array_key_exists($key, $product['img_folder'])) {
                $arrRoot = $product['img_root'];
                $k0 = array_shift($arrRoot);
                $value = $k0 . $product['img_folder'][$key] . $value;
            } else {
                $arrRoot = $product['img_root'];
                $k0 = array_shift($arrRoot);
                $arrFolder = $product['img_folder'];
                $k1 = array_shift($arrFolder);
                $value = $k0 . $k1 . $value;
            }
        }

        return $product;
    }

    protected function shuffleChars($amount = 16)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

        return substr(str_shuffle($permitted_chars), 0, $amount);
    }

    protected function copyImage($source, $dest)
    {
        $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $user_agent = 'php';
        $process = curl_init($source);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $user_agent); //check here
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
        $image_content = curl_exec($process);

        if ($image_content === false) {
            $this->context->controller->errors[] = sprintf(
                'Curl error: %s, %d',
                curl_error($process),
                curl_errno($process)
            );
        }

        curl_close($process);

        return file_put_contents($dest, $image_content);
    }

    protected function addImageProduct($id_product, $source)
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
            $this->context->controller->errors[] = sprintf(
                'Validating image error %s for product %d',
                $validate,
                $id_product
            );
        }

        if (!$image->add()) {
            $error = Tools::displayError('Error while creating additional image');
            $this->context->controller->errors[] = $error;
            $this->context->controller->errors[] = print_r($image, 1);
        } else {
            if (!$new_path = $image->getPathForCreation()) {
                $file['error'] = Tools::displayError('An error occurred during new folder creation');

                return false;
            }
            //copy image
            $sourceImage = $source;
            $fileinfo = pathinfo($sourceImage);
            $extension = '.' . $fileinfo['extension'];
            $destImage = $this->shuffleChars(8) . $extension;
            $copyImage = (int) $this->copyImage($sourceImage, $destImage);
            if (!$copyImage) {
                $this->context->controller->errors[] = sprintf(
                    $this->l('Product %d. Unable to copy image from %s to %s. Copy Image %d bytes '),
                    $id_product,
                    $sourceImage,
                    $destImage,
                    (int) $copyImage
                );
            } else {
                $this->context->controller->warnings[] = sprintf(
                    $this->l('Product %d. Image copied from %s to %s. Copy Image %d bytes '),
                    $id_product,
                    $sourceImage,
                    $destImage,
                    (int) $copyImage
                );
            }

            $error = 0;
            $file = ['error' => []];

            if (!ImageManager::resize($destImage, $new_path . '.' . $image->image_format, null, null, 'jpg', false, $error)) {
                switch ($error) {
                    case ImageManager::ERROR_FILE_NOT_EXIST :
                        $error = Tools::displayError('An error occurred while copying image, the file does not exist anymore.');

                        break;

                    case ImageManager::ERROR_FILE_WIDTH :
                        $error = Tools::displayError('An error occurred while copying image, the file width is 0px.');

                        break;

                    case ImageManager::ERROR_MEMORY_LIMIT :
                        $error = Tools::displayError('An error occurred while copying image, check your memory limit.');

                        break;
                    default:
                        $error = Tools::displayError('An error occurred while copying image.');

                        break;
                }
                $this->context->controller->errors[] = sprintf(
                    '%s Id product %d, image %s to %s: %d',
                    $error,
                    $id_product,
                    $sourceImage,
                    $destImage,
                    (int) $copyImage
                );
            } else {
                $imagesTypes = ImageType::getImagesTypes('products');
                $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');

                foreach ($imagesTypes as $imageType) {
                    if (!ImageManager::resize($destImage, $new_path . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format)) {
                        $error = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                        $this->context->controller->errors[] = $error;

                        return false;
                    }

                    if ($generate_hight_dpi_images) {
                        if (!ImageManager::resize($destImage, $new_path . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format)) {
                            $error = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                            $this->context->controller->errors[] = $error;

                            return false;
                        }
                    }
                }
            }

            if (file_exists($destImage)) {
                unlink($destImage);
            }
            //Necessary to prevent hacking
            unset($source, $img);

            //Hook::exec('actionWatermark', array('id_image' => $image->id, 'id_product' => $product->id));

            if (!$image->update()) {
                $error = Tools::displayError('Error while updating status');
                $this->context->controller->errors[] = $error;

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

    protected function urlExists($url, &$mime = null)
    {
        $allowed_mime_types = [
            'image/bmp',
            'image/gif',
            'image/vnd.microsoft.icon',
            'image/jpeg',
            'image/png',
            'image/svg+xml',
            'image/tiff',
            'image/webp',
        ];
        $parts = explode('//', $url);

        if ($parts && count($parts) == 2) {
            $url = $parts[1];
        }

        $headers = get_headers('http://' . $url, true);
        //Tools::dieObject($headers);
        if (is_array($headers)) {
            foreach ($headers as $field) {
                if (!is_array($field)) {
                    $field = [$field];
                }
                foreach ($field as $value) {
                    if (stripos($value, '200 OK')) {
                        if (isset($headers['Content-Type'])) {
                            if (is_array($headers['Content-Type'])) {
                                $mime = end($headers['Content-Type']);
                            } else {
                                $mime = $headers['Content-Type'];
                            }
                            if (in_array($mime, $allowed_mime_types)) {
                                return true;
                            }
                        }

                        return false;
                    }
                }
            }
        }

        return false;
    }

    protected function getProductFromImportTable($reference)
    {
        $table = $this->tblProd();
        $reference = pSQL($reference);
        $sql = "SELECT * FROM `$table` WHERE `reference` like '$reference'";
        $row = $this->db->getRow($sql);
        $product = $this->prepareProduct($row);

        $fields = [
            ['id_shop_default', $this->id_shop],
            ['id_manufacturer', 0],
            ['id_supplier', 0],
            ['reference', ''],
            ['supplier_reference', ''],
            ['location', ''],
            ['width', 0],
            ['height', 0],
            ['depth', 0],
            ['weight', 0],
            ['quantity_discount', 0],
            ['ean13', 0],
            ['upc', '0'],
            ['cache_is_pack', 0],
            ['cache_has_attachments', 0],
            ['is_virtual', 0],
            ['id_category_default', 2],
            ['id_tax_rules_group', $product['tax']],
            ['on_sale', 0],
            ['online_only', 0],
            ['ecotax', 0],
            ['minimal_quantity', 0],
            ['price', 0],
            ['wholesale_price', 0],
            ['unity', ''],
            ['unit_price_ratio', 0],
            ['additional_shipping_cost', 0],
            ['customizable', 0],
            ['text_fields', 0],
            ['uploadable_files', 0],
            ['active', 0],
            ['redirect_type', ''],
            ['id_product_redirected', 0],
            ['available_for_order', 1],
            ['available_date', date('Y-m-d H:i:s')],
            ['condition', 'new'],
            ['minimal_quantity', 0],
            ['show_price', 1],
            ['indexed', 1],
            ['visibility', 'both'],
            ['cache_default_attribute', 0],
            ['advanced_stock_management', 0],
            ['date_add', date('Y-m-d H:i:s')],
            ['date_upd', date('Y-m-d H:i:s')],
            ['pack_stock_type', 0],
            ['meta_description', ''],
            ['meta_keywords', ''],
            ['meta_title', ''],
            ['link_rewrite', ''],
            ['name', ''],
            ['description', ''],
            ['description_short', ''],
            ['available_now', ''],
            ['available_later', ''],
            ['active', 1],
        ];

        $this->setFields($product, $fields);
        $product['name'] = $product['product_name'];

        /**
         * Create combinations
         */
        $product['attribute_combinations'] = $this->createCombinationList(
            $this->parseAttributeCombinations($product['attributes'])
        );

        if ($product['categories'][0] == 0) {
            $this->context->controller->errors[] = sprintf(
                $this->l('Categories is empty for product %s!', $this->name),
                $product['id_product'] . ' - ' . $product['reference']
            );
        }

        if ($product['suppliers'][0] == 0) {
            $this->context->controller->warnings[] = sprintf(
                $this->l('Suppliers is empty for product %s!', $this->name),
                $product['id_product'] . ' - ' . $product['reference']
            );
        }

        if ((int) $product['tax'] == 0) {
            $this->context->controller->warnings[] = sprintf(
                $this->l('No tax rate for product %s!', $this->name),
                $product['id_product'] . ' - ' . $product['reference']
            );
        }
        $product['attribute_products'] = json_decode($product['attribute_products'], true);
        foreach ($product['attribute_products'] as &$row) {
            $ids = [];
            foreach ($row['attribute'] as $key => &$item) {
                //Tools::dieObject([$key, $item], 0);
                $id_attribute_group = $this->getIdAttributeGroup($key);
                $id_attribute = $this->getIdAttribute($id_attribute_group, $item);
                $ids[] = [
                    'id_attribute_group' => $id_attribute_group,
                    'id_attribute' => $id_attribute,
                ];
            }
            $row['attribute']['id'] = $ids;
            unset($ids);
        }

        //Tools::dieObject($product);
        return $product;
    }

    protected function getIdAttributeGroup($name)
    {
        $table = $this->getTable('attribute_group_lang');
        $sql = "SELECT id_attribute_group from $table WHERE name like '$name'";

        return (int) $this->db->getValue($sql);
    }

    protected function getIdAttribute($id_attribute_group, $name)
    {
        $table = $this->getTable('attribute_lang');
        $innerTable = $this->getTable('attribute');
        $sql = "SELECT a.id_attribute from $table a "
            . "INNER JOIN $innerTable b on "
            . "(b.id_attribute=a.id_attribute and b.id_attribute_group = $id_attribute_group) "
            . "WHERE a.name like '$name'";

        return (int) $this->db->getValue($sql);
    }

    protected function tblProd()
    {
        return $this->getTable($this->table, true);
    }

    protected function tbl($table)
    {
        return $this->getTable($table, true);
    }

    protected function getTable($table, $prefix = true)
    {
        if ($prefix) {
            $table = _DB_PREFIX_ . $table;
        }

        return $table;
    }

    protected function prepareProduct($product)
    {
        if (!$product) {
            return [];
        }

        $product['id_product'] = $this->getIdProduct($product['reference']);
        $product['manufacturer'] = $this->getIdManufacturer($product['manufacturer']);
        $product['suppliers'] = json_decode($product['suppliers'], true);
        $product['categories'] = json_decode($product['categories'], true);
        $product['attributes'] = json_decode($product['attributes'], true);
        $product['features'] = json_decode($product['features'], true);
        $product = $this->prepareImages($product);

        return $product;
    }

    protected function getIdProduct($reference)
    {
        $table = $this->getTable('product');
        $reference = pSQL($reference);
        $sql = "SELECT id_product from `$table` WHERE `reference` like '$reference'";

        return (int) $this->db->getValue($sql);
    }

    protected function getIdManufacturer($name)
    {
        $table = $this->getTable('manufacturer');
        $sql = "SELECT `id_manufacturer` from `$table` WHERE `name` like '$name'";
        $id_manufacturer = (int) $this->db->getValue($sql);

        return $id_manufacturer;
    }

    protected function getIdTaxRulesGroup($tax)
    {
        $table = $this->getTable('tax_rules_group');
        $id_lang = (int) $this->id_lang;
        $tax = pSQL($tax);
        $sql = "SELECT `id_tax_rules_group` FROM `$table` WHERE name like '$tax'";

        return (int) $this->db->getValue($sql);
    }

    protected function setFields(&$product, $fields)
    {
        $updated = [];
        foreach ($fields as $field) {
            $key = $field[0];
            $value = $field[1];
            $isset = (int) isset($product[$key]);
            if (!$isset) {
                $product[$key] = $value;
                $updated[] = ['name' => $key, 'value' => $value];
            }
        }
    }

    protected function createCombinationList($list)
    {
        if (!$list) {
            return [];
        }
        if (count($list) <= 1) {
            return count($list) ? array_map([$this, 'arrayComb'], $list[0]) : $list;
        }
        $res = [];
        $first = array_pop($list);
        foreach ($first as $attribute) {
            $tab = $this->createCombinationList($list);
            foreach ($tab as $to_add) {
                $res[] = is_array($to_add) ? array_merge($to_add, [$attribute]) : [$to_add, $attribute];
            }
        }

        return $res;
    }

    private function arrayComb($v)
    {
        return [$v];
    }

    protected function parseAttributeCombinations($attributes)
    {
        if (!$attributes) {
            return [];
        }
        $output = [];
        foreach ($attributes as $row) {
            $attrs = explode(':', $row);
            if (isset($attrs[1])) {
                $ids_attr = explode(';', $attrs[1]);
                $output[] = array_unique($ids_attr);
                unset($ids_attr);
            } else {
                continue;
            }
        }

        return $output;
    }

    protected function saveCombination($id_product, $combination)
    {
        //Get id product attribute from attributes
        $attributes = [];
        $combination['id_product'] = $id_product;
        foreach ($combination['attribute']['id'] as $pa_attr) {
            $attributes[] = $pa_attr['id_attribute'];
        }
        $tot_attributes = count($attributes);
        $attributes = implode(',', $attributes);
        $id_product = (int) $combination['id_product'];
        $tbl_comb = $this->getTable('product_attribute_combination');
        $tbl_attr = $this->getTable('product_attribute');
        $sql = 'SELECT a.id_product_attribute, COUNT(*) AS tot_attributes '
            . "FROM $tbl_comb a "
            . "INNER JOIN $tbl_attr b ON "
            . "(a.id_product_attribute=b.id_product_attribute and b.id_product=$id_product) "
            . "WHERE id_attribute in ($attributes) "
            . 'GROUP BY id_product_attribute '
            . 'ORDER BY tot_attributes DESC';
        $row = (int) $this->db->getRow($sql);
        $id_product_attribute = 0;
        if ($row['tot_attributes'] == $tot_attributes) {
            $id_product_attribute = (int) $row['id_product_attribute'];
        }
        $combination['id_product_attribute'] = $id_product_attribute;

        $id_product_attribute = (int) $combination['id_product_attribute'];
        $combination['id'] = $id_product_attribute;
        $comb = new Combination($id_product_attribute);
        foreach (Combination::$definition['fields'] as $key => $value) {
            if (isset($combination[$key])) {
                $value = $combination[$key];
                $comb->$key = $value;
            }
        }
        if (!Validate::isDate($comb->available_date)) {
            $comb->available_date = date('Y-m-d H:i:s');
        }
        $comb->default_on = 0;
        //Tools::dieObject($comb);
        if ($comb->id) {
            $res = $comb->update();
        } else {
            $res = $comb->add();
        }
        //Tools::dieobject(["UPDATE COMBINATION" => $res, "COMB" => $comb, "SOURCE" => $combination], 0);
        if ($res) {
            if ($combination['default_on']) {
                $this->setDefaultProductAttribute($comb->id_product, $comb->id);
            }
            $comb->setAttributes(explode(',', $attributes));

            return $comb;
        }
        Context::getContext()->controller->errors[] = sprintf(
            'Unable to save product attribute %d %s',
            $comb->id,
            $this->db->getMsgError()
        );

        return false;
    }

    protected function getIdAttributeProducts($id_product)
    {
        $table = $this->getTable('product_attribute');
        $sql = "SELECT id_product_attribute FROM $table WHERE id_product=$id_product";
        $res = $this->db->executeS($sql);
        $output = [];
        if ($res) {
            foreach ($res as $row) {
                $output[] = $row['id_product_attribute'];
            }
        }

        return $output;
    }

    protected function save($reference)
    {
        $product = $this->getProductFromImportTable($reference);
        if (!$product) {
            return false;
        }
        $id_product = (int) $this->getIdProduct($reference);
        $p = new Product($id_product, true, $this->id_lang);
        if ($id_product) {
            $p->deleteFromSupplier();
            $p->deleteCategories(true);
            $p->deleteTags();
            $p->deleteDefaultAttributes();
            $p->deleteAccessories();
            $p->deleteAttachments();
            $p->deleteCustomization();
            $p->deleteProductAttributes();
            $p->deleteAttributesImpacts();
            $p->deleteProductFeatures();
            $p->deletePack();
            $p->deleteProductSale();
            $p->deleteSceneProducts();
            $p->deleteSearchIndexes();
            $p->deleteFromAccessories();
            $p->deleteDownload();
            $p->deleteImages();
            GroupReduction::deleteProductReduction($product['id_product']);
            SpecificPrice::deleteByProductId($product['id_product']);
            // Update product
            foreach (Product::$definition['fields'] as $key => $field) {
                if (!is_array($product[$key])) {
                    $p->$key = $product[$key];
                }
            }
            $p->id_supplier = $product['suppliers'][0];
            $p->id_category_default = $product['categories'][0];
        }
        // Insert product
        foreach (Product::$definition['fields'] as $key => $field) {
            if (!is_array($product[$key])) {
                $p->$key = $product[$key];
            }
        }
        $p->id_supplier = $product['suppliers'][0];
        $p->id_category_default = $product['categories'][0];

        $res = $p->save();
        if ($id_product == 0) {
            $id_product = $this->getIdProduct($p->reference);
        }
        if ($res) {
            $p = new Product($id_product);
            //Tools::dieObject($product);
            $this->addCategories($id_product, $product['categories']);
            $this->addManufacturer($id_product, $product['manufacturer']);
            $this->addSuppliers($id_product, $product['suppliers'], $product['supplier_reference']);
            $this->addFeatures($id_product, $product['features']);
            $this->addCombinations($id_product, $product);
            $this->setDefaultOn($id_product);
            $this->addImages($id_product, $product);
            //$this->updateProductAttributes($product); //Fill product attributes based on combination
            return true;
        }

        $p = new Product($id_product);
        $this->context->controller->errors[] = sprintf(
            $this->l('Error updating existing product %s! $s', $this->name),
            $product['id_product'] . ' - ' . $product['reference'],
            $this->db->getMsgError()
        );

        return false;
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

    protected function addCombinations($id_product, $product)
    {
        foreach ($product['attribute_products'] as $combination) {
            $comb = $this->saveCombination($id_product, $combination);
            if ($comb) {
                $attributes = [];
                foreach ($combination['attribute']['id'] as $attribute) {
                    $attributes[] = $attribute['id_attribute'];
                }
                $comb->setAttributes($attributes);
            }
        }
    }

    protected function setDefaultProductAttribute($id_product, $id_product_attribute)
    {
        $product = new Product($id_product);
        $product->deleteDefaultAttributes();
        $product->setDefaultAttribute((int) $id_product_attribute);
    }

    protected function setDefaultOn($id_product)
    {
        $id_product_attribute = (int) $this->db->getValue(
            'select max(id_product_attribute) from ' . _DB_PREFIX_ . 'product_attribute where id_product=' . (int) $id_product
        );
        if ($id_product_attribute) {
            $product = new Product($id_product);
            $product->deleteDefaultAttributes();
            $product->setDefaultAttribute($id_product_attribute);
        }
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
        foreach ($row['images'] as $image) {
            $source = str_replace(' ', '%20', $image);
            $this->addImageProduct($id_product, $source);
        }
    }

    protected function getIdProductAttributeFromCombination($id_attributes, $id_product)
    {
        if (!is_array($id_attributes)) {
            $id_attributes = [$id_attributes];
        }
        $res = $this->getIdProductAttributes($id_product);
        if (!$res) {
            return 0;
        }
        $dump = [];
        foreach ($res as $row) {
            $dump[] = $row['id_product_attribute'];
        }
        $id_product_attributes = implode(',', $dump);

        unset($res, $sql);


        $sql = new DbQuery();
        $sql->select('id_product_attribute, count(*)')
            ->from('product_attribute_combination')
            ->where('id_attribute in (' . implode(',', $id_attributes) . ')')
            ->where('id_product_attribute in (' . pSQL($id_product_attributes) . ')')
            ->groupBy('id_product_attribute')
            ->having('count(*) = ' . count($id_attributes));
        $res = $this->db->getValue($sql);
        if ($res) {
            return (int) $res;
        }

        return 0;
    }

    protected function getIdProductAttributes($id_product)
    {
        $table = $this->getTable('product_attribute');
        $sql = "SELECT `id_product_attribute` FROM `$table` where `id_product` = $id_product";
        $res = $this->db->executeS($sql);
        if ($res) {
            return $res;
        }

        return [];
    }
}
