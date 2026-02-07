<?php

namespace App\Helpers;

class MoneyConverter
{
    public static function numberToWords($num = false)
    {
        $amount_after_decimal = round($num - ($decimal_num = floor($num)), 2) * 100;
        $num = str_replace(array(',', ''), '', trim($num));

        if (!$num) {
            return false;
        }

        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen');
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion', 'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion', 'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion');

        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);

        for ($i = 0; $i < count($num_levels); $i++) {

            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ($hundreds == 1 ? '' : '') . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';

            if ($tens < 20) {

                $tens = ($tens ? ' and ' . $list1[$tens] . ' ' : '');
            } elseif ($tens >= 20) {

                $tens = (int)($tens / 10);
                $tens = ' and ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }

            $words[] = $hundreds . $tens . $singles . (($levels && (int) ($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
        } //end for loop

        $commas = count($words);

        if ($commas > 1) {

            $commas = $commas - 1;
        }

        $words = implode(' ', $words);
        $words = preg_replace('/^\s\b(and)/', '', $words);
        $words = trim($words);
        $words = ucwords($words);

        if ($amount_after_decimal >= 20) {

            $num_decimal_part = ($amount_after_decimal > 0) ? "and " . ($list2[$amount_after_decimal / 10] . " " . $list1[$amount_after_decimal % 10]) . ' Pesewas' : '';
        } else {

            $num_decimal_part = ($amount_after_decimal > 0) ? "and " . ($list1[$amount_after_decimal / 100] . " " . $list1[$amount_after_decimal % 100]) . ' Pesewas' : '';
        }

        $num_decimal_part  = ucwords($num_decimal_part);
        $words = $words . " Cedis " . $num_decimal_part;

        return $words;
    }
}
