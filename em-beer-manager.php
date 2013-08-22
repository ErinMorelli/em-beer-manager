<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: http://erinmorelli.com/wordpress/em-beer-manager
 * Description: Catalog and display your beers with WordPress. Integrates very simply with Untappd for individual beer checkins. Great for everyone from home brewers to professional breweries!
 * Version: 1.9.1
 * Author: Erin Morelli
 * Author URI: http://erinmorelli.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
 
 
// Define plugin file paths
define('EMBM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMBM_PLUGIN_URL', plugin_dir_url(__FILE__));


// Initiate plugin files
function embm_plugin_load(){
		
    if(is_admin()) //load admin files only in admin
        require_once(EMBM_PLUGIN_DIR.'includes/admin.php');
        
    require_once(EMBM_PLUGIN_DIR.'includes/core.php');
    require_once(EMBM_PLUGIN_DIR.'includes/output.php');
    
    foreach (scandir(EMBM_PLUGIN_DIR.'includes/components') as $filename) {
    	$path = EMBM_PLUGIN_DIR.'includes/components/' . $filename;
    	if (is_file($path) && ($filename != '.DS_Store')) {
        	require $path;
        }
    }
}
embm_plugin_load();


// Localization
$plugin_dir = EMBM_PLUGIN_DIR . 'languages';
load_plugin_textdomain( 'embm', WP_PLUGIN_DIR.'/'.$plugin_dir, $plugin_dir );


// Activation setup
register_activation_hook(__FILE__, 'embm_plugin_activation');

function embm_plugin_activation() {
	// Check for new version
	$embm_curr_version = '1.9.1';
	 
	if (!defined('EMBM_VERSION_KEY')) {
		// Define new version option
    	define('EMBM_VERSION_KEY', 'embm_version');
    }
    
    if (!defined('EMBM_VERSION_NUM')) {
    	// Add current version value
	    define('EMBM_VERSION_NUM', $embm_curr_version);	
	    add_option(EMBM_VERSION_KEY, EMBM_VERSION_NUM);  
    } 
    
    if (get_option(EMBM_VERSION_KEY) != $embm_curr_version) {
	    // Update the version value
	    update_option(EMBM_VERSION_KEY, $embm_curr_version);
	}
	
	// Refresh permalinks
	flush_rewrite_rules();
	
	// Set default options
	$defaults = array(
	  'embm_untappd_check' => '',
	  'embm_untappd_brewery' => '', 
	  'embm_css_url' => '',
	  'embm_group_slug' => 'group'
	);
	update_option('embm_options', $defaults);
	
	update_option('embm_comment_change', 'false');
	
}


// Deactivation setup
register_deactivation_hook(__FILE__, 'embm_plugin_deactivation');

function embm_plugin_deactivation() {
	// Refresh permalinks
	flush_rewrite_rules();
}


// Uninstall setup
register_uninstall_hook(__FILE__, 'embm_plugin_uninstall');

function embm_plugin_uninstall() {    

	$curr_version = floatval(get_option('embm_version'));
    $new_version = 1.7;
    
    if ($curr_version >= $new_version) { //Keep beer data saved for those upgrading from 1.6 or earlier
    
	// remove Beer post type
	global $wp_post_types;
    if ( isset( $wp_post_types[ 'embm_beer' ] ) ) {
        unset( $wp_post_types[ 'embm_beer' ] );
    }
    $args = array(
		'post_type' =>'embm_beer',
		'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash') 
	);
	$posts = get_posts( $args );
	if (is_array($posts)) {
	   foreach ($posts as $post) {
	       wp_delete_post( $post->ID, true);
	   }
	}
	
	// remove Style taxonomy
	$tax = array( 'embm_style' );
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
	
	// remove Group taxonomy
	$tax = array( 'embm_group' );
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
	
	}
		
	//remove plugin css
	wp_deregister_style( 'embm-widget', EMBM_PLUGIN_URL.'assets/css/widget.css' );
	wp_dequeue_style( 'embm-widget' );
	
	wp_deregister_style( 'embm-output', EMBM_PLUGIN_URL.'assets/css/output.css' );
	wp_dequeue_style( 'embm-output' );
	
	wp_dequeue_style( 'embm-admin' );
	
	$get_style_option = get_option('embm_options');
	$get_custom_css = $style_option['embm_css_url'];
	wp_deregister_style( 'custom-embm-output', $get_custom_css );
	wp_dequeue_style( 'custom-embm-output' );
	
	//remove custom settings
	delete_option('embm_version');
	delete_option('embm_options');
	delete_option('embm_db_upgrade');
	delete_option('widget_beer_list_widget');
	delete_option('widget_recent_untappd_widget');
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

// Load plugin styles or, if added, custom stylesheet
function embm_load_styles() {
	$style_option = get_option('embm_options');
	$has_custom_css = esc_url($style_option['embm_css_url']);
	if ($has_custom_css != '') {
		wp_deregister_style( 'embm-output', EMBM_PLUGIN_URL.'assets/css/output.css' );
		wp_dequeue_style( 'embm-output' );
		
		wp_register_style( 'custom-embm-output', $has_custom_css );
		wp_enqueue_style( 'custom-embm-output' );
	
	} else {
		wp_deregister_style( 'custom-embm-output', $has_custom_css );
		wp_dequeue_style( 'custom-embm-output' );
		
		wp_register_style( 'embm-output', EMBM_PLUGIN_URL.'assets/css/output.css' );
		wp_enqueue_style( 'embm-output' );
	}
	wp_register_style( 'embm-widget', EMBM_PLUGIN_URL.'assets/css/widgets.css' );
	wp_enqueue_style( 'embm-widget' );
}
add_action( 'wp_enqueue_scripts', 'embm_load_styles' );


// Update pre 1.7.0 databases to new naming
add_action('init', 'embm_update_db_names');
function embm_update_db_names() {
	global $wpdb;
	$upgrade = get_option('embm_db_upgrade');
	$curr_version = floatval(get_option('embm_version'));
    $new_version = 1.7;
    
    if ( ($curr_version >= $new_version) && ($upgrade == false) ) {
	    $wpdb->query("
			UPDATE $wpdb->term_taxonomy 
			SET taxonomy = 'embm_style'
			WHERE taxonomy = 'style'
		");
		$wpdb->query("
			UPDATE $wpdb->posts 
			SET post_type = 'embm_beer'
			WHERE post_type = 'beer'
		");
		add_option('embm_db_upgrade', 'true');
    }
}

?>