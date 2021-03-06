<?php require_once 'includes/header.php'; ?>

<!-- CONTENT -->

<?php if (!is_user_logged_in()) { ?>
    <div class="da-container da-login-form">
        <?php
        $args = array(
            'redirect' => '/'
        );
        wp_login_form();
        ?>
    </div>
<?php
} else if (current_user_can('partner') || current_user_can('administrator')) {
    require_once 'includes/sidebar.php'; ?>

<div id="da-content" class="da-dashboard-page">
    <div class="da-preloader">
        <img src="/wp-content/plugins/dream-affiliate/images/preloader.svg" width="126" height="126" alt="preloader" />
    </div>
    <div class="da-ajax-content">
        
    </div>
</div>

<?php } ?>

<!-- CONTENT -->

<?php require_once 'includes/footer.php'; ?>