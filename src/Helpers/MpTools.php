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

use Configuration;

class MpTools
{
    public static function addUrlSlash($url, $addProtocol = false)
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

    public static function setExcelValue($values, $id_product)
    {
        foreach ($values as $key => $value) {
            if ($key == $id_product) {
                return implode('|', $value);
            }
        }

        return '';
    }

    public static function getHeader($row)
    {
        $header = [];
        foreach ($row as $key => $value) {
            $header[] = $key;
        }

        return $header;
    }
}
