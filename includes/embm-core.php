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
        'name'                  => __('Beers', 'em-beer-manager'),
        'singular_name'         => __('Beer', 'em-beer-manager'),
        'add_new'               => __('Add New', 'em-beer-manager'),
        'add_new_item'          => __('Add New Beer', 'em-beer-manager'),
        'edit_item'             => __('Edit Beer', 'em-beer-manager'),
        'new_item'              => __('New Beer', 'em-beer-manager'),
        'all_items'             => __('All Beers', 'em-beer-manager'),
        'view_item'             => __('View Beer', 'em-beer-manager'),
        'search_items'          => __('Search Beers', 'em-beer-manager'),
        'not_found'             => __('No beers found', 'em-beer-manager'),
        'not_found_in_trash'    => __('No beers found in the Trash', 'em-beer-manager'),
        'parent_ithwh_colon'    => '',
        'menu_name'             => __('Beers', 'em-beer-manager')
    );

    // Set up custom post type options
    $args = array(
        'labels'                => $labels,
        'description'           => __('Holds beer specific data', 'em-beer-manager'),
        'public'                => true,
        'capability_type'       => 'post',
        'hierarchical'          => false,
        'taxonomies'            => array(EMBM_STYLE, EMBM_GROUP, EMBM_MENU),
        'has_archive'           => true,
        'menu_position'         => 5,
        'show_in_rest'          => true,
        'rest_base'             => 'embm_beers',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'rewrite'               => array(
            'slug'              => __('beers', 'em-beer-manager'),
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
    register_post_type(EMBM_BEER, $args);

    // Load metaboxes
    EMBM_Core_Beer_metaboxes();
}

// Loads the custom post type
add_action('init', 'EMBM_Core_beer');

// Add thumbnail support to custom post type
add_theme_support('post-thumbnails', array(EMBM_BEER));

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
    $options = get_option(EMBM_OPTIONS);

    // Get setting for comments
    $use_comments = isset($options['embm_comment_check']) ? $options['embm_comment_check'] : null;

    // Close comments if disabled
    if (($use_comments != '1') && ($post->post_type == EMBM_BEER)) {
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
    $options = get_option(EMBM_OPTIONS);

    // Get setting for comments
    $use_comments = isset($options['embm_comment_check']) ? $options['embm_comment_check'] : null;

    // Load blank template if disabled
    if (($use_comments != '1') && ($post->post_type == EMBM_BEER)) {
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
    $options = get_option(EMBM_OPTIONS);

    // Get settings for comments
    $use_comments = isset($options['embm_comment_check']) ? $options['embm_comment_check'] : null;

    if ($use_comments != '1') {
        // If disabled, remove support for comments from post type
        if (post_type_supports(EMBM_BEER, 'comments')) {
            remove_post_type_support(EMBM_BEER, 'comments');
            remove_post_type_support(EMBM_BEER, 'trackbacks');
        }
    } else {
        // If enabled, add support for comments to post type
        if (!post_type_supports(EMBM_BEER, 'comments')) {
            add_post_type_support(EMBM_BEER, 'comments');
            add_post_type_support(EMBM_BEER, 'trackbacks');
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
    $help_screens = array(EMBM_BEER, 'edit-'.EMBM_BEER);

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
        '<p><a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=embm-settings">' . __('EM Beer Manager Settings', 'em-beer-manager') . '</a></p>' .
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
    $ut_option = get_option(EMBM_OPTIONS);
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
function EMBM_Core_Beer_meta($post_id, $attr)
{
    // Get beer profile data
    $profile = get_post_meta($post_id, EMBM_BEER_META, true);

    // Exit if there's no metadata
    if (null == $profile) {
        return null;
    }

    // Format the data
    switch ($attr) {
    case 'abv':
        if (array_key_exists($attr, $profile) && $profile[$attr] > 0) {
            return $profile[$attr] . '%';
        }
        break;
    case 'ibu':
        if (array_key_exists($attr, $profile) && $profile[$attr] > 0) {
            return $profile[$attr];
        }
        break;
    case 'beer_num':
        if (array_key_exists($attr, $profile) && $profile[$attr] !== '') {
            return '#' . $profile[$attr];
        }
        break;
    case 'notes':
        if (array_key_exists($attr, $profile)) {
            return html_entity_decode($profile[$attr]);
        }
        break;
    case 'untappd_url':
        if (array_key_exists('untappd_id', $profile) && null !== $profile['untappd_id']) {
            return EMBM_Core_Beer_disabled() ? null : 'https://untappd.com/beer/'.$profile['untappd_id'];
        }
        break;
    default:
        if (array_key_exists($attr, $profile)) {
            return $profile[$attr];
        }
        break;
    }

    return null;
}

/**
 * Retrieves Untappd data for a given beer
 *
 * @param int $post_id WP post ID
 *
 * @return object
 */
function EMBM_Core_Beer_untappd($post_id)
{
    // Check if Untappd is disabled
    if (EMBM_Core_Beer_disabled()) {
        return null;
    }

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Get beer ID
    $beer_id = EMBM_Core_Beer_meta($post_id, 'untappd_id');
    if (null == $token || is_null($beer_id) || $beer_id == '') {
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

    // Fall back to null
    return null;
}

/**
 * Retrieves UTFB data for a given beer
 *
 * @param int $post_id WP post ID
 *
 * @return object
 */
function EMBM_Core_Beer_utfb($post_id)
{
    // Get beer extras data
    $beer_meta = get_post_meta($post_id, EMBM_BEER_META_UTFB, true);
    $beer_meta = (null == $beer_meta) ? array() : $beer_meta;
    return $beer_meta;
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
                __('Ratings', 'em-beer-manager')
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
    $types = wp_get_object_terms($post_id, EMBM_STYLE);

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
        'name'                          => __('Styles', 'em-beer-manager'),
        'singular_name'                 => __('Style', 'em-beer-manager'),
        'search_items'                  => __('Search Styles', 'em-beer-manager'),
        'all_items'                     => __('All Styles', 'em-beer-manager'),
        'edit_item'                     => __('Edit Style', 'em-beer-manager'),
        'update_item'                   => __('Update Style', 'em-beer-manager'),
        'add_new_item'                  => __('Add New Style', 'em-beer-manager'),
        'new_item_name'                 => __('New Style Name', 'em-beer-manager'),
        'popular_items'                 => __('Popular Styles', 'em-beer-manager'),
        'choose_from_most_used'         => __('Choose from the most used styles', 'em-beer-manager'),
        'separate_items_with_commas'    => __('Separate styles with commas', 'em-beer-manager'),
        'add_or_remove_items'           => __('Add or remove styles', 'em-beer-manager'),
        'menu_name'                     => __('Styles', 'em-beer-manager')
    );

    // Set up custom taxonomy options
    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array(
            'slug'              => __('beers/style', 'em-beer-manager'),
            'with_front'        => false
        ),
        'show_in_rest'          => true,
        'rest_base'             => 'embm_styles',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );

    // Register the styles taxonomy with the EMBM custom post type
    register_taxonomy(EMBM_STYLE, array(EMBM_BEER), $args);

    // Populate taxonomy with terms, if they haven't been loaded yet
    if (!get_option(EMBM_STYLES_LOADED)) {
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
        $beer_style = trim(fgets($beer_styles));

        // Add style as term in taxonomy
        if (false == term_exists($beer_style, EMBM_STYLE)) {
            $res = wp_insert_term($beer_style, EMBM_STYLE);
        }
    }

    // Close file
    fclose($beer_styles);

    // Store the fact that styles were loaded
    update_option(EMBM_STYLES_LOADED, true);
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
        'name'                          => __('Groups', 'em-beer-manager'),
        'singular_name'                 => __('Group', 'em-beer-manager'),
        'search_items'                  => __('Search Groups', 'em-beer-manager'),
        'all_items'                     => __('All Groups', 'em-beer-manager'),
        'edit_item'                     => __('Edit Group', 'em-beer-manager'),
        'update_item'                   => __('Update Group', 'em-beer-manager'),
        'add_new_item'                  => __('Add New Group', 'em-beer-manager'),
        'new_item_name'                 => __('New Group Name', 'em-beer-manager'),
        'popular_items'                 => __('Popular Groups', 'em-beer-manager'),
        'choose_from_most_used'         => __('Choose from the most used groups', 'em-beer-manager'),
        'separate_items_with_commas'    => __('Separate groups with commas', 'em-beer-manager'),
        'add_or_remove_items'           => __('Add or remove groups', 'em-beer-manager'),
        'menu_name'                     => __('Groups', 'em-beer-manager')
    );

    // Set default slug
    $group_slug = 'beer/group';

    // Override slug if user has custom option set
    $options = get_option(EMBM_GROUP);
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
    register_taxonomy(EMBM_GROUP, array(EMBM_BEER), $args);
}

// Loads the custom Group taxonomy
add_action('init', 'EMBM_Core_group', 0);

/**
 * Loads the custom EMBM menu taxonomy
 *
 * @return void
 */
function EMBM_Core_menu()
{
    // Set custom taxonomy terminology
    $labels = array(
        'name'                          => __('Menus', 'em-beer-manager'),
        'singular_name'                 => __('Menu', 'em-beer-manager'),
        'search_items'                  => __('Search Menus', 'em-beer-manager'),
        'all_items'                     => __('All Menus', 'em-beer-manager'),
        'edit_item'                     => __('Edit Menu', 'em-beer-manager'),
        'update_item'                   => __('Update Menu', 'em-beer-manager'),
        'add_new_item'                  => __('Add New Menu', 'em-beer-manager'),
        'new_item_name'                 => __('New Menu Name', 'em-beer-manager'),
        'popular_items'                 => __('Popular Menus', 'em-beer-manager'),
        'choose_from_most_used'         => __('Choose from the most used menus', 'em-beer-manager'),
        'separate_items_with_commas'    => __('Separate menus with commas', 'em-beer-manager'),
        'add_or_remove_items'           => __('Add or remove menus', 'em-beer-manager'),
        'menu_name'                     => __('Menus', 'em-beer-manager')
    );

    // Set up custom taxonomy options
    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array(
            'slug'              => 'beer/menu',
            'with_front'        => false
        ),
        'show_in_rest'          => true,
        'rest_base'             => 'embm_menus',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );

    // Register the group taxonomy with the EMBM custom post type
    register_taxonomy(EMBM_MENU, array(EMBM_BEER), $args);
}

// Loads the custom Group taxonomy
add_action('init', 'EMBM_Core_menu', 0);

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
        EMBM_BEER,
        'embm_profile',
        array(
            'get_callback'    => $get_callback,
            'update_callback' => $update_callback,
            'schema'          => EMBM_Core_Beer_Api_schema('profile')
        )
    );

    // Register extras API field
    register_rest_field(
        EMBM_BEER,
        'embm_extras',
        array(
            'get_callback'    => $get_callback,
            'update_callback' => $update_callback,
            'schema'          => EMBM_Core_Beer_Api_schema('extras')
        )
    );

    // Retrieve Untappd settings
    $ut_option = get_option(EMBM_OPTIONS);

    // Check if Untappd is disabled
    if (!EMBM_Core_Beer_disabled()) {
        // Register Untappd URL API field
        register_rest_field(
            EMBM_BEER,
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

    // Get post meta for beer
    $beer_meta = get_post_meta($beer_id, EMBM_BEER_META, true);
    $beer_meta = (null == $beer_meta) ? array() : $beer_meta;

    // Return beer profile data
    if ($field_name == 'embm_profile') {
        // Get ABV and IBU as int/float
        $abv = floatval($beer_meta['abv']);
        $ibu = intval($beer_meta['ibu']);

        // Return formatted info
        return array(
            'malts'     => $beer_meta['malts'],
            'hops'      => $beer_meta['hops'],
            'additions' => $beer_meta['adds'],
            'yeast'     => $beer_meta['yeast'],
            'abv'       => ($abv == 0) ? null : $abv,
            'ibu'       => ($ibu == 0) ? null : $ibu
        );
    }

    // Return beer extras data
    if ($field_name == 'embm_extras') {
        // Get Beer Number as int
        $beer_num = intval($beer_meta['beer_num']);

        // Return formatted array
        return array(
            'availability'  => $beer_meta['avail'],
            'notes'         => $beer_meta['notes'],
            'beer_number'   => ($beer_num == 0) ? null : $beer_num
        );
    }

    // Return beer Untappd information
    if ($field_name == 'embm_untappd') {
        // Get Untappd ID as int
        $untappd_id = intval($beer_meta['untappd_id']);

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

    // Get post meta for beer
    $beer_meta = get_post_meta($beer_id, EMBM_BEER_META, true);
    $beer_meta = (null == $beer_meta) ? array() : $beer_meta;

    // Set up attr hash
    $fields = array(
        'embm_profile' => array('malts', 'hops', 'adds', 'yeast', 'ibu', 'abv'),
        'embm_extras'  => array('beer_num', 'avail', 'notes')
    );

    // Iterate over fields
    if (array_key_exists($field_name, $fields)) {
        // Get field attributes
        $attrs = $fields[$field_name];

        // Iterate over field attributes
        foreach ($attrs as $attr) {
            if (isset($value[$attr]) && is_string($value[$attr])) {
                $beer_meta[$attr] = esc_attr($value[$attr]);
            }
        }

        // Save data
        update_post_meta($post_id, EMBM_BEER_META, $beer_meta);
    }

    // Handle Untappd information separately
    if ($field_name == 'embm_untappd') {
        // Save input
        if (isset($value['id']) && is_int($value['id'])) {
            // Get old id
            $new_id = esc_attr($value['id']);
            $old_id = $beer_meta['untappd_id'];

            // Skip if this is not a new ID
            if ($beer_id !== $old_id) {
                // Save new ID
                $beer_meta['untappd_id'] = $new_id;
                update_post_meta($post_id, EMBM_BEER_META, $beer_meta);

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
            'description'   => esc_html__('The beer profile information for the object.', 'em-beer-manager'),
            'context'       => array('view', 'edit'),
            'items'         => array(
                'malts'     => array(
                    'description'   => esc_html__('The beer malt data for the object.', 'em-beer-manager'),
                    'type'          => 'string'
                ),
                'hops'      => array(
                    'description'   => esc_html__('The beer hops data for the object.', 'em-beer-manager'),
                    'type'          => 'string'
                ),
                'additions' => array(
                    'description'   => esc_html__('The beer additions/spices data for the object.', 'em-beer-manager'),
                    'type'          => 'string'
                ),
                'yeast'     => array(
                    'description'   => esc_html__('The beer yeast data for the object.', 'em-beer-manager'),
                    'type'          => 'string'
                ),
                'ibu'       => array(
                    'description'   => esc_html__('The beer IBU measurement for the object.', 'em-beer-manager'),
                    'type'          => 'integer'
                ),
                'abv'       => array(
                    'description'   => esc_html__('The beer ABV percentage for the object.', 'em-beer-manager'),
                    'type'          => 'number'
                )
            )
        );
    }

    // Return beer extras data schema
    if ($field_name == 'extras') {
        return array(
            'type'          => 'object',
            'description'   => esc_html__('The beer extras information for the object.', 'em-beer-manager'),
            'context'       => array('view', 'edit'),
            'items'         => array(
                'availability'  => array(
                    'description'   => esc_html__('The beer availability data for the object.', 'em-beer-manager'),
                    'type'          => 'string'
                ),
                'notes'         => array(
                    'description'   => esc_html__('The beer additional notes/food parings data for the object.', 'em-beer-manager'),
                    'type'          => 'string'
                ),
                'beer_number'   => array(
                    'description'   => esc_html__('The beer number for the object.', 'em-beer-manager'),
                    'type'          => 'integer'
                )
            )
        );
    }

    // Return beer extras data schema
    if ($field_name == 'untappd') {
        return array(
            'type'          => 'object',
            'description'   => esc_html__('The Untappd information for the object.', 'em-beer-manager'),
            'context'       => array('view', 'edit'),
            'items'         => array(
                'id'    => array(
                    'description'   => esc_html__('The Untappd ID for the object.', 'em-beer-manager'),
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
    $errors = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['save_errors']);
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
    delete_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['save_errors']);
}

// Add to admin notices
add_action('admin_notices', 'EMBM_Core_errors');
