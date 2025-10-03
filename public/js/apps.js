"use strict";

// Theme switcher
$("#modeSwitcher").on("click", function (e) {
    e.preventDefault();
    modeSwitch();
});

// Sidebar collapse
$(".collapseSidebar").on("click", function (e) {
    if ($(".vertical").hasClass("narrow")) {
        $(".vertical").toggleClass("open");
    } else {
        $(".vertical").toggleClass("collapsed");
        if ($(".vertical").hasClass("hover")) {
            $(".vertical").removeClass("hover");
        }
    }
    e.preventDefault();
});

// Sidebar hover effects
$(".sidebar-left").hover(
    function () {
        if ($(".vertical").hasClass("collapsed")) {
            $(".vertical").addClass("hover");
        }
        if ($(".narrow").hasClass("open")) {
            $(".vertical").addClass("hover");
        }
    },
    function () {
        if ($(".vertical").hasClass("collapsed")) {
            $(".vertical").removeClass("hover");
        }
        if ($(".narrow").hasClass("open")) {
            $(".vertical").removeClass("hover");
        }
    }
);

// Toggle sidebar
$(".toggle-sidebar").on("click", function () {
    $(".navbar-slide").toggleClass("show");
});

// Dropdown submenu functionality
(function ($) {
    $(".dropdown-menu a.dropdown-toggle").on("click", function (e) {
        if (!$(this).next().hasClass("show")) {
            $(this).parents(".dropdown-menu").first().find(".show").removeClass("show");
        }
        var $subMenu = $(this).next(".dropdown-menu");
        $subMenu.toggleClass("show");

        $(this).parents("li.nav-item.dropdown.show").on("hidden.bs.dropdown", function (e) {
            $(".dropdown-submenu .show").removeClass("show");
        });

        return false;
    });
})(jQuery);

// Navbar dropdown cleanup
$(".navbar .dropdown").on("hidden.bs.dropdown", function () {
    $(this).find("li.dropdown").removeClass("show open");
    $(this).find("ul.dropdown-menu").removeClass("show open");
});

// File panel functionality
$(".file-panel .card").on("click", function () {
    if ($(this).hasClass("selected")) {
        $(this).removeClass("selected");
        $(this).find("bg-light").removeClass("shadow-lg");
        $(".file-container").removeClass("collapsed");
    } else {
        $(this).addClass("selected");
        $(this).addClass("shadow-lg");
        $(".file-panel .card").not(this).removeClass("selected");
        $(".file-container").addClass("collapsed");
    }
});

// Close info panel
$(".close-info").on("click", function () {
    if ($(".file-container").hasClass("collapsed")) {
        $(".file-container").removeClass("collapsed");
        $(".file-panel").find(".selected").removeClass("selected");
    }
});

// Sticky content
$(function () {
    $(".info-content").stickOnScroll({
        topOffset: 0,
        setWidthOnStick: true
    });
});

// Wizard functionality
var basic_wizard = $("#example-basic");
if (basic_wizard.length) {
    basic_wizard.steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        autoFocus: true
    });
}

var vertical_wizard = $("#example-vertical");
if (vertical_wizard.length) {
    vertical_wizard.steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        stepsOrientation: "vertical"
    });
}

// Form validation and steps
var form = $("#example-form");
if (form.length) {
    form.validate({
        errorPlacement: function (error, element) {
            element.before(error);
        },
        rules: {
            confirm: {
                equalTo: "#password"
            }
        }
    });

    form.children("div").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        onStepChanging: function (event, currentIndex, newIndex) {
            form.validate().settings.ignore = ":disabled,:hidden";
            return form.valid();
        },
        onFinishing: function (event, currentIndex) {
            form.validate().settings.ignore = ":disabled";
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            alert("Submitted!");
        }
    });
}

