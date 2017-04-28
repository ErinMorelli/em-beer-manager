<?php
/**
 * Plugin Name: EM Beer Manager
 * Plugin URI: https://www.erinmorelli.com/projects/em-beer-manager
 * Description: Catalog and display your beers with WordPress. Integrates very simply with Untappd for individual beer check-ins. Great for everyone from home brewers to professional breweries!
 * Version: 3.0.5
 * Author: Erin Morelli
 * Author URI: https://www.erinmorelli.com/
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
    // Set current version
    $embm_curr_version = '3.0.5';

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

    // Load core and output files
    include_once EMBM_PLUGIN_DIR.'includes/embm-core.php';
    include_once EMBM_PLUGIN_DIR.'includes/embm-output.php';

    // Load admin files only in admin
    if (is_admin()) {
        include_once EMBM_PLUGIN_DIR.'includes/embm-admin.php';
    }

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
    load_plugin_textdomain('embm', false, plugin_basename(dirname(__FILE__)).'/languages');

    // Do any upgrades
    if (!function_exists('EMBM_Upgrade_check')) {
        include_once EMBM_PLUGIN_DIR.'includes/embm-upgrades.php';
        EMBM_Upgrade_check();
    }
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
    $options = get_option('embm_options');

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
    update_option('embm_options', $options);

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
    if (isset($wp_post_types['embm_beer'])) {
        unset($wp_post_types['embm_beer']);
    }

    // Set up EMBM post query
    $args = array(
        'post_type'     =>'embm_beer',
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
    $tax = array('embm_group', 'embm_style');

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
    wp_deregister_style('embm-widget');
    wp_dequeue_style('embm-widget');

    // Remove EMBM output CSS
    wp_deregister_style('embm-output');
    wp_dequeue_style('embm-output');

    // Remove EMBM admin CSS
    wp_deregister_style('embm-admin');
    wp_dequeue_style('embm-admin');

    // Retrieve custom CSS info
    $get_style_option = get_option('embm_options');
    $get_custom_css = $style_option['embm_css_url'];

    // Remove custom CSS
    wp_deregister_style('custom-embm-output');
    wp_dequeue_style('custom-embm-output');

    // Remove EMBM admin JS
    wp_deregister_script('embm-admin-script');
    wp_dequeue_script('embm-admin-script');

    // Remove EMBM settings
    delete_option(EMBM_VERSION_KEY);
    delete_option('embm_options');
    delete_option('embm_db_upgrade');
    delete_option('embm_styles_loaded');
    delete_option('widget_embm_beer_list_widget');
    delete_option('widget_embm_recent_untappd_widget');
    delete_option('embm_untappd_brewery_id');
    delete_option('embm_untappd_token');
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
    $settings_link .= __('Settings', 'embm') . '</a>';

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
    $style_option = get_option('embm_options');
    $has_custom_css = esc_url($style_option['embm_css_url']);

    // If a file is defined, use it
    if ($has_custom_css != '') {
        // Remove existing output stylesheet
        wp_deregister_style('embm-output');
        wp_dequeue_style('embm-output');

        // Add custom output stylesheet
        wp_register_style('custom-embm-output', $has_custom_css);
        wp_enqueue_style('custom-embm-output');
    } else {
        // Remove custom output stylesheet
        wp_deregister_style('custom-embm-output');
        wp_dequeue_style('custom-embm-output');

        // Add default stylesheet
        wp_register_style('embm-output', EMBM_PLUGIN_URL.'assets/css/output.css');
        wp_enqueue_style('embm-output');
    }

    // Add widget stylesheet
    wp_register_style('embm-widget', EMBM_PLUGIN_URL.'assets/css/widgets.css');
    wp_enqueue_style('embm-widget');

    // Add WP Dashicons
    wp_enqueue_style('dashicons');
}

// Enqueue plugin styles
add_action('wp_enqueue_scripts', 'EMBM_Plugin_styles');

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
            'title'     => __('Untappd Integration', 'embm'),
            'content'   => '<p>'.
                __('Checking the "Disable site-wide integration" option under the EM Beer Manager "Untappd settings", will completely disable all Untappd functionality, including the Recent Check-ins widget, ratings, check-ins, check-in buttons, and any Untappd-related Labs features.', 'embm').
                '</p><p>'.
                __('You can disable the Untappd check-in button for an individual beer by simply leaving the "Beer ID" setting empty. Beers that have an active check-in button will display a square Untappd icon next to their entry on the Beers admin page.', 'embm').
                '</p><p>'.
                __('You can display Untappd beer ratings and recent check-ins if you are logged in to Untappd. Ratings are shown in all beer views, including shortcodes. Check-ins are only displayed on single beer pages. This option can be disabled for specific beers. You can specify how many check-ins to show.', 'embm').
                '</p></p>'.
                __('Data from Untappd for ratings and check-ins is refreshed automatically, or can be refreshed manually. We do not recommend doing this often as Untappd places a limit on how many API calls can be made per hour.', 'embm').
                '</p>'
        ),
        'untappd_id'    => array(
            'id'        => 'embm-untappd-beer-id',
            'title'     => __('Untappd Beer ID', 'embm'),
            'content'   => '<p>'.
                __('Find your Untappd Beer ID by visiting your beer\'s official page. The URL will be formatted like this', 'embm').
                ':</p><p><code>https://untappd.com/b/the-alchemist-heady-topper/<strong>4691</strong></code></p><p>'.
                __('The string of numbers at the end of the URL is your beer\'s ID.', 'embm').
                '</p>'
        ),
        'untappd_limit' => array (
            'id'        => 'embm-untappd-api-ratelimit',
            'title'     => __('API Rate-Limit', 'embm'),
            'content'   => '<p>'.
                sprintf(
                    __('From the %s', 'embm').':',
                    sprintf(
                        '<a href="https://untappd.com/api/docs" target="_blank">%s</a>',
                        __('Untappd API documentation', 'embm')
                    )
                ).'</p><p><blockquote><em>"'.
                __('All API applications are rate-limited to protect against abuse and keep the platform healthy. The default limit for API access is 100 calls per hour per key.', 'embm').'"</em></blockquote></p><p>'.
                __('If you see this message, it means your authenticated API session has reached this limit and any actions that require an API call will be limited until your access is reset in the next hour.', 'embm').'</p><p>'.
                __('In most cases you should still be able to use all of the Untappd features with cached data, but rare cases may display a rate-limit warning messages when no cached data is available.', 'embm').
                '</p>'
        ),
        'sidebar'       => '<p><strong>' . __('For more information', 'embm') . ':</strong></p>' .
            '<p><a href="https://www.erinmorelli.com/projects/em-beer-manager" target="_blank">' . __('Plugin Website', 'embm') . '</a></p>' .
            '<p><a href="https://wordpress.org/support/plugin/em-beer-manager" target="_blank">' . __('Support Forums', 'embm') . '</a></p>'
    );
}
