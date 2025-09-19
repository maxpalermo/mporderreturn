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

class GetProductSupplier
{
    public static function getProductSuppliers($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('distinct s.name as supplier')
            ->from('supplier', 's')
            ->innerJoin('product_supplier', 'ps', 'ps.id_supplier=s.id_supplier')
            ->where('ps.id_product=' . (int) $id_product);
        $res = $db->executeS($sql);
        if (!$res) {
            return '';
        }
        $output = [];
        foreach ($res as $row) {
            $output[] = $row['supplier'];
        }
        $suppliers = implode('|', $output);

        return $suppliers;
    }
}
