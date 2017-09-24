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
 * @package EMBM\Output\Shortcodes
 */

define('EMBM_SHORTCODE_BEER', 'beer');
define('EMBM_SHORTCODE_BEER_LIST', 'beer-list');
define('EMBM_SHORTCODE_BEER_MENU', 'beer-menu');

/**
 * Loads the [beer] single beer display shortcode
 *
 * @param array $atts List of support shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_beer($atts)
{
    // Extract shortcode attributes
    $args = shortcode_atts(
        array(
            'id'                => 0,
            'show_profile'      => 'true',
            'show_extras'       => 'true',
            'show_rating'       => 'true',
            'show_checkins'     => 'true',
            'checkins_count'    => 5
        ),
        $atts,
        EMBM_SHORTCODE_BEER
    );

    // Load shortcode content
    return EMBM_Output_Shortcodes_Beer_display($args['id'], $args);
}

// Load single beer shortcode
add_shortcode(EMBM_SHORTCODE_BEER, 'EMBM_Output_Shortcodes_beer');

/**
 * Displays the single beer shortcode content
 *
 * @param int   $post_id WP post ID
 * @param array $input   Shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_Beer_display($post_id, $input=array())
{
    // Set default attribute values
    $attrs = array(
        'profile'       => array(
            'key'       => 'show_profile',
            'default'   => true,
            'type'      => 'bool'
        ),
        'extras'        => array(
            'key'       => 'show_extras',
            'default'   => true,
            'type'      => 'bool'
        ),
        'rating'        => array(
            'key'       => 'show_rating',
            'default'   => true,
            'type'      => 'bool'
        ),
        'reviews'       => array(
            'key'       => 'show_checkins',
            'default'   => true,
            'type'      => 'bool'
        ),
        'reviews_count'  => array(
            'key'       => 'checkins_count',
            'default'   => 5,
            'type'      => 'int'
        )
    );

    // Set up values to pass on, remove bad values
    $args = EMBM_Output_Shortcodes_normalize($attrs, $input, $post_id);

    // Return formatted beer content
    return EMBM_Output_Shortcodes_Beer_load($args);
}

/**
 * Loads the single beer shortcode display
 *
 * @param array $beer User-provided display settings
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_Beer_load($beer)
{
    // Set up display options
    $bid = $beer['id'];

    // Initialize output string
    $output = '';

    // Get the global post object
    global $post;

    // Set up new WP database query
    $wp_query = new WP_Query();

    // Set query args
    $args = array (
        'post_type'  => EMBM_BEER,
        'page_id'    => $bid
    );

    // Get post from WP database
    $wp_query->query($args);

    // Enter post data loop
    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        $output .= EMBM_Output_beer($post->ID, $beer);
    }

    // Reset query and post data
    wp_reset_query();
    wp_reset_postdata();

    return $output;
}

/**
 * Loads the [beer-list] shortcode
 *
 * @param array $atts List of support shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_list($atts)
{
    // Load shortcode content
    return EMBM_Output_Shortcodes_List_display(
        shortcode_atts(
            array(
                'exclude'           => '',
                'show_profile'      => 'true',
                'show_extras'       => 'true',
                'show_rating'       => 'true',
                'style'             => '',
                'group'             => '',
                'beers_per_page'    => -1,
                'offset'            => 0,
                'paginate'          => 'true',
                'orderby'           => '',
                'order'             => '',
                'meta_key'          => ''
            ),
            $atts,
            EMBM_SHORTCODE_BEER_LIST
        )
    );
}

// Load beer list shortcode
add_shortcode(EMBM_SHORTCODE_BEER_LIST, 'EMBM_Output_Shortcodes_list');

/**
 * Display the beer list shortcode content
 *
 * @param array $input Shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_List_display($input=array())
{
    // Set attribute defaults
    $attrs = array(
        'exclude'   => array(
            'key'       => 'exclude',
            'default'   => '',
            'type'      => 'string'
        ),
        'profile'   => array(
            'key'       => 'show_profile',
            'default'   => true,
            'type'      => 'bool'
        ),
        'extras'    => array(
            'key'       => 'show_extras',
            'default'   => true,
            'type'      => 'bool'
        ),
        'rating'        => array(
            'key'       => 'show_rating',
            'default'   => true,
            'type'      => 'bool'
        ),
        'style'     => array(
            'key'       => 'style',
            'default'   => '',
            'type'      => 'string'
        ),
        'group'     => array(
            'key'       => 'group',
            'default'   => '',
            'type'      => 'string'
        ),
        'page_num'  => array(
            'key'       => 'beers_per_page',
            'default'   => -1,
            'type'      => 'int'
        ),
        'offset'  => array(
            'key'       => 'offset',
            'default'   => 0,
            'type'      => 'int'
        ),
        'use_pages' => array(
            'key'       => 'paginate',
            'default'   => true,
            'type'      => 'bool'
        ),
        'sortby'    => array(
            'key'       => 'orderby',
            'default'   => '',
            'type'      => 'string'
        ),
        'sort'      => array(
            'key'       => 'order',
            'default'   => '',
            'type'      => 'string'
        ),
        'meta_key'  => array(
            'key'       => 'meta_key',
            'default'   => '',
            'type'      => 'string'
        )
    );

    // Set up values to pass on, remove bad values
    $args = EMBM_Output_Shortcodes_normalize($attrs, $input);

    // Return formatted beer list content
    return EMBM_Output_Shortcodes_List_load($args);
}

/**
 * Display beer list shortcode content
 *
 * @param array $beers User-provided display settings
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_List_load($beers)
{
    // Set up display options
    $excludes = explode(',', $beers['exclude']);
    $showprofile = $beers['profile'];
    $showextras = $beers['extras'];
    $showstyle = $beers['style'];
    $showgroup = $beers['group'];
    $showpages = $beers['page_num'];
    $offset = $beers['offset'];
    $usepages = $beers['use_pages'];
    $sortby = $beers['sortby'];
    $sort = strtoupper($beers['sort']);
    $meta_key = $beers['meta_key'];

    // Initialize output string
    $output = '';

    // Get global post object
    global $post;

    // Set up pagination data
    if (get_query_var('paged')) {
        $paged = get_query_var('paged');
    } else if (get_query_var('page')) {
        $paged = get_query_var('page');
    } else {
        $paged = 1;
    }

    // Set up new WP database query
    $wp_query = new WP_Query();

    // Set up query args
    $args = array (
        'post_type'         => EMBM_BEER,
        'posts_per_page'    => $showpages
    );

    // Add offset filter
    if ($offset != 0) {
        $args['offset'] = $offset;

        if ($showpages == -1) {
            unset($args['posts_per_page']);
        }
    } else {
        $args['paged'] = $paged;
    }

    // Get taxonomy filter query
    $args['tax_query'] = EMBM_Output_Shortcodes_taxonomies(
        array(
            EMBM_STYLE => $showstyle,
            EMBM_GROUP => $showgroup
        )
    );

    // Add id filter
    if ($excludes) {
        $args['post__not_in'] = $excludes;
    }
    // Add sortby filter
    if ($sortby != '') {
        $args['orderby'] = $sortby;
    }
    // Add sort filter
    if ($sort != '') {
        $args['order'] = $sort;
    }

    // Add meta key
    if ($meta_key != '') {
        $args['meta_key'] = $meta_key;
    }

    // Get posts from WP database
    $wp_query->query($args);

    // Start beer list HTML output
    $output .= '<div class="embm-beer--list">'."\n";

    // Enter post data loop
    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        $output .= EMBM_Output_beer($post->ID, $beers);
    }

    // Display pagination
    if ($usepages) {
        // Start page navigation output
        $output .= '<div class="nav-below">'."\n";

        // A ridiculously large int
        $big = 999999999;

        // Display pagination links
        $output .= paginate_links(
            array(
                'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format'    => '?paged=%#%',
                'current'   => max(1, $paged),
                'total'     => $wp_query->max_num_pages
            )
        );

        // Finish page navigation output
        $output .= '</div>'."\n";
    }

    // Finish beer list HTML output
    $output .= '</div>'."\n";

    // Reset query and post data
    wp_reset_query();
    wp_reset_postdata();

    // Return HTML content
    return $output;
}

/**
 * Loads the [beer-menu] shortcode
 *
 * @param array $atts List of support shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_menu($atts)
{
    // Extract shortcode attributes
    $args = shortcode_atts(
        array(
            'menu'              => '',
            'show_rating'       => 'true',
            'show_last_updated' => 'true',
            'show_thumbnail'    => 'true',
            'show_description'  => 'true'
        ),
        $atts,
        EMBM_SHORTCODE_BEER_MENU
    );

    // Load shortcode content
    return EMBM_Output_Shortcodes_Menu_display($args['menu'], $args);
}

// Load beer list shortcode
add_shortcode(EMBM_SHORTCODE_BEER_MENU, 'EMBM_Output_Shortcodes_menu');

/**
 * Displays the beer menu shortcode content
 *
 * @param string $menu_id Untappd menu name, slug or ID
 * @param array  $input   Shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_Menu_display($menu_id, $input=array())
{
    // Set attribute defaults
    $attrs = array(
        'rating'        => array(
            'key'       => 'show_rating',
            'default'   => true,
            'type'      => 'bool'
        ),
        'updated'       => array(
            'key'       => 'show_last_updated',
            'default'   => true,
            'type'      => 'bool'
        ),
        'thumbnail'     => array(
            'key'       => 'show_thumbnail',
            'default'   => true,
            'type'      => 'bool'
        ),
        'description'   => array(
            'key'       => 'show_description',
            'default'   => true,
            'type'      => 'bool'
        )
    );

    // Get menu ID
    $menu = EMBM_Output_Shortcodes_Taxonomies_parse($menu_id, EMBM_MENU);

    // Set up values to pass on, remove bad values
    $args = EMBM_Output_Shortcodes_normalize($attrs, $input, $menu);

    // Return formatted beer list content
    return EMBM_Output_Shortcodes_Menu_load($args);
}

/**
 * Loads the menu shortcode display
 *
 * @param array $args User-provided display settings
 *
 * @return string/html
 */
