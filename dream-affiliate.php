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

            add_action('wp_loaded', [$this, 'wp_loaded_action'], 99);
            add_action('user_register', [$this, 'user_register_action'], 99);
        }

        function wp_loaded_action() {
            /* $user_ID = get_current_user_id();

              global $wpdb;
              $table_name = $wpdb->prefix . "dream_affiliate";

              $affiliate_id = $wpdb->get_var("SELECT id FROM $table_name WHERE partner_id=$user_ID");

              DreamAffiliate::$affiliate_id = $affiliate_id; */

            //wp_set_password('qwerty123', 85);
        }

        function user_register_action($user_id) {
            if (isset($_COOKIE['da_affiliate'])) {
                $this->addClient($_COOKIE['da_affiliate'], $user_id);
                setcookie('da_affiliate', "", strtotime('-1 day'));
            }
        }

        public function registerUser($userdata, $usermeta) {

            //$user_id = wp_create_user($user_name, $password, $user_email, $userdata, $usermeta);
            $user = array(
                'role' => 'partner', // (строка) роль пользователя
            );

            $userdata = array_merge($user, $userdata);

            $user_id = wp_insert_user($userdata);

            if (gettype($user_id) != 'object') {

                wp_set_password($password, $user_id);

                foreach ($usermeta as $key => $meta) {
                    update_user_meta($user_id, $key, $meta);
                }

                wp_set_auth_cookie($user_id, false, is_ssl());

                global $wpdb;
                $table_name = $wpdb->prefix . "dream_affiliate";
                $wpdb->insert($table_name, array(
                    'partner_id' => $user_id
                ));
                wp_redirect('/affiliate/dashboard');
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

//            if (isset($userdata['user_pass'])) {
//                $userdata['user_pass'] = wp_hash_password($userdata['user_pass']);
//            }

            foreach ($usermeta as $key => $meta) {
                update_user_meta($current_user->ID, $key, $meta);
            }

            return wp_update_user($userdata);
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
                    . " WHERE users.ID = clients.client_id AND clients.affiliate_id = $affiliate_id GROUP BY users.ID ORDER BY users.user_registered DESC");

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

    require_once 'functions.php';
}