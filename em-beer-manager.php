<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: http://erinmorelli.com/projects/em-beer-manager
 * Description: Catalog and display your beers with WordPress. Integrates very simply with Untappd for individual beer checkins. Great for everyone from home brewers to professional breweries!
 * Version: 1.10.0
 * Author: Erin Morelli
 * Author URI: http://erinmorelli.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: embm
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2013-2016, Erin Morelli.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package EMBM\Plugin
 */


// Define plugin file paths
define('EMBM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMBM_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('PLUGINDIR')) {
    define('PLUGINDIR', 'wp-content/plugins');
}


/**
 * Loads EMBM plugin files
 *
 * @return void
 */
function EMBM_Plugin_load()
{
    // Load admin files only in admin
    if (is_admin()) {
        include_once EMBM_PLUGIN_DIR.'includes/embm-admin.php';
    }

    // Load core and output files
    include_once EMBM_PLUGIN_DIR.'includes/embm-core.php';
    include_once EMBM_PLUGIN_DIR.'includes/embm-output.php';

    // Iteratively load any widgets
    foreach (scandir(EMBM_PLUGIN_DIR.'includes/widgets') as $filename) {
        // Set widgets path
        $path = EMBM_PLUGIN_DIR.'includes/widgets/' . $filename;

        // If the PHP file exists, load it
        if (is_file($path) && preg_match('/embm-widget-.*\.php$/', $filename)) {
            include $path;
        }
    }
}

// Initialize load
EMBM_Plugin_load();

// Plugin localization
load_plugin_textdomain(
    'embm',
    PLUGINDIR.'/em-beer-manager/languages',
    'em-beer-manager/languages'
);


/**
 * Plugin activation setup
 *
 * @return void
 */
function EMBM_Plugin_activation()
{
    // Set current version
    $embm_curr_version = '1.10.0';

    // Define version key name
    if (!defined('EMBM_VERSION_KEY')) {
        define('EMBM_VERSION_KEY', 'embm_version');
    }

    // Create new version option
    if (!defined('EMBM_VERSION_NUM')) {
        define('EMBM_VERSION_NUM', $embm_curr_version);

        // Store version in WP database
        add_option(EMBM_VERSION_KEY, EMBM_VERSION_NUM);
    }

    // Update the version value
    if (get_option(EMBM_VERSION_KEY) != $embm_curr_version) {
        update_option(EMBM_VERSION_KEY, $embm_curr_version);
    }

    // Refresh permalinks
    flush_rewrite_rules();

    // Set default settings options
    $defaults = array(
        'embm_untappd_check'    => '',
        'embm_untappd_brewery'    => '',
        'embm_css_url'        => '',
        'embm_group_slug'    => 'group'
    );

    update_option('embm_options', $defaults);
}

// Set activation hook
register_activation_hook(__FILE__, 'EMBM_Plugin_activation');


/**
 * Plugin deactivation setup
 *
 * @return void
 */
function EMBM_Plugin_deactivation()
{
    // Refresh permalinks
    flush_rewrite_rules();
}

// Set deactivation hook
register_deactivation_hook(__FILE__, 'EMBM_Plugin_deactivation');


/**
 * Add plugin listing action link
 *
 * @param array $links Existing action links
 *
 * @return array       Updated action links
 */
function EMBM_Plugin_links($links)
{
    // Define settings link HTML
    $settings_link = '<a href="' . get_bloginfo('wpurl');
    $settings_link .= '/wp-admin/admin.php?page=embm-settings">';
    $settings_link .= __('Settings', 'embm') . '</a>';

    // Add to to existing links array
    return array_merge($links, array($settings_link));
}

// Set plugin links filter
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'EMBM_Plugin_links');


/**
 * Load plugin output CSS stylesheet
 *
 * @return void
 */
function EMBM_Plugin_styles()
{
    // Get custom CSS URL from DB
    $style_option = get_option('embm_options');
    $has_custom_css = esc_url($style_option['embm_css_url']);

    // If a file is defined, use it
    if ($has_custom_css != '') {
        // Remove existing output stylesheet
        wp_deregister_style('embm-output', EMBM_PLUGIN_URL.'assets/css/output.css');
        wp_dequeue_style('embm-output');

        // Add custom output stylesheet
        wp_register_style('custom-embm-output', $has_custom_css);
        wp_enqueue_style('custom-embm-output');
    } else {
        // Remove custom output stylesheet
        wp_deregister_style('custom-embm-output', $has_custom_css);
        wp_dequeue_style('custom-embm-output');

        // Add default stylesheet
        wp_register_style('embm-output', EMBM_PLUGIN_URL.'assets/css/output.css');
        wp_enqueue_style('embm-output');
    }

    // Add widget stylesheet
    wp_register_style('embm-widget', EMBM_PLUGIN_URL.'assets/css/widgets.css');
    wp_enqueue_style('embm-widget');
}

// Enqueue plugin styles
add_action('wp_enqueue_scripts', 'EMBM_Plugin_styles');


/**
 * Update pre 1.7.0 databases to new naming
 *
 * @return void
 */
function EMBM_Plugin_upgrade()
{
    // Get global WP database reference
    global $wpdb;

    // Get upgrade status from DB
    $upgrade = get_option('embm_db_upgrade');

    // Get current EMBM version
    $curr_version = floatval(get_option('embm_version'));

    // Set upgrade version
    $new_version = 1.7;

    // Do version 1.6 -> 1.7 upgrade
    if (($curr_version >= $new_version) && (!$upgrade)) {
        // Rename style taxonomy
        $wpdb->query(
            "
            UPDATE $wpdb->term_taxonomy
            SET taxonomy = 'embm_style'
            WHERE taxonomy = 'style'
            "
        );
        // Rename beer taxonomy
        $wpdb->query(
            "
            UPDATE $wpdb->posts
            SET post_type = 'embm_beer'
            WHERE post_type = 'beer'
            "
        );

        // Save upgrade status to DB
        update_option('embm_db_upgrade', true);
    }

    // Check for old DB content
    delete_option('embm_comment_change');

    // Update DB data format for upgrade
    if ($upgrade == 'true') {
        update_option('embm_db_upgrade', true);
    } elseif (!$upgrade) {
        update_option('embm_db_upgrade', false);
    }

    // Get styles loaded option
    $loaded = get_option('embm_styles_loaded');

    // Update DB data format for styles
    if ($loaded == 'true') {
        update_option('embm_styles_loaded', true);
    } elseif (!$loaded) {
        update_option('embm_styles_loaded', false);
    }

}

// Initialize plugin update
add_action('init', 'EMBM_Plugin_upgrade');
