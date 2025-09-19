<?php

namespace MpSoft\MpMassImport\Helpers;

use Context;
use Image;
use Shop;
use Tools;

class ImageProduct
{
    protected $module;
    protected $controller;
    protected $context;
    protected $controller_name;

    public function __construct($module, $controller)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->controller_name = (new GetControllerName($controller))->get();
        $this->context = \Context::getContext();
    }

    public function addImages($id_product, $images)
    {
        /** @var \ModuleAdminController */
        $controller = $this->controller;
        /** @var \Module */
        $module = $this->module;

        if (!is_array($images)) {
            $images = [$images];
        }

        foreach ($images as $image) {
            $source = $image;
            $url_exists = ImageProduct::urlExists($source, $mime);
            if ($url_exists) {
                $file = [
                    'save_path' => $source,
                    'name' => basename($source),
                    'mime' => $mime,
                    'error' => 0,
                    'size' => getimagesize($source),
                ];

                $res = (int) $this->addImageProduct($id_product, $file);
                if (!$res) {
                    $controller->errors[] = sprintf(
                        $module->l('Images for product %d not inserted.', $this->controller_name),
                        $id_product
                    );
                }
            } else {
                $controller->errors[] = sprintf(
                    $module->l('URL incorrect: %s', $this->controller_name),
                    $source
                );
            }
        }
    }

    public function addImageProduct($id_product, &$file)
    {
        $image = new \Image();
        $image->id_product = (int) ($id_product);
        $image->position = \Image::getHighestPosition($id_product) + 1;

        if (!\Image::getCover($image->id_product)) {
            $image->cover = 1;
        } else {
            $image->cover = 0;
        }

        if (($validate = $image->validateFieldsLang(false, true)) !== true) {
            $this->controller->errors[] = $validate;
        }

        if (!$image->add()) {
            $this->controller->errors[] = $this->module->l('Error while creating additional image', $this->controller_name);
        } else {
            if (!$new_path = $image->getPathForCreation()) {
                $this->controller->errors[] = $this->module->l('An error occurred during new folder creation', $this->controller_name);

                return false;
            }
            // copy image
            $url = $file['save_path'];
            $img = $this->shuffleChars(8) . '.' . $image->image_format;

            try {
                $copy = copy($url, $img);
                $this->context->controller->confirmations[] = sprintf(
                    $this->module->l('Copied image from %s to %s: %d', $this->controller_name) . '<br>',
                    $url,
                    $img,
                    $copy
                );
            } catch (\Exception $e) {
                $this->context->controller->errors[] = sprintf(
                    $this->module->l('Unable to copy image from %s to %s', $this->controller_name),
                    $url,
                    $img
                );
            }

            $error = 0;

            if (!\ImageManager::resize($img, $new_path . '.' . $image->image_format, null, null, 'jpg', false, $error)) {
                switch ($error) {
                    case \ImageManager::ERROR_FILE_NOT_EXIST :
                        $this->controller->errors[] = $this->module->l('An error occurred while copying image, the file does not exist anymore.', $this->controller_name);

                        break;

                    case \ImageManager::ERROR_FILE_WIDTH :
                        $this->controller->errors[] = $this->module->l('An error occurred while copying image, the file width is 0px.', $this->controller_name);

                        break;

                    case \ImageManager::ERROR_MEMORY_LIMIT :
                        $this->controller->errors[] = $this->module->l('An error occurred while copying image, check your memory limit.', $this->controller_name);

                        break;
                    default:
                        $this->controller->errors[] = $this->module->l('An error occurred while copying image.', $this->controller_name);

                        break;
                }

                return false;
            } else {
                $imagesTypes = \ImageType::getImagesTypes('products');
                $generate_hight_dpi_images = (bool) \Configuration::get('PS_HIGHT_DPI');

                foreach ($imagesTypes as $imageType) {
                    if (!\ImageManager::resize($img, $new_path . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format)) {
                        $this->controller->errors[] = $this->module->l('An error occurred while copying image:', $this->controller_name) . ' ' . stripslashes($imageType['name']);

                        return false;
                    }

                    if ($generate_hight_dpi_images) {
                        if (!\ImageManager::resize($img, $new_path . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format)) {
                            $this->controller->errors[] = $this->module->l('An error occurred while copying image:', $this->controller_name) . ' ' . stripslashes($imageType['name']);

                            return false;
                        }
                    }
                }
            }

            unlink($img);
            // Necessary to prevent hacking
            unset($file['save_path'], $img);

            // Hook::exec('actionWatermark', array('id_image' => $image->id, 'id_product' => $product->id));

            if (!$image->update()) {
                $this->controller->errors[] = $this->module->l('Error while updating status', $this->controller_name);

                return false;
            }

            // Associate image to shop from context
            $shops = \Shop::getContextListShopID();
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

    private function shuffleChars($amount = 16)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

        // Output: 54esmdr0qf
        return substr(str_shuffle($permitted_chars), 0, $amount);
    }

    public static function urlExists($url, &$mime = null)
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
        // Tools::dieObject($headers);
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

    public static function getImageCoverProduct($id_product)
    {
        $link = \Context::getContext()->link;
        $id_lang = (int) \Context::getContext()->language->id;
        $cover = \Product::getCover($id_product);
        $product = new \Product($id_product);
        $imagePath = $link->getImageLink(
            $product->link_rewrite[$id_lang],
            $cover['id_image'],
            'home_default'
        );
        $img = '<img src="' . $imagePath . '" style="height: 64px; width: auto;">';

        return $img;
    }

    private function getImagePath($row)
    {
        $image_root = \Tools::jsonDecode($row['image_root']);
        $image_folder = \Tools::jsonDecode($row['image_folder']);
        $images = \Tools::jsonDecode($row['images']);

        if (count($image_root) > 0 && count($image_folder) > 0 && count($images) > 0) {
            $image_path = $image_root[0] . $image_folder[0] . $images[0];

            return $image_path;
        }

        return '';
    }
}