function EMBM_Output_Shortcodes_Menu_load($args)
{
    return EMBM_Output_Menus_display($args);
}

/**
 * Normalizes shortcode input
 *
 * @param array $taxonomies Array of taxonomy slugs to query
 *
 * @return array
 */
function EMBM_Output_Shortcodes_taxonomies($taxonomies)
{
    // Start query with OR relationship
    $tax_query = array(
        'relation' => 'OR'
    );

    // Iterate over list of taxonomies and terms
    foreach ($taxonomies as $taxonomy => $raw_terms) {
        // Skip if list is empty
        if ($raw_terms == '') {
            continue;
        }

        // Get a list of terms
        $raw_terms = array_map('trim', explode(',', $raw_terms));
        $terms = array();

        // Parse the list of raw terms into term IDs
        foreach ($raw_terms as $raw_term) {
            $term = EMBM_Output_Shortcodes_Taxonomies_parse($raw_term, $taxonomy);
            if (!is_null($term)) {
                array_push($terms, $term->term_id);
            }
        }

        // Set up array for taxonomy
        $term_query = array(
            'taxonomy' => $taxonomy,
            'terms'    => $terms
        );

        // Append to query
        array_push($tax_query, $term_query);
    }

    // Return query
    return $tax_query;
}

/**
 * Parses a term from name, slug, or ID to WP object
 *
 * @param string $raw_term Raw term input to parse (name, slug, ID)
 * @param string $taxonomy Taxonomy name for term
 *
 * @return array
 */
