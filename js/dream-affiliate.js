jQuery(document).ready(function ($) {
    $('#da-sidebar > ul > li').click(function (e) {
        $('#da-sidebar > ul > li').removeClass('active');
        $(this).addClass('active');
    });

    $('#da-sidebar > ul > li a[href="#"]').click(function (e) {
        e.preventDefault();
    });

    $('#da-sidebar > ul > li a').click(function (e) {
        //e.preventDefault();
        var body = $("html, body");
        body.stop().animate({scrollTop: 0}, '500');

        var page = $(this).data('id');

        $('.da-ajax-content').addClass('da-hidden');
        $('.da-preloader').removeClass('da-hidden');

        $.post(da_variables.ajax_url, {
            action: "da_ajax",
            page: page
        }).success(function (response) {
            $('.da-ajax-content').html(response);

            $('.da-ajax-content').removeClass('da-hidden');
            $('.da-preloader').addClass('da-hidden');

            afterAjaxActions();
        }).error(function () {
            $('.da-ajax-content').html('<h2>Error!</h2>');

            $('.da-ajax-content').removeClass('da-hidden');
            $('.da-preloader').addClass('da-hidden');
        });
    });

    $('#da-sidebar > ul > li:first-child a').click();

    function afterAjaxActions() {
        $('#da-content table .da-delete').click(function (e) {
            e.preventDefault();

            if (confirm("Delete row?")) {
                $(this).parents('tr').fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });

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

            myChart2.addSeries(new EJSC.AreaSeries(
                    new EJSC.ArrayDataHandler(
                            [
                                ["January", 10000],
                                ["February", 1500],
                                ["March", 856],
                                ["April", 6200],
                                ["May", 750],
                                ["June", 8500],
                                ["July", 2000],
                                ["August", 2235],
                                ["September", 3400],
                                ["October", 6528],
                                ["November", 4200],
                                ["December", 5500]
                            ]
                            ), {
                title: "Area",
                color: 'rgb(0,192,227)'
            }));
        }


        $('#da-edit-user').on("submit", function () {
            var data = $(this).serialize();

            var body = $("html, body");
            body.stop().animate({scrollTop: 0}, '500');
            $('.da-inner-preloader').removeClass('da-hidden');

            $.post(da_variables.ajax_url, data).success(function (response) {
                //alert(typeof response);

                alert('Personal information is updated.');

                $('.da-inner-preloader').addClass('da-hidden');
            }).error(function () {
                alert('error!');
                $('.da-inner-preloader').addClass('da-hidden');
            });
            return false;
        });
    }
});