<?php

namespace MpSoft\MpMassImport\Helpers;

class CheckExtension
{
    public static function check($filename, $value)
    {
        $file = pathinfo($filename);
        $extension = $file['extension'];

        return $extension == $value;
    }
}
