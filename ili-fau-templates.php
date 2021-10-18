<?php

/**
 * Plugin Name:     ILI FAU Templates (RRZE-Webteam Fork)
 * Plugin URI:      https://ili.fau.de
 * Description:     Erweiterung der FAU-Website um zusätzliche Templates, Fork vom RRZE-Webteam
 * Version:         1.6.4
 * Author:          Sebastian Honert
 * Author URI:      https://www.ili.fau.de/team/sebastian-honert/
 * License:         GNU General Public License v3
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:     /languages
 * Text Domain:     ili-fau-templates
 */

namespace ILI\FAUTemplates;

use ILI\FAUTemplates\Main;

defined('ABSPATH') || exit;

const RRZE_PHP_VERSION = '7.0';
const RRZE_WP_VERSION = '5.4';

register_activation_hook(__FILE__, 'ILI\FAUTemplates\activation');
register_deactivation_hook(__FILE__, 'ILI\FAUTemplates\deactivation');

add_action('plugins_loaded', 'ILI\FAUTemplates\loaded');

// AJAX
add_action('admin_init', 'ILI\FAUTemplates\ilifautpl_add_ajax_actions');

function ilifautpl_add_ajax_actions() {
    add_action('wp_ajax_ilifautpl_get_posts', 'ILI\FAUTemplates\ilifautpl_get_posts_ajax_callback');
    add_action('wp_ajax_nopriv_ilifautpl_get_posts', 'ILI\FAUTemplates\ilifautpl_get_posts_ajax_callback');
}

function ilifautpl_get_posts_ajax_callback() {
    $return = array();
     
    $search_results = new \WP_Query( array( 
		's' => sanitize_text_field( $_GET['q'] ),
        'post_type' => array('post', 'page'),
		'post_status' => 'publish',
		'posts_per_page' => 50,
	) );
    
	if( $search_results->have_posts() ) :
		while( $search_results->have_posts() ) : $search_results->the_post();	
			// shorten the title a little
			$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title ); // array( Post ID, Post Title )
		endwhile;
	endif;
    
    echo json_encode( $return );
    die;
}

// CSS und JS einbinden.
add_action('wp_enqueue_scripts', 'ILI\FAUTemplates\register_scripts_and_styles');
add_action('admin_enqueue_scripts', 'ILI\FAUTemplates\register_admin_scripts_and_styles');
add_action('init', 'ILI\FAUTemplates\add_image_sizes', 99, 1);

/*
 * Einbindung der Sprachdateien.
 * @return void
 */
