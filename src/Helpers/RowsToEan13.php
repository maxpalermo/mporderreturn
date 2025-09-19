<?php

namespace MpSoft\MpMassImport\Helpers;

class RowsToEan13
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
                    switch ($key) {
                        case 'id_product':
                        case 'id_product_attribute':
                        case 'quantity':
                            $parsed[$key] = (int) $value;

                            break;
                        case 'reference':
                        case 'supplier_reference':
                        case 'ean13':
                        case 'location':
                        case 'available_date':
                            $parsed[$key] = pSQL($value);

                            break;
                        case 'price':
                        case 'wholesale_price':
                            $parsed[$key] = number_format($value, 6);

                            break;
                        case 'img_root':
                        case 'img_folder':
                        case 'images':
                            $parsed[$key] = explode(';', $value);

                            break;
                        default:
                            $parsed[$key] = $value;
                    }
                }
            }

            if (isset($parsed['img_root']) && isset($parsed['img_folder']) && isset($parsed['images'])) {
                $a = count($parsed['img_root']);
                $b = count($parsed['img_folder']);
                $c = count($parsed['images']);
                if (abs($a - $b - $c) == $a) {
                    foreach ($parsed['img_root'] as $img_key => $img_value) {
                        $parsed['images'][$img_key] =
                            $parsed['img_root'][$img_key] .
                            $parsed['img_folder'][$img_key] .
                            rawurlencode($parsed['images'][$img_key]);
                    }
                } else {
                    foreach ($parsed['images'] as $img_key => $img_value) {
                        $parsed['images'][$img_key] =
                            $parsed['img_root'][0] .
                            $parsed['img_folder'][0] .
                            rawurlencode($parsed['images'][$img_key]);
                    }
                }
                unset($parsed['img_root'], $parsed['img_folder']);
            } else {
                $keys = ['img_root', 'img_folder', 'images'];
                foreach ($keys as $key) {
                    if (isset($parsed[$key])) {
                        unset($parsed[$key]);
                    }
                }
            }
            $parsed_rows[] = $parsed;
        }

        return $parsed_rows;
    }
}
