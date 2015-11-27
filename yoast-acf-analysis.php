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
		$this->plugin_data = get_plugin_data(dirname(__FILE__));
		
		add_filter('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}
	
	public function enqueue_scripts() {
		wp_enqueue_script('yoast-acf-analysis', plugins_url('/js/yoast-acf-analysis.js', __FILE__), array('jquery', 'wp-seo-post-scraper'), $this->plugin_data['Version']);
	}
}
 
new Yoast_ACF_Analysis();
