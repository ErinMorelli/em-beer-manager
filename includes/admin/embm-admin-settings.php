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
 * @package EMBM\Admin\Settings
 */

// Import additional admin functions
require EMBM_PLUGIN_DIR.'includes/admin/embm-admin-notices.php';
require EMBM_PLUGIN_DIR.'includes/admin/embm-admin-authorize.php';

// Set global admin page object
global $embm_admin_page;

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
        __('EM Beer Manager Settings', 'embm'),
        __('EM Beer Manager', 'embm'),
        'manage_options',
        'embm-settings',
        'EMBM_Admin_Settings_page'
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
    register_setting('embm_options', 'embm_options', 'EMBM_Admin_sanitize');

    // Get Untappd logged in status
    $logged_in = !is_null(EMBM_Admin_Authorize_token());

    // Untappd Settings
    add_settings_section('embm_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_untappd', 'embm');
    add_settings_field('embm_untappd_integration', __('Site-wide Integration', 'embm'), 'EMBM_Admin_Settings_Untappd_integration', 'embm', 'embm_untappd_settings');
    add_settings_field('embm_untappd_icons', __('Icon Set', 'embm'), 'EMBM_Admin_Settings_Untappd_icons', 'embm', 'embm_untappd_settings');
    if ($logged_in) {
        add_settings_field('embm_untappd_rating_format', __('Rating Display Format', 'embm'), 'EMBM_Admin_Settings_Untappd_rating', 'embm', 'embm_untappd_settings');
        add_settings_field('embm_untappd_rating_color', __('Rating Star Color', 'embm'), 'EMBM_Admin_Settings_Untappd_Rating_color', 'embm', 'embm_untappd_settings');
        add_settings_field('embm_untappd_rating_opacity', __('Rating Star Empty Opacity', 'embm'), 'EMBM_Admin_Settings_Untappd_Rating_opacity', 'embm', 'embm_untappd_settings');
    } else {
        add_settings_field('embm_untappd_logged_out', '', 'EMBM_Admin_Settings_Untappd_login', 'embm', 'embm_untappd_settings');
    }

    // Global settings
    add_settings_section('embm_global_settings', __('Global Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_css_url', __('Custom Stylesheet (URL)', 'embm'), 'EMBM_Admin_Settings_Global_css', 'embm', 'embm_global_settings', array('label_for' => 'embm_css_url'));
    add_settings_field('embm_display_settings', __('Display Settings', 'embm'), 'EMBM_Admin_Settings_Global_display', 'embm', 'embm_global_settings');
    if ($logged_in) {
        add_settings_field('embm_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Global_untappd', 'embm', 'embm_global_settings');
    } else {
        add_settings_field('embm_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Untappd_login', 'embm', 'embm_global_settings');
    }

    // Group Tax Settings
    add_settings_section('embm_group_settings', __('Beer Group Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_group_slug', __('Custom Taxonomy Slug', 'embm'), 'EMBM_Admin_Settings_Group_slug', 'embm', 'embm_group_settings', array('label_for' => 'embm_group_slug'));
    add_settings_field('embm_group_display_settings', __('Display Settings', 'embm'), 'EMBM_Admin_Settings_Group_display', 'embm', 'embm_group_settings');
    if ($logged_in) {
        add_settings_field('embm_group_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Group_untappd', 'embm', 'embm_group_settings');
    } else {
        add_settings_field('embm_group_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Untappd_login', 'embm', 'embm_group_settings');
    }

    // Style Tax Settings
    add_settings_section('embm_style_settings', __('Beer Style Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_style_rest', __('Restore Styles', 'embm'), 'EMBM_Admin_Settings_Style_reset', 'embm', 'embm_style_settings');
    add_settings_field('embm_style_display_settings', __('Display Settings', 'embm'), 'EMBM_Admin_Settings_Style_display', 'embm', 'embm_style_settings');
    if ($logged_in) {
        add_settings_field('embm_style_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Style_untappd', 'embm', 'embm_style_settings');
    } else {
        add_settings_field('embm_style_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Untappd_login', 'embm', 'embm_style_settings');
    }

    // Single Beer Settings
    add_settings_section('embm_single_settings', __('Single Beer Page Settings', 'embm'), 'EMBM_Admin_Settings_section', 'embm');
    add_settings_field('embm_comments_toggle', __('Comments', 'embm'), 'EMBM_Admin_Settings_Single_comments', 'embm', 'embm_single_settings');
    add_settings_field('embm_single_display_settings', __('Display Settings', 'embm'), 'EMBM_Admin_Settings_Single_display', 'embm', 'embm_single_settings');
    if ($logged_in) {
        add_settings_field('embm_single_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Single_untappd', 'embm', 'embm_single_settings');
    } else {
        add_settings_field('embm_single_untappd_settings', __('Untappd Settings', 'embm'), 'EMBM_Admin_Settings_Untappd_login', 'embm', 'embm_single_settings');
    }
}

// Load admin settings
add_action('admin_init', 'EMBM_Admin_settings');

/**
 * Sanitize each setting field as needed
 *
 * @param array $input Contains all settings fields as array keys
 *
 * @return array Sanitized field values
 */
function EMBM_Admin_sanitize($input)
{
    // Get new check-in count
    $new_count = $input['embm_reviews_count_single'];

    // Get previously save options
    $options = get_option('embm_options');
    $old_count = $options['embm_reviews_count_single'];

    // Check if we need to update counts
    if ($new_count !== $old_count) {
        // Get global WordPress DB object
        global $wpdb;

        // Update post metadata where needed
        $wpdb->query(
            "
            UPDATE
                $wpdb->postmeta
            SET
                meta_value = '$new_count'
            WHERE
                meta_key = 'embm_reviews_count' &&
                meta_value = '$old_count'
            "
        );
    }

    // Return new input
    return $input;
}

/**
 * Output for Untappd admin settings section
 *
 * @param array $section WP settings section data
 *
 * @return void
 */
function EMBM_Admin_Settings_untappd($section)
{
    EMBM_Admin_Authorize_status();
    echo '<a name="' . $section['id'] . '"></a>';
}

/**
 * Output for Untappd admin settings section
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_login()
{
    echo '<p class="description">'.__('Log in to Untappd to access additional display options.', 'embm').'</p>';
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
 * Outputs Untappd rating format options
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_rating()
{
    $options = get_option('embm_options');
    $formats = EMBM_Core_Beer_ratings();

    // Format select
    echo '<p><select name="embm_options[embm_untappd_rating_format]" class="embm-settings--rating-format-select" id="embm_untappd_rating_format">';
    foreach ($formats as $format_id => $format) {
        echo '<option value="'.$format_id.'"';
        if ($options['embm_untappd_rating_format'] == $format_id) {
            echo ' selected="selected"';
        }
        echo '>'.$format['desc'].'</option>';
    }
    echo '</select></p>';
}

/**
 * Outputs Untappd rating color settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_Rating_color()
{
    $options = get_option('embm_options');

    echo '<p><input name="embm_options[embm_untappd_rating_color]" id="embm_untappd_rating_color" value="';
    echo $options['embm_untappd_rating_color'];
    echo '" data-default-color="#FFCC00" class="embm-settings--rating-color" /></p>';
}

/**
 * Outputs Untappd rating opacity settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_Rating_opacity()
{
    $options = get_option('embm_options');

    echo '<div class="embm-settings--rating-opacity">';
    echo '<input type="hidden" name="embm_options[embm_untappd_rating_opacity]" id="embm_untappd_rating_opacity" value="';
    echo $options['embm_untappd_rating_opacity'].'" />';
    echo '<div id="embm-settings--rating-opacity--slider"><div class="ui-slider-handle"></div></div></div>';
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
 * Output for admin settings sections
 *
 * @param array $section WP settings section data
 *
 * @return void
 */
function EMBM_Admin_Settings_section($section)
{
    echo '<a name="' . $section['id'] . '"></a>';
}

/**
 * Outputs custom stylesheet URL input
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_css()
{
    $options = get_option('embm_options');


    echo '<p><input id="embm_css_url" name="embm_options[embm_css_url]" size="50" type="url" value="'.esc_url($options['embm_css_url']).'" /></p>';
    echo '<p class="description">';
    echo __('Enter a full URL that points to a stylesheet file to override default EM Beer Manager styles.', 'embm');
    echo '</p>';
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
 * Outputs global Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_untappd()
{
    $options = get_option('embm_options');

    $view_rating = null;
    if (isset($options['embm_rating_show'])) {
        $view_rating = $options['embm_rating_show'];
    }

    echo '<p><input name="embm_options[embm_rating_show]" type="checkbox" id="embm_rating_show" value="1"'.checked('1', $view_rating, false).' /> ';
    echo '<label for="embm_rating_show">'.__('Globally hide Untappd rating', 'embm').'</label>';
}

/**
 * Outputs custom group slug option
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_slug()
{
    $options = get_option('embm_options');

    echo '<p><input id="embm_group_slug" name="embm_options[embm_group_slug]" size="15" type="text" value="';
    echo sanitize_key($options['embm_group_slug']);
    echo '" /></p><p class="description">';
    echo __('Rename the beer group URLs with your own custom slug name.', 'embm') . '<br />';
    echo sprintf(
        __('You must %s after changing this.', 'embm'),
        sprintf('<a href="options-permalink.php">%s</a>', __('refresh your permalinks', 'embm'))
    ).'</p><p class="timezone-info">';
    echo __('By default URLs will look like', 'embm').': <code>yoursite.com/<strong>group</strong>/your-group-name</code>.</p>';
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
 * Outputs group Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_untappd()
{
    $options = get_option('embm_options');

    $view_rating = null;
    if (isset($options['embm_rating_show_group'])) {
        $view_rating = $options['embm_rating_show_group'];
    }

    echo '<p><input name="embm_options[embm_rating_show_group]" type="checkbox" id="embm_rating_show_group" value="1"'.checked('1', $view_rating, false).' /> ';
    echo '<label for="embm_rating_show_group">'.__('Hide Untappd rating in groups', 'embm').'</label>';
}

/**
 * Outputs style reset settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Style_reset()
{
    echo '<p><button class="embm-settings--styles-button button-secondary">'.__('Restore Styles', 'embm').'</button></p>';
    echo '<p class="description">'.__('Restore missing or deleted beer styles from the pre-loaded list.', 'embm').'</p>';

    // Add modal prompt
    add_thickbox();

?>
    <div id="embm-styles-reset-prompt" style="display:none;">
        <div class="embm-styles-reset-prompt--content">
            <p><?php _e('This will restore any missing or deleted beer styles from the pre-loaded Untappd list.', 'embm'); ?></p>
            <p>
                <?php
                    printf(
                        __('Your custom or modified styles will %s be affected by this action.', 'embm'),
                        sprintf('<span class="emphasis">%s</span>', __('NOT', 'embm'))
                    );
                ?>
            </p>
            <p><strong><?php _e('Do you wish to proceed?', 'embm'); ?></strong></p>
            <p>
                <a href="#" id="embm-styles-reset-prompt--yes" class="button-primary"><?php _e('YES', 'embm'); ?></a>
                <a href="#" id="embm-styles-reset-prompt--no" class="button-secondary"><?php _e('NO', 'embm'); ?></a>
            </p>
        </div>
    </div>
    <a
        href="#TB_inline?width=550&height=155&inlineId=embm-styles-reset-prompt"
        class="thickbox"
        id="embm-styles-reset-prompt--button"
        title="<?php _e('Restore EM Beer Manager Styles', 'embm'); ?>"
        style="display:none;"
    ></a>
<?php
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
 * Outputs style Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Style_untappd()
{
    $options = get_option('embm_options');

    $view_rating = null;
    if (isset($options['embm_rating_show_style'])) {
        $view_rating = $options['embm_rating_show_style'];
    }

    echo '<p><input name="embm_options[embm_rating_show_style]" type="checkbox" id="embm_rating_show_style" value="1"'.checked('1', $view_rating, false).' /> ';
    echo '<label for="embm_rating_show_style">'.__('Hide Untappd rating on styles pages', 'embm').'</label>';
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
 * Outputs single Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Single_untappd()
{
    $options = get_option('embm_options');

    $view_rating = null;
    if (isset($options['embm_rating_show_single'])) {
        $view_rating = $options['embm_rating_show_single'];
    }

    echo '<p><input name="embm_options[embm_rating_show_single]" type="checkbox" id="embm_rating_show_single" value="1"'.checked('1', $view_rating, false).' /> ';
    echo '<label for="embm_rating_show_single">'.__('Hide Untappd rating on single beer pages', 'embm').'</label>';

    $view_reviews = null;
    if (isset($options['embm_reviews_show_single'])) {
        $view_reviews = $options['embm_reviews_show_single'];
    }

    echo '<p><input name="embm_options[embm_reviews_show_single]" type="checkbox" id="embm_reviews_show_single" value="1"'.checked('1', $view_reviews, false).' /> ';
    echo '<label for="embm_reviews_show_single">'.__('Hide Untappd check-ins on single beer pages', 'embm').'</label>';

    $reviews_count = 5;
    if (isset($options['embm_reviews_count_single'])) {
        $reviews_count = $options['embm_reviews_count_single'];
    }

    echo '<p class="embm-settings--review-count"><label for="embm_reviews_count_single">'.__('Show', 'embm');
    echo '<input id="embm_reviews_count_single" name="embm_options[embm_reviews_count_single]" type="number" min="1" max="15" value="'.$reviews_count.'" />';
    echo sprintf(__('check-ins (max. %d)', 'embm'), 15);
    echo '</label></p>';

    echo '<p class="description">('.__('This setting may be overridden for individual beers.', 'embm').')</p>';
}

/**
 * Loads settings page for users with valid permissions
 *
 * @return void
 */
function EMBM_Admin_Settings_page()
{
    // Get settings page sections
    global $wp_settings_sections;
    $sections = $wp_settings_sections['embm'];

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'embm'));
    }

    // Show any notices
    EMBM_Admin_Notices_show();

