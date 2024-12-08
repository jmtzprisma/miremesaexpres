$(function() {
    'use strict';

    function loadRegisteredUsersByTime() {
        $.get("/home/registered/users/time", function(data) {
            var options = {
                chart: {
                    height: 300,
                    type: "line",
                    stacked: false,
                    toolbar: {
                        enabled: true
                    },
                    dropShadow: {
                        enabled: true,
                        opacity: 0.1,
                    },
                },
                colors: ["#6259ca", "#f99433", 'rgba(119, 119, 142, 0.05)'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: [3, 3, 0],
                    dashArray: [0, 4],
                    lineCap: "round"
                },
                grid: {
                    padding: {
                        left: 0,
                        right: 0
                    },
                    strokeDashArray: 3
                },
                markers: {
                    size: 0,
                    hover: {
                        size: 0
                    }
                },
                series: data.sections,
                xaxis: {
                    type: "month",
                    categories: data.categories,
                    axisBorder: {
                        show: false,
                        color: 'rgba(119, 119, 142, 0.08)',
                    },
                    labels: {
                        style: {
                            color: '#8492a6',
                            fontSize: '12px',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            color: '#8492a6',
                            fontSize: '12px',
                        },
                    },
                    axisBorder: {
                        show: false,
                        color: 'rgba(119, 119, 142, 0.08)',
                    },
                },
                fill: {
                    gradient: {
                        inverseColors: false,
                        shade: 'light',
                        type: "vertical",
                        opacityFrom: 0.85,
                        opacityTo: 0.55,
                        stops: [0, 100, 100, 100]
                    }
                },
                tooltip: {
                    show:false
                },
                legend: {
                    position: "top",
                    show:true
                }
            }

            var chart = new ApexCharts(document.querySelector("#registerUserByTime"), options);
            chart.render();
        });
    }

    function loadPetitionsOnTime() {
        $.get("/home/petitions/time", function(data) {
            var options = {
                chart: {
                    height: 300,
                    type: "line",
                    stacked: false,
                    toolbar: {
                        enabled: true
                    },
                    dropShadow: {
                        enabled: true,
                        opacity: 0.1,
                    },
                },
                colors: ["#6259ca", "#f99433", 'rgba(119, 119, 142, 0.05)'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: [3, 3, 0],
                    dashArray: [0, 4],
                    lineCap: "round"
                },
                grid: {
                    padding: {
                        left: 0,
                        right: 0
                    },
                    strokeDashArray: 3
                },
                markers: {
                    size: 0,
                    hover: {
                        size: 0
                    }
                },
                series: data.sections,
                xaxis: {
                    type: "month",
                    categories: data.categories,
                    axisBorder: {
                        show: false,
                        color: 'rgba(119, 119, 142, 0.08)',
                    },
                    labels: {
                        style: {
                            color: '#8492a6',
                            fontSize: '12px',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            color: '#8492a6',
                            fontSize: '12px',
                        },
                    },
                    axisBorder: {
                        show: false,
                        color: 'rgba(119, 119, 142, 0.08)',
                    },
                },
                fill: {
                    gradient: {
                        inverseColors: false,
                        shade: 'light',
                        type: "vertical",
                        opacityFrom: 0.85,
                        opacityTo: 0.55,
                        stops: [0, 100, 100, 100]
                    }
                },
                tooltip: {
                    show:false
                },
                legend: {
                    position: "top",
                    show:true
                }
            }

            var chart = new ApexCharts(document.querySelector("#requestOnTime"), options);
            chart.render();
        });
    }

    function loadPetitionsOnTimeByHour() {
        $.get("/home/petitions/hours", function(data) {
            var options = {
                chart: {
                    height: 300,
                    type: "line",
                    stacked: false,
                    toolbar: {
                        enabled: true
                    },
                    dropShadow: {
                        enabled: true,
                        opacity: 0.1,
                    },
                },
                colors: ["#6259ca", "#f99433", 'rgba(119, 119, 142, 0.05)'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: [3, 3, 0],
                    dashArray: [0, 4],
                    lineCap: "round"
                },
                grid: {
                    padding: {
                        left: 0,
                        right: 0
                    },
                    strokeDashArray: 3
                },
                markers: {
                    size: 0,
                    hover: {
                        size: 0
                    }
                },
                series: data.sections,
                xaxis: {
                    type: "month",
                    categories: data.categories,
                    axisBorder: {
                        show: false,
                        color: 'rgba(119, 119, 142, 0.08)',
                    },
                    labels: {
                        style: {
                            color: '#8492a6',
                            fontSize: '12px',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            color: '#8492a6',
                            fontSize: '12px',
                        },
                    },
                    axisBorder: {
                        show: false,
                        color: 'rgba(119, 119, 142, 0.08)',
                    },
                },
                fill: {
                    gradient: {
                        inverseColors: false,
                        shade: 'light',
                        type: "vertical",
                        opacityFrom: 0.85,
                        opacityTo: 0.55,
                        stops: [0, 100, 100, 100]
                    }
                },
                tooltip: {
                    show:false
                },
                legend: {
                    position: "top",
                    show:true
                }
            }

            var chart = new ApexCharts(document.querySelector("#requestOnTimeByHour"), options);
            chart.render();
        });
    }

    function loadInvoicesByArea() {
        $.get("/home/petitions/time", function(data) {
            var options = {
                series: data.sections,
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: data.categories,
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "$ " + val + " thousands"
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#invoiceByArea"), options);
            chart.render();
        });
    }

    function loadInvoicesOnTimeByHour() {
        $.get("/home/petitions/hours", function(data) {
            var options = {
                series: data.sections,
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: data.categories,
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "$ " + val + " thousands"
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#invoiceByAreaInDay"), options);
            chart.render();
        });
    }

    function initial() {
        loadInvoicesByArea();
        loadPetitionsOnTime();
        loadInvoicesOnTimeByHour();
        loadPetitionsOnTimeByHour();
        loadRegisteredUsersByTime();
    }

    $(document).ready(initial);
});
