<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: http://erinmorelli.com/wordpress/em-beer-manager
 * Description: Catalog and display your beers with WordPress. Integrates very simply with Untappd for individual beer checkins. Great for everyone from home brewers to professional breweries!
 * Version: 1.0
 * Author: Erin Morelli
 * Author URI: http://erinmorelli.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
 
 
// Define plugin file paths
define('EMBM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMBM_PLUGIN_URL', plugin_dir_url(__FILE__));


// Set plugin version
if (!defined('EMBM_VERSION_KEY'))
    define('EMBM_VERSION_KEY', 'embm_version');

if (!defined('EMBM_VERSION_NUM'))
    define('EMBM_VERSION_NUM', '1.0');

add_option(EMBM_VERSION_KEY, EMBM_VERSION_NUM);


// Initiate plugin files
function embm_plugin_load(){
		
    if(is_admin()) //load admin files only in admin
        require_once(EMBM_PLUGIN_DIR.'includes/admin.php');
        
    require_once(EMBM_PLUGIN_DIR.'includes/core.php');
    require_once(EMBM_PLUGIN_DIR.'includes/output.php');
}
embm_plugin_load();


// Load plugin styles or, if added, custom stylesheet
$style_option = get_option('embm_options');
$has_custom_css = $style_option['embm_css_url'];
if ($has_custom_css != '') {
	wp_deregister_style( 'beer-output', EMBM_PLUGIN_URL.'assets/css/output.css' );
	wp_dequeue_style( 'beer-output' );
	
	wp_register_style( 'custom-beer-output', $has_custom_css );
	wp_enqueue_style( 'custom-beer-output' );

} else {
	wp_deregister_style( 'custom-beer-output', $has_custom_css );
	wp_dequeue_style( 'custome-beer-output' );
	
	wp_register_style( 'beer-output', EMBM_PLUGIN_URL.'assets/css/output.css' );
	wp_enqueue_style( 'beer-output' );
}


// Activation setup
register_activation_hook(__FILE__, 'embm_plugin_activation');

function embm_plugin_activation() {  
	//actions to perform once on plugin activation go here   
}


// Uninstall setup
register_uninstall_hook(__FILE__, 'embm_plugin_uninstall');

function embm_plugin_uninstall() {    

	// remove Beer post type
	global $wp_post_types;
    if ( isset( $wp_post_types[ 'beer' ] ) ) {
        unset( $wp_post_types[ 'beer' ] );
    }
    $args = array(
		'post_type' =>'beer',
		'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash') 
	);
	$posts = get_posts( $args );
	if (is_array($posts)) {
	   foreach ($posts as $post) {
	       wp_delete_post( $post->ID, true);
	   }
	}
	
	// remove Style taxonomy
	$tax = array( 'style' );
	if( $tax ) {
		global $wp_taxonomies;
		foreach( $tax as $taxonomy ) {
			register_taxonomy( $taxonomy );
			$terms = get_terms( $taxonomy, array( 'hide_empty' => 0 ) );
			foreach( $terms as $term ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}
			unset( $wp_taxonomies[$taxonomy] );
		}
	}  
		
	//remove plugin css
	wp_deregister_style( 'beer-output', EMBM_PLUGIN_URL.'assets/css/output.css' );
	wp_dequeue_style( 'beer-output' );
	$get_style_option = get_option('embm_options');
	$get_custom_css = $style_option['embm_css_url'];
	wp_deregister_style( 'custom-beer-output', $get_custom_css );
	wp_dequeue_style( 'custome-beer-output' );
	
	//remove custom settings
	delete_option('embm_version');
	delete_option('embm_options');
	  
}


add_filter('plugin_action_links', 'embm_plugin_action_links', 10, 2);

function embm_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        // The "page" query string value must be equal to the slug
        // of the Settings admin page we defined earlier, which in
        // this case equals "myplugin-settings".
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=embm-settings">';
        $settings_link .= __('Settings', 'embm');
        $settings_link .= '</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}