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
                  var counter
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
            if (form.find('select[name="month\[month\]"]').length > 0) {
                form.find('select[name="month\[month\]"]').val('');
            }
            if (form.find('select[name="year\[year\]"]').length > 0) {
                form.find('select[name="year\[year\]"]').val('');
            }
            
        });
    },
   
    loadWorkshopDetailChartRepaired : function(data, displayLabel) {
        
        displayLabel = displayLabel || false;
        
        data = $.parseJSON(data);
       
        var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: data.data,
                    backgroundColor: data.backgroundColor,
                    borderColor: data.borderColor,
                    borderWidth: data.borderWidth
                }],
                labels: data.labels
            },
            options: {
                responsive: true,
                tooltips: {
                    callbacks: {
                        label: function(item, data) {
                            var sumItems = data.datasets[0].data[0] + data.datasets[0].data[1];
                            var datasetLabel = '';
                            if (data.labels[item.index]) {
                                datasetLabel = data.labels[item.index].replace(/\((.*)\)/, '').trim();
                            }
                            var value = data.datasets[item.datasetIndex].data[item.index].toLocaleString();
                            var newLabel = value + 'x ' + datasetLabel;
                            newLabel += ' (' + Math.round(value / sumItems * 100) + ' %)';
                            return newLabel;
                        }
                    }
                },                
                legend: {
                    display: displayLabel
                },
                pieceLabel: {
                    render: 'value',
                    fontSize: 20
                }
            }
        };
       
        new Chart($('#chartRepaired'), config);
       
    },
   
    loadWorkshopDetailChartCategories : function(data) {
       
        data = $.parseJSON(data);
       
        var config = {
            type: 'bar',
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options: {
                responsive: true,
                tooltips: {
                    callbacks: {
                        label: function(item, data) {
                            var datasetLabel = '';
                            if (data.datasets[item.datasetIndex].label) {
                                datasetLabel = data.datasets[item.datasetIndex].label[0].replace(/\((.*)\)/, '').trim();
                            }
                            var value = data.datasets[item.datasetIndex].data[item.index].toLocaleString();
                            var newLabel = value + 'x ' + datasetLabel;
                            return newLabel;
                        }
                    }
                },                
                scales: {
                    xAxes: [{
                        stacked: true,
                        maxBarThickness: 100,
                        ticks: {
                            autoSkip: false,
                            maxRotation: 90,
                            minRotation: 90
                        },
                        gridLines: {
                            display: false
                        }                       
                    }],
                    yAxes: [{
                        stacked: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return value.toLocaleString();
                            }
                        }
                    }]
                }                   
            }
        };
       
        new Chart($('#chartCategories'), config);
       
    }
         
};