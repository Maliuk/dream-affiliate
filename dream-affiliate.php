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

        public function __construct() {

            add_action('wp_enqueue_scripts', array($this, 'initStylesScripts'), 9999);

            // Функция которая исполняется при активации плагина
            register_activation_hook(__FILE__, array('DreamAffiliate', 'activate'));
            // Функция которая исполняется при деактивации плагина
            register_deactivation_hook(__FILE__, array('DreamAffiliate', 'deactivate'));
            //  Функция которая исполняется удалении плагина
            register_uninstall_hook(__FILE__, array('DreamAffiliate', 'uninstall'));

            if (isset($_GET['affiliate'])) {
                setcookie('da_affiliate', $_GET['affiliate'], strtotime('+90 day'));
            }

            add_action('wp_loaded', [$this, 'wp_loaded_action'], 99);
            add_action('user_register', [$this, 'user_register_action'], 99);
        }

        function wp_loaded_action() {
            //wp_set_password('qwerty123', 85);
        }

        function user_register_action($user_id) {
            if (isset($_COOKIE['da_affiliate'])) {
                $this->addClient($_COOKIE['da_affiliate'], $user_id);
                setcookie('da_affiliate', "", strtotime('-90 day'));
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

                wp_set_password($userdata['user_pass'], $user_id);

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

        public function getCurrentUser() {
            $current_user = wp_get_current_user();
            return $current_user;
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

            $sql = "SELECT users.* FROM $table_users as users, $table_clients as clients, $table_affiliate as affiliate"
                    . " WHERE users.ID = clients.client_id AND clients.affiliate_id = $affiliate_id GROUP BY users.ID ORDER BY users.user_registered DESC";

            if (current_user_can('administrator')) {
                $sql = "SELECT users.* FROM $table_users as users, $table_clients as clients, $table_affiliate as affiliate"
                        . " WHERE users.ID = clients.client_id GROUP BY users.ID ORDER BY users.user_registered DESC";
            }

            $results = $wpdb->get_results($sql);

            return $results;
        }

        public function isClientActive($client_id) {
            global $wpdb;
            $affiliate_id = $this->getAffiliateId();
            $table_name = $wpdb->prefix . "dream_affiliate_payments";
            $result = $wpdb->get_row("SELECT amount, date FROM $table_name WHERE date IN (SELECT max(date) FROM $table_name WHERE client_id = $client_id)");
            return $result;
        }

        public function isClient($user_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_clients";
            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE client_id = $user_id");
            return $result ? $result : false;
        }

        public function clientAmount($client_id) {
            global $wpdb;
            $affiliate_id = $this->getAffiliateId();
            $table_name = $wpdb->prefix . "dream_affiliate_payments";
            $result = $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE client_id = $client_id AND affiliate_id = $affiliate_id");
            return $result;
        }

        public function getClientsCount() {
            global $wpdb;
            $table_clients = $wpdb->prefix . "dream_affiliate_clients";
            $affiliate_id = $this->getAffiliateId();
            $result = $wpdb->get_var("SELECT COUNT(*) FROM $table_clients WHERE affiliate_id = $affiliate_id");
            return $result;
        }

        public function getAffiliateId() {
            $user_ID = get_current_user_id();

            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate";

            $affiliate_id = $wpdb->get_var("SELECT id FROM $table_name WHERE partner_id=$user_ID");

            return $affiliate_id;
        }
        
        public function getAffiliateByClient($user_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_clients";
            $result = $wpdb->get_var("SELECT affiliate_id FROM $table_name WHERE client_id = $user_id");
            return $result ? (int)$result : false;
        }

        public function getAffiliateUrl() {
            return get_home_url() . '/?affiliate=' . $this->getAffiliateId();
        }

        public function setPayment($user_id, $amount) {
            $affiliate_id = $this->getAffiliateByClient($user_id);
            if ($affiliate_id && isset($user_id) && isset($amount)) {
                $amount = (float)($amount / 2.0);
                
                global $wpdb;
                $table_name = $wpdb->prefix . "dream_affiliate_payments";
                $result = $wpdb->insert($table_name, array(
                    'affiliate_id' => $affiliate_id,
                    'client_id' => $user_id,
                    'amount' => $amount,
                    'date' => date("Y-m-d H:i:s")
                ));
            }
            else {
                $result = false;
            }
            
            return $result;
        }

        /* STATISTIC */

        public function getPartnersCount() {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate";
            $result = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            return $result;
        }

        public function getPartners() {
            global $wpdb;
            $table_affiliate = $wpdb->prefix . "dream_affiliate";
            $args = array(
                'role' => 'partner'
            );
            $results = get_users($args);
            return $results;
        }

        public function getReports() {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_payments";
            $affiliate_id = $this->getAffiliateId();
            $results = $wpdb->get_results("SELECT users.display_name, payments.* "
                    . "FROM $wpdb->users as users, $table_name as payments "
                    . "WHERE payments.affiliate_id = $affiliate_id AND users.ID = payments.client_id ORDER BY payments.date DESC");
            return $results;
        }

        public function getIncome() {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_payments";
            $affiliate_id = $this->getAffiliateId();
            $result = $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE affiliate_id = $affiliate_id");
            return $result ? round($result, 2) : 0;
        }
        
        public function getAverageIncome() {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_payments";
            $affiliate_id = $this->getAffiliateId();
            $result = $wpdb->get_var("SELECT AVG(amount) FROM $table_name WHERE affiliate_id = $affiliate_id");
            return $result ? round($result, 2) : 0;
        }

        public function getMonthStatistic() {
            global $wpdb;
            $table_name = $wpdb->prefix . "dream_affiliate_payments";
            $affiliate_id = $this->getAffiliateId();
            $results = $wpdb->get_results("SELECT SUM(amount) as amount, YEAR(date) as year, MONTHNAME(date) as month "
                    . "FROM $table_name "
                    . "WHERE affiliate_id = $affiliate_id "
                    . "GROUP BY YEAR(date), MONTH(date) ORDER BY YEAR(date), MONTH(date)");

            return $results ? $results : 0;
        }

        public function getAverageMonth() {
            $monthStatistic = $this->getMonthStatistic();
            if ($monthStatistic) {
                $result = $this->getIncome() / count($monthStatistic);
            }
            return $result ? round($result, 2) : 0;
        }

        /* STYLES & SCRIPTS */

        public function initStylesScripts() {
            /* STYLES */
            wp_enqueue_style('datatable', '/wp-content/plugins/dream-affiliate/js/vendor/datatables/datatables.min.css');
            wp_enqueue_style('da-frontend', '/wp-content/plugins/dream-affiliate/css/affiliate-frontend.css');

            /* SCRIPTS */
            wp_register_script("EJSChart", "/wp-content/plugins/dream-affiliate/js/vendor/EJSCharts/EJSChart.js");
            wp_register_script("datatable", "/wp-content/plugins/dream-affiliate/js/vendor/datatables/datatable.min.js", array("jquery"));
            wp_register_script("dream-affiliate", "/wp-content/plugins/dream-affiliate/js/dream-affiliate.js", array("jquery"), null);

            wp_enqueue_script("EJSChart");
            wp_enqueue_script("datatable");
            wp_enqueue_script("dream-affiliate");
            
            wp_localize_script('dream-affiliate', 'da_variables', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'affiliateId' => $this->getAffiliateId()
            ));
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
                    FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}dream_affiliate(id) 
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                    FOREIGN KEY (client_id) REFERENCES {$wpdb->prefix}users(ID)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
                 ) DEFAULT CHARACTER SET $wpdb->charset";

                dbDelta($sql);
            }

            $table_name = $wpdb->prefix . "dream_affiliate_payments";

            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

                $sql = "CREATE TABLE " . $table_name . " (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    affiliate_id bigint(20) NOT NULL,
                    client_id bigint(20) NOT NULL,
                    amount float(10,2),
                    date DATETIME NOT NULL,
                    UNIQUE KEY id (id),
                    FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}dream_affiliate(id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                    FOREIGN KEY (client_id) REFERENCES {$wpdb->prefix}users(ID)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
                 ) DEFAULT CHARACTER SET $wpdb->charset";
                    
                dbDelta($sql);
            }
        }

        public static function clearTable() {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->prefix}dream_affiliate");
            $wpdb->query("DELETE FROM {$wpdb->prefix}dream_affiliate_clients");
            $wpdb->query("DELETE FROM {$wpdb->prefix}dream_affiliate_payments");
        }

        public static function dropTable() {
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dream_affiliate;");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dream_affiliate_clients;");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dream_affiliate_payments;");
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