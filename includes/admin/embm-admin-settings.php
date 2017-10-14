<?php
/**
 * Copyright (c) 2013-2017, Erin Morelli.
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
require EMBM_PLUGIN_DIR.'includes/admin/integrations/embm-integrations-authorize.php';

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
        __('EM Beer Manager Settings', EMBM_DOMAIN),
        __('EM Beer Manager', EMBM_DOMAIN),
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
    register_setting(EMBM_OPTIONS, EMBM_OPTIONS, 'EMBM_Admin_sanitize');

    // Get Untappd logged in status
    $logged_in = !is_null(EMBM_Admin_Authorize_token());

    // Untappd Settings
    add_settings_section('embm_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_untappd', EMBM_DOMAIN);
    add_settings_field('embm_untappd_integration', __('Site-wide Integration', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_integration', EMBM_DOMAIN, 'embm_untappd_settings');
    add_settings_field('embm_untappd_icons', __('Icon Set', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_icons', EMBM_DOMAIN, 'embm_untappd_settings');
    if ($logged_in) {
        add_settings_field('embm_untappd_rating_format', __('Rating Display Format', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_rating', EMBM_DOMAIN, 'embm_untappd_settings');
        add_settings_field('embm_untappd_rating_color', __('Rating Star Color', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_Rating_color', EMBM_DOMAIN, 'embm_untappd_settings');
        add_settings_field('embm_untappd_rating_opacity', __('Rating Star Empty Opacity', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_Rating_opacity', EMBM_DOMAIN, 'embm_untappd_settings');
    } else {
        add_settings_field('embm_untappd_logged_out', '', 'EMBM_Admin_Settings_Untappd_login', EMBM_DOMAIN, 'embm_untappd_settings');
    }

    // Global settings
    add_settings_section('embm_global_settings', __('Global Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_section', EMBM_DOMAIN);
    add_settings_field('embm_css_url', __('Custom Stylesheet (URL)', EMBM_DOMAIN), 'EMBM_Admin_Settings_Global_css', EMBM_DOMAIN, 'embm_global_settings', array('label_for' => 'embm_css_url'));
    add_settings_field('embm_display_settings', __('Display Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Global_display', EMBM_DOMAIN, 'embm_global_settings');
    if ($logged_in) {
        add_settings_field('embm_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Global_untappd', EMBM_DOMAIN, 'embm_global_settings');
    } else {
        add_settings_field('embm_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_login', EMBM_DOMAIN, 'embm_global_settings');
    }

    // Group Tax Settings
    add_settings_section('embm_group_settings', __('Beer Group Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_section', EMBM_DOMAIN);
    add_settings_field('embm_group_slug', __('Custom Taxonomy Slug', EMBM_DOMAIN), 'EMBM_Admin_Settings_Group_slug', EMBM_DOMAIN, 'embm_group_settings', array('label_for' => 'embm_group_slug'));
    add_settings_field('embm_group_display_settings', __('Display Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Group_display', EMBM_DOMAIN, 'embm_group_settings');
    if ($logged_in) {
        add_settings_field('embm_group_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Group_untappd', EMBM_DOMAIN, 'embm_group_settings');
    } else {
        add_settings_field('embm_group_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_login', EMBM_DOMAIN, 'embm_group_settings');
    }

    // Style Tax Settings
    add_settings_section('embm_style_settings', __('Beer Style Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_section', EMBM_DOMAIN);
    add_settings_field('embm_style_rest', __('Restore Styles', EMBM_DOMAIN), 'EMBM_Admin_Settings_Style_reset', EMBM_DOMAIN, 'embm_style_settings');
    add_settings_field('embm_style_display_settings', __('Display Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Style_display', EMBM_DOMAIN, 'embm_style_settings');
    if ($logged_in) {
        add_settings_field('embm_style_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Style_untappd', EMBM_DOMAIN, 'embm_style_settings');
    } else {
        add_settings_field('embm_style_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_login', EMBM_DOMAIN, 'embm_style_settings');
    }

    // Menu Tax Settings
    add_settings_section('embm_menu_settings', __('Beer Menu Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_section', EMBM_DOMAIN);
    add_settings_field('embm_menu_display_settings', __('Display Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Menu_display', EMBM_DOMAIN, 'embm_menu_settings');
    if ($logged_in) {
        add_settings_field('embm_menu_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Menu_untappd', EMBM_DOMAIN, 'embm_menu_settings');
    } else {
        add_settings_field('embm_menu_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_login', EMBM_DOMAIN, 'embm_menu_settings');
    }

    // Single Beer Settings
    add_settings_section('embm_single_settings', __('Single Beer Page Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_section', EMBM_DOMAIN);
    add_settings_field('embm_comments_toggle', __('Comments', EMBM_DOMAIN), 'EMBM_Admin_Settings_Single_comments', EMBM_DOMAIN, 'embm_single_settings');
    add_settings_field('embm_single_display_settings', __('Display Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Single_display', EMBM_DOMAIN, 'embm_single_settings');
    if ($logged_in) {
        add_settings_field('embm_single_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Single_untappd', EMBM_DOMAIN, 'embm_single_settings');
    } else {
        add_settings_field('embm_single_untappd_settings', __('Untappd Settings', EMBM_DOMAIN), 'EMBM_Admin_Settings_Untappd_login', EMBM_DOMAIN, 'embm_single_settings');
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
    $options = get_option(EMBM_OPTIONS);
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
    echo '<p class="description">'.__('Log in to Untappd to access additional display options.', EMBM_DOMAIN).'</p>';
}

/**
 * Outputs Untappd integration options
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_integration()
{
    $options = get_option(EMBM_OPTIONS);

    $use_untappd = null;
    if (isset($options['embm_untappd_check'])) {
        $use_untappd = $options['embm_untappd_check'];
    }

    echo '<p><input name="embm_options[embm_untappd_check]" type="checkbox" id="embm_untappd_check" value="1"'.checked('1', $use_untappd, false).' /> ';
    echo '<label for="embm_untappd_check">'.__('Disable site-wide integration', EMBM_DOMAIN).'</label>';
    echo '<a data-help="embm-untappd-integration" class="embm-settings--help">?</a></p>';
}

/**
 * Outputs Untappd rating format options
 *
 * @return void
 */
