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
        'labels'                => $labels,
        'description'           => __('Holds beer specific data', 'embm'),
        'public'                => true,
        'capability_type'       => 'post',
        'hierarchical'          => false,
        'taxonomies'            => array('embm_style', 'embm_group'),
        'has-archive'           => true,
        'menu_position'         => 5,
        'show_in_rest'          => true,
        'rest_base'             => 'embm_beers',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'rewrite'               => array(
            'slug'              => 'beers',
            'with_front'        => false,
            'feeds'             => true,
            'pages'             => true
        ),
        'supports'              => array(
            'title',
            'editor',
            'thumbnail',
            'revisions',
            'comments'
        )
    );

    // Register post type
    register_post_type('embm_beer', $args);

    // Load metaboxes
    EMBM_Core_Beer_metaboxes();
}

// Loads the custom post type
add_action('init', 'EMBM_Core_beer');

// Add thumbnail support to custom post type
add_theme_support('post-thumbnails', array('embm_beer'));

/**
 * Add custom metaboxes to post type
 *
 * @return void
 */
function EMBM_Core_Beer_metaboxes()
{
    // Set path to metabox files
    $metabox_root = EMBM_PLUGIN_DIR.'includes/metaboxes';

    // Iteratively load any metaboxes
    foreach (scandir($metabox_root) as $filename) {
        // Set metaboxes path
        $path = $metabox_root . '/' . $filename;

        // If the PHP file exists, load it
        if (is_file($path) && preg_match('/embm-metabox-.*\.php$/', $filename)) {
            include $path;
        }
    }
}

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
    $use_comments = isset($options['embm_comment_check']) ? $options['embm_comment_check'] : null;

    // Close comments if disabled
    if (($use_comments != '1') && ($post->post_type == 'embm_beer')) {
        $open = false;
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
    $use_comments = isset($options['embm_comment_check']) ? $options['embm_comment_check'] : null;

    // Load blank template if disabled
    if (($use_comments != '1') && ($post->post_type == 'embm_beer')) {
        return EMBM_PLUGIN_DIR.'includes/templates/embm-template-comments.php';
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
    $use_comments = isset($options['embm_comment_check']) ? $options['embm_comment_check'] : null;

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

    // Add Untappd tabs
    $screen->add_help_tab($default_help['untappd']);
    $screen->add_help_tab($default_help['untappd_id']);
    $screen->add_help_tab($default_help['untappd_limit']);

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
 * Determines whether or not Untappd integration is disabled.
 *
 * @return bool
 */
function EMBM_Core_Beer_disabled()
{
    $ut_option = get_option('embm_options');
    return (isset($ut_option['embm_untappd_check']) && $ut_option['embm_untappd_check'] == '1');
}

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
    // Set attr name
    $attr_name = 'embm_' . $attr;

    // Get beer attribute data
    $b_attr = get_post_meta($post_id, $attr_name, true);

    // Format the data
    switch ($attr) {
    case 'abv':
        return $b_attr . '%';
    case 'beer_num':
        return '#' . $b_attr;
    case 'notes':
        return html_entity_decode($b_attr);
    case 'untappd':
        return EMBM_Core_Beer_disabled() ? null : 'https://untappd.com/beer/' . $b_attr;
    case 'untappd_data':
        if (EMBM_Core_Beer_disabled()) {
            return null;
        }

        // Get token
        $token = EMBM_Admin_Authorize_token();

        // Get beer ID
        $beer_id = get_post_meta($post_id, 'embm_untappd', true);
        if (null == $token || $beer_id == '') {
            return null;
        }

        // Get beer data
        $api_root = EMBM_UNTAPPD_API_URL.$token;
        $res = EMBM_Admin_Untappd_beer($api_root, $beer_id, $post_id);

        // Return data
        if (!is_null($res) && array_key_exists('beer', $res)) {
            return $res['beer'];
        } else if (is_string($res)) {
            return $res;
        }
        return null;
    default:
        return $b_attr;
    }
}

/**
 * Get an array of ratings format options
 *
 * @return array Ratings formats and options
 */
function EMBM_Core_Beer_ratings()
{
    $stars = '<span class="embm-beer--rating-stars">%s</span>';
    $rating = '<span class="embm-beer--rating-score">(%.2f)</span>';
    $count = '<span class="embm-beer--rating-count">%s %s</span>';

    return array(
        '1' => array(
            'form'  => $stars,
            'desc'  => '&starf;&starf;&starf;&starf;&star;'
        ),
        '2' => array(
            'form'  => $stars . ' ' . $rating,
            'desc'  => '&starf;&starf;&starf;&starf;&star; (4.0)'
        ),
        '3' => array(
            'form'  => $stars . ' ' . $rating . ' | ' . $count,
            'desc'  => sprintf(
                '&starf;&starf;&starf;&starf;&star; (4.0) | 1,234 %s',
                __('Ratings', 'embm')
            )
        )
    );
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
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array(
            'slug'              => 'beers/style',
            'with_front'        => false
        ),
        'show_in_rest'          => true,
        'rest_base'             => 'embm_styles',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
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
    // Load styles list from text files (Generated from Untappd)
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
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array(
            'slug'              => $group_slug,
            'with_front'        => false
        ),
        'show_in_rest'          => true,
        'rest_base'             => 'embm_groups',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );

    // Register the group taxonomy with the EMBM custom post type
    register_taxonomy('embm_group', array('embm_beer'), $args);
}

