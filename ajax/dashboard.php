<?php
if (isset($_COOKIE['da_affiliate'])) {
    //echo '<h1>' . $_COOKIE['da_affiliate'] . '</h1>';
}

//var_dump( $da->getMonthStatistic() );
//
//$stat = $da->getMonthStatistic();
//
//foreach($stat as $key => $st) {
//    echo "amount: $st->amount <br />year: $st->year <br />month: $st->month <br /><br />";
//}

//$da->setPayment(56, 45);
?>

<h3 class="da-title">Dashboard</h3>

<div class="da-inner-content da-dashboard">

    <h6>Monthly data</h6>

    <ul class="da-bigicons-list">
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Average Income
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getAverageMonth(); ?>
                </div>

            </figure>
        </li>

        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Monthly Income
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getIncome(); ?>
                </div>
            </figure>
        </li>
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Number of Cilents
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getClientsCount(); ?>
                </div>
            </figure>
        </li>
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Number of Partners
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getPartnersCount(); ?>
                </div>
            </figure>
        </li>
    </ul>


    <h6>Total revenue</h6>

    <div id="chartTotalRevenue" style="width: 100%; height: 250px; overflow: hidden;"></div>

    <script>
        //alert('<?php echo "test 2"; ?>');
        jQuery(document).ready(function ($) {

            if ($('#chartTotalRevenue').length > 0) {
                var myChart2 = new EJSC.Chart('chartTotalRevenue', {
                    title: "",
                    show_legend: false,
                    axis_bottom: {
                        caption: "Month"
                    },
                    axis_left: {
                        caption: "",
                        formatter: new EJSC.NumberFormatter({currency_symbol: "$", forced_decimals: 2, variable_decimals: 2})
                    },
                    show_hints: true
                }
                );


                var data = <?php echo wp_json_encode($da->getMonthStatistic()); ?>;
                
                console.log(JSON.stringify(data));
                
                var chartData = new Array();
                for (var i in data) {
                    chartData.push(new Array(data[i]['month'], data[i]['amount']));
                }
                chartData.unshift(new Array('|', 0));

                myChart2.addSeries(new EJSC.AreaSeries(
                        new EJSC.ArrayDataHandler(chartData), {
                    title: "Area",
                    color: 'rgb(0,192,227)'
                }));
            }
        });

    </script>

</div>