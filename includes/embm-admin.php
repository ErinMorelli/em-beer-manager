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
    wp_enqueue_style(
        'embm-admin',
        EMBM_PLUGIN_URL.'assets/css/admin.css'
    );

    // Load EMBM admin JS
    wp_enqueue_script(
        'embm-admin-script',
        EMBM_PLUGIN_URL.'assets/js/admin.js',
        array('jquery-ui-tabs')
    );

    // Share EMBM settings with admin script
    wp_localize_script(
        'embm-admin-script',
        'embm_settings',
        array(
              'plugin_url'      => EMBM_PLUGIN_URL,
              'options'         => get_option('embm_options')
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

    // Get Untappd options from DB
    $ut_option = get_option('embm_options');

    // Check if we should use Untappd
    if (isset($ut_option['embm_untappd_check'])) {
        $use_untappd = $ut_option['embm_untappd_check'];
    } else {
        $use_untappd = null;
    }

    // Add Untappd column, if enabled
    if ($use_untappd != '1') {
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
        $beer_num = get_post_meta($post_id, 'beer_num', true);

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
        $untap = get_post_meta($post_id, 'untappd', true);

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

    // Untappd Integration help tab
    $screen->add_help_tab($default_help['untappd']);

    // Untappd Beer ID help
    $screen->add_help_tab($default_help['untappd_id']);

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
                __('The "extras" setting refers to the "More Beer Information" content stored for each beer, excluding the Untappd check-in button, which is handled separately.', 'embm').
                '</p>'
        )
    );

    // Help sidebar
    $screen->set_help_sidebar($default_help['sidebar']);
}


/**
 * Add EMBM settings page to the WP menu
 *
 * @return void
 */
function EMBM_Admin_menu()
{
    // Set global admin screen
    global $embm_admin_page;

    // Setup admin page
    $embm_admin_page = add_options_page(
        __('EM Beer Manager Settings', 'embm'), // Page title
        __('EM Beer Manager', 'embm'),          // Menu title
        'manage_options',                       // Capability
        'embm-settings',                        // Menu slug
        'EMBM_Admin_Settings_page'              // Function
    );

    // Add contextual help
    add_action('load-' . $embm_admin_page, 'EMBM_Admin_help');
}

// Load settings page menu link
add_action('admin_menu', 'EMBM_Admin_menu');


/**
 * Add settings page sections and fields
 *
 * @return void
 */
