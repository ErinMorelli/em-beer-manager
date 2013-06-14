<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: http://www.erinmorelli.com/
 * Description: A beer management plugin
 * Version: 0.1 (Alpha)
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



// Activation setup
register_activation_hook(__FILE__, 'em_beermanage_activation');

function em_beermanage_activation() {
    
	//actions to perform once on plugin activation go here   
}


// Uninstall setup
register_uninstall_hook(__FILE__, 'em_beermanage_uninstall');

function em_beermanage_uninstall() {    
	// actions to perform once on plugin deactivation go here	    
}