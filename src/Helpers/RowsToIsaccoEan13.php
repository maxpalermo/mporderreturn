<?php

namespace MpSoft\MpMassImport\Helpers;

use Context;
use Db;
use DbQuery;
use Product;

class RowsToIsaccoEan13
{
    protected $rows = [];

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function parse()
    {
        if (!is_array($this->rows)) {
            return [];
        }

        $parsed_rows = [];
        foreach ($this->rows as $row) {
            $parsed = [];
            foreach ($row as $key => $value) {
                if (trim($value)) {
                    switch (strtolower($key)) {
                        case 'codice':
                        case 'articolo':
                            $out = [];
                            $code = preg_match('/(.*)\((.*) -.*\)/', $value, $out);
                            if ($code) {
                                if (count($out) >= 3) {
                                    $reference = trim($out[1]);
                                    $size = trim($out[2]);
                                    $ids = $this->findProduct($reference, $size);
                                    if ($ids) {
                                        $parsed['id_product_attribute'] = $ids['id_product_attribute'];
                                        $parsed['id_product'] = $ids['id_product'];
                                        $parsed['reference'] = $ids['reference'];
                                    } else {
                                        $parsed['id_product_attribute'] = 0;
                                        $parsed['id_product'] = 0;
                                        $parsed['reference'] = '';
                                    }
                                }
                            } else {
                                $parsed['id_product_attribute'] = 0;
                                $parsed['id_product'] = 0;
                                $parsed['reference'] = '';
                            }

                            break;
                        case 'ean 13':
                        case 'ean13':
                            $parsed['ean13'] = trim($value);

                            break;
                        default:
                            $parsed[$key] = $value;
                    }
                }
            }

            $parsed_rows[] = $parsed;
        }

        return $parsed_rows;
    }

    protected function findProduct($reference, $size)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product')
            ->where('reference like \'%' . pSQL($reference) . '\'');
        $id_product = (int) $db->getValue($sql);
        if ($id_product) {
            $id_product_attribute = 0;
            $product = new Product($id_product);
            if (strpos($product->name[$id_lang], 'grembiul') !== false) {
                $size .= ' Anni';
            }
            $combinations = $product->getAttributeCombinations($id_lang);
            foreach ($combinations as $comb) {
                if ($comb['attribute_name'] == $size) {
                    $id_product_attribute = $comb['id_product_attribute'];

                    return [
                        'id_product_attribute' => $id_product_attribute,
                        'id_product' => (int) $comb['id_product'],
                        'reference' => $comb['reference'],
                    ];
                }
            }

            return false;
        }

        return false;
    }
}