function load_textdomain() {
    load_plugin_textdomain('ili-fau-templates', FALSE, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

/*
 * Bildgrößen definieren
 * @return void
 */
function add_image_sizes() {
    add_image_size( 'ilifautpl-1920', 1920, 1080 );
    add_image_size( 'ilifautpl-1600', 1600, 900 );
    add_image_size( 'ilifautpl-1366', 1366, 768 );
    add_image_size( 'ilifautpl-1024', 1024, 768 );
    add_image_size( 'ilifautpl-800', 800, 480 );
    add_image_size( 'ilifautpl-640', 640, 360 );

    add_image_size( 'ilifautpl-1920-portrait', 1080, 1920, true, array('center', 'top') );
    add_image_size( 'ilifautpl-1600-portrait', 900, 1600, true, array('center', 'top') );
    add_image_size( 'ilifautpl-1366-portrait', 768, 1366, true, array('center', 'top') );
    add_image_size( 'ilifautpl-1024-portrait', 768, 1024, true, array('center', 'top') );
    add_image_size( 'ilifautpl-800-portrait', 480, 800, true, array('center', 'top') );
    add_image_size( 'ilifautpl-640-portrait', 360, 640, true, array('center', 'top') );

    add_image_size( 'ilifautpl-topic-box', 480, 480, array('center', 'center') );
}

/*
 * Wird durchgeführt, nachdem das Plugin aktiviert wurde.
 * @return void
 */
function activation() {
    // Sprachdateien werden eingebunden.
    load_textdomain();
    
    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    system_requirements();

    // Ab hier können die Funktionen hinzugefügt werden, 
    // die bei der Aktivierung des Plugins aufgerufen werden müssen.
    // Bspw. wp_schedule_event, flush_rewrite_rules, etc.    
}

/*
 * Wird durchgeführt, nachdem das Plugin deaktiviert wurde.
 * @return void
 */
function deactivation() {
    // Hier können die Funktionen hinzugefügt werden, die
    // bei der Deaktivierung des Plugins aufgerufen werden müssen.
    // Bspw. wp_clear_scheduled_hook, flush_rewrite_rules, etc.
    
    // Bildgröße für Slides entfernen
    // Landscape
    remove_image_size( 'ilifautpl-1920' );
    remove_image_size( 'ilifautpl-1600' );
    remove_image_size( 'ilifautpl-1366' );
    remove_image_size( 'ilifautpl-1024' );
    remove_image_size( 'ilifautpl-800' );
    remove_image_size( 'ilifautpl-640' );

    remove_image_size( 'ilifautpl-1920-portrait' );
    remove_image_size( 'ilifautpl-1600-portrait' );
    remove_image_size( 'ilifautpl-1366-portrait' );
    remove_image_size( 'ilifautpl-1024-portrait' );
    remove_image_size( 'ilifautpl-800-portrait' );
    remove_image_size( 'ilifautpl-640-portrait' );

    remove_image_size( 'ilifautpl-topic-box' );
}

/*
 * Überprüft die minimal erforderliche PHP- u. WP-Version.
 * @return void
 */
function system_requirements() {
    $error = '';

    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(__('Your server is running PHP version %s. Please upgrade at least to PHP version %s.', 'ili-fau-templates'), PHP_VERSION, RRZE_PHP_VERSION);
    }

    if (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(__('Your Wordpress version is %s. Please upgrade at least to Wordpress version %s.', 'ili-fau-templates'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }

    // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
    if (!empty($error)) {
        deactivate_plugins(plugin_basename(__FILE__), FALSE, TRUE);
        wp_die($error);
    }
}

/*
 * Wird durchgeführt, nachdem das WP-Grundsystem hochgefahren
 * und alle Plugins eingebunden wurden.
 * @return void
 */
function loaded() {
    // Sprachdateien werden eingebunden.
    load_textdomain();
    
    // Automatische Laden von Klassen.
    autoload();
}

/*
 * Automatische Laden von Klassen.
 * @return void
 */
function autoload() {
    require 'autoload.php';
    return new Main(plugin_basename(__FILE__));
}

/*
 * Laden von CSS und JS
 * @return void
 */
function register_scripts_and_styles() {
    $is_ilifautpl_landing_page = ilifautpl_is_landing_page('frontend');
    $has_ilifautpl_topic_box_shortcode = has_shortcode(get_post_field('post_content', get_the_ID()), 'ilifautpl_topic_boxes') || has_shortcode(get_post_field('post_content', get_the_ID()), 'themenboxen');

    if( ! $is_ilifautpl_landing_page && ! $has_ilifautpl_topic_box_shortcode )
        return;

    wp_register_style( 'ili-fau-templates', plugins_url('assets/css/main.css', __FILE__ ) );
    wp_enqueue_style( 'ili-fau-templates' );
    
    wp_register_script( 'ili-fau-templates-main', plugins_url('assets/js/main.js', __FILE__), array('jquery'), '0.0.1', true );
    wp_enqueue_script( 'ili-fau-templates-main' );

    // Localize scripts
    $ilifautpl_slider_has_dots = get_post_meta( get_the_ID(), '_ilifautpl_slider_has_dots', true);
    $ilifautpl_slider_has_arrows = get_post_meta( get_the_ID(), '_ilifautpl_slider_has_arrows', true);
    $ilifautpl_slider_fade = get_post_meta( get_the_ID(), '_ilifautpl_slider_fade', true);
    $ilifautpl_slider_skew = get_post_meta( get_the_ID(), '_ilifautpl_slider_skew', true);

    wp_localize_script( 'ili-fau-templates-main', 'ilifautpl_slider_has_dots', array( $ilifautpl_slider_has_dots ) );
    wp_localize_script( 'ili-fau-templates-main', 'ilifautpl_slider_has_arrows', array( $ilifautpl_slider_has_arrows ) );
    wp_localize_script( 'ili-fau-templates-main', 'ilifautpl_slider_fade', array( $ilifautpl_slider_fade ) );
    wp_localize_script( 'ili-fau-templates-main', 'ilifautpl_slider_skew', array( $ilifautpl_slider_skew ) );

    // Slick Slider
    $ilifautpl_show_slider = get_post_meta(get_the_ID(), '_ilifautpl_show_slider', true ) === '1';
    
    if( $is_ilifautpl_landing_page && $ilifautpl_show_slider ) {
        wp_register_style( 'ili-fau-templates-slick', plugins_url('inc/slick/slick.css', __FILE__ ) );
        wp_enqueue_style( 'ili-fau-templates-slick' );
        
        wp_register_style( 'ili-fau-templates-slick-theme', plugins_url('inc/slick/slick-theme.css', __FILE__ ) );
        wp_enqueue_style( 'ili-fau-templates-slick-theme' );
        
        wp_register_script( 'ili-fau-templates-slick', plugins_url('inc/slick/slick.js', __FILE__), array('jquery'), '1.8.0', true );
        wp_enqueue_script( 'ili-fau-templates-slick' );
    }
}

/*
 * Laden von CSS und JS für Admin-Bereich
 * @return void
 */
function register_admin_scripts_and_styles()
{
    if( ! ilifautpl_is_landing_page('admin') )
        return;

    // Multi Select
    wp_register_script( 'ili-fau-templates-multiselect', plugins_url('inc/lou-multi-select-e052211/js/jquery.multi-select.js', __FILE__), array('jquery'), '0.9.12', true );
    wp_enqueue_script( 'ili-fau-templates-multiselect' );
    
    wp_register_style( 'ili-fau-templates-multiselect', plugins_url('inc/lou-multi-select-e052211/css/multi-select.dist.css', __FILE__ ) );
    wp_enqueue_style( 'ili-fau-templates-multiselect' );

    // Select2
    wp_enqueue_style('ili-fau-templates-select2', plugins_url('assets/js/select2/select2.min.css', __FILE__ ) );
    wp_enqueue_script('ili-fau-templates-select2', plugins_url('assets/js/select2/select2.full.min.js', __FILE__ ), array('jquery') );
    wp_enqueue_script('ili-fau-templates-select2-de', plugins_url('assets/js/select2/de.js', __FILE__ ), array('jquery') );

    $options = get_option('ili_fau_templates');
    $max_num_slides = $options['ili_fau_templates_max_num_slides'] ? $options['ili_fau_templates_max_num_slides'] : 3;
    
    wp_register_script( 'ili-fau-templates-admin', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), '0.0.1', true );
    wp_enqueue_script( 'ili-fau-templates-admin' );
    
    wp_register_style( 'ili-fau-templates-admin', plugins_url('assets/css/admin.css', __FILE__ ) );
    wp_localize_script( 'ili-fau-templates-admin', 'ilifautpl_options_admin', array(
        'max_num_slides' => $max_num_slides
    ) );
    wp_enqueue_style( 'ili-fau-templates-admin' );
}

/*
 * Prüft den Seitenkontext
 * @return bool
 */
function ilifautpl_is_landing_page( $context = 'frontend' ) {

    $post_id = get_the_ID();

    // Frontend
    $allowed_templates = array('templates/template-landing-page.php');

    if( $context === 'frontend' )
        return in_array( get_page_template_slug( $post_id ), $allowed_templates ); 

    // Backend
    $screen = function_exists('get_current_screen') ? get_current_screen( $post_id ) : '';
    $allowed_post_types = array('post', 'page');
    $allowed_templates = array('ilifautpl_topic_box', 'templates/template-landing-page.php');

    $is_ili_fau_template = in_array( $screen->post_type, $allowed_post_types ) && ( in_array( get_page_template_slug( $post_id ), $allowed_templates ) );
    $is_ili_fau_settings = $screen->base === 'settings_page_ili-fau-templates';
    $is_ili_fau_topic_box = $screen->id === 'ilifautpl_topic_box';
    
    $ilifautpl_is_landing_page = $is_ili_fau_template || $is_ili_fau_topic_box|| $is_ili_fau_settings;
    
    return $ilifautpl_is_landing_page;
}
