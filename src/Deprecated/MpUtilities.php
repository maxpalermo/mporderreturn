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
use DbQuery;
use Tools;

class MpUtilities
{
    protected $id_lang;
    protected $id_shop;
    protected $db;
    protected $context;
    protected $tools;

    public function __construct()
    {
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->db = Db::getInstance();
    }

    public function sortAttributeSize($attr)
    {
        $arrSize = [
            'XXS',
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            '3XL',
            '4XL',
            '5XL',
        ];

        $attributes = explode(',', Tools::strtoupper($attr));
        if (!$attributes) {
            return '';
        }
        sort($attributes);
        $attrArray = [];
        $sortArray = [];
        foreach ($attributes as $attribute) {
            $attrArray[$attribute] = $attribute;
        }

        foreach ($arrSize as $value) {
            if (in_array($value, $attributes)) {
                $sortArray[] = $value;
            }
        }

        if ($sortArray) {
            return implode(',', $sortArray);
        } else {
            $output = implode(',', $attributes);

            return $output;
        }
    }

    private function replace_accent($str)
    {
        $a = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];

        return str_replace($a, $b, $str);
    }

    private function toURI($str, $replace = [], $delimiter = '-')
    {
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }

        $clean = $this->replace_accent($str);
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    public function setLinkRewrite($product_name)
    {
        $url = $this->toURI($product_name);

        return $url;
    }

    public function getColor($attributes)
    {
        $color = '';

        foreach ($attributes as $attr) {
            foreach ($attr as $key) {
                foreach ($key as $k) {
                    if ($k == 'Colore') {
                        $color = array_shift($attr['attributes']);
                        $color = array_shift($color);
                    }
                }
            }
        }

        return $color;
    }

    public function getIdSupplier($name)
    {
        $db = $this->db;
        $sql = new DbQuery();
        $sql->select('id_supplier')
            ->from('supplier')
            ->where('name =\'' . $name . '\'');

        return (int) $db->getValue($sql);
    }

    public function getIdManufacturer($name)
    {
        $db = $this->db;
        $sql = new DbQuery();
        $sql->select('id_manufacturer')
            ->from('manufacturer')
            ->where('name =\'' . $name . '\'');

        return (int) $db->getValue($sql);
    }

    public function getIdFeatureGroup($name)
    {
        return $this->findFeatureGroup($name);
    }

    public function findFeatureGroup($name)
    {
        $db = $this->db;
        $name = trim(Tools::strtolower($name));
        $sql = 'select id_feature '
            . 'from ' . _DB_PREFIX_ . 'feature_lang '
            . "where LOWER(name) like '" . pSQL($name) . "' "
            . 'and id_lang = ' . (int) $this->id_lang;
        //print "<pre>".$sql."</pre>";
        return (int) $db->getValue($sql);
    }

    public function cleanFeaturename($name)
    {
        $name = str_replace('*', '', $name);
        $pos = Tools::strpos($name, ':', 5);
        if ($pos !== false) {
            $name = Tools::substr($name, 0, $pos);
        }

        return $name;
    }

    public function getIdFeatureValue($id_feature, $name)
    {
        return $this->findFeatureValue($id_feature, $name);
    }

    public function findFeatureValue($id_feature, $value)
    {
        $db = $this->db;
        $value = trim(Tools::strtolower($value));
        $sql = 'select fv.id_feature_value '
            . 'from ' . _DB_PREFIX_ . 'feature_value fv '
            . 'inner join ' . _DB_PREFIX_ . 'feature_value_lang fvl on '
                . '(fvl.id_feature_value=fv.id_feature_value and fvl.id_lang=' . (int) $this->id_lang . ') '
            . "where LOWER(value) like '" . pSQL($value) . "' "
            . 'and fv.id_feature=' . (int) $id_feature;
        //print "<pre>".$sql."</pre>";
        return (int) $db->getValue($sql);
    }

    public function getAttributeGroup($id_attribute_group)
    {
        $sql = 'select a.*, b.name from ' . _DB_PREFIX_ . 'attribute_group a '
            . 'inner join ' . _DB_PREFIX_ . 'attribute_group_lang b on ('
            . 'b.id_attribute_group=a.id_attribute_group and b.id_lang=' . (int) $this->id_lang
            . ') where id_attribute_group = ' . (int) $id_attribute_group;

        return $this->db->getRow($sql);
    }

    public function getIdAttributeGroup($name)
    {
        return $this->findAttributeGroup($name);
    }

    public function findAttributeGroup($name)
    {
        $db = $this->db;
        $name = trim(Tools::strtolower($name));
        $sql = 'select id_attribute_group '
            . 'from ' . _DB_PREFIX_ . 'attribute_group_lang '
            . "where LOWER(name) like '" . pSQL($name) . "' "
            . 'and id_lang = ' . (int) $this->id_lang;

        return (int) $db->getValue($sql);
    }

    public function getIdAttributeValue($id_attribute_group, $value)
    {
        return $this->findAttributeValue($id_attribute_group, $value);
    }

    public function findAttributeValue($id_attribute_group, $value)
    {
        $db = $this->db;
        $value = trim(Tools::strtolower($value));
        $sql = 'select a.id_attribute '
            . 'from ' . _DB_PREFIX_ . 'attribute a '
            . 'inner join ' . _DB_PREFIX_ . 'attribute_lang al on '
                . '(al.id_attribute=a.id_attribute and al.id_lang=' . (int) $this->id_lang . ') '
            . "where LOWER(al.name) like '" . pSQL($value) . "' "
            . 'and a.id_attribute_group=' . (int) $id_attribute_group;
        //print "<pre>".$sql."</pre>";
        return (int) $db->getValue($sql);
    }


    /**
     * Alias for getProductIdByReference
     *
     * @param string $reference
     * @return int Product id
     */
    public function getIdProductByReference($reference)
    {
        return $this->getProductIdByReference($reference);
    }

    public function getIdProductAttributes($id_product)
    {
        $db = $this->db;
        $sql = new DbQuery();
        $sql->select('id_product_attribute')
            ->from('product_attribute')
            ->where('id_product=' . (int) $id_product);
        $res = $db->executeS($sql);
        if ($res) {
            return $res;
        }

        return [];
    }

    public function getIdProductFromIdProductAttribute($id_product_attribute)
    {
        $sql = 'select id_product from ' . _DB_PREFIX_ . 'product_attribute '
            . 'where id_product_attribute=' . (int) $id_product_attribute;
        $id_product = (int) $this->db->getValue($sql);

        return $id_product;
    }

    public function getIdProductAttributeFromCombination($id_attributes, $id_product)
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

    public function existsProduct($reference)
    {
        $db = $this->db;
        $sql = new DbQuery();
        $sql->select('count(*)')
            ->from('product')
            ->where('reference=\'' . pSQL($reference) . '\'');
        $res = (int) $db->getValue($sql);

        return $res;
    }

    public function detax($price, $tax_rate)
    {
        $detax = $price / (1 + ($tax_rate / 100));

        return round($detax, 6);
    }

    public function productExists($reference)
    {
        if (Tools::substr($reference, 0, 3) != 'ISA') {
            $reference = 'ISA' . $reference;
        }
        $db = $this->db;
        $sql = 'select count(*) from ' . _DB_PREFIX_ . "product where reference like '" . pSQL($reference) . "'";

        return (int) $db->getValue($sql);
    }

    public function idValue($id, $value)
    {
        return [
            'id' => (int) $id,
            'value' => $value,
        ];
    }

    public function getTablesByField($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as &$field) {
                if (substr($field, 0, 1) != '`' && substr($field, -1, 1) != '`') {
                    $field = "`$field`";
                }
            }
            $fields = implode(',', $fields);
        }
        $sql = 'SELECT DISTINCT TABLE_NAME '
            . 'FROM INFORMATION_SCHEMA.COLUMNS '
            . "WHERE COLUMN_NAME IN ($fields) "
            . "AND TABLE_SCHEMA='" . _DB_NAME_ . "'";

        $res = $this->db->executeS($sql);
        if (!$res) {
            return [];
        }
        $output = [];
        foreach ($res as $row) {
            $output[] = $row['TABLE_NAME'];
        }

        return $output;
    }

    public function getFieldsList($table)
    {
        $sql = 'SELECT COLUMN_NAME '
          . 'FROM INFORMATION_SCHEMA.COLUMNS '
          . "WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "' AND TABLE_NAME = '$table'";
        $res = $this->db->executeS($sql);
        $output = [];
        if ($res) {
            foreach ($res as $row) {
                $output[] = $row['COLUMN_NAME'];
            }
        }

        return $output;
    }

    public function arrayToString($array)
    {
        Tools::dieObject($array, 0);
        if (!is_array($array)) {
            return $array;
        }

        $output = [];
        foreach ($array as $key => $value) {
            $values = [];
            if (is_array($value)) {
                foreach ($value as $item) {
                    $values[] = $item;
                }
            } else {
                $values[] = $value;
            }
            $output[] = $key . ':' . implode(';', $values);
        }
        $output = implode('#', $output);
        Tools::dieObject($output, 0);

        return $output;
    }

    public function stringToArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $output = explode('#', $value);
        foreach ($output as &$out) {
            $out = explode(':', $out);
            if (strpos($out[1], ';') == !false) {
                $out[1] = explode(';', $out[1]);
            }
        }

        return $output;
    }

    public function strEncode($str)
    {
        $str = str_replace("\t", '{_tab_}', $str);
        $str = str_replace("\n", '{_n_}', $str);
        $str = str_replace("\r", '{_r_}', $str);
        $str = str_replace(';', '{semicolon}', $str);
        $str = str_replace(':', '{dots}', $str);
        $str = str_replace('"', '{2quot}', $str);
        $str = str_replace("'", '{quot}', $str);

        return $str;
    }

    public function strDecode($str)
    {
        $str = str_replace('{_tab_}', "\t", $str);
        $str = str_replace('{_n_}', "\n", $str);
        $str = str_replace("\r", '{_r_}', $str);
        $str = str_replace('{semicolon}', ';', $str);
        $str = str_replace('{dots}', ':', $str);
        $str = str_replace('{2quot}', '"', $str);
        $str = str_replace('{quot}', "'", $str);

        return $str;
    }

    public function shuffleChars($amount = 16)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        // Output: 54esmdr0qf
        return substr(str_shuffle($permitted_chars), 0, $amount);
    }

    public function getFieldValue($string, $separator = ':', $toUpperCase = true, $id = false)
    {
        $parts = explode($separator, $string);
        if (!is_array($parts)) {
            return $string;
        }
        if (count($parts) == 2) {
            if ($id) {
                return (int) $parts[0];
            } else {
                if ($toUpperCase) {
                    return Tools::strtoupper($parts[1]);
                }

                return $parts[1];
            }
        } else {
            return $string;
        }
    }

    public function getId($array)
    {
        if (is_array($array) && isset($array['id'])) {
            return (int) $array['id'];
        }

        return 0;
    }

    public function getIdTaxRulesGroup($name)
    {
        $sql = 'select id_tax_rules_group from ' . _DB_PREFIX_ . 'tax_rules_group '
            . "where name like '" . pSQL($name) . "' and deleted = 0";

        return (int) $this->db->getValue($sql);
    }

    public function toPriceFloat($value)
    {
        $value = (float) $value;

        return number_format($value, 6);
    }

    public function urlExists($url, &$mime = null)
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





    public function addUrlSlash($url, $addProtocol = false)
    {
        if ($addProtocol) {
            if (Configuration::get('PS_SSL_ENABLED')) {
                $url = 'https://' . $url;
            } else {
                $url = 'http://' . $url;
            }
        }

        return rtrim($url, '/') . '/';
    }

    public function urlSpace($url)
    {
        return str_replace(' ', '%20', trim($url));
    }

    /**
     *
     * Get file extension
     *
     * @param  array $file The file params from Tools::fileAttachment()
     * @return string $extension The extension file
     *
     **/
    public function getExtension(?array $file)
    {
        $pathinfo = pathinfo($file['tmp_name']);
        $extension = $pathinfo['extension'];

        return $extension;
    }

    /**
     *
     * Create a file with the given name
     *
     * @param  array $file The file params from Tools::fileAttachment()
     * @return string $filename The path to the file just created
     *
     **/
    public function createfile($file)
    {
        $dirname = dirname($file['tmp_name']);
        $filename = $dirname . DIRECTORY_SEPARATOR . $file['rename'];
        copy($file['tmp_name'], $filename);
        chmod($filename, 0777);

        return $filename;
    }

    /**
     *
     * Delete a file
     *
     * @param  string $file The full file path
     * @return void
     *
     **/
    public function unlinkfile(?string $file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function getProductIdByReference($reference)
    {
        $sql = 'select id_product from ' . _DB_PREFIX_ . "product where reference = '" . pSQL($reference) . "'";

        return (int) $this->db->getValue($sql);
    }

    public function getProductNameByReference($reference)
    {
        $pre = _DB_PREFIX_;
        $sql = 'select a.name from ' . $pre . 'product_lang a '
            . 'inner join ' . $pre . 'product b on '
            . '(b.id_product=a.id_product and a.id_lang=' . $this->id_lang . ' and a.id_shop=' . $this->id_shop . ') '
            . "where b.reference = '" . pSQL($reference) . "'";

        return $this->db->getValue($sql);
    }

    public function getCookie($cookie)
    {
        $this->context = Context::getContext();
        if ($this->context->cookie->__isset($cookie)) {
            return Tools::jsonDecode($this->context->cookie->__get($cookie));
        }

        return false;
    }

    public function setCookie($cookie, $value)
    {
        $this->context = Context::getContext();
        $this->context->cookie->__set($cookie, Tools::jsonEncode($value));
        $this->context->cookie->write();
    }

    public function delCookie($cookie)
    {
        $this->context = Context::getContext();
        $this->context->cookie->__unset($cookie);
    }

    public function setExcelValue($values, $id_product)
    {
        foreach ($values as $key => $value) {
            if ($key == $id_product) {
                return implode(';', $value);
            }
        }

        return '';
    }

    public function listAll($id)
    {
        $sql = $this->tools->getCookie('listsql');
        $sql = substr($sql, 0, strpos($sql, 'LIMIT'));
        $rows = $this->db->executeS($sql);
        $output = [];
        if ($rows) {
            foreach ($rows as $row) {
                $output[] = $row[$id];
            }
        }

        return $output;
    }
}
