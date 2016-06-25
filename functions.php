<?php

add_action("template_redirect", 'my_theme_redirect');

function my_theme_redirect() {
    global $wp;
    $plugindir = dirname(__FILE__);

    if ($wp->query_vars["pagename"] == 'affiliate/register') {
        $templatefilename = 'page-register.php';

        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/pages/' . $templatefilename;
        }

        do_theme_redirect($return_template);
    } else if ($wp->query_vars["pagename"] == 'affiliate/dashboard') {
        $templatefilename = 'page-dashboard.php';

        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/pages/' . $templatefilename;
        }

        do_theme_redirect($return_template);
    } else if ($wp->query_vars["pagename"] == 'affiliate') {
        $templatefilename = 'page-affiliate.php';

        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/pages/' . $templatefilename;
        }

        do_theme_redirect($return_template);
    }
}

function do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

/* AJAX FUNCTIONS */

function da_ajax() {

    if (isset($_POST['page'])) {
        $filename = $_POST['page'] . '.php';
        //$file = file_get_contents(PLUGIN_DIR . '/ajax/' . $filename, FILE_USE_INCLUDE_PATH);
        //echo $file;
        global $da;
        require 'ajax/' . $filename;
    }
    die();
    //wp_send_json_success($dates);
}

add_action('wp_ajax_nopriv_da_ajax', 'da_ajax');
add_action('wp_ajax_da_ajax', 'da_ajax');

function update_user() {
    global $da;
    $userdata = array();
    $userdata['first_name'] = $_POST['first_name'];
    $userdata['last_name'] = $_POST['last_name'];
    $userdata['user_url'] = $_POST['website'];
    $userdata['user_email'] = $_POST['account_email'];

    $usermeta = array();
    $usermeta['partner_company_name'] = $_POST['partner_company_name'];
    $usermeta['partner_phone_number'] = $_POST['partner_phone_number'];
    $usermeta['partner_address'] = $_POST['partner_address'];
    $usermeta['partner_city'] = $_POST['partner_city'];
    $usermeta['partner_zip'] = $_POST['partner_zip'];
    $usermeta['partner_country'] = $_POST['partner_country'];
    $usermeta['partner_state'] = $_POST['partner_state'];
    $usermeta['partner_paypal'] = $_POST['partner_paypal'];

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (isset($_POST['password']) && isset($_POST['confirm_password']) && $password === $confirm_password) {
        $userdata['user_pass'] = $password;
    }

    if ($password === $confirm_password) {
        wp_send_json_success($da->updateCurrentUser($userdata, $usermeta));
    }
    else {
        $data = array(
            'errors' => array(
                'confirm_password' => 'Password is wrong!'
            )
        );
        
        wp_send_json_success($data);
    }
    die();
}

add_action('wp_ajax_nopriv_update_user', 'update_user');
add_action('wp_ajax_update_user', 'update_user');