function EMBM_Admin_settings()
{
    // Register new settings options
    register_setting('embm_options', 'embm_options');

    // Global settings
    add_settings_section('embm_global_settings', __('Global Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_css_url', __('Custom stylesheet', 'embm'), 'EMBM_Admin_Settings_Global_css', 'embm', 'embm_global_settings', array('label_for' => 'embm_css_url'));
    add_settings_field('embm_display_settings', __('Display settings', 'embm'), 'EMBM_Admin_Settings_Global_display', 'embm', 'embm_global_settings');

    // Untappd Settings
    add_settings_section('embm_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_untappd_integration', __('Site-wide integration', 'embm'), 'EMBM_Admin_Settings_Untappd_integration', 'embm', 'embm_untappd_settings');
    add_settings_field('embm_untappd_icons', __('Icon set', 'embm'), 'EMBM_Admin_Settings_Untappd_icons', 'embm', 'embm_untappd_settings');

    // Group Tax Settings
    add_settings_section('embm_group_settings', __('Group Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_group_slug', __('Custom taxonomy slug', 'embm'), 'EMBM_Admin_Settings_Group_slug', 'embm', 'embm_group_settings', array('label_for' => 'embm_group_slug'));
    add_settings_field('embm_group_display_settings', __('Display settings', 'embm'), 'EMBM_Admin_Settings_Group_display', 'embm', 'embm_group_settings');

    // Style Tax Settings
    add_settings_section('embm_style_settings', __('Style Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_style_rest', __('Restore styles', 'embm'), 'EMBM_Admin_Settings_Style_reset', 'embm', 'embm_style_settings');
    add_settings_field('embm_style_display_settings', __('Display settings', 'embm'), 'EMBM_Admin_Settings_Style_display', 'embm', 'embm_style_settings');

    // Single Beer Settings
    add_settings_section('embm_single_settings', __('Single Page Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_comments_toggle', __('Comments', 'embm'), 'EMBM_Admin_Settings_Single_comments', 'embm', 'embm_single_settings');
    add_settings_field('embm_single_display_settings', __('Display settings', 'embm'), 'EMBM_Admin_Settings_Single_display', 'embm', 'embm_single_settings');

}

// Load admin settings
add_action('admin_init', 'EMBM_Admin_settings');


/**
 * Output for admin settings sections
 *
 * @return void
 */
function EMBM_Admin_Settings_section()
{
}

/**
 * Outputs custom stylesheet URL input
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_css()
{
    $options = get_option('embm_options');

    echo '<p>'.__('Override default EM Beer Manager CSS with your own stylesheet.', 'embm').'</p>';
    echo '<p><input id="embm_css_url" name="embm_options[embm_css_url]" size="50" type="url" value="'.esc_url($options['embm_css_url']).'" /></p>';
    echo '<p class="description">('.__('Enter a full URL that points to the stylesheet file.', 'embm').')</p>';
}

/**
 * Outputs global display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_display()
{
    $options = get_option('embm_options');

    $view_profile = null;
    if (isset($options['embm_profile_show'])) {
        $view_profile = $options['embm_profile_show'];
    }

    echo '<p><input name="embm_options[embm_profile_show]" type="checkbox" id="embm_profile_show" value="1"'.checked('1', $view_profile, false).' /> ';
    echo '<label for="embm_profile_show">'.__('Globally hide "profile" info', 'embm').'</label>';
    echo '<a data-help="embm-settings-faq" class="embm-settings--help">?</a></p>';

    $view_extras = null;
    if (isset($options['embm_extras_show'])) {
        $view_extras = $options['embm_extras_show'];
    }

    echo '<p><input name="embm_options[embm_extras_show]" type="checkbox" id="embm_extras_show" value="1"'.checked('1', $view_extras, false).' /> ';
    echo '<label for="embm_extras_show">'.__('Globally hide "extras" info', 'embm').'</label>';
    echo '<a data-help="embm-settings-faq" class="embm-settings--help">?</a></p>';
}

/**
 * Outputs Untappd integration options
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_integration()
{
    $options = get_option('embm_options');

    $use_untappd = null;
    if (isset($options['embm_untappd_check'])) {
        $use_untappd = $options['embm_untappd_check'];
    }

    echo '<p><input name="embm_options[embm_untappd_check]" type="checkbox" id="embm_untappd_check" value="1"'.checked('1', $use_untappd, false).' /> ';
    echo '<label for="embm_untappd_check">'.__('Disable site-wide integration', 'embm').'</label>';
    echo '<a data-help="embm-untappd-integration" class="embm-settings--help">?</a></p>';
}

/**
 * Outputs Untappd icon options
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_icons()
{
    $options = get_option('embm_options');
    $icon_img = EMBM_PLUGIN_URL.'assets/img/checkin-button-'.$options['embm_untappd_icons'].'.png';

    // Set up possible options
    $icon_sets = array(
        '1' => __('Original', 'embm').' (v1)',
        '2' => __('Modern', 'embm').' (v2)'
    );

    echo '<p><select name="embm_options[embm_untappd_icons]" class="embm-settings--untappd-select" id="embm_untappd_icons">';

    foreach ($icon_sets as $set_id => $set_name) {
        echo '<option value="'.$set_id.'"';
        if ($options['embm_untappd_icons'] == $set_id) {
            echo ' selected="selected"';
        }
        echo '>'.$set_name.'</option>';
    }

    echo '</select><img class="embm-settings--untappd-icon" src="'.$icon_img.'"" border="0" /></p>';
}

/**
 * Outputs custom group slug option
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_slug()
{
    $options = get_option('embm_options');

    echo '<p>'.__('Rename the beer group URLs with your own custom slug name.', 'embm');
    echo '<br />'.__('By default URLs will look like', 'embm').': <code>yoursite.com/<strong>group</strong>/your-group-name</code>.</p>';
    echo '<p><input id="embm_group_slug" name="embm_options[embm_group_slug]" size="15" type="text" value="'.sanitize_key($options['embm_group_slug']).'" /></p>';
    echo '<p class="description">('.sprintf(
        __('You will need to refresh your permalinks %s after updating this setting', 'embm'),
        sprintf('<a href="options-permalink.php">%s</a>', __('here', 'embm'))
    ).'</p>';
}

/**
 * Outputs group display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_display()
{
    $options = get_option('embm_options');

    $view_profile = null;
    if (isset($options['embm_profile_show_group'])) {
        $view_profile = $options['embm_profile_show_group'];
    }

    echo '<p><input name="embm_options[embm_profile_show_group]" type="checkbox" id="embm_profile_show_group" value="1"'.checked('1', $view_profile, false).' /> ';
    echo '<label for="embm_profile_show_group">'.__('Hide "profile" info in groups', 'embm').'</label></p>';

    $view_extras = null;
    if (isset($options['embm_extras_show_group'])) {
        $view_extras = $options['embm_extras_show_group'];
    }

    echo '<p><input name="embm_options[embm_extras_show_group]" type="checkbox" id="embm_extras_show_group" value="1"'.checked('1', $view_extras, false).' /> ';
    echo '<label for="embm_extras_show_group">'.__('Hide "extras" info in groups', 'embm').'</label></p>';
}

/**
 * Outputs style reset settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Style_reset()
{
    echo '<p>'.__('Restore missing or deleted beer styles from the pre-loaded list.', 'embm').'</p>';
    echo '<p><button class="embm-settings--styles-button button-secondary">'.__('Restore Styles', 'embm').'</button></p>';
}

/**
 * Outputs style display settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Style_display()
{
    $options = get_option('embm_options');

    $view_profile = null;
    if (isset($options['embm_profile_show_style'])) {
        $view_profile = $options['embm_profile_show_style'];
    }

    echo '<p><input name="embm_options[embm_profile_show_style]" type="checkbox" id="embm_profile_show_style" value="1"'.checked('1', $view_profile, false).' /> ';
    echo '<label for="embm_profile_show_style">'.__('Hide "profile" info on styles pages', 'embm').'</label></p>';

    $view_extras = null;

    if (isset($options['embm_extras_show_style'])) {
        $view_extras = $options['embm_extras_show_style'];
    }

    echo '<p><input name="embm_options[embm_extras_show_style]" type="checkbox" id="embm_extras_show_style" value="1"'.checked('1', $view_extras, false).' /> ';
    echo '<label for="embm_extras_show_style">'.__('Hide "extras" info on styles pages', 'embm').'</label></p>';
}

/**
 * Outputs single beer comment options
 *
 * @return void
 */
function EMBM_Admin_Settings_Single_comments()
{
    $options = get_option('embm_options');

    $use_comments = null;
    if (isset($options['embm_comment_check'])) {
        $use_comments = $options['embm_comment_check'];
    }

    echo '<p><input name="embm_options[embm_comment_check]" type="checkbox" id="embm_comment_check" value="1"'.checked('1', $use_comments, false).' /> ';
    echo '<label for="embm_comment_check">'.__('Enable comments on single beer pages', 'embm').'</label></p>';
}

/**
 * Outputs single beer display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Single_display()
{
    $options = get_option('embm_options');

    $view_profile = null;
    if (isset($options['embm_profile_show_single'])) {
        $view_profile = $options['embm_profile_show_single'];
    }

    echo '<p><input name="embm_options[embm_profile_show_single]" type="checkbox" id="embm_profile_show_single" value="1"'.checked('1', $view_profile, false).' /> ';
    echo '<label for="embm_profile_show_single">'.__('Hide "profile" info on single beer pages', 'embm').'</label></p>';

    $view_extras = null;
    if (isset($options['embm_extras_show_single'])) {
        $view_extras = $options['embm_extras_show_single'];
    }
    echo '<p><input name="embm_options[embm_extras_show_single]" type="checkbox" id="embm_extras_show_single" value="1"'.checked('1', $view_extras, false).' /> ';
    echo '<label for="embm_extras_show_single">'.__('Hide "extras" info on single beer pages', 'embm').'</label></p>';
}


/**
 * Loads settings page for users with valid permissions
 *
 * @return void
 */
function EMBM_Admin_Settings_page()
{
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'embm'));
    }

    // Handle styles reset request
    if (isset($_GET['embm-styles-reset'])) {
        if ($_GET['embm-styles-reset'] == '1') {
?>
            <div class="wrap embm-settings--styles-page">

                <h2><?php _e('Restore EM Beer Manager Styles', 'embm'); ?></h2>

                <p><?php _e('This will restore any missing or deleted beer styles from the pre-loaded BeerAdvocate list.', 'embm'); ?></p>
                <p>
                    <?php
                        printf(
                            __('Your custom or modified styles will %s be affected by this action.', 'embm'),
                            sprintf('<span class="emphasis">%s</span>', __('NOT', 'embm'))
                        );
                    ?>
                </p>
                <p><?php _e('Do you wish to proceed?', 'embm'); ?></p>

                <form method="post" action="<?php echo EMBM_PLUGIN_URL.'includes/admin/action-reset-styles.php'; ?>" class="embm-settings--styles-form">
                    <input type="hidden" name="embm-styles-reset-request" value="1" />
                    <input type="hidden" name="embm-settings-page" value="<?php echo $_SERVER['PHP_SELF']; ?>" />
                    <input name="Yes" type="submit" class="button-primary" value="<?php _e('YES', 'embm'); ?>" />
                    <input name="No" type="button" class="button-secondary" value="<?php _e('NO', 'embm'); ?>" />
                </form>
            </div>
<?php
            // Don't display the other content
            return;
        } elseif ($_GET['embm-styles-reset'] == '2') {
            // Show success message admin notice
?>
            <div class="updated notice embm-settings--notice">
                <p><strong><?php _e('Success!', 'embm'); ?></strong> <?php _e('Your beer styles have been restored.', 'embm'); ?></p>
                <button type="button" class="notice-dismiss"></button>
            </div>
<?php
        }
    } elseif (isset($_GET['embm-import-success'])) {
        // Show success notice for Untappd import
?>
        <div class="updated notice embm-settings--notice">
            <?php if ($_GET['embm-import-success'] == '1') : ?>
                <p><strong><?php _e('Success!', 'embm'); ?></strong> <?php _e('Your beer has been imported from Untappd.', 'embm'); ?></p>
            <?php elseif ($_GET['embm-import-success'] == '2') : ?>
                <p><strong><?php _e('Success!', 'embm'); ?></strong> <?php _e('Your beers have been imported from Untappd.', 'embm'); ?></p>
            <?php endif; ?>
            <button type="button" class="notice-dismiss"></button>
        </div>
<?php
    }

?>
<div class="wrap embm-settings--page">

    <h1 class="embm-settings--title">
        <?php _e('EM Beer Manager', 'embm'); ?>
        <span class="embm-settings--title-version"><?php echo 'v'.get_option('embm_version'); ?></span>
    </h1>

    <div id="embm-settings--tabs" class="embm-settings--tabs-wrapper">
        <ul class="nav-tab-wrapper">
            <li><a href="#settings" class="embm-nav-tab nav-tab nav-tab-active nav-tab-settings"><?php _e('Settings', 'embm'); ?></a></li>
            <li><a href="#usage" class="embm-nav-tab nav-tab nav-tab-usage"><?php _e('Usage', 'embm'); ?></a></li>
            <li><a href="#labs" class="embm-nav-tab nav-tab nav-tab-labs"><?php _e('Labs', 'embm'); ?></a></li>
        </ul>

        <div id="settings" class="embm-settings--tab-settings">
            <form method="post" action="options.php" class="embm-settings--form">
                <?php
                    settings_fields('embm_options');
                    do_settings_sections('embm');
                ?>
                <p style="margin-top:1em;">
                    <input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', 'embm'); ?>" />
                </p>
            </form>
        </div>

        <div id="usage" class="embm-settings--tab-usage">
            <?php include_once EMBM_PLUGIN_DIR.'includes/admin/tab-usage.php'; ?>
        </div>

        <div id="labs" class="embm-settings--tab-labs">
            <?php include_once EMBM_PLUGIN_DIR.'includes/admin/tab-labs.php'; ?>
        </div>
    </div>

    <?php include_once EMBM_PLUGIN_DIR.'includes/admin/footer.php'; ?>

</div>
<?php
}
