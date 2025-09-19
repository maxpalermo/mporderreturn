<?php
/**
* Since 2007 PrestaShop
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

class Cookies
{
    protected $cookie;

    public function __construct()
    {
        $this->cookie = Context::getContext()->cookie;
    }

    public function getValue($key)
    {
        if ($this->cookie->__isset($key)) {
            $value = $this->cookie->__get($key);
            if ($this->isJson($value)) {
                $value = json_decode($value, true);
            }

            return $value;
        }

        return false;
    }

    public function setValue($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            $this->cookie->__set($key, json_encode($value));
        } else {
            $this->cookie->__set($key, $value);
        }
        $this->cookie->write();
    }

    public function delValue($key)
    {
        if ($this->cookie->__isset($key)) {
            $this->cookie->__unset($key);
        }
    }

    public function isSet($key)
    {
        return $this->cookie->__isset($key);
    }

    protected function isJson($string)
    {
        $dummy = json_decode($string);
        $error = json_last_error();

        return  $error === JSON_ERROR_NONE;
    }
}
