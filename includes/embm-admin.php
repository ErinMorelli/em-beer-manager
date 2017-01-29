<?php
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
 * @package EMBM\Admin
 */

// Include additional Admin functions
require EMBM_PLUGIN_DIR.'includes/admin/embm-admin-untappd.php';
require EMBM_PLUGIN_DIR.'includes/admin/embm-admin-actions.php';
require EMBM_PLUGIN_DIR.'includes/admin/embm-admin-settings.php';

// Set global admin page object
global $embm_admin_page;

/**
 * Loads admin CSS and JS
 *
 * @return void
 */
function EMBM_Admin_styles()
{
    // Load EMBM admin CSS
    wp_enqueue_style('embm-admin', EMBM_PLUGIN_URL.'assets/css/admin.css');
    wp_enqueue_style('wp-color-picker');

    // Load EMBM admin JS
    wp_enqueue_script(
        'embm-admin-script',
        EMBM_PLUGIN_URL.'assets/js/admin.js',
        array(
            'jquery-effects-core',
            'jquery-ui-tabs',
            'jquery-ui-slider',
            'wp-color-picker'
        )
    );

    // Set AJAX Nonce
    $ajax_nonce = wp_create_nonce(EMBM_AJAX_NONCE);

    // Share EMBM settings with admin script
    wp_localize_script(
        'embm-admin-script',
        'embm_settings',
        array(
              'ajax_nonce'          => $ajax_nonce,
              'plugin_url'          => EMBM_PLUGIN_URL,
              'options'             => get_option('embm_options'),
              'error'               => __('There was a problem with your request! Please try again later.', 'embm')
        )
    );
}

// Loads styles in WP admin
add_action('admin_enqueue_scripts', 'EMBM_Admin_styles');

/**
 * Add custom columns to EMBM post listing
 *
 * @param array $cols Existing WP post listing columns
 *
 * @return array Updated WP post listing columns
 */
function EMBM_Admin_columns($cols)
{
    // Set array of new columns
    $cols = array(
        'cb'                    => '<input type="checkbox" />',
        'id'                    => __('ID', 'embm'),
        'beer_num'              => __('Beer No.', 'embm'),
        'title'                 => __('Beer', 'embm'),
        'taxonomy-embm_group'   => __('Group', 'embm'),
        'taxonomy-embm_style'   => __('Style', 'embm'),
        'abv'                   => __('ABV', 'embm'),
        'ibu'                   => __('IBU', 'embm'),
        'avail'                 => __('Availability', 'embm')
    );

    // Add Untappd column, if enabled
    if (!EMBM_Core_Beer_disabled()) {
        $cols['untappd'] = __('Untappd', 'embm');
    }

    // Add released date column
    $cols['date'] = __('Released', 'embm');

    // Return new column array
    return $cols;
}

// Load custom columns
add_filter('manage_embm_beer_posts_columns', 'EMBM_Admin_columns');

/**
 * Defines custom admin column values
 *
 * @param string $column  Name of the column
 * @param int    $post_id EMBM post ID
 *
 * @return void
 */
function EMBM_Admin_Columns_values($column, $post_id)
{
    switch ($column) {
    case 'id':
        // Display beer post ID
        echo $post_id;
        break;
    case 'beer_num':
        // Get raw beer no
        $beer_num = get_post_meta($post_id, 'embm_beer_num', true);

        // Check if it's defined
        if ($beer_num != '') {
            // Display formatted beer number
            echo EMBM_Core_Beer_attr($post_id, 'beer_num');
        } else {
            echo '';
        }
        break;
    case 'abv':
        // Display formatted beer ABV
        echo EMBM_Core_Beer_attr($post_id, 'abv');
        break;
    case 'ibu':
        // Display beer IBU
        echo EMBM_Core_Beer_attr($post_id, 'ibu');
        break;
    case 'avail':
        // Display beer availability
        echo EMBM_Core_Beer_attr($post_id, 'avail');
        break;
    case 'untappd':
        // Get raw Untappd value from DB
        $untap = get_post_meta($post_id, 'embm_untappd', true);

        // If it's defined, add icon
        if ($untap != '') {
            // Get Untapped link
            $untap_link = EMBM_Core_Beer_attr($post_id, 'untappd');

            // Get EMBM options
            $options = get_option('embm_options');

            // Get Untappd icon
            $uticon = EMBM_PLUGIN_URL.'assets/img/ut-icon-'.$options['embm_untappd_icons'].'.png';

            // Display linked Untappd icon
            echo '<a href="'.$untap_link.'" target="_blank">';
            echo '<img src="'.$uticon.'" border="0" alt="Untappd" /></a>';
        } else {
            // Otherwise, column is blank
            echo '';
        }
        break;
    }
}

