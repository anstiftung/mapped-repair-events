MappedRepairEvents.WidgetStatistics = {

    initFilterForm : function() {
        this.initResetButton();
        this.initFormFieldChangeEvent();
    },

    initFormFieldChangeEvent : function() {
        $('form input, form select').on('change', function() {
            $(this).closest('form').submit();
        });
    },

    initMaterialFootprintAnimation : function() {

        var wrapperSelector = '.material-footprint-wrapper';

        var contentWidth = $('#content').width();
        var erWrapper = $(wrapperSelector);

        if (contentWidth <= 650) {
            erWrapper.addClass('small');
        }

        var totalCount = MappedRepairEvents.Helper.getStringAsFloat($(wrapperSelector + ' .amount-persons-per-year').text());
        $(wrapperSelector + ' b').html(totalCount).text();

        var digitsUpToCount = 10; // also adapt in MyHtmlHelper::getMaterialFootprintAsString

        $('.material-footprint-wrapper b').each(function () {
            var $this = $(this);
            var newCounter = MappedRepairEvents.Helper.getStringAsFloat($this.text());
            var step = 1;
            if (totalCount < digitsUpToCount) {
                step = 0.1;
            }
            newCounter -= step;
            $({ counter: 0 }).animate({
                counter: newCounter
            },
            {
                duration: 2000,
                easing: 'swing',
                step: function () {
                    var digits = 0;
                    var step = 1;
                    var counter;
                    if (totalCount < digitsUpToCount) {
                        digits = 1;
                        step = 0.1;
                    }
                    counter = this.counter += step;
                    counter = MappedRepairEvents.Helper.formatFloatAsString(counter, digits);
                    $this.text(counter);
                },
                // sometimes there are rounding differences after increasing the counter
                // assure that after animation there's always the correct number
                complete : function() {
                    $this.text(MappedRepairEvents.Helper.formatFloatAsString(totalCount));
                }
            });
        });
    },

    initCarbonFootprintAnimation : function() {

        var contentWidth = $('#content').width();
        var erWrapper = $('.carbon-footprint-wrapper');

        if (contentWidth <= 650) {
            erWrapper.addClass('small');
        }

        if (!erWrapper.hasClass('small')) {
            erWrapper.find('.info-text').appendTo(erWrapper.find('.line'));
        }

        var lineWidthInPx = contentWidth - erWrapper.find('img').width() * 2 - 50;

        // has scrollbar?
        if ($('body').get(0).scrollWidth == $('html').width()) {
            lineWidthInPx -= 20;
        }

        erWrapper.find('.line').animate({
            width: lineWidthInPx + 'px'
        }, 2000, function() {
            $(this).find('span').animate({
                opacity: 'toggle'
            });
        });
    },

    initResetButton : function() {
        $('#reset').on('click', function(event) {

            var form = $(this).closest('form');

            if (form.find('#datefrom').length > 0) {
                form.find('#datefrom').val('01.01.2010');
            }
            if (form.find('#dateto').length > 0) {
                form.find('#dateto').val(form.find('#today').val());
            }

            if (form.find('input[name="defaultDataSource"]').length > 0) {
                form.find('#datasource').val(form.find('input[name="defaultDataSource"]'));
            }
            if (form.find('select[name="month"]').length > 0) {
                form.find('select[name="month"]').val('');
            }
            if (form.find('select[name="year"]').length > 0) {
                form.find('select[name="year"]').val('');
            }

        });
    },

    loadWorkshopDetailChartRepaired : function(data, displayLabel) {

        displayLabel = displayLabel || false;

        data = $.parseJSON(data);

        var config = {
            type: 'doughnut',
            plugins: [ChartDataLabels],
            data: {
                datasets: [{
                    data: data.data,
                    backgroundColor: data.backgroundColor,
                    borderColor: data.borderColor,
                    borderWidth: data.borderWidth,
                    datalabels: {
                        labels: {
                            outer: {
                                color: '#333333',
                                font: {
                                    size: 15
                                },
                                formatter: function(value, ctx) {
                                    return value.toLocaleString();
                                },
                                offset: 15,
                            }
                        }
                    }
                }],
                labels: data.labels
            },
            options: {
                aspectRatio: 1.3,
                layout: {
                    padding: 10
                },
                plugins: {
                    legend: {
                        display: displayLabel
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var sumItems = ctx.dataset.data[0] + ctx.dataset.data[1] + ctx.dataset.data[2];
                                var value = ctx.parsed.toLocaleString();
                                var newLabel = value + 'x ' + ctx.label.replace(/\((.*)\)/, '').trim();
                                newLabel += ' (' + Math.round(ctx.parsed / sumItems * 100) + ' %)';
                                return newLabel;
                            }
                        }
                    },
                }
            }
        };

        new Chart($('#chartRepaired'), config);

    },

    loadWorkshopDetailChartCategories : function(data) {

        data = $.parseJSON(data);

        for(var i in data.datasets)
        {
            data.datasets[i].maxBarThickness = 100;
        }

        var config = {
            type: 'bar',
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.formattedValue > 0) {
                                    var newLabel = ctx.formattedValue + 'x ' + ctx.dataset.label.toString().replace(/\((.*)\)/, '').trim();
                                    return newLabel;
                                }
                            }
                        }
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            autoSkip: false,
                            maxRotation: 90,
                            minRotation: 90
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        };

        new Chart($('#chartCategories'), config);

    }

};