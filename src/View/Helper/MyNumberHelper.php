<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\I18n\I18n;
use Cake\View\Helper\NumberHelper;

class MyNumberHelper extends NumberHelper
{

    public function formatAsDecimal(string|float|int $amount, $decimals = 2, $removeTrailingZeros = false, $minDecimals = null): string
    {
        $options = [
            'locale' => I18n::getLocale()
        ];
        if (!$removeTrailingZeros) {
            $options = array_merge($options, [
                'places' => $decimals,
                'precision' => $decimals
            ]);
        }
        if (!is_null($minDecimals)) {
            $options = array_merge($options, [
                'places' => $minDecimals,
            ]);
        }
        $result = self::format($amount, $options);
        return $result;
    }

}
?>