?>
<div class="wrap embm-settings--page">

    <h1 class="embm-settings--title">
        <?php _e('EM Beer Manager', 'embm'); ?>
        <span class="embm-settings--title-version"><?php echo 'v'.get_option('embm_version'); ?></span>
    </h1>

    <div id="embm-settings--tabs" class="embm-settings--tabs-wrapper">
        <ul class="nav-tab-wrapper">
            <li><a href="#settings" class="embm-nav-tab nav-tab nav-tab-active nav-tab-settings"><?php _e('Settings', 'embm'); ?></a></li>
            <li><a href="#labs" class="embm-nav-tab nav-tab nav-tab-labs"><?php _e('Labs', 'embm'); ?></a></li>
            <li><a href="#usage" class="embm-nav-tab nav-tab nav-tab-usage"><?php _e('Usage', 'embm'); ?></a></li>
        </ul>

        <div id="settings" class="embm-settings--tab-settings">
            <div class="embm-settings--navbox">
                <span
                    class="dashicons dashicons-arrow-right-alt2"
                    title="<?php _e('Collpase/Expand Panel', 'embm'); ?>"
                    id="embm-settings--navbox-toggle">
                </span>
                <ul>
                    <li><strong>Jump to:</strong></li>
                    <li><a href="#top"><?php _e('Top', 'embm'); ?></a></li>
                    <?php foreach ($sections as $section): ?>
                        <li><a href="#<?php echo $section['id']; ?>"><?php echo $section['title']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
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

        <div id="labs" class="embm-settings--tab-labs">
            <?php include_once EMBM_PLUGIN_DIR.'includes/admin/tabs/embm-tabs-labs.php'; ?>
        </div>

        <div id="usage" class="embm-settings--tab-usage">
            <?php include_once EMBM_PLUGIN_DIR.'includes/admin/tabs/embm-tabs-usage.php'; ?>
        </div>
    </div>

    <?php include_once EMBM_PLUGIN_DIR.'includes/admin/embm-admin-footer.php'; ?>

</div>
<?php
}
