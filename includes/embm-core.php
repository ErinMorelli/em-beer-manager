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
 * @package EMBM\Core
 */


/**
 * Loads the custom EMBM post type
 *
 * @return void
 */
function EMBM_Core_beer()
{
    // Set custom post type terminology
    $labels = array(
        'name'                  => __('Beers', 'embm'),
        'singular_name'         => __('Beer', 'embm'),
        'add_new'               => __('Add New', 'embm'),
        'add_new_item'          => __('Add New Beer', 'embm'),
        'edit_item'             => __('Edit Beer', 'embm'),
        'new_item'              => __('New Beer', 'embm'),
        'all_items'             => __('All Beers', 'embm'),
        'view_item'             => __('View Beer', 'embm'),
        'search_items'          => __('Search Beers', 'embm'),
        'not_found'             => __('No beers found', 'embm'),
        'not_found_in_trash'    => __('No beers found in the Trash', 'embm'),
        'parent_ithwh_colon'    => '',
        'menu_name'             => __('Beers', 'embm')
    );

    // Set up custom post type options
    $args = array(
        'labels'            => $labels,
        'description'       => 'Holds beer specific data',
        'public'            => true,
        'capability_type'   => 'post',
        'hierarchical'      => false,
        'taxonomies'        => array('embm_style', 'embm_group'),
        'has-archive'       => true,
        'menu_position'     => 5,
        'show_in_rest'       => true,
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'rewrite'           => array(
            'slug'          => 'beers',
            'with_front'    => false,
            'feeds'         => true,
            'pages'         => true
        ),
        'supports'        => array(
            'title',
            'editor',
            'thumbnail',
            'revisions',
            'comments'
        )
    );

    // Register post type
    register_post_type('embm_beer', $args);
}

// Loads the custom post type
add_action('init', 'EMBM_Core_beer');

// Add thumbnail support to custom post type
add_theme_support('post-thumbnails', array('embm_beer'));


/**
 * Determine comment open/closed status
 *
 * @param bool $open    Current open/closed status
 * @param int  $post_id WP post ID
 *
 * @return bool
 */
function EMBM_Core_Comments_status($open, $post_id)
{
    // Get the post from ID
    $post = get_post($post_id);

    // Get EMBM options
    $options = get_option('embm_options');

    // Get setting for comments
    $use_comments = null;
    if (isset($options['embm_comment_check'])) {
        $use_comments = $options['embm_comment_check'];
    }

    // Close comments if disabled
    if ($use_comments != '1') {
        if ($post->post_type == 'embm_beer') {
            $open = false;
        }
    }

    return $open;
}

/**
 * Set comments template
 *
 * @return void
 */
function EMBM_Core_Comments_template()
{
    // Get global post object
    global $post;

    // Get EMBM options
    $options = get_option('embm_options');

    // Get setting for comments
    $use_comments = null;
    if (isset($options['embm_comment_check'])) {
        $use_comments = $options['embm_comment_check'];
    }

    // Load blank template if disabled
    if ($use_comments != '1') {
        if ($post->post_type == 'embm_beer') {
            return EMBM_PLUGIN_DIR.'includes/templates/embm-comments.php';
        }
    }
}

/**
 * Toggles comments as enabled/disabled
 *
 * @return void
 */
function EMBM_Core_Comments_toggle()
{
    // Get EMBM options
    $options = get_option('embm_options');

    // Get settings for comments
    $use_comments = null;
    if (isset($options['embm_comment_check'])) {
        $use_comments = $options['embm_comment_check'];
    }

    if ($use_comments != '1') {
        // If disabled, remove support for comments from post type
        if (post_type_supports('embm_beer', 'comments')) {
            remove_post_type_support('embm_beer', 'comments');
            remove_post_type_support('embm_beer', 'trackbacks');
        }
    } else {
        // If enabled, add support for comments to post type
        if (!post_type_supports('embm_beer', 'comments')) {
            add_post_type_support('embm_beer', 'comments');
            add_post_type_support('embm_beer', 'trackbacks');
        }
    }

    // Add custom filters for comments
    add_filter('comments_open', 'EMBM_Core_Comments_status', 20, 2);
    add_filter('pings_open', 'EMBM_Core_Comments_status', 20, 2);
    add_filter('comments_template', 'EMBM_Core_Comments_template');
}

add_action('init', 'EMBM_Core_Comments_toggle');


/**
 * Add custom contextual help
 *
 * @return void
 */
