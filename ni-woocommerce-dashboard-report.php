<?php 
/*
Plugin Name: Ni WooCommerce Dashboard Sales Report
Plugin URI: http://naziinfotech.com/
Description: Enhance your WooCommerce store with the "Ni WooCommerce Dashboard Report" plugin. Gain insights, track sales, and optimize your business.
Version: 2.2.9
Author: anzia
Author URI: http://naziinfotech.com/
Plugin URI: https://wordpress.org/plugins/ni-woocommerce-dashboard-report/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/agpl-3.0.html
Text Domain: nidashboardreport
Domain Path: /languages/
Requires at least: 4.7
Tested up to: 6.6.1
WC requires at least: 3.0.0
WC tested up to: 9.1.4
Last Updated Date: 20-August-2024
Requires PHP: 7.0
*/
if ( ! defined( 'ABSPATH' ) ) { exit;}
if(!class_exists('NiWooDR_Dashbaord_Report')){
	class NiWooDR_Dashbaord_Report{
		public function __construct(){
			add_action('plugins_loaded', array($this, 'plugins_loaded'));
			add_action( 'before_woocommerce_init',  array(&$this,'before_woocommerce_init') );
		}
		function before_woocommerce_init(){
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}	 
		}
		function plugins_loaded(){
			
			load_plugin_textdomain('nidashboardreport', WP_PLUGIN_DIR.'/ni-woocommerce-dashboard-report/languages','ni-woocommerce-dashboard-report/languages');
			include_once('include/ni-dashboard-report.php'); 
			$obj = new ni_dashboard_report();
		}

	}

}
$obj = new NiWooDR_Dashbaord_Report();