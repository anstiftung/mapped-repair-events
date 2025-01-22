<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\HtmlHelper;

class WidgetHelper extends HtmlHelper {


    function getDefaultChartBackgroundColorOk(): string
    {
        return 'rgba(200,210,24,.6)';
    }

    function getDefaultChartBackgroundColorNotOk(): string
    {
        return 'rgba(181,124,219,.6)';
    }

    function getDefaultChartBackgroundColorRepairable(): string
    {
        return 'rgba(77,142,162,1)';
    }

    function getDefaultChartBorderColorOk(): string
    {
        return 'rgba(200,210,24,1)';
    }

    function getDefaultChartBorderColorNotOk(): string
    {
        return 'rgba(181,124,219,1)';
    }

    function getDefaultChartBorderColorRepairable(): string
    {
        return 'rgba(77,142,162,1)';
    }

}