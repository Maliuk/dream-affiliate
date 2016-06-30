jQuery(document).ready(function ($) {

    var isAjaxing = false;

    $('#da-sidebar > ul > li a[href="#"]').click(function (e) {
        e.preventDefault();
    });


    $('#da-sidebar > ul > li a').click(function (e) {
        if (isAjaxing) {
            e.preventDefault();
            return;
        }
        $('#da-sidebar > ul > li').removeClass('active');
        $(this).parents('li').addClass('active');
        isAjaxing = true;

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
            isAjaxing = false;
            afterAjaxActions();
        }).error(function () {
            $('.da-ajax-content').html('<h2>Error!</h2>');

            $('.da-ajax-content').removeClass('da-hidden');
            $('.da-preloader').addClass('da-hidden');

            isAjaxing = false;
        });
    });

    var hash = window.location.hash;
    var $sLink = $('#da-sidebar > ul > li a[href="' + hash + '"]');
    if ($sLink.length < 1) {
        $sLink = $('#da-sidebar > ul > li:first-child a');
    }
    $sLink.click();


    function afterAjaxActions() {

        $('#da-content table .da-delete').click(function (e) {
            e.preventDefault();

            if (confirm("Delete row?")) {
                $(this).parents('tr').fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });


        $('#da-edit-user').on("submit", function () {
            var data = $(this).serialize();

            var body = $("html, body");
            body.stop().animate({scrollTop: 0}, '500');
            $('.da-inner-preloader').removeClass('da-hidden');

            $.post(da_variables.ajax_url, data).success(function (response) {
                //alert(JSON.stringify(response));
                if (response.success == true) {
                    if (response.data.errors && response.data.errors.existing_user_email) {
                        showMessage('.messages', response.data.errors.existing_user_email, true);
                    }
                    else if (response.data.errors && response.data.errors.confirm_password) {
                        showMessage('.messages', response.data.errors.confirm_password, true);
                    }
                    else if (response.data.errors) {
                        showMessage('.messages', 'Error', true);
                    }
                    else {
                        showMessage('.messages', 'Personal Information is updated.');
                    }
                }
                else {
                    alert('error!');
                }

                $('.da-inner-preloader').addClass('da-hidden');
            }).error(function () {
                alert('error!');
                $('.da-inner-preloader').addClass('da-hidden');
            });
            return false;
        });


        $("#da-marketing-table textarea").focus(function () {
            var $this = $(this);
            $this.select();

            // Work around Chrome's little problem
            $this.mouseup(function () {
                // Prevent further mouseup intervention
                $this.unbind("mouseup");
                return false;
            });
        });


        if ($('.da-partners table').length > 0) {
            var table = $('.da-partners table').DataTable({
                "order": [[1, "desc"]]
            });

            $('#search_partner_name').on("keyup", function () {
                table
                        .columns(0)
                        .search(this.value)
                        .draw();
            });
        }
        
        if ($('.da-reports table').length > 0) {
            var table = $('.da-reports table').DataTable({
                "order": [[2, "desc"]]
            });

            $('#search_client_name').on("keyup", function () {
                table
                        .columns(0)
                        .search(this.value)
                        .draw();
            });
        }
        
        if ($('.da-clients table').length > 0) {
            var table = $('.da-clients table').DataTable({
                "order": [[2, "desc"]]
            });

            $('#search_client_name').on("keyup", function () {
                table
                        .columns(0)
                        .search(this.value)
                        .draw();
            });
        }
    }


    function showMessage(obj, message, error) {
        var $target = $(obj);
        $target.append('<span class="da-message ' + (error ? 'da-error' : 'da-success') + '">' + message + '</span>');

        var $message = $('.da-message:last-child', $target);
        $message.slideDown(300);

        setTimeout(function () {
            $message.slideUp(300, function () {
                $message.remove();
            });
        }, 6000);
    }
});




/* LANDING PAGE */
jQuery(document).ready(function ($) {
    $(window).load(function () {
        $('.da-iMac, .da-iPad', '#da-landing-afterheader').addClass('da-shown');
        $('.da-animated', '#da-landing-afterheader').addClass('da-shown');
    });
});