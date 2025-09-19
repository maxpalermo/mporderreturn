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

namespace MpSoft\MpMassImport\Traits;
trait Cookies
{
    public function cookieSetValue($key, $value, $prefix = true)
    {
        if ($prefix && !preg_match('/^' . $this->name . '/i', $key)) {
            $key = $this->ucPrefix($key);
        }
        $cookie = \Context::getContext()->cookie;
        $cookie->__set($key, json_encode($value));

        return $cookie->write();
    }

    public function cookieGetValue($key, $prefix = true)
    {
        if ($prefix && !preg_match('/^' . $this->name . '/i', $key)) {
            $key = $this->ucPrefix($key);
        }
        $cookie = \Context::getContext()->cookie;
        if ($cookie->__isset($key)) {
            return json_decode($cookie->__get($key), true);
        }

        return null;
    }

    public function cookieDelete($key, $prefix = true)
    {
        if ($prefix && !preg_match('/^' . $this->name . '/i', $key)) {
            $key = $this->ucPrefix($key);
        }
        $cookie = \Context::getContext()->cookie;
        if ($cookie->__isset($key)) {
            return $cookie->__unset($key);
        }

        return true;
    }

    public function cookieIsSet($key, $prefix = true)
    {
        if ($prefix && !preg_match('/^' . $this->name . '/i', $key)) {
            $key = $this->ucPrefix($key);
        }
        $cookie = \Context::getContext()->cookie;

        return $cookie->__isset($key);
    }

    protected function setDefaultCurrentPage()
    {
        if (!$this->cookieIsSet('CURRENT_PAGE')) {
            $this->setCurrentPage('default');
        }
    }

    protected function setCurrentPage($page = null)
    {
        if (!$page) {
            $page = 'default';
        }

        return $this->cookieSetValue('CURRENT_PAGE', $page);
    }

    protected function getCurrentPage()
    {
        $currentPage = $this->cookieGetValue('CURRENT_PAGE');

        return $currentPage;
    }

    public function prefix($string, $name = '')
    {
        if (!$name) {
            $name = $this->name;
        }

        return $this->name . '_' . $string;
    }

    public function ucPrefix($string, $name = '')
    {
        return \Tools::strtoupper($this->prefix($string, $name));
    }
}
