<?php

namespace MpSoft\MpMassImport\Helpers;

use Combination;
use Product;

class Combinations
{
    public function cleanAttributes($attributes)
    {
        /**
          [attributes] => Array
                (
                    [colore:14] => Array
                        (
                            [0] => Arancione:21
                        )

                    [rifiniture:15] => Array
                        (
                            [0] => Monocolore:995
                        )

                    [taglia:13] => Array
                        (
                            [0] => XS:7
                            [1] => S:4
                            [2] => M:3
                            [3] => L:1
                            [4] => XL:5
                            [5] => XXL:6
                            [6] => 3XL:35
                            [7] => 4XL:36
                        )

                )
         */
        $output = [];
        $i = 0;
        foreach ($attributes as $attribute) {
            foreach ($attribute as $value) {
                $split = explode(':', $value);
                $id_attribute = (int) trim($split[1]);
                if ($id_attribute) {
                    $output[$i][] = $id_attribute;
                }
            }
            $i++;
        }

        return $output;
    }

    public function createCombinationList($list)
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

    public function addCombinations($id_product, $combinations, $ean13 = [])
    {
        $product = new Product($id_product);
        $product->deleteProductAttributes();
        foreach ($combinations as $key_combination => $combination) {
            $pa = new Combination();
            $fields = [
                'id_product' => $id_product,
                'price' => 0,
                'weight' => 0,
                'ecotax' => 0,
                'quantity' => 0,
                'reference' => $product->reference,
                'supplier_reference' => $product->supplier_reference,
                'default_on' => 0,
                'available_date' => date('Y-m-d'),
            ];
            foreach ($fields as $key => $value) {
                $pa->$key = $value;
            }
            if ($ean13 && isset($ean13[$key_combination])) {
                $pa->ean13 = $ean13[$key_combination];
            } else {
                $pa->ean13 = '';
            }
            $add = $pa->add();
            if ($add) {
                $pa->setAttributes($combination);
            }
        }
    }
}
