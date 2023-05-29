<?php

namespace IAWP_SCOPED\IAWP\Utils;

class Array_To_CSV
{
    public static function array2csv($data, $delimiter = ',', $enclosure = '"', $escape_char = '\\')
    {
        $f = \fopen('php://memory', 'r+');
        foreach ($data as $item) {
            \fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
        }
        \rewind($f);
        return \stream_get_contents($f);
    }
}
