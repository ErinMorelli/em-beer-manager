<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: http://www.erinmorelli.com/
 * Description: A beer management plugin
 * Version: 1.0
 * Author: Erin Morelli
 * Author URI: http://www.erinmorelli.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
 
 
// Define plugin file paths
define('EM_BEERMANAGE_DIR', plugin_dir_path(__FILE__));
define('EM_BEERMANAGE_URL', plugin_dir_url(__FILE__));



// Initiate plugin files
function em_beermanage_load(){
		
    if(is_admin()) //load admin files only in admin
        require_once(EM_BEERMANAGE_DIR.'includes/admin.php');
        
    require_once(EM_BEERMANAGE_DIR.'includes/core.php');
    require_once(EM_BEERMANAGE_DIR.'includes/output.php');
}
em_beermanage_load();


// Load plugin styles or, if added, custom stylesheet
$style_option = get_option('embm_options');
$has_custom_css = $$style_option['embm_css_url'];
if ($has_custom_css != '') {
	wp_deregister_style( 'beer-output', EM_BEERMANAGE_URL.'assets/css/output.css' );
	wp_dequeue_style( 'beer-output' );
	
	wp_register_style( 'custom-beer-output', get_option('custom_style_url') );
	wp_enqueue_style( 'custom-beer-output' );

} else {
	wp_deregister_style( 'custom-beer-output', get_option('custom_style_url') );
	wp_dequeue_style( 'custome-beer-output' );
	
	wp_register_style( 'beer-output', EM_BEERMANAGE_URL.'assets/css/output.css' );
	wp_enqueue_style( 'beer-output' );
}


// Activation setup
register_activation_hook(__FILE__, 'em_beermanage_activation');

function em_beermanage_activation() {
    
	//actions to perform once on plugin activation go here   
}


// Uninstall setup
register_uninstall_hook(__FILE__, 'em_beermanage_uninstall');

function em_beermanage_uninstall() {    
	// actions to perform once on plugin deactivation go here
	
	//remove plugin css
	wp_deregister_style( 'beer-output', EM_BEERMANAGE_URL.'assets/css/output.css' );
	wp_dequeue_style( 'beer-output' );
	wp_deregister_style( 'custom-beer-output', get_option('custom_style_url') );
	wp_dequeue_style( 'custome-beer-output' );
	
	//remove custom settings
	unregister_setting( 'embm-group', 'disable_untappd' );  
	unregister_setting( 'embm-group', 'custom_style_url' );
	  
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
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=embm-settings">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}