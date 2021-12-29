"use strict";

/*
chartType это тип графика или диаграммы
- Pie_type_1 обычная круговая диаграмма,
- SimpleLine простой линейный график,
- StackedBar горизонтальная столбовая диаграмма
- SemiCircle диаграмма в виде полукруга

*/
function getCharts(chartType, data, objInfo) {
  switch (chartType) {
    case 'Pie_type_1':
      getChartTypePieType1(data, objInfo);
      break;

    case 'SimpleLine':
      getChartTypeSimpleLine(data, objInfo);
      break;

    case 'StackedBar':
      getChartTypeStackedBar(data, objInfo);
      break;

    case 'SemiCircle':
      getChartTypeSemiCircle(data, objInfo);
      break;

    default:
      break;
  }
} //простая круговая диаграмма


function getChartTypePieType1(data, objInfo) {
  $(function () {
    $('#container').highcharts({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: 1,
        plotShadow: false
      },
      title: {
        text: objInfo.title,
        align: 'center',
        style: {
          "color": "#363636",
          "fontSize": "12px"
        }
      },
      tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
            style: {
              color: Highcharts.theme && Highcharts.theme.contrastTextColor || 'black'
            }
          }
        }
      },
      series: [{
        type: 'pie',
        name: 'ip_src',
        data: data
      }]
    });
  });
} //полукруглая диаграмма


function getChartTypeSemiCircle(data, objInfo) {
  $(function () {
    $('#container').highcharts({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: 0,
        plotShadow: false
      },
      title: {
        text: objInfo.title,
        align: 'center',
        verticalAlign: 'middle',
        y: 50
      },
      tooltip: {//pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
      },
      plotOptions: {
        pie: {
          dataLabels: {
            enabled: true,
            distance: -50,
            style: {
              fontWeight: 'bold',
              color: 'white',
              textShadow: '0px 1px 2px black'
            }
          },
          startAngle: -90,
          endAngle: 90,
          center: ['50%', '75%']
        }
      },
      series: [{
        type: 'pie',
        name: objInfo.titleLegend,
        innerSize: '50%',
        data: data
      }]
    });
  });
} //простой линейный график


function getChartTypeSimpleLine(data, objInfo) {
  $(function () {
    $('#container').highcharts({
      title: {
        text: objInfo.title,
        align: 'center',
        style: {
          "color": "#363636",
          "fontSize": "12px"
        },
        x: -20 //center

      },
      xAxis: {
        categories: objInfo.dates,
        labels: {
          rotation: 40,
          style: {
            "color": "#363636",
            "fontSize": "10px"
          }
        }
      },
      yAxis: {
        title: {
          text: objInfo.titleY
        },
        plotLines: [{
          value: 0,
          width: 1,
          color: '#808080'
        }]
      },
      tooltip: {
        valueSuffix: objInfo.tooltip
      },
      legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle',
        borderWidth: 0
      },
      series: data
    });
  });
} //горизонтальный линейный график


function getChartTypeStackedBar(data, objInfo) {
  $(function () {
    $('#container').highcharts({
      chart: {
        height: objInfo.grafHeight,
        type: 'bar'
      },
      colors: ['#a52a2a', '#2f7ed8'],
      title: {
        text: objInfo.title,
        align: 'center',
        style: {
          "color": "#363636",
          "fontSize": "13px"
        }
      },
      xAxis: {
        categories: objInfo.categories,
        labels: {
          style: {
            "color": "#363636",
            "fontSize": "8px"
          }
        }
      },
      yAxis: {
        min: 0,
        title: {
          text: objInfo.titleY
        }
      },
      legend: {
        reversed: true
      },
      plotOptions: {
        series: {
          stacking: 'normal'
        }
      },
      series: data
    });
  });
}