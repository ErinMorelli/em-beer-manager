<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: https://www.erinmorelli.com/projects/em-beer-manager
 * Description: Manage and display your beers with WordPress. Integrates simply with Untappd and Untappd for Business. Great for everyone from home brewers to professional breweries!
 * Version: 3.2.3
 * Author: Erin Morelli
 * Author URI: https://www.erinmorelli.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: em-beer-manager
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2013-2021, Erin Morelli.
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

// Define plugin constants
define('EMBM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMBM_PLUGIN_URL', plugin_dir_url(__FILE__));

// WP general options
define('EMBM_OPTIONS', 'embm_options');
define('EMBM_DB_VERSION', 'embm_db_upgrade');
define('EMBM_STYLES_LOADED', 'embm_styles_loaded');

// Untappd/UTFB options
define('EMBM_UNTAPPD_BREWERY', 'embm_untappd_brewery_id');
define('EMBM_UNTAPPD_TOKEN', 'embm_untappd_token');
define('EMBM_UTFB_CREDENTIALS', 'embm_utfb_credentials');

// Widget options
define('EMBM_WIDGET_BEER_LIST', 'embm_beer_list_widget');
define('EMBM_WIDGET_RECENT_UNTAPPD', 'embm_recent_untappd_widget');

// Post Types
define('EMBM_BEER', 'embm_beer');
define('EMBM_STYLE', 'embm_style');
define('EMBM_GROUP', 'embm_group');
define('EMBM_MENU', 'embm_menu');

// Post meta
define('EMBM_BEER_META', 'embm_meta');
define('EMBM_BEER_META_UNTAPPD', 'embm_meta_untappd');
define('EMBM_BEER_META_UTFB', 'embm_meta_utfb');

// CSS/JS Files
define('EMBM_WIDGET_CSS', 'embm-widget');
define('EMBM_OUTPUT_CSS', 'embm-output');
define('EMBM_CUSTOM_CSS', 'custom-embm-output');
define('EMBM_ADMIN_CSS', 'embm-admin');
define('EMBM_ADMIN_JS', 'embm-admin-script');

// Set up plugin directory
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
    // Check for outdated PHP version
    if (floatval(phpversion()) < 5.3) {
        // Display incorrect version error
        add_action('admin_notices', 'EMBM_Plugin_php');

        // Deactivate plugin on error and exit
        deactivate_plugins(plugin_basename(__FILE__), true);
        return;
    }

    // Set current version
    $embm_curr_version = '3.2.3';

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

    // Check if this is a new version
    if (get_option(EMBM_VERSION_KEY) != $embm_curr_version) {
        // Do any upgrades
        if (!function_exists('EMBM_Upgrade_check')) {
            include_once EMBM_PLUGIN_DIR.'includes/embm-upgrades.php';
            EMBM_Upgrade_check();
        }

        // Update version key
        update_option(EMBM_VERSION_KEY, $embm_curr_version);
    }

    // Load included files
    include_once EMBM_PLUGIN_DIR.'includes/embm-core.php';
    include_once EMBM_PLUGIN_DIR.'includes/embm-output.php';
    include_once EMBM_PLUGIN_DIR.'includes/embm-admin.php';

    // Iteratively load any widgets
    foreach (scandir(EMBM_PLUGIN_DIR.'includes/widgets') as $filename) {
        // Set widgets path
        $path = EMBM_PLUGIN_DIR.'includes/widgets/' . $filename;

        // If the PHP file exists, load it
        if (is_file($path) && preg_match('/embm-widget-.*\.php$/', $filename)) {
            include $path;
        }
    }

    // Plugin localization
    load_plugin_textdomain('em-beer-manager', false, plugin_basename(dirname(__FILE__)).'/languages');
}

// Initial plugin load
add_action('plugins_loaded', 'EMBM_Plugin_load', 10);

/**
 * Plugin activation setup
 *
 * @return void
 */
function EMBM_Plugin_activate()
{
    // Set default settings options
    $defaults = array(
        'embm_untappd_check'            => '',
        'embm_untappd_icons'            => '2',
        'embm_untappd_rating_format'    => '3',
        'embm_untappd_rating_color'     => '#FFCC00',
        'embm_untappd_rating_opacity'   => '25',
        'embm_css_url'                  => '',
        'embm_group_slug'               => 'group',
        'embm_reviews_count_single'     => '5'
    );

    // Get any existing options
    $options = get_option(EMBM_OPTIONS);

    // If options exist, fill in any missing with defaults
    if (is_array($options)) {
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
    } else {
        $options = $defaults;
    }

    // Save the updated options
    update_option(EMBM_OPTIONS, $options);

    // Load core files
    if (!function_exists('EMBM_Core_beer')) {
        include_once EMBM_PLUGIN_DIR.'includes/embm-core.php';
    }

    // Load CPTs
    EMBM_Core_beer();
    EMBM_Core_styles();
    EMBM_Core_group();

    // Refresh permalinks
    flush_rewrite_rules();
}

