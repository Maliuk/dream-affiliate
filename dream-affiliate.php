<?php

/*
  Plugin Name: dream-affiliate
  Plugin URI:
  Description:
  Version: 1.0.0
  Author:
  Author URI:
  License: GPLv2
 */

//error_reporting(E_ALL | E_STRICT);

define('PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if (!class_exists('DreamAffiliate')) {

    class DreamAffiliate {
        
        public static $affiliate_id;

        public function __construct() {

            add_action('wp_enqueue_scripts', array('DreamAffiliate', 'initStylesScripts'), 9999);

            // Функция которая исполняется при активации плагина
            register_activation_hook(__FILE__, array('DreamAffiliate', 'activate'));
            // Функция которая исполняется при деактивации плагина
            register_deactivation_hook(__FILE__, array('DreamAffiliate', 'deactivate'));
            //  Функция которая исполняется удалении плагина
            register_uninstall_hook(__FILE__, array('DreamAffiliate', 'uninstall'));

            if (isset($_GET['affiliate'])) {
                setcookie('da_affiliate', $_GET['affiliate'], strtotime('+1 day'));
            }

            //add_action('wp_loaded', [$this, 'wp_loaded_action'], 99);
            add_action('user_register', [$this, 'user_register_action'], 99);
        }

        function wp_loaded_action() {
            $user_ID = get_current_user_id();

            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate";

            $affiliate_id = $wpdb->get_var("SELECT id FROM $table_name WHERE partner_id=$user_ID");

            DreamAffiliate::$affiliate_id = $affiliate_id;
        }

        function user_register_action($user_id) {
            if (isset($_COOKIE['da_affiliate'])) {
                $this->addClient($_COOKIE['da_affiliate'], $user_id);
                setcookie('da_affiliate', "",  strtotime('-1 day'));
            }
        }

        public function registerUser($user_name, $user_email, $password) {

            $user_id = wp_create_user($user_name, $password, $user_email);

            if (gettype($user_id) != 'object') {
                $userdata = array(
                    'ID' => $user_id, // когда нужно обновить пользователя
                    'user_pass' => $password, // обязательно
                    'user_login' => $user_name, // обязательно
                    'user_email' => $user_email,
                    'role' => 'partner', // (строка) роль пользователя
                );

                wp_insert_user($userdata);
                
                wp_set_password($password, $user_id);

                wp_set_auth_cookie($user_id, false, is_ssl());

                global $wpdb;
                $table_name = $wpdb->prefix . "dream_affiliate";
                $wpdb->insert($table_name, array(
                    'partner_id' => $user_id
                ));
                wp_redirect('/affiliate');
            }

            return $user_id;
        }
        
        public function updateCurrentUser($userdata = array(), $usermeta = array()) {
            $current_user = wp_get_current_user();
            
            $user = array(
                'ID' => $current_user->ID,
                'user_login' => $current_user->user_login
            );
            
            $userdata = array_merge($user, $userdata);
            
            foreach($usermeta as $key => $meta) {
                update_user_meta($current_user->ID, $key, $meta);
            }
            
            return wp_insert_user($userdata);
        }
        
        function addClient($affiliate_id, $client_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_clients";
            
            return $wpdb->insert($table_name, array(
                'affiliate_id' => $affiliate_id,
                'client_id' => $client_id
            ));
        }
        
        public function getClients() {
            global $wpdb;
            $table_affiliate = $wpdb->prefix . "dream_affiliate";
            $table_clients = $wpdb->prefix . "dream_affiliate_clients";
            $table_users = $wpdb->users;
            $affiliate_id = $this->getAffiliateId();
            
            $results = $wpdb->get_results("SELECT users.* FROM $table_users as users, $table_clients as clients, $table_affiliate as affiliate"
                    . " WHERE users.ID = clients.client_id GROUP BY users.ID ORDER BY users.user_registered DESC");
            
            return $results;
        }

        public function getAffiliateId() {
            $user_ID = get_current_user_id();

            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate";

            $affiliate_id = $wpdb->get_var("SELECT id FROM $table_name WHERE partner_id=$user_ID");

            return $affiliate_id;
        }

        public function getAffiliateUrl() {
            return get_home_url() . '/?affiliate=' . $this->getAffiliateId();
        }
        
        public function getCurrentUser() {
            $current_user = wp_get_current_user();
            return $current_user;
        }

        /* STYLES & SCRIPTS */

        public static function initStylesScripts() {
            /* STYLES */
            wp_enqueue_style('da-frontend', '/wp-content/plugins/dream-affiliate/css/affiliate-frontend.css');

            /* SCRIPTS */
            wp_register_script("EJSChart", "/wp-content/plugins/dream-affiliate/js/vendor/EJSCharts/EJSChart.js");
            wp_register_script("dream-affiliate", "/wp-content/plugins/dream-affiliate/js/dream-affiliate.js", array("jquery"), null);

            wp_enqueue_script("EJSChart");
            wp_enqueue_script("dream-affiliate");
            wp_localize_script('dream-affiliate', 'da_variables', array('ajax_url' => admin_url('admin-ajax.php')));
        }

        public static function createTable() {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate";

            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

                $sql = "CREATE TABLE " . $table_name . " (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    partner_id bigint(20) NOT NULL,
                    UNIQUE KEY id (id),
                    FOREIGN KEY (partner_id) REFERENCES {$wpdb->prefix}users(ID)
                 ) DEFAULT CHARACTER SET $wpdb->charset";

                dbDelta($sql);
            }

            $table_name = $wpdb->prefix . "dream_affiliate_clients";

            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

                $sql = "CREATE TABLE " . $table_name . " (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    affiliate_id bigint(20) NOT NULL,
                    client_id bigint(20) NOT NULL,
                    UNIQUE KEY id (id),
                    FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}dream_affiliate(id),
                    FOREIGN KEY (client_id) REFERENCES {$wpdb->prefix}users(ID)
                 ) DEFAULT CHARACTER SET $wpdb->charset";

                dbDelta($sql);
            }
        }

        public static function clearTable() {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->prefix}dream_affiliate");
            $wpdb->query("DELETE FROM {$wpdb->prefix}dream_affiliate_clients");
        }

        public static function dropTable() {
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dream_affiliate;");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dream_affiliate_clients;");
        }

        /* PLUGIN ACTIONS */

        public static function activate() {
            DreamAffiliate::createTable();
            add_role('partner', 'Partner', array('read' => true, 'level_0' => true));
        }

        public static function deactivate() {
            //DreamAffiliate::dropTable();
            //remove_role('partner');
        }

        public static function uninstall() {
            DreamAffiliate::dropTable();
            //remove_role('partner');
        }

    }

    global $da;
    $da = new DreamAffiliate();
}



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
    } else if ($wp->query_vars["pagename"] == 'affiliate') {
        $templatefilename = 'page-dashboard.php';

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
    $usermeta['company_name'] = $_POST['company_name'];
    $usermeta['partner_phone_number'] = $_POST['partner_phone_number'];
    $usermeta['partner_address'] = $_POST['partner_address'];
    $usermeta['partner_city'] = $_POST['partner_city'];
    $usermeta['partner_zip'] = $_POST['partner_zip'];
    $usermeta['partner_country'] = $_POST['partner_country'];
    $usermeta['partner_state'] = $_POST['partner_state'];
    $usermeta['partner_paypal'] = $_POST['partner_paypal'];
    
    echo $da->updateCurrentUser($userdata, $usermeta);
    die();
}
add_action('wp_ajax_nopriv_update_user', 'update_user');
add_action('wp_ajax_update_user', 'update_user');