// Chart initialization - only run if Chart.js is available
if (typeof Chart !== 'undefined') {
    var ChartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            display: false
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                }
            }],
            yAxes: [{
                gridLines: {
                    display: false,
                    color: colors.borderColor,
                    zeroLineColor: colors.borderColor
                }
            }]
        }
    };

    var ChartData = {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"],
        datasets: [{
            label: "Visitors",
            barThickness: 10,
            backgroundColor: base.primaryColor,
            borderColor: base.primaryColor,
            pointRadius: false,
            pointColor: "#3b8bba",
            pointStrokeColor: "rgba(60,141,188,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(60,141,188,1)",
            data: [28, 48, 40, 19, 64, 27, 90, 85, 92],
            fill: "",
            lineTension: 0.1
        }, {
            label: "Orders",
            barThickness: 10,
            backgroundColor: "rgba(210, 214, 222, 1)",
            borderColor: "rgba(210, 214, 222, 1)",
            pointRadius: false,
            pointColor: "rgba(210, 214, 222, 1)",
            pointStrokeColor: "#c1c7d1",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [65, 59, 80, 42, 43, 55, 40, 36, 68],
            fill: "",
            borderWidth: 2,
            lineTension: 0.1
        }]
    };

    var lineChartData = {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"],
        datasets: [{
            label: "Visitors",
            barThickness: 10,
            borderColor: base.primaryColor,
            pointRadius: false,
            pointColor: "#3b8bba",
            pointStrokeColor: "rgba(60,141,188,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(60,141,188,1)",
            data: [28, 48, 30, 19, 64, 27, 90, 85, 92],
            fill: "",
            lineTension: 0.2
        }, {
            label: "Sales",
            barThickness: 10,
            borderColor: "rgba(40, 167, 69, 0.8)",
            pointRadius: false,
            pointColor: "#3b8bba",
            pointStrokeColor: "rgba(60,141,188,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(60,141,188,1)",
            data: [8, 18, 20, 29, 26, 7, 30, 25, 48],
            fill: "",
            borderWidth: 2,
            lineTension: 0.2
        }, {
            label: "Orders",
            backgroundColor: "rgba(210, 214, 222, 1)",
            borderColor: "rgba(210, 214, 222, 1)",
            pointRadius: false,
            pointColor: "rgba(210, 214, 222, 1)",
            pointStrokeColor: "#c1c7d1",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [65, 59, 80, 42, 43, 55, 40, 36, 68],
            fill: "",
            borderWidth: 2,
            lineTension: 0.2
        }]
    };

    var pieChartData = {
        labels: ["Clothing", "Shoes", "Electronics", "Books", "Cosmetics"],
        datasets: [{
            data: [18, 30, 42, 12, 7],
            backgroundColor: chartColors,
            borderColor: colors.borderColor
        }]
    };

    var areaChartData = {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Visitors",
            barThickness: 10,
            backgroundColor: base.primaryColor,
            borderColor: base.primaryColor,
            pointRadius: false,
            pointColor: "#3b8bba",
            pointStrokeColor: "rgba(60,141,188,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(60,141,188,1)",
            data: [19, 64, 37, 76, 68, 88, 54, 46, 58],
            lineTension: 0.1
        }, {
            label: "Orders",
            barThickness: 10,
            backgroundColor: "rgba(210, 214, 222, 1)",
            borderColor: "rgba(255, 255, 255, 1)",
            pointRadius: false,
            pointColor: "rgba(210, 214, 222, 1)",
            pointStrokeColor: "#c1c7d1",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [42, 43, 55, 40, 36, 68, 22, 66, 49],
            lineTension: 0.1
        }]
    };

    // Initialize charts
    var barChartjs = document.getElementById("barChartjs");
    if (barChartjs) {
        new Chart(barChartjs, {
            type: "bar",
            data: ChartData,
            options: ChartOptions
        });
    }

    var lineChartjs = document.getElementById("lineChartjs");
    if (lineChartjs) {
        new Chart(lineChartjs, {
            type: "line",
            data: lineChartData,
            options: ChartOptions
        });
    }

    var pieChartjs = document.getElementById("pieChartjs");
    if (pieChartjs) {
        new Chart(pieChartjs, {
            type: "pie",
            data: pieChartData,
            options: {
                maintainAspectRatio: false,
                responsive: true
            }
        });
    }

    var areaChartjs = document.getElementById("areaChartjs");
    if (areaChartjs) {
        new Chart(areaChartjs, {
            type: "line",
            data: areaChartData,
            options: ChartOptions
        });
    }
}

// Sparkline initialization - only run if sparkline elements exist
if ($(".sparkline").length) {
    $(".inlinebar").sparkline([3, 2, 7, 5, 4, 6, 8], {
        type: "bar",
        width: "100%",
        height: "32",
        barColor: base.primaryColor,
        barWidth: 4,
        barSpacing: 2
    });

    $(".inlineline").sparkline([2, 0, 5, 7, 4, 6, 8], {
        type: "line",
        width: "100%",
        height: "32",
        defaultPixelsPerValue: 5,
        lineColor: base.primaryColor,
        fillColor: "transparent",
        minSpotColor: false,
        spotColor: false,
        highlightSpotColor: "",
        maxSpotColor: false,
        lineWidth: 2
    });

    $(".inlinepie").sparkline([5, 7, 4, 6, 8], {
        type: "pie",
        height: "32",
        width: "32",
        sliceColors: chartColors
    });
}

// Gauge initialization - only run if gauge elements exist
var gauge1, svgg1 = document.getElementById("gauge1");
if (svgg1 && typeof Gauge !== 'undefined') {
    gauge1 = Gauge(svgg1, {
        max: 100,
        dialStartAngle: -90,
        dialEndAngle: -90.001,
        value: 100,
        showValue: false,
        label: function (value) {
            return Math.round(value * 100) / 100;
        },
        color: function (value) {
            if (value < 20) return base.primaryColor;
            if (value < 40) return base.successColor;
            if (value < 60) return base.warningColor;
            return base.dangerColor;
        }
    });

    (function updateGauge1() {
        gauge1.setValue(90);
        gauge1.setValueAnimated(30, 1);
        window.setTimeout(updateGauge1, 6000);
    })();
}

var gauge2, svgg2 = document.getElementById("gauge2");
if (svgg2 && typeof Gauge !== 'undefined') {
    gauge2 = Gauge(svgg2, {
        max: 100,
        value: 46,
        dialStartAngle: -0,
        dialEndAngle: -90.001
    });

    (function updateGauge2() {
        gauge2.setValue(40);
        gauge2.setValueAnimated(30, 1);
        window.setTimeout(updateGauge2, 6000);
    })();
}

var gauge3, svgg3 = document.getElementById("gauge3");
if (svgg3 && typeof Gauge !== 'undefined') {
    gauge3 = Gauge(svgg3, {
        max: 100,
        dialStartAngle: -90,
        dialEndAngle: -90.001,
        value: 80,
        showValue: false,
        label: function (value) {
            return Math.round(value * 100) / 100;
        }
    });
}

var gauge4, svgg4 = document.getElementById("gauge4");
if (svgg4 && typeof Gauge !== 'undefined') {
    gauge4 = Gauge(document.getElementById("gauge4"), {
        max: 500,
        dialStartAngle: 90,
        dialEndAngle: 0,
        value: 50
    });
}