// Set activation hook
register_activation_hook(__FILE__, 'EMBM_Plugin_activate');

/**
 * Plugin deactivation setup
 *
 * @return void
 */
function EMBM_Plugin_deactivate()
{
    // Refresh permalinks
    flush_rewrite_rules();
}

// Set deactivation hook
register_deactivation_hook(__FILE__, 'EMBM_Plugin_deactivate');

/**
 * Plugin uninstallation setup
 *
 * @return void
 */
function EMBM_Plugin_uninstall()
{
    // Get global post types variable
    global $wp_post_types;

    // Remove EMBM post type
    if (isset($wp_post_types[EMBM_BEER])) {
        unset($wp_post_types[EMBM_BEER]);
    }

    // Set up EMBM post query
    $args = array(
        'post_type'     => EMBM_BEER,
        'post_status'   => array(
            'publish',
            'pending',
            'draft',
            'auto-draft',
            'future',
            'private',
            'inherit',
            'trash'
        )
    );

    // Get all existing EMBM posts
    $posts = get_posts($args);

    // Iteratively remove existing EMBM posts
    if (is_array($posts)) {
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

    // Set EMBM taxonomies
    $tax = array(EMBM_GROUP, EMBM_STYLE, EMBM_MENU);

    // Get global WP taxonomies
    global $wp_taxonomies;

    // Remove all EMBM taxonomies
    foreach ($tax as $taxonomy) {
        // Set taxonomy
        register_taxonomy($taxonomy);

        // Find all terms for taxonomy
        $terms = get_terms($taxonomy, array('hide_empty' => 0));

        // Iteratively remove all terms for taxonomy
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy);
        }

        // Remove taxonomy from WP
        unset($wp_taxonomies[$taxonomy]);
    }

    // Remove EMBM widget CSS
    wp_deregister_style(EMBM_WIDGET_CSS);
    wp_dequeue_style(EMBM_WIDGET_CSS);

    // Remove EMBM output CSS
    wp_deregister_style(EMBM_OUTPUT_CSS);
    wp_dequeue_style(EMBM_OUTPUT_CSS);

    // Remove EMBM admin CSS
    wp_deregister_style(EMBM_ADMIN_CSS);
    wp_dequeue_style(EMBM_ADMIN_CSS);

    // Retrieve custom CSS info
    $get_style_option = get_option(EMBM_OPTIONS);
    $get_custom_css = $style_option['embm_css_url'];

    // Remove custom CSS
    wp_deregister_style(EMBM_CUSTOM_CSS);
    wp_dequeue_style(EMBM_CUSTOM_CSS);

    // Remove EMBM admin JS
    wp_deregister_script(EMBM_ADMIN_JS);
    wp_dequeue_script(EMBM_ADMIN_JS);

    // Remove EMBM settings
    delete_option(EMBM_VERSION_KEY);
    delete_option(EMBM_OPTIONS);
    delete_option(EMBM_DB_VERSION);
    delete_option(EMBM_STYLES_LOADED);
    delete_option(EMBM_UNTAPPD_BREWERY);
    delete_option(EMBM_UNTAPPD_TOKEN);
    delete_option('widget_'.EMBM_WIDGET_BEER_LIST);
    delete_option('widget_'.EMBM_WIDGET_RECENT_UNTAPPD);
}

// Set uninstall hook
register_uninstall_hook(__FILE__, 'EMBM_Plugin_uninstall');

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
    $settings_link .= __('Settings', 'em-beer-manager') . '</a>';

    // Add to to existing links array
    return array_merge(array($settings_link), $links);
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
    $style_option = get_option(EMBM_OPTIONS);
    $has_custom_css = esc_url($style_option['embm_css_url']);

    // If a file is defined, use it
    if ($has_custom_css != '') {
        // Remove existing output stylesheet
        wp_deregister_style(EMBM_OUTPUT_CSS);
        wp_dequeue_style(EMBM_OUTPUT_CSS);

        // Add custom output stylesheet
        wp_register_style(EMBM_CUSTOM_CSS, $has_custom_css);
        wp_enqueue_style(EMBM_CUSTOM_CSS);
    } else {
        // Remove custom output stylesheet
        wp_deregister_style(EMBM_CUSTOM_CSS);
        wp_dequeue_style(EMBM_CUSTOM_CSS);

        // Add default stylesheet
        wp_register_style(EMBM_OUTPUT_CSS, EMBM_PLUGIN_URL.'assets/css/output.css');
        wp_enqueue_style(EMBM_OUTPUT_CSS);
    }

    // Add widget stylesheet
    wp_register_style(EMBM_WIDGET_CSS, EMBM_PLUGIN_URL.'assets/css/widgets.css');
    wp_enqueue_style(EMBM_WIDGET_CSS);

    // Add WP Dashicons
    wp_enqueue_style('dashicons');
}

