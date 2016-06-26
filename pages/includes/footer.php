<!-- FOOTER -->
<footer>

</footer>
<!-- ./FOOTER -->

<?php wp_footer(); ?>

<script>
    jQuery(document).ready(function ($) {
        console.log('Current Affiliate ID: ' + da_variables.affiliateId);
        
        console.log('Affiliate Cookie: ' + getCookie('da_affiliate'));
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