// Loads the custom Group taxonomy
add_action('init', 'EMBM_Core_group', 0);

/**
 * Register custom beer fields with WP API
 *
 * @return void
 */
function EMBM_Core_Beer_api()
{
    // Make sure that the WP REST API plugin is installed
    if (!function_exists('register_rest_field')) {
        return;
    }

    // Set callback functions
    $get_callback = 'EMBM_Core_Beer_Api_get';
    $update_callback = 'EMBM_Core_Beer_Api_update';

    // Register profile API field
    register_rest_field(
        'embm_beer',
        'embm_profile',
        array(
            'get_callback'    => $get_callback,
            'update_callback' => $update_callback,
            'schema'          => EMBM_Core_Beer_Api_schema('profile')
        )
    );

    // Register extras API field
    register_rest_field(
        'embm_beer',
        'embm_extras',
        array(
            'get_callback'    => $get_callback,
            'update_callback' => $update_callback,
            'schema'          => EMBM_Core_Beer_Api_schema('extras')
        )
    );

    // Retrieve Untappd settings
    $ut_option = get_option('embm_options');

    // Check if Untappd is disabled
    if (!EMBM_Core_Beer_disabled()) {
        // Register Untappd URL API field
        register_rest_field(
            'embm_beer',
            'embm_untappd',
            array(
                'get_callback'    => $get_callback,
                'update_callback' => $update_callback,
                'schema'          => EMBM_Core_Beer_Api_schema('untappd')
            )
        );
    }
}

// Load additional WP API fields
add_action('rest_api_init', 'EMBM_Core_Beer_api');

/**
 * Handle GET requests for additional beer fields
 *
 * @param object $object     The WP object being requested
 * @param string $field_name The name of the API field requested
 * @param object $request    The HTTP request object
 *
 * @return string/array
 */
