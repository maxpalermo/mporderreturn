<?php

namespace MpSoft\MpMassImport\Helpers;

use MpSoft\MpMassImport\Excel\XlsxReader;

class ParseExcel
{
    public static function parse($content, $worksheet = '')
    {
        $xlsx = XlsxReader::parse($content, true);
        $rows = null;
        if ($worksheet && count($xlsx->sheetNames()) > 1) {
            foreach ($xlsx->sheetNames() as $key => $value) {
                if ($value == $worksheet) {
                    $rows = $xlsx->rows($key);
                }
            }
        } else {
            $rows = $xlsx->rows(0);
        }
        if (count($rows) > 1) {
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $output[] = array_combine($header, $row);
            }
        }

        return $output;
    }
}
