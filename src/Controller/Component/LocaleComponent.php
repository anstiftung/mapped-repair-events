<?php
/**
 *     Locale Class
 */
namespace Controller\Component;

use Cake\Controller\Component;

class LocaleComponent extends Component
{

    public static $locale = 'de_DE';

    public static $moneyFormat = '';

    public static $numberFormat = '%!.0n';

    function startup(&$controller)
    {}

    /**
     * Format a number to a string
     * 
     * @param integer|float $number
     * @return string
     */
    public static function number($number, $format = null)
    {
        return money_format(($format === null) ? self::$numberFormat : $format, $number);
    }
}

?>