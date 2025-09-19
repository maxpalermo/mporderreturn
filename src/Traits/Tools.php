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
trait Tools
{
    public function invertColor($hex)
    {
        $hex = str_replace('#', '', $hex);
        $rgb = 0;
        $invert_0 = 0;
        $invert_255 = 0;
        for ($i = 0; $i < 3; $i++) {
            $rgb = hexdec(substr($hex, (2 * $i), 2));
            if ($rgb < 128) {
                ++$invert_0;
            } else {
                ++$invert_255;
            }
        }

        if ($invert_0 > $invert_255) {
            return '#F0F0F0';
        } else {
            return '#101010';
        }
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($params));
    }

    public function displayException(string $object, string $message = '', string $query = ''):void
    {
        print '<pre>';
        print "\n<h1>{$object}</h1>";
        print "ERROR:<p style='color: red;'>" . $message . '</p>';
        $query = str_replace("\t", '', $this->_listsql);
        $query = str_replace("\n", ' ', $query);
        $query = str_replace("\r", ' ', $query);
        $query = str_replace('  ', ' ', $query);
        print "QUERY:<p style='color: blue;'>$query</p>";
        print '<hr>';
        print '<p>' . print_r(debug_print_backtrace(), 1) . '</p>';
        print '</pre>';
        exit();
    }

    public function extractClassName($class)
    {
        $class = get_class($class);
        $name = $class;
        if ($name) {
            $name = explode('\\', $class);
            if ($name) {
                $name = $name[count($name) - 1];
            } else {
                $name = $class;
            }
        }
        if (preg_match('/(.*)Controller$/', $name)) {
            $name = preg_replace('/(.*)Controller$/', '$1', $name);
        }

        return $name;
    }
}