// Load custom column values
add_action('manage_embm_beer_posts_custom_column', 'EMBM_Admin_Columns_values', 10, 2);

/**
 * Make custom columns sortable
 *
 * @return array List of column names to make sortable
 */
function EMBM_Admin_Columns_sortable()
{
    return array(
        'title'     => 'title',
        'abv'       => 'abv',
        'ibu'       => 'ibu',
        'avail'     => 'avail',
        'date'      => 'date',
        'beer_num'  => 'beer_num'
    );
}

// Load sortable columns
add_filter('manage_edit-embm_beer_sortable_columns', 'EMBM_Admin_Columns_sortable');

/**
 * Sorts the custom sortable columns based on their data
 *
 * @param array $vars Array of column sorting data
 *
 * @return array Updated array of column sorting data
 */
function EMBM_Admin_Columns_orderby($vars)
{
    // Make sure we're viewing the EMBM post type
    if (isset($vars['post_type']) && 'embm_beer' == $vars['post_type']) {
        // Set numerical sort list
        $num_vars = array('beer_num', 'abv', 'ibu');

        // Set alphabetical sort list
        $alpha_vars = array('avail');

        // Make sure orderby is set
        if (isset($vars['orderby'])) {
            // Look for numerical value
            if (in_array($vars['orderby'], $num_vars)) {
                $vars = array_merge(
                    $vars,
                    array(
                        'meta_key'  => $vars['orderby'],
                        'orderby'   => 'meta_value_num'
                    )
                );
            }

            // Look for alphabetical value
            if (in_array($vars['orderby'], $alpha_vars)) {
                $vars = array_merge(
                    $vars,
                    array(
                        'meta_key'  => $vars['orderby'],
                        'orderby'   => 'meta_value'
                    )
                );
            }
        }
    }

    return $vars;
}

/**
 * Load custom sortable columns
 *
 * @return void
 */
function EMBM_Admin_Columns_load()
{
    add_filter('request', 'EMBM_Admin_Columns_orderby');
}

// Only load custom columns in the admin.
add_action('load-edit.php', 'EMBM_Admin_Columns_load');

/**
 * Add custom contextual help menu to admin
 *
 * @return void
 */
function EMBM_Admin_help()
{
    // Get global page vars
    global $embm_admin_page;
    $screen = get_current_screen();

    // Check if current screen is admin page
    if ($screen->id != $embm_admin_page) {
        return;
    }

    // Get default help data
    $default_help = EMBM_Plugin_help();

    // Add Untappd tabs
    $screen->add_help_tab($default_help['untappd']);
    $screen->add_help_tab($default_help['untappd_id']);
    $screen->add_help_tab($default_help['untappd_limit']);

    // Settings FAQ help tab
    $screen->add_help_tab(
        array(
            'id'       => 'embm-settings-faq',
            'title'    => __('Settings FAQ', 'embm'),
            'content'  => '<p><strong>'.
                __('I don\'t want to show that big grey box of information, how do I get rid of it?', 'embm').
                '</strong></p><p>'.
                __('For each of the different displays there is the option to "Hide extras info" and "Hide extras info". Check both of these to hide the grey box.', 'embm').
                '</p><p><strong>'.
                __('What\'s the difference between "profile" and "extras"?', 'embm').
                '</strong></p><p>'.
                __('The "profile" refers to all the content in the "Beer Profile" information stored for each beer. This includes ABV, IBU, Hops, Malts, Additions, and Yeast.', 'embm').
                '</p><p>'.
                __('The "extras" setting refers to the "Extra Beer Information" content stored for each beer. This includes Beer Number, Availability, and Additional Notes.', 'embm').
                '</p>'
        )
    );

    // Help sidebar
    $screen->set_help_sidebar($default_help['sidebar']);
}