function EMBM_Core_Beer_Api_get($object, $field_name, $request)
{
    // Get the beer id
    $beer_id = $object['id'];

    // Return beer profile data
    if ($field_name == 'embm_profile') {
        // Get ABV and IBU as int/float
        $abv = floatval(get_post_meta($beer_id, 'embm_abv', true));
        $ibu = intval(EMBM_Core_Beer_attr($beer_id, 'ibu'));

        // Return formatted info
        return array(
            'malts'     => EMBM_Core_Beer_attr($beer_id, 'malts'),
            'hops'      => EMBM_Core_Beer_attr($beer_id, 'hops'),
            'additions' => EMBM_Core_Beer_attr($beer_id, 'adds'),
            'yeast'     => EMBM_Core_Beer_attr($beer_id, 'yeast'),
            'abv'       => ($abv == 0) ? null : $abv,
            'ibu'       => ($ibu == 0) ? null : $ibu
        );
    }

    // Return beer extras data
    if ($field_name == 'embm_extras') {
        // Get Beer Number as int
        $beer_num = intval(get_post_meta($beer_id, 'embm_beer_num', true));

        // Return formatted array
        return array(
            'availability'  => EMBM_Core_Beer_attr($beer_id, 'avail'),
            'notes'         => EMBM_Core_Beer_attr($beer_id, 'notes'),
            'beer_number'   => ($beer_num == 0) ? null : $beer_num
        );
    }

    // Return beer Untappd information
    if ($field_name == 'embm_untappd') {
        // Get Untappd ID as int
        $untappd_id = intval(get_post_meta($beer_id, 'embm_untappd', true));

        // Return formatted array
        return array (
            'id' => ($untappd_id == 0) ? null : $untappd_id
        );
    }
}

/**
 * Handle PUT/POST requests for additional beer fields
 *
 * @param mixed  $value      The value of the field
 * @param object $object     The object from the response
 * @param string $field_name Name of field
 *
 * @return string/array
 */
function EMBM_Core_Beer_Api_update($value, $object, $field_name)
{
    // Check for valid entry
    if (!$value || !is_array($value)) {
        return;
    }

    // Get the beer id
    $beer_id = $object->ID;

    // Return beer profile data
    if ($field_name == 'embm_profile') {
        // Save input
        if (isset($value['malts']) && is_string($value['malts'])) {
            update_post_meta($beer_id, 'embm_malts', esc_attr($value['malts']));
        }
        if (isset($value['hops']) && is_string($value['hops'])) {
            update_post_meta($beer_id, 'embm_hops', esc_attr($value['hops']));
        }
        if (isset($value['additions']) && is_string($value['additions'])) {
            update_post_meta($beer_id, 'embm_adds', esc_attr($value['additions']));
        }
        if (isset($value['yeast']) && is_string($value['yeast'])) {
            update_post_meta($beer_id, 'embm_yeast', esc_attr($value['yeast']));
        }
        if (isset($value['ibu']) && is_int($value['ibu'])) {
            update_post_meta($beer_id, 'embm_ibu', esc_attr($value['ibu']));
        }
        if (isset($value['abv']) && (is_float($value['abv']) || is_int($value['abv']))) {
            update_post_meta($beer_id, 'embm_abv', esc_attr($value['abv']));
        }
    }

    // Return beer extras data
    if ($field_name == 'embm_extras') {
        // Save input
        if (isset($value['beer_number']) && is_int($value['beer_number'])) {
            update_post_meta($beer_id, 'embm_beer_num', esc_attr($value['beer_number']));
        }
        if (isset($value['availability']) && is_string($value['availability'])) {
            update_post_meta($beer_id, 'embm_avail', esc_attr($value['availability']));
        }
        if (isset($value['notes']) && is_string($value['notes'])) {
            update_post_meta($beer_id, 'embm_notes', esc_attr($value['notes']));
        }
    }

    // Return beer Untappd information
    if ($field_name == 'embm_untappd') {
        // Save input
        if (isset($value['id']) && is_int($value['id'])) {
            // Get old id
            $new_id = esc_attr($value['id']);
            $old_id = get_post_meta($post_id, 'embm_untappd', true);

            // Skip if this is not a new ID
            if ($beer_id !== $old_id) {
                // Save new ID
                update_post_meta($beer_id, 'embm_untappd', $new_id);

                // Get token
                $token = EMBM_Admin_Authorize_token();

                // Make sure we're authorized
                if (null !== $token) {
                    // Set API Root
                    $api_root = EMBM_UNTAPPD_API_URL.$token;

                    // Update cached data
                    EMBM_Admin_Untappd_beer($api_root, $new_id, $beer_id, true);
                }
            }
        }
    }
}