function EMBM_Core_Meta_help()
{
    // Get the current screen
    $screen = get_current_screen();
    // echo var_dump($screen);

    $help_screens = array(
        'embm_beer',
        'edit-embm_beer'
    );

    // Check if current screen is admin page
    if (!in_array($screen->id, $help_screens)) {
        return;
    }

    // Get default help data
    $default_help = EMBM_Plugin_help();

    // Untappd Integration help tab
    $screen->add_help_tab($default_help['untappd']);

    // Untappd Beer ID help
    $screen->add_help_tab(
        array(
            'id'       => 'embm-untappd-beer-id',
            'title'    => __('Untappd Beer ID', 'embm'),
            'content'  => __(
                '<p>Find your Untappd beer ID by visiting your beer\'s official page. The URL will be formatted like this:</p>'.
                '<p><code>https://untappd.com/b/the-alchemist-heady-topper/<strong>4691</strong></code></p>'.
                '<p>The string of numbers at the end of the URL is your beer\'s ID.</p>',
                'embm'
            )
        )
    );

    // Help sidebar
    $screen->set_help_sidebar(
        '<p><a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=embm-settings">' . __('EM Beer Manager Settings', 'embm') . '</a></p>' .
        $default_help['sidebar']
    );
}

// Add contextual help
add_action('load-post.php', 'EMBM_Core_Meta_help');
add_action('load-post-new.php', 'EMBM_Core_Meta_help');
add_action('load-edit.php', 'EMBM_Core_Meta_help');


/**
 * Add custom meta boxes to post type
 *
 * @return void
 */
function EMBM_Core_Meta_boxes()
{
    // Add Beer Profile metabox to sidebar
    add_meta_box(
        'beer-specs',
        'Beer Profile',
        'EMBM_Core_Meta_specs',
        'embm_beer',
        'side',
        'core'
    );

    // Add More Beer Information metabox to main content
    add_meta_box(
        'beer-info',
        'More Beer Information',
        'EMBM_Core_Meta_info',
        'embm_beer',
        'normal',
        'core'
    );
}

// Load metaboxes
add_action('add_meta_boxes', 'EMBM_Core_Meta_boxes');

/**
 * Outputs Beer Profile metabox content
 *
 * @return void
 */
function EMBM_Core_Meta_specs()
{
    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_custom($post->ID);

    // Set custom post data values
    $b_malts = isset($beer_entry['malts']) ? esc_attr($beer_entry['malts'][0]) : '';
    $b_hops = isset($beer_entry['hops']) ? esc_attr($beer_entry['hops'][0]) : '';
    $b_adds= isset($beer_entry['adds']) ? esc_attr($beer_entry['adds'][0]) : '';
    $b_yeast = isset($beer_entry['yeast']) ? esc_attr($beer_entry['yeast'][0]) : '';
    $b_ibu = isset($beer_entry['ibu']) ? esc_attr($beer_entry['ibu'][0]) : '0';
    $b_abv = isset($beer_entry['abv']) ? esc_attr($beer_entry['abv'][0]) : '0';

    // Setup nonce field for options
    wp_nonce_field('embm_specs_save', 'embm_specs_save_nonce');

?>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
                    <p><label for="malts"><strong><?php _e('Malts', 'embm'); ?></strong></label><br />
                    <input type="text" name="malts" id="malts" style="width:100%;" value="<?php echo $b_malts; ?>" /></p>
                    <p><label for="hops"><strong><?php _e('Hops', 'embm'); ?></strong></label><br />
                    <input type="text" name="hops" id="hops" style="width:100%;" value="<?php echo $b_hops; ?>" /></p>
                    <p><label for="adds"><strong><?php _e('Additions/Spices', 'embm'); ?></strong></label><br />
                    <input type="text" name="adds" id="adds" style="width:100%;" value="<?php echo $b_adds; ?>" /></p>
                    <p><label for="yeast"><strong><?php _e('Yeast', 'embm'); ?></strong></label><br />
                    <input type="text" name="yeast" id="yeast" style="width:100%;" value="<?php echo $b_yeast; ?>" /></p>
                    <hr />
                    <p><label for="abv"><strong><?php _e('ABV', 'embm'); ?></strong></label><br />
                    <input type="number" name="abv" id="abv" min="0.0" max="100.0" step="0.1" value="<?php echo $b_abv; ?>" /> %</p>
                    <p><label for="ibu"><strong><?php _e('IBU', 'embm'); ?></strong></label><br />
                    <input type="number" name="ibu" id="style" min="0" max="100" step="1" value="<?php echo $b_ibu; ?>" /></p>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}

/**
 * Save the options from the Beer Profile metabox
 *
 * @param int $post_id WP post ID
 *
 * @return void
 */