function EMBM_Output_Shortcodes_Taxonomies_parse($raw_term, $taxonomy)
{
    // Attempt to get by ID first
    $term = get_term_by('id', $raw_term, $taxonomy);
    if (false === $term) {
        // Then by slug
        $term = get_term_by('slug', $raw_term, $taxonomy);
        if (false === $term) {
            // Then by name
            $term = get_term_by('name', $raw_term, $taxonomy);
            if (false == $term) {
                return null;
            }
        }
    }

    // Return parsed term
    return $term;
}

/**
 * Normalizes shortcode input
 *
 * @param array $attrs   List of shortcode attributes defaults
 * @param array $input   List of submitted shortcode attributes
 * @param int   $post_id WP post ID (default: null)
 *
 * @return array
 */
function EMBM_Output_Shortcodes_normalize($attrs, $input, $post_id = null)
{
    // Set up array
    $args = array();

    // Set post ID, if there
    if (!is_null($post_id)) {
        $args['id'] = $post_id;
    }

    // Set up values to pass on, remove bad values
    foreach ($attrs as $attr => $value) {
        // Populate args and fall back to defaults when needed
        if (!array_key_exists($value['key'], $input)) {
            $args[$attr] = $value['default'];
        } else {
            $args[$attr] = $input[$value['key']];
        }

        // Check for bools
        if ($value['type'] == 'bool') {
            $args[$attr] = $args[$attr] != 'true' ? false : true;
        }
    }

    // Return normalized values
    return $args;
}
