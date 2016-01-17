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
        // Display formatted beer number
        echo EMBM_Core_Beer_attr($post_id, 'beer_num');
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

            // Get Untappd icon
            $uticon = EMBM_PLUGIN_URL.'assets/img/ut-icon.png';

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
 * Add EMBM settings page to the WP menu
 *
 * @return void
 */
function EMBM_Admin_menu()
{
    global $embm_admin_page;

    $embm_admin_page = add_options_page(
        __('EM Beer Manager Settings', 'embm'), // Page title
        __('EM Beer Manager', 'embm'),          // Menu title
        'manage_options',                       // Capability
        'embm-settings',                        // Menu slug
        'EMBM_Admin_Settings_page'              // Function
    );
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
    add_settings_field('embm_untappd_settings', __('Untappd settings', 'embm'), 'EMBM_Admin_Settings_Global_untappd', 'embm', 'embm_global_settings');
    add_settings_field('embm_display_settings', __('Display settings', 'embm'), 'EMBM_Admin_Settings_Global_display', 'embm', 'embm_global_settings');

    // Group Tax Settings
    add_settings_section('embm_group_settings', __('Group Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_group_slug', __('Custom taxonomy slug', 'embm'), 'EMBM_Admin_Settings_Group_slug', 'embm', 'embm_group_settings', array('label_for' => 'embm_group_slug'));
    add_settings_field('embm_group_display_settings', __('Display settings', 'embm'), 'EMBM_Admin_Settings_Group_display', 'embm', 'embm_group_settings');

    // Style Tax Settings
    add_settings_section('embm_style_settings', __('Style Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
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
 * Outputs Untappd integration options
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_untappd()
{
    $options = get_option('embm_options');

    $use_untappd = null;
    if (isset($options['embm_untappd_check'])) {
        $use_untappd = $options['embm_untappd_check'];
    }

    echo '<p><input name="embm_options[embm_untappd_check]" type="checkbox" id="embm_untappd_check" value="1"'.checked('1', $use_untappd, false).' /> ';
    echo '<label for="embm_untappd_check">'.__('Disable Untappd integration', 'embm').'</label>';
    echo '<span class="whats-this"><a href="#TB_inline?width=550&height=250&inlineId=embm-untappd-help-box" class="thickbox" title="'.__('EM Beer Manager Help', 'embm').'"">?</a></span></p>';
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
    echo '<span class="whats-this"><a href="#TB_inline?width=550&height=450&inlineId=embm-settings-help-box" class="thickbox" title="'.__('EM Beer Manager Help', 'embm').'"">?</a></span></p>';

    $view_extras = null;
    if (isset($options['embm_extras_show'])) {
        $view_extras = $options['embm_extras_show'];
    }

    echo '<p><input name="embm_options[embm_extras_show]" type="checkbox" id="embm_extras_show" value="1"'.checked('1', $view_extras, false).' /> ';
    echo '<label for="embm_extras_show">'.__('Globally hide "extras" info', 'embm').'</label>';
    echo '<span class="whats-this"><a href="#TB_inline?width=550&height=450&inlineId=embm-settings-help-box" class="thickbox" title="'.__('EM Beer Manager Help', 'embm').'">?</a></span></p>';
}

/**
 * Outputs custom group slug option
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_slug()
{
    $options = get_option('embm_options');

    echo '<p>'.__('Rewrite the beer group URLs with your own slug. By default URLs will look like: yoursite.com/<strong>group</strong>/your-group-name.', 'embm').'</p>';
    echo '<p><input id="embm_group_slug" name="embm_options[embm_group_slug]" size="15" type="text" value="'.sanitize_key($options['embm_group_slug']).'" /></p>';
    echo '<p class="description">('.__('You will need to refresh your permalinks ', 'embm').'<a href="options-permalink.php">'.__('here', 'embm').'</a>'.__(' after updating this setting.', 'embm').')</p>';
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
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'embm'));
    }

?>
<div class="wrap embm-settings-page">

    <h1 class="embm-settings-title"><?php _e('EM Beer Manager', 'embm'); ?><span class="embm-version"><?php echo 'v'.get_option('embm_version'); ?></span></h1>

    <div id="embm-settings-tabs">
        <ul class="nav-tab-wrapper">
            <li><a href="#settings" class="nav-tab nav-tab-active nav-tab-settings"><?php _e('Settings', 'embm'); ?></a></li>
            <li><a href="#usage" class="nav-tab nav-tab-usage"><?php _e('Usage', 'embm'); ?></a></li>
        </ul>

        <div id="settings" class="embm-settings-section-settings">
            <form method="post" action="options.php" class="embm-form-settings">
                <?php
                    settings_fields('embm_options');
                    do_settings_sections('embm');
                ?>
                <p style="margin-top:1em;"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
            </form>
        </div>

        <div id="usage" class="embm-settings-section-usage">

            <h2><?php _e('Single Beer Display', 'embm'); ?></h2>

            <p><?php _e('These will display a single beer entry given it\'s ID number.', 'embm'); ?></p>

            <h3 class="embm-subhead"><?php _e('Shortcode', 'embm'); ?></h3>

            <blockquote>
                <code>[beer id="beer id"]</code>
            </blockquote>

            <h3 class="embm-subhead"><?php _e('Template tag', 'embm'); ?></h3>

            <blockquote>
                <code><?php echo htmlentities('<?php echo EMBM_Output_Beer_display( $beer_id, $args ); ?>'); ?></code></p>
                <p><?php _e('Where <code>$beer_id</code> is required and <code>$args</code> is a PHP array of comma-separated <code>key => value</code> pairs. For example:', 'embm'); ?></p>
                <p><code><?php echo htmlentities(
                    "<?php echo EMBM_Output_Beer_display( 123, array(
                        'show_profile'    => 'false',
                        'show_extras'    => 'true'
                    ) ); ?>"
                ); ?></code></p>
            </blockquote>

            <h3 class="embm-subhead"><?php _e('Options', 'embm'); ?></h3>

            <p><?php _e('For use with both the shortcode and template code', 'embm'); ?></p>

            <table class="embm-usage-table" cellpadding="0" cellspacing="0" border="0">
                <thead>
                    <tr>
                        <th><?php _e('Option Name', 'embm'); ?></th>
                        <th><?php _e('Values', 'embm'); ?></th>
                        <th><?php _e('Default', 'embm'); ?></th>
                        <th><?php _e('Description', 'embm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code><strong>show_profile</strong></code></td>
                        <td><code>true, false</code></td>
                        <td><code>true</code></td>
                        <td><?php _e('Displays or hides the "Beer Profile" information', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>show_extras</strong></code></td>
                        <td><code>true, false</code></td>
                        <td><code>true</code></td>
                        <td><?php _e('Displays or hides the "More Information" section', 'embm'); ?></td>
                    </tr>
                </tbody>
            </table>

            <br />

            <h2><?php _e('List All Beers', 'embm'); ?></h2>

            <p><?php _e('These will display a formatted listing of all beers.', 'embm'); ?></p>

            <h3 class="embm-subhead"><?php _e('Shortcode', 'embm'); ?></h3>

            <blockquote>
                <code>[beer-list]</code>
            </blockquote>

            <h3 class="embm-subhead"><?php _e('Template tag', 'embm'); ?></h3>

            <blockquote>
                <code><?php echo htmlentities('<?php echo EMBM_Output_List_display( $args ); ?>'); ?></code></p>
                <p><?php _e('Where <code>$args</code> is a PHP array of comma-separated <code>key => value</code> pairs, e.g.:', 'embm'); ?></p>
                <p><code><?php echo htmlentities(
                    "<?php echo EMBM_Output_List_display( array(
                        'show_extras'        => 'false',
                        'beers_per_page'    => 3,
                        'orderby'        => 'name',
                        'order'            => 'ASC'
                    ) ); ?>"
                ); ?></code></p>
            </blockquote>

            <h3 class="embm-subhead"><?php _e('Options', 'embm'); ?></h3>

            <p><?php _e('For use with both the shortcode and template code', 'embm'); ?></p>

            <table class="embm-usage-table" cellpadding="0" cellspacing="0" border="0">
                <thead>
                    <tr>
                        <th><?php _e('Option Name', 'embm'); ?></th>
                        <th><?php _e('Values', 'embm'); ?></th>
                        <th><?php _e('Default', 'embm'); ?></th>
                        <th><?php _e('Description', 'embm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code><strong>exclude</strong></code></td>
                        <td><?php _e('Comma-separated list of beer IDs', 'embm'); ?><br />e.g. <code>"4,23,24"</code></td>
                        <td>n/a</td>
                        <td><?php _e('Hides listed beers from output', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>show_profile</strong></code></td>
                        <td><code>true, false</code></td>
                        <td><code>true</code></td>
                        <td><?php _e('Displays or hides the "Beer Profile" information for each listing', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>show_extras</strong></code></td>
                        <td><code>true, false</code></td>
                        <td><code>true</code></td>
                        <td><?php _e('Displays or hides the "More Information" section for each listing', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>style</strong></code></td>
                        <td><?php _e('String of style name'); ?><br />e.g. <code>"India Pale Ale"</code></td>
                        <td>n/a</td>
                        <td><?php _e('Displays only beers belonging to a specific beer style', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>group</strong></code></td>
                        <td><?php _e('String of group name'); ?><br />e.g. <code>"Seasonals"</code></td>
                        <td>n/a</td>
                        <td><?php _e('Displays only beers belonging to a specific group', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>beers_per_page</strong></code></td>
                        <td><?php _e('A number', 'embm'); ?><br /> e.g. <code>5</code></td>
                        <td><code>-1</code><br /><?php _e('Shows all beers', 'embm'); ?></td>
                        <td><?php _e('Paginates output and displays the given number of beers per page', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>paginate</strong></code></td>
                        <td><code>true, false</code></td>
                        <td><code>true</code></td>
                        <td><?php _e('Disables/enables pagination', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>orderby</strong></code></td>
                        <td><?php _e('See ', 'embm'); ?><a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank"><?php _e('this list', 'embm'); ?></a><?php _e(' for options', 'embm'); ?></td>
                        <td><code>"date"</code></td>
                        <td><?php _e('Orders beer list output by the given paramater', 'embm'); ?></td>
                    </tr>
                    <tr>
                        <td><code><strong>order</strong></code></td>
                        <td><code>ASC, DSC</code></td>
                        <td><code>DSC</code></td>
                        <td><?php _e('Sorts beer list by <code>orderby</code> value in ascending or descending order', 'embm'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <br /><hr />

    <p><?php _e('If you like this plugin, please consider donating to help support future development. Thank you!', 'embm'); ?></p>

    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHTwYJKoZIhvcNAQcEoIIHQDCCBzwCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBLVDHHzBYoHHf+L3b+unlZe05cmlq5kl4s5fcwlT8HLNmg2uRH/sDSREDqzLfrWkUKp+K5fhSelo+Cuz+h/22cSGZS1JuGMXR7Uo6Nj4Z+HCoyN+tMMJDyeQ2QvhoEz04HsUn0JxAevHPDrn2qHIJhmvICLQVO/umeTy14t5AonDELMAkGBSsOAwIaBQAwgcwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIbrrK57DdcmKAgahVU0xwDNglSrNHU0itm4VVH9hOW//OQ5OuXQYJA42zs6U2+zI3wMNvPR6amkCgXSTFoHkilfl+U6qM5f+x3Tb3VrvSqfSxlC3LjZFf3qnsUabL7rgqjlbS5RvCuFjBcKke/i4VUxg+Ghve5d7+GQcLFsk0oGzhCjCAK1JulLPuJ+qL6F7Vhw5wd01Zn33/lUkAU/0ofXzc44Mfp29s0EdmIJEcBhWGfo6gggOHMIIDgzCCAuygAwIBAgIBADANBgkqhkiG9w0BAQUFADCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wHhcNMDQwMjEzMTAxMzE1WhcNMzUwMjEzMTAxMzE1WjCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAMFHTt38RMxLXJyO2SmS+Ndl72T7oKJ4u4uw+6awntALWh03PewmIJuzbALScsTS4sZoS1fKciBGoh11gIfHzylvkdNe/hJl66/RGqrj5rFb08sAABNTzDTiqqNpJeBsYs/c2aiGozptX2RlnBktH+SUNpAajW724Nv2Wvhif6sFAgMBAAGjge4wgeswHQYDVR0OBBYEFJaffLvGbxe9WT9S1wob7BDWZJRrMIG7BgNVHSMEgbMwgbCAFJaffLvGbxe9WT9S1wob7BDWZJRroYGUpIGRMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAIFfOlaagFrl71+jq6OKidbWFSE+Q4FqROvdgIONth+8kSK//Y/4ihuE4Ymvzn5ceE3S/iBSQQMjyvb+s2TWbQYDwcp129OPIbD9epdr4tJOUNiSojw7BHwYRiPh58S1xGlFgHFXwrEBb3dgNbMUa+u4qectsMAXpVHnD9wIyfmHMYIBmjCCAZYCAQEwgZQwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tAgEAMAkGBSsOAwIaBQCgXTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0xMzA2MTYwMDEzNTJaMCMGCSqGSIb3DQEJBDEWBBT43STJzqxxM7XW6mxyUu5Zj9vwRDANBgkqhkiG9w0BAQEFAASBgFT4fbD6cr9/rk2mCU3GFbqqK5vn0GozAM5Q0g6ENO+0h78jJEsRwAvkPnCm6KWGjUxnqYHAc2/nIMlXRzK/98LIn/0OHIERbSxIcisRp3HmBxwGlpUKTH5CgSpMf6vScPKvG0eGO8o1Jb2rY6CMT0zC1Wf8ulR2gtd9OFDXVm4F-----END PKCS7-----
        ">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>

    <p><?php _e('Free beer icon from <a href="http://simpleicon.com" title="simple icon">simple icon</a>.', 'embm'); ?></p>

    <?php add_thickbox(); ?>

    <div id="embm-untappd-help-box" style="display:none;">
        <h2><?php _e('Untappd Integration', 'embm'); ?></h2>
        <p><?php _e('Checking the "Disable Untappd integration" option under the "EM Beer Manager" settings, will completely disable all Untappd functionality, including per-beer check-in buttons and the Recent Check-Ins widget.', 'embm'); ?></p>
        <p><?php _e('You can disable the Untappd check-in button for an individual beer by simply leaving the setting empty. Beers that have an active check-in button will display a square Untappd icon next to their entry on the Beers admin page.', 'embm'); ?></p>
    </div>

    <div id="embm-settings-help-box" style="display:none;">
        <h2><?php _e('Settings FAQ', 'embm'); ?></h2>
        <p><strong><?php _e('How do I display an image of my beer next to its name and description?', 'embm'); ?></strong></p>
        <p><?php _e('When creating your new beer entry, set the "featured image" option in the sidebar to the beer image you wish to use, it will display alongside the entry when the beer is displayed on your site. If this option is not available in your post settings, your theme may be blocking post thumbnails.', 'embm'); ?></p>

        <p><strong><?php _e('I don\'t want to show that big grey box of information, how do I get rid of it?', 'embm'); ?></strong></p>
        <p><?php _e('For each of the different displays there is the option to "Hide extras info" and "Hide extras info". Check both of these to hide the grey box.', 'embm'); ?></p>

        <p><strong><?php _e('What\'s the difference between "profile" and "extras"?', 'embm'); ?></strong></p>
        <p><?php _e('The "profile" refers to all the content in the "Beer Profile" information stored for each beer. This includes ABV, IBU, Hops, Malts, Additions, and Yeast. The "extras" setting refers to the "Additional Notes" and "Availability" information stored for each beer.', 'embm'); ?></p>
    </div>
</div>
<?php
}
