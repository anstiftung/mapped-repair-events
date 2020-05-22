<?php

namespace App\View\Helper;

use Cake\View\Helper\HtmlHelper;

class WidgetHelper extends HtmlHelper {


    function getDefaultChartBackgroundColorOk()
    {
        return 'rgba(200,210,24,.6)';
    }

    function getDefaultChartBackgroundColorNotOk()
    {
        return 'rgba(181,124,219,.6)';
    }

    function getDefaultChartBorderColorOk()
    {
        return 'rgba(200,210,24,1)';
    }

    function getDefaultChartBorderColorNotOk()
    {
        return 'rgba(181,124,219,1)';
    }

}