/**
 * Get JSON schema data for a given API field
 *
 * @param string $field_name Name of field
 *
 * @return array JSON schema data
 */
function EMBM_Core_Beer_Api_schema($field_name)
{
    // Return beer profile data schema
    if ($field_name == 'profile') {
        return array(
            'type'          => 'object',
            'description'   => esc_html__('The beer profile information for the object.', 'embm'),
            'context'       => array('view', 'edit'),
            'items'         => array(
                'malts'     => array(
                    'description'   => esc_html__('The beer malt data for the object.', 'embm'),
                    'type'          => 'string'
                ),
                'hops'      => array(
                    'description'   => esc_html__('The beer hops data for the object.', 'embm'),
                    'type'          => 'string'
                ),
                'additions' => array(
                    'description'   => esc_html__('The beer additions/spices data for the object.', 'embm'),
                    'type'          => 'string'
                ),
                'yeast'     => array(
                    'description'   => esc_html__('The beer yeast data for the object.', 'embm'),
                    'type'          => 'string'
                ),
                'ibu'       => array(
                    'description'   => esc_html__('The beer IBU measurement for the object.', 'embm'),
                    'type'          => 'integer'
                ),
                'abv'       => array(
                    'description'   => esc_html__('The beer ABV percentage for the object.', 'embm'),
                    'type'          => 'number'
                )
            )
        );
    }

    // Return beer extras data schema
    if ($field_name == 'extras') {
        return array(
            'type'          => 'object',
            'description'   => esc_html__('The beer extras information for the object.', 'embm'),
            'context'       => array('view', 'edit'),
            'items'         => array(
                'availability'  => array(
                    'description'   => esc_html__('The beer availability data for the object.', 'embm'),
                    'type'          => 'string'
                ),
                'notes'         => array(
                    'description'   => esc_html__('The beer additional notes/food parings data for the object.', 'embm'),
                    'type'          => 'string'
                ),
                'beer_number'   => array(
                    'description'   => esc_html__('The beer number for the object.', 'embm'),
                    'type'          => 'integer'
                )
            )
        );
    }

    // Return beer extras data schema
    if ($field_name == 'untappd') {
        return array(
            'type'          => 'object',
            'description'   => esc_html__('The Untappd information for the object.', 'embm'),
            'context'       => array('view', 'edit'),
            'items'         => array(
                'id'    => array(
                    'description'   => esc_html__('The Untappd ID for the object.', 'embm'),
                    'type'          => 'integer'
                )
            )
        );
    }

    return null;
}

/**
 * Displays any plugin-related errors to the user
 *
 * @return void
 */
function EMBM_Core_errors()
{
    // Get list of errors
    $errors = get_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['save_errors']);
    if (!$errors) {
        return;
    }

    // Iterate over errors
    foreach ($errors as $error) {
        if (!array_key_exists($error, $GLOBALS['EMBM_NOTICE_MAP']['save-error'])) {
            continue;
        }

        // Get notice content
        $notice = $GLOBALS['EMBM_NOTICE_MAP']['save-error'][$error];

?>
        <div class="<?php echo $notice['type']; ?> notice embm-notice">
            <p>
                <span class="embm-notice--title"><?php echo $notice['title']; ?></span>
                <span class="embm-notice--message"><?php echo $notice['message'];?></span>
            </p>
            <button type="button" class="notice-dismiss"></button>
        </div>
<?php
    }

    // Remove the errors from cache
    delete_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['save_errors']);
}

// Add to admin notices
add_action('admin_notices', 'EMBM_Core_errors');