function EMBM_Core_Meta_Specs_save($post_id)
{
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Validate nonce
    if (!isset($_POST['embm_specs_save_nonce']) || !wp_verify_nonce($_POST['embm_specs_save_nonce'], 'embm_specs_save')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post')) {
        return;
    }

    // Save input
    if (isset($_POST['malts'])) {
        update_post_meta($post_id, 'malts', esc_attr($_POST['malts']));
    }
    if (isset($_POST['hops'])) {
        update_post_meta($post_id, 'hops', esc_attr($_POST['hops']));
    }
    if (isset($_POST['adds'])) {
        update_post_meta($post_id, 'adds', esc_attr($_POST['adds']));
    }
    if (isset($_POST['yeast'])) {
        update_post_meta($post_id, 'yeast', esc_attr($_POST['yeast']));
    }
    if (isset($_POST['ibu'])) {
        update_post_meta($post_id, 'ibu', esc_attr($_POST['ibu']));
    }
    if (isset($_POST['abv'])) {
        update_post_meta($post_id, 'abv', esc_attr($_POST['abv']));
    }
}

// Save Beer Profile metabox inputs
add_action('save_post', 'EMBM_Core_Meta_Specs_save');

/**
 * Outputs More Beer Information metabox content
 *
 * @return void
 */
function EMBM_Core_Meta_info()
{
    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_custom($post->ID);

    // Set custom post data values
    $b_num = isset($beer_entry['beer_num']) ? esc_attr($beer_entry['beer_num'][0]) : '';
    $b_avail = isset($beer_entry['avail']) ? esc_attr($beer_entry['avail'][0]) : '';
    $b_untap = isset($beer_entry['untappd']) ? esc_attr($beer_entry['untappd'][0]) : '';
    $b_notes = isset($beer_entry['notes']) ? esc_attr($beer_entry['notes'][0]) : '';

    // Get Untapped settings from DB
    $ut_option = get_option('embm_options');
    $use_untappd = null;
    if (isset($ut_option['embm_untappd_check'])) {
        $use_untappd = $ut_option['embm_untappd_check'];
    }

    // Setup nonce field for options
    wp_nonce_field('embm_info_save', 'embm_info_save_nonce');

?>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td valign="top">
                    <div class="embm-more-info">
                        <p><label for="beer_num"><strong><?php _e('Beer Number', 'embm'); ?></strong></label><br />
                        <input type="number" name="beer_num" id="beer_num" min="000" max="999" step="1" value="<?php echo $b_num; ?>" /></p>
                    </div>

                    <?php if ($use_untappd != '1') : ?>
                        <div class="embm-more-info">
                            <p><label for="untappd"><strong><?php _e('Untappd Beer ID', 'embm'); ?></strong></label><br />
                            <input type="number" name="untappd" id="untappd" value="<?php echo $b_untap; ?>" />
                            <a data-help="embm-untappd-beer-id" class="embm-settings--help">?</a></p>
                        </div>
                    <?php endif; ?>

                    <div class="embm-more-info">
                        <p><label for="avail"><strong><?php _e('Availability', 'embm'); ?></strong></label><br />
                        <input type="text" name="avail" id="avail" value="<?php echo $b_avail; ?>" /></p>
                    </div>
                </td>
            <tr>
            <tr>
                <td valign="top">
                    <p><label for="notes"><strong><?php _e('Additional Notes/Food Pairings', 'embm'); ?></strong></label><br />
                    <textarea name="notes" id="notes" rows="7" style="width:100%"><?php echo $b_notes; ?></textarea></p>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}

/**
 * Save the options from the More Beer Information metabox
 *
 * @param int $post_id WP post ID
 *
 * @return void
 */
function EMBM_Core_Meta_Info_save($post_id)
{
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Validate nonce
    if (!isset($_POST['embm_info_save_nonce']) || !wp_verify_nonce($_POST['embm_info_save_nonce'], 'embm_info_save')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post')) {
        return;
    }

    // Save input
    if (isset($_POST['beer_num'])) {
        update_post_meta($post_id, 'beer_num', esc_attr($_POST['beer_num']));
    }
    if (isset($_POST['avail'])) {
        update_post_meta($post_id, 'avail', esc_attr($_POST['avail']));
    }
    if (isset($_POST['notes'])) {
        update_post_meta($post_id, 'notes', esc_attr($_POST['notes']));
    }
    if (isset($_POST['untappd'])) {
        update_post_meta($post_id, 'untappd', esc_attr($_POST['untappd']));
    }
}

// Save Beer meta box inputs
add_action('save_post', 'EMBM_Core_Meta_Info_save');


/**
 * Retrieves and formats beer custom post meta data
 *
 * @param int    $post_id WP post ID
 * @param string $attr    Attribute name
 *
 * @return string
 */
