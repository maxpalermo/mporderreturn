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

require_once _PS_MODULE_DIR_ . 'mpmassimport/src/Excel/XlsxReader.php';
require_once _PS_MODULE_DIR_ . 'mpmassimport/src/Excel/XlsxWriter.php';

use \MpSoft\MpMassImport\Excel\XlsXReader;
use \MpSoft\MpMassImport\Excel\XlsXWriter;

trait Excel
{
    public function excelParse($content)
    {
        $reader = new XlsXReader();
        $sheet = $reader->parse($content, true);
        $rows = $sheet->rows();

        return $rows;
    }

    public function excelParseAssoc($content)
    {
        $rows = $this->excelParse($content);
        if (is_array($rows) && count($rows) > 1) {
            $header = array_shift($rows);
            $out = [];
            foreach ($rows as $row) {
                $out[] = array_combine($header, $row);
            }

            return $out;
        }

        return [];
    }

    public function excelWrite(array $rows, string $sheet_name = 'sheet001')
    {
        $filename = $sheet_name . '_' . date('YmdHis') . '.xlsx';
        $writer = new XlsXWriter();
        $writer->addSheet($rows, $sheet_name);
        $writer->downloadAs($filename);
    }
}