function EMBM_Admin_Settings_Untappd_rating()
{
    $options = get_option(EMBM_OPTIONS);
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
    $options = get_option(EMBM_OPTIONS);

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
    $options = get_option(EMBM_OPTIONS);

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
    $options = get_option(EMBM_OPTIONS);
    $icon_img = EMBM_PLUGIN_URL.'assets/img/checkin-button-'.$options['embm_untappd_icons'].'.png';

    // Set up possible options
    $icon_sets = array(
        '1' => __('Original', EMBM_DOMAIN).' (v1)',
        '2' => __('Modern', EMBM_DOMAIN).' (v2)'
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
 * Output display options for a given WP settings section
 *
 * @param string $title   Text output title for section
 * @param string $section WP settings section name
 *
 * @return html
 */
function EMBM_Admin_Settings_Section_display($title, $section = null)
{
    $options = get_option(EMBM_OPTIONS);

    // Get section ID
    $section = is_null($section) ? '' : '_'.$section;

    // Get profile options
    $show_profile = 'embm_profile_show'.$section;
    $view_profile = isset($options[$show_profile]) ? $options[$show_profile] : null;

    // Get extras options
    $show_extras = 'embm_extras_show'.$section;
    $view_extras = isset($options[$show_extras]) ? $options[$show_extras] : null;

    // Output profile settings
    $output = '<p><input name="embm_options['.$show_profile.']" type="checkbox" id="'.$show_profile.'" value="1"';
    $output .= checked('1', $view_profile, false).' /> ';
    $output .= '<label for="'.$show_profile.'">';
    $output .= sprintf(__('Hide "profile" info %s', EMBM_DOMAIN), __($title, EMBM_DOMAIN));
    $output .= '</label><a data-help="embm-settings-faq" class="embm-settings--help">?</a></p>';

    // Output extras settings
    $output .= '<p><input name="embm_options['.$show_extras.']" type="checkbox" id="'.$show_extras.'" value="1"';
    $output .= checked('1', $view_extras, false).' /> ';
    $output .= '<label for="'.$show_extras.'">';
    $output .= sprintf(__('Hide "extras" info %s', EMBM_DOMAIN), __($title, EMBM_DOMAIN));
    $output .= '</label><a data-help="embm-settings-faq" class="embm-settings--help">?</a></p>';

    // Return display output
    return $output;
}

/**
 * Output Untappd options for a given WP settings section
 *
 * @param string $title   Text output title for section
 * @param string $section WP settings section name
 *
 * @return html
 */
function EMBM_Admin_Settings_Section_untappd($title, $section = null)
{
    $options = get_option(EMBM_OPTIONS);

    // Get section ID
    $section = is_null($section) ? '' : '_'.$section;

    // Get section options
    $show_rating = 'embm_rating_show'.$section;
    $view_rating = isset($options[$show_rating]) ? $options[$show_rating] : null;

    // Output Untappd settings
    $output = '<p><input name="embm_options['.$show_rating.']" type="checkbox" id="'.$show_rating.'" value="1"';
    $output .= checked('1', $view_rating, false).' /> ';
    $output .= '<label for="'.$show_rating.'">';
    $output .= sprintf(__('Hide Untappd rating %s', EMBM_DOMAIN), __($title, EMBM_DOMAIN));
    $output .= '</label></p>';

    // Return Untappd output
    return $output;
}

/**
 * Outputs custom stylesheet URL input
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_css()
{
    $options = get_option(EMBM_OPTIONS);


    echo '<p><input id="embm_css_url" name="embm_options[embm_css_url]" size="50" type="url" value="'.esc_url($options['embm_css_url']).'" /></p>';
    echo '<p class="description">';
    echo __('Enter a full URL that points to a stylesheet file to override default EM Beer Manager styles.', EMBM_DOMAIN);
    echo '</p>';
}

/**
 * Outputs global display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_display()
{
    echo EMBM_Admin_Settings_Section_display(__('globally', EMBM_DOMAIN));
}

/**
 * Outputs global Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Global_untappd()
{
    echo EMBM_Admin_Settings_Section_untappd(__('globally', EMBM_DOMAIN));
}

/**
 * Outputs custom group slug option
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_slug()
{
    $options = get_option(EMBM_OPTIONS);

    echo '<p><input id="embm_group_slug" name="embm_options[embm_group_slug]" size="15" type="text" value="';
    echo sanitize_key($options['embm_group_slug']);
    echo '" /></p><p class="description">';
    echo __('Rename the beer group URLs with your own custom slug name.', EMBM_DOMAIN) . '<br />';
    echo sprintf(
        __('You must %s after changing this.', EMBM_DOMAIN),
        sprintf('<a href="options-permalink.php">%s</a>', __('refresh your permalinks', EMBM_DOMAIN))
    ).'</p><p class="timezone-info">';
    echo __('By default URLs will look like', EMBM_DOMAIN).': <code>yoursite.com/<strong>group</strong>/your-group-name</code>.</p>';
}

/**
 * Outputs group display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_display()
{
    echo EMBM_Admin_Settings_Section_display(__('in groups', EMBM_DOMAIN), 'group');
}

/**
 * Outputs group Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Group_untappd()
{
    echo EMBM_Admin_Settings_Section_untappd(__('in groups', EMBM_DOMAIN), 'group');
}

/**
 * Outputs style reset settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Style_reset()
{
    echo '<p><button class="embm-settings--styles-button button-secondary">'.__('Restore Styles', EMBM_DOMAIN).'</button></p>';
    echo '<p class="description">'.__('Restore missing or deleted beer styles from the pre-loaded list.', EMBM_DOMAIN).'</p>';

    // Add modal prompt
    add_thickbox();

?>
    <div id="embm-styles-reset-prompt" style="display:none;">
        <div class="embm-styles-reset-prompt--content">
            <p><?php _e('This will restore any missing or deleted beer styles from the pre-loaded Untappd list.', EMBM_DOMAIN); ?></p>
            <p>
                <?php
                    printf(
                        __('Your custom or modified styles will %s be affected by this action.', EMBM_DOMAIN),
                        sprintf('<span class="emphasis">%s</span>', __('NOT', EMBM_DOMAIN))
                    );
                ?>
            </p>
            <p><strong><?php _e('Do you wish to proceed?', EMBM_DOMAIN); ?></strong></p>
            <p>
                <a href="#" id="embm-styles-reset-prompt--yes" class="button-primary"><?php _e('YES', EMBM_DOMAIN); ?></a>
                <a href="#" id="embm-styles-reset-prompt--no" class="button-secondary"><?php _e('NO', EMBM_DOMAIN); ?></a>
            </p>
        </div>
    </div>
    <a
        href="#TB_inline?width=550&height=155&inlineId=embm-styles-reset-prompt"
        class="thickbox"
        id="embm-styles-reset-prompt--button"
        title="<?php _e('Restore EM Beer Manager Styles', EMBM_DOMAIN); ?>"
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
    echo EMBM_Admin_Settings_Section_display(__('on style pages', EMBM_DOMAIN), 'style');
}

/**
 * Outputs style Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Style_untappd()
{
    echo EMBM_Admin_Settings_Section_untappd(__('on style pages', EMBM_DOMAIN), 'style');
}

/**
 * Outputs menu display settings
 *
 * @return void
 */
function EMBM_Admin_Settings_Menu_display()
{
    echo EMBM_Admin_Settings_Section_display(__('on menu pages', EMBM_DOMAIN), 'menu');
}

/**
 * Outputs menu Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Menu_untappd()
{
    echo EMBM_Admin_Settings_Section_untappd(__('on menu pages', EMBM_DOMAIN), 'menu');
}

/**
 * Outputs single beer comment options
 *
 * @return void
 */
function EMBM_Admin_Settings_Single_comments()
{
    $options = get_option(EMBM_OPTIONS);

    $use_comments = null;
    if (isset($options['embm_comment_check'])) {
        $use_comments = $options['embm_comment_check'];
    }

    echo '<p><input name="embm_options[embm_comment_check]" type="checkbox" id="embm_comment_check" value="1"'.checked('1', $use_comments, false).' /> ';
    echo '<label for="embm_comment_check">'.__('Enable comments on single beer pages', EMBM_DOMAIN).'</label></p>';
}

/**
 * Outputs single beer display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Single_display()
{
    echo EMBM_Admin_Settings_Section_display(__('on single beer pages', EMBM_DOMAIN), 'single');
}

/**
 * Outputs single Untappd display options
 *
 * @return void
 */
function EMBM_Admin_Settings_Single_untappd()
{
    $options = get_option(EMBM_OPTIONS);

    // Get review settings
    $view_reviews = isset($options['embm_reviews_show_single']) ? $options['embm_reviews_show_single'] : null;
    $reviews_count = isset($options['embm_reviews_count_single']) ? $options['embm_reviews_count_single'] : 5;

    // Output ratings setting
    echo EMBM_Admin_Settings_Section_untappd(__('on single beer pages', EMBM_DOMAIN), 'single');

    // Output review setting
    echo '<p><input name="embm_options[embm_reviews_show_single]" type="checkbox" id="embm_reviews_show_single" value="1"'.checked('1', $view_reviews, false).' /> ';
    echo '<label for="embm_reviews_show_single">'.__('Hide Untappd check-ins on single beer pages', EMBM_DOMAIN).'</label>';

    // Output review count setting
    echo '<p class="embm-settings--review-count"><label for="embm_reviews_count_single">'.__('Show', EMBM_DOMAIN);
    echo '<input id="embm_reviews_count_single" name="embm_options[embm_reviews_count_single]" type="number" min="1" max="15" value="'.$reviews_count.'" />';
    echo sprintf(__('check-ins (max. %d)', EMBM_DOMAIN), 15);
    echo '</label></p>';

    // Output section description
    echo '<p class="description">('.__('This setting may be overridden for individual beers.', EMBM_DOMAIN).')</p>';
}

/**
 * Loads settings page for users with valid permissions
 *
 * @return void
 */
function EMBM_Admin_Settings_page()
{
    // Get tabs data
    $tabs = array(
        array(
            'id'      => 'untappd',
            'name'    => sprintf('%s <span>%s</span>', __('Untappd Import', EMBM_DOMAIN), __('Labs', EMBM_DOMAIN)),
            'hide'    => EMBM_Core_Beer_disabled()
        ),
        array(
            'id'      => 'utfb',
            'name'    => sprintf('%s <span>%s</span>', __('Untappd for Business Import', EMBM_DOMAIN), __('Labs', EMBM_DOMAIN)),
            'hide'    => EMBM_Core_Beer_disabled()
        ),
        array(
            'id'      => 'usage',
            'name'    => __('Usage', EMBM_DOMAIN),
            'hide'    => false
        )
    );

    // Get settings page sections
    global $wp_settings_sections;
    $sections = $wp_settings_sections[EMBM_DOMAIN];

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', EMBM_DOMAIN));
    }

    // Show any notices
    EMBM_Admin_Notices_show();

?>
<div class="wrap embm-settings--page">

    <h1 class="embm-settings--title">
        <?php _e('EM Beer Manager', EMBM_DOMAIN); ?>
        <span class="embm-settings--title-version"><?php echo 'v'.get_option(EMBM_VERSION_KEY); ?></span>
    </h1>

    <div id="embm-settings--tabs" class="embm-settings--tabs-wrapper">
        <ul class="nav-tab-wrapper">
            <li>
                <a href="#settings" class="embm-nav-tab nav-tab nav-tab-active nav-tab-settings">
                    <?php _e('Settings', EMBM_DOMAIN); ?>
                </a>
            </li>
            <?php foreach ($tabs as $tab) : ?>
                <?php if (!$tab['hide']) : ?>
                    <li>
                        <a href="#<?php echo $tab['id']; ?>" class="embm-nav-tab nav-tab nav-tab-<?php echo $tab['id']; ?>">
                            <?php echo $tab['name']; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <div id="settings" class="embm-settings--tab-settings">
            <div class="embm-settings--navbox">
                <span
                    class="dashicons dashicons-arrow-right-alt2"
                    title="<?php _e('Collpase/Expand Panel', EMBM_DOMAIN); ?>"
                    id="embm-settings--navbox-toggle">
                </span>
                <ul>
                    <li><strong>Jump to:</strong></li>
                    <li><a href="#top"><?php _e('Top', EMBM_DOMAIN); ?></a></li>
                    <?php foreach ($sections as $section): ?>
                        <li><a href="#<?php echo $section['id']; ?>"><?php echo $section['title']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="post" action="options.php" class="embm-settings--form">
                <?php
                    settings_fields(EMBM_OPTIONS);
                    do_settings_sections(EMBM_DOMAIN);
                ?>
                <p style="margin-top:1em;">
                    <input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', EMBM_DOMAIN); ?>" />
                </p>
            </form>
        </div>

        <?php foreach ($tabs as $tab) : ?>
            <?php if (!$tab['hide']) : ?>
                <div id="<?php echo $tab['id']; ?>" class="embm-settings--tab embm-settings--tab-<?php echo $tab['id']; ?>">
                    <?php include_once EMBM_PLUGIN_DIR.'includes/admin/tabs/embm-tabs-'.$tab['id'].'.php'; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php include_once EMBM_PLUGIN_DIR.'includes/admin/embm-admin-footer.php'; ?>

</div>
<?php
}
