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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Yoast_ACF_Analysis
 *
 * Adds ACF data to the content analyses of WordPress SEO
 *
 */
class Yoast_ACF_Analysis {
	private $plugin_data = null;

	/**
	 * Yoast_ACF_Analysis constructor.
	 *
	 * Add hooks and filters.
	 *
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_filter( 'wpseo_post_content_for_recalculation', array( $this, 'add_recalculation_data_to_post_content' ) );
		add_filter( 'wpseo_term_description_for_recalculation', array( $this, 'add_recalculation_data_to_term_content' ) );
	}

	/**
	 * Add notifications to admin if plugins ACF or WordPress SEO are not present.
	 */
	public function admin_init() {

		// Require ACF and Yoast
		if ( current_user_can( 'activate_plugins' ) ) {
			$deactivate = false;

			// ACF
			if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) && ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
				add_action( 'admin_notices', array( $this, 'acf_not_active_notification' ) );
				$deactivate = true;
			}

			// WordPress SEO
			if ( ! is_plugin_active( 'wordpress-seo/wp-seo.php' ) && ! is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
				add_action( 'admin_notices', array( $this, 'wordpress_seo_requirements_not_met' ) );
				$deactivate = true;
			}
			else {
				// Compare if version is >= 3.0
				if ( defined( 'WPSEO_VERSION' ) ) {
					if ( version_compare( substr( WPSEO_VERSION, 0, 3 ), '3.0', '<' ) ) {
						add_action( 'admin_notices', array( $this, 'wordpress_seo_requirements_not_met' ) );
						$deactivate = true;
					}
				}
			}

			// Deactivate when we cannot do the job we are hired to do.
			if ( $deactivate ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );

				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}

				return;
			}

			// Only enqueue when we are active.
			add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			$this->plugin_data = get_plugin_data( dirname( __FILE__ ) );
		}
	}

	/**
	 * Notify that we need ACF to be installed and active.
	 */
	public function acf_not_active_notification() {
		$message = __( 'ACF Yoast Analysis requires Advanced Custom Fields (free or pro) to be installed and activated.', 'wordpress-seo' );

		printf( '<div class="error"><p>%s</p></div>', esc_html( $message ) );
	}

	/**
	 * Notify that we need WordPress SEO to be installed and active.
	 */
	public function wordpress_seo_requirements_not_met() {
		$message = __( 'ACF Yoast Analysis requires Yoast SEO 3.0+ to be installed and activated.', 'wordpress-seo' );

		printf( '<div class="error"><p>%s</p></div>', esc_html( $message ) );
	}

	/**
	 * Enqueue JavaScript file to feed data to Yoast Content Analyses.
	 */
	public function enqueue_scripts() {
		// Post page enqueue.
		wp_enqueue_script(
			'yoast-acf-analysis-post',
			plugins_url( '/js/yoast-acf-analysis.js', __FILE__ ),
			array(
				'jquery',
				'wp-seo-post-scraper',
			),
			$this->plugin_data['Version']
		);

		// Term page enqueue.
		wp_enqueue_script(
			'yoast-acf-analysis-term',
			plugins_url( '/js/yoast-acf-analysis.js', __FILE__ ),
			array(
				'jquery',
				'wp-seo-term-scraper',
			),
			$this->plugin_data['Version']
		);
	}

	/**
	 * Add ACF data to post content
	 *
	 * @param string  $content String of the content to add data to.
	 * @param WP_Post $post    Item the content belongs to.
	 *
	 * @return string Content with added ACF data.
	 */
	public function add_recalculation_data_to_post_content( $content, $post ) {
		// ACF defines this function.
		if ( ! function_exists( 'get_fields' ) ) {
			return '';
		}

		if ( false === ( $post instanceof WP_Post ) ) {
			return '';
		}

		$post_acf_fields = get_fields( $post->ID );
		$acf_content     = $this->get_field_data( $post_acf_fields );

		return trim( $content . ' ' . $acf_content );
	}

	/**
	 * Add custom fields to term content
	 *
	 * @param string  $content String of the content to add data to.
	 * @param WP_Term $term    The term to get the custom ffields of.
	 *
	 * @return string Content with added ACF data.
	 */
	public function add_recalculation_data_to_term_content( $content, $term ) {
		// ACF defines this function.
		if ( ! function_exists( 'get_fields' ) ) {
			return '';
		}

		if ( false === ( $term instanceof WP_Term ) ) {
			return '';
		}

		$term_acf_fields = get_fields( $term->taxonomy . '_' . $term->term_id );
		$acf_content     = $this->get_field_data( $term_acf_fields );

		return trim( $content . ' ' . $acf_content );
	}

	/**
	 * Filter what ACF Fields not to score
	 *
	 * @param array $fields ACF Fields to parse.
	 *
	 * @return string Content of all ACF fields combined.
	 */
	function get_field_data( $fields ) {
		$output = '';

		if ( ! is_array( $fields ) ) {
			return $output;
		}

		foreach ( $fields as $key => $field ) {
			switch ( gettype( $field ) ) {
				case 'string':
					$output .= ' ' . $field;
					break;

				case 'array':
					if ( isset( $field['sizes']['thumbnail'] ) ) {
						// Put all images in img tags for scoring.
						$alt   = ( isset( $field['alt'] ) ) ? $field['alt'] : '';
						$title = ( isset( $field['title'] ) ) ? $field['title'] : '';

						$output .= ' <img src="' . esc_url( $field['sizes']['thumbnail'] ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" />';
					}
					else {
						$output .= ' ' . $this->get_field_data( $field );
					}

					break;
			}
		}

		return trim( $output );
	}
}

new Yoast_ACF_Analysis();
