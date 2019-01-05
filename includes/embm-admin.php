<?php
/**
 * Copyright (c) 2013-2019, Erin Morelli.
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
require EMBM_PLUGIN_DIR.'includes/admin/integrations/embm-integrations-untappd.php';
require EMBM_PLUGIN_DIR.'includes/admin/integrations/embm-integrations-utfb.php';
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
    wp_enqueue_style(EMBM_ADMIN_CSS, EMBM_PLUGIN_URL.'assets/css/admin.css');
    wp_enqueue_style('wp-color-picker');

    // Load EMBM admin JS
    wp_enqueue_script(
        EMBM_ADMIN_JS,
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

    // Set sync confirmation text
    $confirm = __('Are you sure you want to continue?', 'em-beer-manager');
    $sconfirm = __(
        'WARNING: This will override any changes you have made to %s. %s',
        'em-beer-manager'
    );
    $uconfirm = __(
        'WARNING: This will override any custom menu-beer associations you have made '.
        'as well as any changes made to Untappd for Business-linked menus. %s',
        'em-beer-manager'
    );
    $urmconfirm = __(
        'WARNING: Enabling this will permanently remove any Untappd for Business-linked menus not found during the sync. %s',
        'em-beer-manager'
    );
    $rmconfirm = __(
        'WARNING: Enabling this will move any Untappd-linked beers not found during the sync to the trash. %s',
        'em-beer-manager'
    );

    // Share EMBM settings with admin script
    wp_localize_script(
        EMBM_ADMIN_JS,
        'embm_settings',
        array(
              'ajax_nonce'           => $ajax_nonce,
              'plugin_url'           => EMBM_PLUGIN_URL,
              'options'              => get_option(EMBM_OPTIONS),
              'error'                => __('There was a problem with your request! Please try again later.', 'em-beer-manager'),
              'utfb_resources'       => array_keys($GLOBALS['EMBM_UTFB_RESOURCE_MAP']),
              'utfb_section_notice'  => __('Select an option from the dropdown in the section above to enable.', 'em-beer-manager'),
              'sync_confirm_plural'  => sprintf($sconfirm, __('ANY Untappd-linked beers', 'em-beer-manager'), $confirm),
              'sync_confirm_single'  => sprintf($sconfirm, __('this beer', 'em-beer-manager'), $confirm),
              'sync_confirm_utfb'    => sprintf($uconfirm, $confirm),
              'sync_confirm_utfb_rm' => sprintf($urmconfirm, $confirm),
              'sync_confirm_rm'      => sprintf($rmconfirm, $confirm)
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
        'id'                    => __('ID', 'em-beer-manager'),
        'beer_num'              => __('Beer No.', 'em-beer-manager'),
        'title'                 => __('Beer', 'em-beer-manager'),
        'taxonomy-'.EMBM_STYLE  => __('Style', 'em-beer-manager'),
        'taxonomy-'.EMBM_GROUP  => __('Group(s)', 'em-beer-manager'),
        'taxonomy-'.EMBM_MENU   => __('Menu(s)', 'em-beer-manager'),
        'abv'                   => __('ABV', 'em-beer-manager'),
        'ibu'                   => __('IBU', 'em-beer-manager'),
        'avail'                 => __('Availability', 'em-beer-manager')
    );

    // Add Untappd columns, if enabled
    if (!EMBM_Core_Beer_disabled()) {
        $cols['untappd'] = __('Untappd', 'em-beer-manager');
        $cols['sync_exclude'] = __('Exclude from Sync', 'em-beer-manager');
    }

    // Add released date column
    $cols['date'] = __('Released', 'em-beer-manager');

    // Return new column array
    return $cols;
}

// Load custom columns
add_filter('manage_'.EMBM_BEER.'_posts_columns', 'EMBM_Admin_columns');

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
        // Display formatted beer number
        echo EMBM_Core_Beer_meta($post_id, 'beer_num');
        break;
    case 'abv':
        // Display formatted beer ABV
        echo EMBM_Core_Beer_meta($post_id, 'abv');
        break;
    case 'ibu':
        // Display beer IBU
        echo EMBM_Core_Beer_meta($post_id, 'ibu');
        break;
    case 'avail':
        // Display beer availability
        echo EMBM_Core_Beer_meta($post_id, 'avail');
        break;
    case 'untappd':
        // Get raw Untappd value from DB
        $untap = EMBM_Core_Beer_meta($post_id, 'untappd_id');

        // If it's defined, add icon
        if (!is_null($untap) && $untap != '') {
            // Get Untapped link
            $untap_link = EMBM_Core_Beer_meta($post_id, 'untappd_url');

            // Get EMBM options
            $options = get_option(EMBM_OPTIONS);

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
    case 'sync_exclude':
        // Get sync exclusion data
        $sync_exclude = EMBM_Core_Beer_meta($post_id, 'sync_exclude');

        // Show checkmark only if it's excluded
        if (null == $sync_exclude) {
            echo '';
        } else {
            echo '<span class="dashicons dashicons-yes"></span>';
        }
        break;
    }
}

// Load custom column values
add_action('manage_'.EMBM_BEER.'_posts_custom_column', 'EMBM_Admin_Columns_values', 10, 2);

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
add_filter('manage_edit-'.EMBM_BEER.'_sortable_columns', 'EMBM_Admin_Columns_sortable');

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
    if (isset($vars['post_type']) && EMBM_BEER == $vars['post_type']) {
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

    // Untappd for Business help tab
    $screen->add_help_tab(
        array(
            'id'      => 'embm-utfb-integration',
            'title'   => __('Untappd for Business Integration', 'em-beer-manager'),
            'content' => '<p><strong>'.
                __('Why is an Untappd account required in addition to an UTFB account?', 'em-beer-manager').
                '</strong></p><p>'.
                __('Untappd for Business (UTFB) account credentials do not work with Untappd\'s API. In order to link Untappd data to beers imported from UTFB, Untappd API access is also needed.', 'em-beer-manager').
                '</p><p>'.
                __('An Untappd brewery account is not required to work with UTFB. A standard user account will work.', 'em-beer-manager').
                '</p><p><strong>'.
                __('Where do I find my API key?', 'em-beer-manager').
                '</strong></p><p>'.
                sprintf(
                    __('You can find your API key under the "API Access Tokens" section %s.', 'em-beer-manager'),
                    sprintf(
                        '<a href="https://business.untappd.com/api_tokens" target="_blank">%s</a>',
                        __('here', 'em-beer-manager')
                    )
                ).
                '</p>'
        )
    );

    // Add Syncing help tab
    $screen->add_help_tab(
        array (
            'id'        => 'embm-untappd-api-sync',
            'title'     => __('Syncing', 'em-beer-manager'),
            'content'   => '<p>'.
                __('Use the "Sync" feature to update the beers that you have imported from Untappd or Untappd for Business (UTFB).', 'em-beer-manager').'</p><p>'.
                __('This will pull in any changes you might have made on Untappd or UTFB, but will override any changes you have made to the imported beers or menus via WordPress.', 'em-beer-manager').'</p><p>'.
                __('Use the "Delete Missing" feature to run a sync that will delete any of your Untappd-linked WordPress beers or UTFB-linked menus that no longer exist on Untappd or UTFB. This does not make any changes to your Untappd or UTFB accounts, only to your data in WordPress.', 'em-beer-manager').'</p><p>'.
                '<strong>**'.__('IMPORTANT', 'em-beer-manager').'**</strong><br />'.
                __('The "Delete Missing" feature works with ALL beers or menus that are attributed to an Untappd or UTFB ID number, not just beers or menus that were imported. This means that beers and menus added manually with an Untappd or UTFB ID associated with them WILL be affected by the "Delete Missing" feature. You can choose to override this functionality by checking the "Exclude from Sync" checkbox for each individual beer or menu on its respective edit page.', 'em-beer-manager').
                '</p>'
        )
    );

    // Settings FAQ help tab
    $screen->add_help_tab(
        array(
            'id'       => 'embm-settings-faq',
            'title'    => __('Settings FAQ', 'em-beer-manager'),
            'content'  => '<p><strong>'.
                __('I don\'t want to show that big grey box of information, how do I get rid of it?', 'em-beer-manager').
                '</strong></p><p>'.
                __('For each of the different displays there is the option to "Hide profile info" and "Hide extras info". Check both of these to hide the grey box.', 'em-beer-manager').
                '</p><p><strong>'.
                __('What\'s the difference between "profile" and "extras"?', 'em-beer-manager').
                '</strong></p><p>'.
                __('The "profile" refers to all the content in the "Beer Profile" information stored for each beer. This includes ABV, IBU, Hops, Malts, Additions, and Yeast.', 'em-beer-manager').
                '</p><p>'.
                __('The "extras" setting refers to the "Extra Beer Information" content stored for each beer. This includes Beer Number, Availability, and Additional Notes.', 'em-beer-manager').
                '</p>'
        )
    );

    // Help sidebar
    $screen->set_help_sidebar($default_help['sidebar']);
}
