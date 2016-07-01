<!-- FOOTER -->
<footer>

</footer>
<!-- ./FOOTER -->

<?php wp_footer(); ?>

<script>
    jQuery(document).ready(function ($) {
        console.log('Current Affiliate ID: ' + da_variables.affiliateId);
        console.log('Affiliate Cookie: ' + getCookie('da_affiliate'));
        console.log('is logined in: ' + da_variables.is_loginedin);
    });

    function getCookie(name) {
        var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
</script>

</body>
</html>