// Enqueue plugin styles
add_action('wp_enqueue_scripts', 'EMBM_Plugin_styles');

/**
 * Displays PHP upgrade notification
 *
 * @return void
 */
function EMBM_Plugin_php()
{
    echo '<div class="notice notice-error is-dismissible"><p>';
    printf(
        __('%s only supports PHP version %s or higher. Please upgrade your PHP to use this plugin.', 'em-beer-manager'),
        sprintf('<strong>%s</strong>', __('EM Beer Manager', 'em-beer-manager')),
        '5.3'
    );
    echo '</p></div>';

    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
}

/**
 * Returns array of default plugin contextual help
 *
 * @return array
 */
function EMBM_Plugin_help()
{
    return array(
        'untappd'       => array(
            'id'        => 'embm-untappd-integration',
            'title'     => __('Untappd Integration', 'em-beer-manager'),
            'content'   => '<p>'.
                __('Checking the "Disable site-wide integration" option under the EM Beer Manager "Untappd settings", will completely disable all Untappd functionality, including the Recent Check-ins widget, ratings, check-ins, check-in buttons, and any Untappd-related Labs features.', 'em-beer-manager').
                '</p><p>'.
                __('You can disable the Untappd check-in button for an individual beer by simply leaving the "Beer ID" setting empty. Beers that have an active check-in button will display a square Untappd icon next to their entry on the Beers admin page.', 'em-beer-manager').
                '</p><p>'.
                __('You can display Untappd beer ratings and recent check-ins if you are logged in to Untappd. Ratings are shown in all beer views, including shortcodes. Check-ins are only displayed on single beer pages. This option can be disabled for specific beers. You can specify how many check-ins to show.', 'em-beer-manager').
                '</p></p>'.
                __('Data from Untappd for ratings and check-ins is refreshed automatically, or can be refreshed manually. We do not recommend doing this often as Untappd places a limit on how many API calls can be made per hour.', 'em-beer-manager').
                '</p>'
        ),
        'untappd_id'    => array(
            'id'        => 'embm-untappd-beer-id',
            'title'     => __('Untappd Beer ID', 'em-beer-manager'),
            'content'   => '<p>'.
                __('Find your Untappd Beer ID by visiting your beer\'s official page. The URL will be formatted like this', 'em-beer-manager').
                ':</p><p><code>https://untappd.com/b/the-alchemist-heady-topper/<strong>4691</strong></code></p><p>'.
                __('The string of numbers at the end of the URL is your beer\'s ID.', 'em-beer-manager').
                '</p>'
        ),
        'untappd_limit' => array (
            'id'        => 'embm-untappd-api-ratelimit',
            'title'     => __('API Rate-Limit', 'em-beer-manager'),
            'content'   => '<p>'.
                sprintf(
                    __('From the %s', 'em-beer-manager').':',
                    sprintf(
                        '<a href="https://untappd.com/api/docs" target="_blank">%s</a>',
                        __('Untappd API documentation', 'em-beer-manager')
                    )
                ).'</p><p><blockquote><em>"'.
                __('All API applications are rate-limited to protect against abuse and keep the platform healthy. The default limit for API access is 100 calls per hour per key.', 'em-beer-manager').'"</em></blockquote></p><p>'.
                __('If you see this message, it means your authenticated API session has reached this limit and any actions that require an API call will be limited until your access is reset in the next hour.', 'em-beer-manager').'</p><p>'.
                __('In most cases you should still be able to use all of the Untappd features with cached data, but rare cases may display a rate-limit warning messages when no cached data is available.', 'em-beer-manager').
                '</p>'
        ),
        'sidebar'       => '<p><strong>' . __('For more information', 'em-beer-manager') . ':</strong></p>' .
            '<p><a href="https://www.erinmorelli.com/projects/em-beer-manager" target="_blank">' . __('Plugin Website', 'em-beer-manager') . '</a></p>' .
            '<p><a href="https://wordpress.org/support/plugin/em-beer-manager" target="_blank">' . __('Support Forums', 'em-beer-manager') . '</a></p>'
    );
}
