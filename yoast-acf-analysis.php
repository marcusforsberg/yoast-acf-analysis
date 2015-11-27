<?php
/**
 * Plugin Name: Yoast ACF Analysis
 * Plugin URI: https://forsberg.ax
 * Description: Adds the content of all ACF fields to the Yoast SEO score analysis.
 * Version: 1.0.0
 * Author: Marcus Forsberg
 * Author URI: https://forsberg.ax
 * License: GPL v3
 */
 
if(! defined('ABSPATH')) exit;

class Yoast_ACF_Analysis {
	private $plugin_data = null;
	
	function __construct() {
		add_action('admin_init', array($this, 'admin_init'));
		add_filter('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}
	
	public function admin_init() {
		$this->plugin_data = get_plugin_data(dirname(__FILE__));
		
		// Require ACF and Yoast
		if (current_user_can('activate_plugins')) {
			$deactivate = false;
			
			// ACF
			if(!is_plugin_active('advanced-custom-fields/acf.php') && !is_plugin_active('advanced-custom-fields-pro/acf.php')) {
				add_action('admin_notices', array($this, 'require_acf'));
				$deactivate = true;
			}
			
			// Yoast
			if(!is_plugin_active('wordpress-seo/wp-seo.php')) {
				add_action('admin_notices', array($this, 'require_yoast'));
				$deactivate = true;
			}
			
			if($deactivate) {
				deactivate_plugins(plugin_basename( __FILE__ )); 

				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			}
		}
	}
	
	public function require_acf() { ?>
		<div class="error"><p>ACF Yoast Analysis requires Advanced Custom Fields (free or pro) to be installed and activated.</p></div><?php
	}
	
	public function require_yoast() { ?>
		<div class="error"><p>ACF Yoast Analysis requires Yoast SEO 3.0+ to be installed and activated.</p></div><?php
	}
	
	public function enqueue_scripts() {
		wp_enqueue_script('yoast-acf-analysis', plugins_url('/js/yoast-acf-analysis.js', __FILE__), array('jquery', 'wp-seo-post-scraper'), $this->plugin_data['Version']);
	}
}
 
new Yoast_ACF_Analysis();