function EMBM_Core_Beer_attr($post_id, $attr)
{
    // Get beer attribute data
    $b_attr = get_post_meta($post_id, $attr, true);

    // Format the data
    if ($attr == 'abv') {
        return $b_attr . '%';
    } elseif ($attr == 'beer_num') {
        return '#' . $b_attr;
    } elseif ($attr == 'untappd') {
        return 'https://untappd.com/beer/' . $b_attr;
    } else {
        return $b_attr;
    }
}


/**
 * Retrieves beer style name
 *
 * @param int $post_id WP post ID
 *
 * @return string
 */
function EMBM_Core_Beer_style($post_id)
{
    // Get the styles for the beer
    $types = wp_get_object_terms($post_id, 'embm_style');

    // Return the first item's name
    foreach ($types as $type) {
        return $type->name;
    }
}


/**
 * Loads the custom EMBM styles taxonomy
 *
 * @return void
 */
function EMBM_Core_styles()
{
    // Set custom taxonomy terminology
    $labels = array(
        'name'                          => __('Styles', 'embm'),
        'singular_name'                 => __('Style', 'embm'),
        'search_items'                  => __('Search Styles', 'embm'),
        'all_items'                     => __('All Styles', 'embm'),
        'edit_item'                     => __('Edit Style', 'embm'),
        'update_item'                   => __('Update Style', 'embm'),
        'add_new_item'                  => __('Add New Style', 'embm'),
        'new_item_name'                 => __('New Style Name', 'embm'),
        'popular_items'                 => __('Popular Styles', 'embm'),
        'choose_from_most_used'         => __('Choose from the most used styles', 'embm'),
        'separate_items_with_commas'    => __('Separate styles with commas', 'embm'),
        'add_or_remove_items'           => __('Add or remove styles', 'embm'),
        'menu_name'                     => __('Styles', 'embm')
    );

    // Set up custom taxonomy options
    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'          => 'beers/style',
            'with_front'    => false
        )
    );

    // Register the styles taxonomy with the EMBM custom post type
    register_taxonomy('embm_style', array('embm_beer'), $args);

    // Populate taxonomy with terms, if they haven't been loaded yet
    if (!get_option('embm_styles_loaded')) {
        EMBM_Core_Styles_populate();
    }
}

// Loads the custom Styles taxonomy
add_action('init', 'EMBM_Core_styles', 0);

/**
 * Populates the styles taxonomy with terms from Beer Advocate
 *
 * @return void
 */
function EMBM_Core_Styles_populate()
{
    // Load styles list from text files
    // Generated from BeerAdvocate (http://www.beeradvocate.com/beer/style/)
    $beer_styles_file = EMBM_PLUGIN_DIR.'assets/beer-styles.txt';

    // Open file
    $beer_styles = @fopen($beer_styles_file, 'r');

    // Read file
    while (!feof($beer_styles)) {
        // Get styles, line-by-line
        $beer_style = fgets($beer_styles);

        // Add style as term in taxonomy
        wp_insert_term($beer_style, 'embm_style');
    }

    // Close file
    fclose($beer_styles);

    // Store the fact that styles were loaded
    update_option('embm_styles_loaded', true);
}


/**
 * Loads the custom EMBM group taxonomy
 *
 * @return void
 */
function EMBM_Core_group()
{
    // Set custom taxonomy terminology
    $labels = array(
        'name'                          => __('Groups', 'embm'),
        'singular_name'                 => __('Group', 'embm'),
        'search_items'                  => __('Search Groups', 'embm'),
        'all_items'                     => __('All Groups', 'embm'),
        'edit_item'                     => __('Edit Group', 'embm'),
        'update_item'                   => __('Update Group', 'embm'),
        'add_new_item'                  => __('Add New Group', 'embm'),
        'new_item_name'                 => __('New Group Name', 'embm'),
        'popular_items'                 => __('Popular Groups', 'embm'),
        'choose_from_most_used'         => __('Choose from the most used groups', 'embm'),
        'separate_items_with_commas'    => __('Separate groups with commas', 'embm'),
        'add_or_remove_items'           => __('Add or remove groups', 'embm'),
        'menu_name'                     => __('Groups', 'embm')
    );

    // Set default slug
    $group_slug = 'beer/group';

    // Override slug if user has custom option set
    $options = get_option('embm_options');
    if (isset($options['embm_group_slug'])) {
        $new_slug = sanitize_key($options['embm_group_slug']);
        $group_slug = 'beer/'.$new_slug;
    }

    // Set up custom taxonomy options
    $args = array(
        'hierarchical'       => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'          => $group_slug,
            'with_front'    => false
        )
    );

    // Register the group taxonomy with the EMBM custom post type
    register_taxonomy('embm_group', array('embm_beer'), $args);
}

// Loads the custom Group taxonomy
add_action('init', 'EMBM_Core_group', 0);
