/*
* Base version of the device temp chart seen throughout the app - uses dummy values
* Uses ApexCharts library
* The application uses a minified version of the code below, with Twig loops and variables for the time series and category
* Author: D Hollis <p2533140@my365.dmu.ac.uk> (Team 21-3110-AS)
 */
let options = {
    chart: {
        height: 380,
        type: "line",
        foreColor: '#6D6D6D'
    },
    series: [
        {
            name: "Temperature (°C)",
            data: [15, 20, 25, 29, 33, 36, 37, 32, 27, 23]
        }
    ],
    fill: {
        type: "gradient",
        gradient: {
            type: 'vertical',
            shadeIntensity: 1,
            opacityFrom: 1,
            opacityTo: 1,
            colorStops: [
                {
                    offset: 10,
                    color: "#fc440b",
                    opacity: 1
                },
                {
                    offset: 55,
                    color: "#ffce63",
                    opacity: 1
                },
                {
                    offset: 90,
                    color: "#0a95f9",
                    opacity: 1
                }
            ]
        }
    },
    stroke: {
        curve: 'smooth'
    },
    yaxis: {
        min: 0,
        max: 45
    },
    xaxis: {
        type: 'category',
        tickAmount: 8,
        categories: [
            ['10/01/21 10:00AM'],
            ['10/01/21 11:00AM'],
            ['10/01/21 12:00PM'],
            ['10/01/21 01:00PM'],
            ['10/01/21 02:00PM'],
            ['10/01/21 13:00PM'],
            ['10/01/21 14:00PM'],
            ['10/01/21 15:00PM'],
            ['10/01/21 16:00PM'],
            ['10/01/21 17:00PM']
        ],
        labels: {
            show: true
        }
    }
};
let chart = new ApexCharts(document.querySelector("#device-temp"), options);
chart.render();