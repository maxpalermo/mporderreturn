<?php

namespace MpSoft\MpMassImport\Helpers;

use Db;
use DbQuery;

class ProductExists
{
    public static function productExistsByReference($reference)
    {
        $sql = self::prepareQuery();
        $sql->where('reference=\'' . pSQL($reference) . '\'');

        return self::setQuery($sql);
    }

    public static function productExistsById($id_product)
    {
        $sql = self::prepareQuery();
        $sql->where('id_product=' . (int) $id_product);

        return self::setQuery($sql);
    }

    public static function productExistsByEan13($ean13)
    {
        $sql = self::prepareQuery();
        $sql->where('ean13=\'' . pSQL($ean13) . '\'');

        return self::setQuery($sql);
    }

    private static function prepareQuery()
    {
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product');

        return $sql;
    }

    private static function setQuery($sql)
    {
        $db = Db::getInstance();

        return (int) $db->getValue($sql);
    }
}
