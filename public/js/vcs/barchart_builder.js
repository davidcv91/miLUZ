function buildBarChart(chart, chart_settings) {
    return new Chart(chart, {
        type: 'bar',
        data: {
            labels: chart_settings['labels'],
            datasets: [{
                backgroundColor: chart_settings['bg_color'],
                borderColor: chart_settings['border_color'],
                data: chart_settings['data'],
            }]
        },
        options: {
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }],
                xAxes: [{
                    ticks: {
                        maxRotation: 90,
                        autoSkip: false
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    title: function(tooltipItem) {
                        if (typeof chart_settings['tooltip_titles'] !== 'undefined') {
                            return chart_settings['tooltip_titles'][tooltipItem[0].index]
                        }

                        return tooltipItem[0].xLabel;
                    },
                    label: function(tooltipItem) {
                        return chart_settings['tooltip_label'].replace(
                            '%s',
                            tooltipItem.yLabel.toString().replace(".", chart_settings['decimal_separator'])
                        );
                    }
                }
            }
        }
    });
}