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
 * @package EMBM\Output
 */


/**
 * Loads the [beer] single beer display shortcode
 *
 * @param array $atts List of support shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_beer($atts)
{
    // Extract shortcode attributes
    $args = shortcode_atts(
        array(
            'id'            => 0,
            'show_profile'  => 'true',
            'show_extras'   => 'true',
        ),
        $atts,
        'beer'
    );

    // Load shortcode content
    return EMBM_Output_Beer_display($args['id'], $args);
}

// Load single beer shortcode
add_shortcode('beer', 'EMBM_Output_beer');

/**
 * Displays the single beer shortcode content
 *
 * @param int   $post_id WP post ID
 * @param array $input   Shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_Beer_display($post_id, $input=array())
{
    // Set default attribut values
    $attrs = array(
        'profile'   => array(
            'key'       => 'show_profile',
            'default'   => true,
            'type'      => 'bool'
        ),
        'extras'    => array(
            'key'       => 'show_extras',
            'default'   => true,
            'type'      => 'bool'
        )
    );

    // Set up values to pass on, remove bad values
    $args = array('id' => $post_id);
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

    // Return formatted beer content
    return EMBM_Output_Beer_load($args);
}

/**
 * Loads the single beer shortcode display
 *
 * @param array $beer User-provided display settings
 *
 * @return string/html
 */
function EMBM_Output_Beer_load($beer)
{
    // Set up display options
    $bid = $beer['id'];
    $showprofile = $beer['profile'];
    $showextras = $beer['extras'];

    // Initialize output string
    $output = '';

    // Get the global post object
    global $post;

    // Set up new WP database query
    $wp_query = new WP_Query();

    // Set query args
    $args = array (
        'post_type'  => 'embm_beer',
        'page_id'    => $bid
    );

    // Get post from WP database
    $wp_query->query($args);

    // Enter post data loop
    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        $output .= EMBM_Output_Content_beer($post->ID, $showprofile, $showextras);
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
function EMBM_Output_list($atts)
{
    // Load shortcode content
    return EMBM_Output_List_display(
        shortcode_atts(
            array(
                'exclude'           => '',
                'show_profile'      => 'true',
                'show_extras'       => 'true',
                'style'             => '',
                'group'             => '',
                'beers_per_page'    => -1,
                'paginate'          => 'true',
                'orderby'           => '',
                'order'             => ''
            ),
            $atts,
            'beer-list'
        )
    );
}

// Load beer list shortcode
add_shortcode('beer-list', 'EMBM_Output_list');

/**
 * Display the beer list shortcode content
 *
 * @param array $input Shortcode attributes
 *
 * @return string/html
 */
function EMBM_Output_List_display($input=array())
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
        )
    );

    // Set up values to pass on, remove bad values
    $args = array();
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

    // Return formatted beer list content
    return EMBM_Output_List_load($args);
}

/**
 * Display beer list shortcode content
 *
 * @param array $beers User-provided display settings
 *
 * @return string/html
 */
function EMBM_Output_List_load($beers)
{
    // Set up display options
    $excludes = explode(',', $beers['exclude']);
    $showprofile = $beers['profile'];
    $showextras = $beers['extras'];
    $showstyle = $beers['style'];
    $showgroup = $beers['group'];
    $showpages = $beers['page_num'];
    $usepages = $beers['use_pages'];
    $sortby = $beers['sortby'];
    $sort = strtoupper($beers['sort']);

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
        'post_type'    => 'embm_beer',
        'showposts'    => $showpages,
        'paged'        => $paged,
    );

    // Add styles filter
    if ($showstyle != '') {
        $style_slug = get_term_by('name', $showstyle, 'embm_style', 'ARRAY_A');
        $args['embm_style'] = $style_slug['slug'];
    }
    // Add groups filter
    if ($showgroup != '') {
        $group_slug = get_term_by('name', $showgroup, 'embm_group', 'ARRAY_A');
        $args['embm_group'] = $group_slug['slug'];
    }
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

    // Get posts from WP database
    $wp_query->query($args);

    // Start beer list HTML output
    $output .= '<div class="embm-beer--list">'."\n";

    // Enter post data loop
    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        $output .= EMBM_Output_Content_beer($post->ID, $showprofile, $showextras);
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
 * Generate HTML output for a single beer entry
 *
 * @param int  $beer_id     WP post ID for single beer
 * @param bool $showprofile True/false value to show beer profile
 * @param bool $showextras  True/false value to show beer extras
 *
 * @return string/html
 */
function EMBM_Output_Content_beer($beer_id, $showprofile=true, $showextras=true)
{
    // Initialize output string
    $output = '';

    // Begin single beer
    $output .= '<div id="embm-beer-'.$beer_id.'" class="embm-beer--single embm-beer embm-beer-'.$beer_id.'">'."\n";

    // Begin beer header
    $output .= '<h2 class="embm-beer--header">'."\n";

    // Beer title
    if (is_page() || is_archive() || is_tax('embm_group')) {
        $output .= '<a href="'.get_permalink($beer_id).'" title="'.get_the_title($beer_id).'">';
        $output .= '<span class="embm-beer--header-title">'.get_the_title($beer_id).'</span>';
        $output .= '</a>'."\n";
    } else {
        $output .= '<span class="embm-beer--header-title">'.get_the_title($beer_id).'</span>'."\n";
    }

    // Beer style
    if (EMBM_Core_Beer_style($beer_id)) {
        $output .= '<span class="embm-beer--header-style">(';
        $output .= '<a href="'.get_term_link(EMBM_Core_Beer_style($beer_id), 'embm_style').'" title="View All '.EMBM_Core_Beer_style($beer_id).'s">';
        $output .= EMBM_Core_Beer_style($beer_id);
        $output .= '</a>)</span>'."\n";
    }

    // End beer header
    $output .= '</h2>'."\n";

    // Beer image
    if (!is_archive()) {
        if (get_the_post_thumbnail($beer_id) != '') {
            $output .= '<div class="embm-beer--image">'."\n";
            $output .= get_the_post_thumbnail($beer_id, 'full')."\n";
            $output .= '</div>'."\n";
        }
    }

    // Begin beer description
    $output .= '<div class="embm-beer--description">'."\n";
    $output .= apply_filters('the_content', get_the_content($beer_id));

    // Set read more link
    if ((is_tax('embm_style') || is_archive()) && !is_tax('embm_group')) {
        $output .= ' <a class="read-more" href="'.get_permalink($beer_id).'">';
        $output .= __('More...', 'embm');
        $output .= '</a>';
    }

    // End beer description
    $output .= EMBM_Output_Content_untappd($beer_id);
    $output .= '</div>'."\n";

    // Beer meta
    if ($showprofile || $showextras) {
        // Begin beer meta output
        $output .= '<div class="embm-beer--meta">'."\n";

        // Display beer profile
        if ($showprofile) {
            // Get profile HTML
            $profile = EMBM_Output_Content_profile($beer_id);

            if ($profile != null) {
                $output .= $profile;
            }
        }

        // Display beer extras
        if ($showextras) {
            // Get extras HTML
            $extras = EMBM_Output_Content_extras($beer_id);

            if ($extras != null) {
                $output .= $extras;
            }
        }

        // End beer meta output
        $output .= '</div>'."\n";
    } else {
        $output .= '<div class="embm-beer--clear"></div>'."\n";
    }

    // End single beer
    $output .= '</div>'."\n";

    // Return HTML content
    return $output;
}

/**
 * Output HTML content for Untappd integration
 *
 * @param int $beer_id WP post ID for beer entry
 *
 * @return string/html
 */
function EMBM_Output_Content_untappd($beer_id)
{
    // Initialize output string
    $output = '';

    // Retrieve Untappd settings
    $ut_option = get_option('embm_options');
    $use_untappd = null;
    if (isset($ut_option['embm_untappd_check'])) {
        $use_untappd = $ut_option['embm_untappd_check'];
    }

    if ($use_untappd != '1') {
        // Get raw Untappd value from DB
        $untap = get_post_meta($beer_id, 'untappd', true);

        // If an Untappd value is set for this beer, display the link
        if ($untap != '') {
            $output = '<div class="untappd"><a href="'.EMBM_Core_Beer_attr($beer_id, 'untappd').'" target="_blank" title="Check In on Untappd"></a></div>'."\n";
        }
    }

    // Return HTML content
    return $output;
}

/**
 * Display single beer profile content
 *
 * @param int $beer_id WP post ID for beer entry
 *
 * @return string/html
 */
function EMBM_Output_Content_profile($beer_id)
{
    // Set up beer profile attributes
    $abv = EMBM_Core_Beer_attr($beer_id, 'abv');
    $ibu = EMBM_Core_Beer_attr($beer_id, 'ibu');
    $malts = EMBM_Core_Beer_attr($beer_id, 'malts');
    $hops = EMBM_Core_Beer_attr($beer_id, 'hops');
    $adds = EMBM_Core_Beer_attr($beer_id, 'adds');
    $yeast = EMBM_Core_Beer_attr($beer_id, 'yeast');

    // Initialize output string
    $output = '';

    // Get global profile display setting
    $options = get_option('embm_options');
    $view_profile = null;
    if (isset($options['embm_profile_show'])) {
        $view_profile = $options['embm_profile_show'];
    }

    // If global display is not set, show the content
    if ($view_profile != '1') {
        if (($abv!='0%') || ($ibu!='0') || ($malts!='') || ($hops!='') || ($adds!='') || ($yeast!='')) {
            // Start profile output
            $output = '<div class="embm-beer--meta-profile">'."\n";

            // Display ABV
            if ($abv != '0%') {
                $output .= '<div class="abv"><span class="label">';
                $output .= __('ABV:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'abv').'</span></div>'."\n";
            }
            // Display IBU
            if ($ibu != '0') {
                $output .= '<div class="ibu"><span class="label">';
                $output .= __('IBU:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'ibu').'</span></div>'."\n";
            }
            // Display Malts
            if ($malts != '') {
                $output .= '<div class="malts"><span class="label">';
                $output .= __('Malts:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'malts').'</span></div>'."\n";
            }
            // Display Hops
            if ($hops != '') {
                $output .= '<div class="hops"><span class="label">';
                $output .= __('Hops:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'hops').'</span></div>'."\n";
            }
            // Display Additions
            if ($adds != '') {
                $output .= '<div class="other"><span class="label">';
                $output .= __('Other:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'adds').'</span></div>'."\n";
            }
            // Display Yeast
            if ($yeast != '') {
                $output .= '<div class="yeast"><span class="label">';
                $output .= __('Yeast:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'yeast').'</span></div>'."\n";
            }

            // End profile output
            $output .= '</div>'."\n";
        } else {
            $output = null;
        }
    }

    // Return HTML content
    return $output;
}

/**
 * Display single beer extras content
 *
 * @param int $beer_id WP post ID for beer entry
 *
 * @return string/html
 */
function EMBM_Output_Content_extras($beer_id)
{
    // Set up beer extras attributes
    $bnum = EMBM_Core_Beer_attr($beer_id, 'beer_num');
    $avail = EMBM_Core_Beer_attr($beer_id, 'avail');
    $notes = EMBM_Core_Beer_attr($beer_id, 'notes');

    // Initialize output string
    $output = '';

    // Get global extras display setting
    $options = get_option('embm_options');
    $view_extras = null;
    if (isset($options['embm_extras_show'])) {
        $view_extras = $options['embm_extras_show'];
    }

    // If global display is not set, show the content
    if ($view_extras != '1') {
        if (($avail!='') || ($notes!='')) {
            // Start extras output
            $output = '<div class="embm-beer--meta-extras">'."\n";

            // Display beer number
            if ($bnum != '#') {
                $output .= '<div class="beer_num"><span class="label">';
                $output .= __('Beer Number:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'beer_num').'</span></div>'."\n";
            }
            // Display availability
            if ($avail != '') {
                $output .= '<div class="avail"><span class="label">';
                $output .= __('Availability:', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'avail').'</span></div>'."\n";
            }
            // Display notes
            if ($notes != '') {
                $output .= '<div class="notes"><span class="label">';
                $output .= __('Additional Notes', 'embm');
                $output .= '</span><span class="value">'.wpautop(EMBM_Core_Beer_attr($beer_id, 'notes')).'</span></div>'."\n";
            }

            // End extras output
            $output .= '</div>'."\n";

        } else {
            $output = null;
        }
    }

    // Return HTML content
    return $output;
}


/**
 * Adds extra HTML content to beer posts
 *
 * @param string $content WP post content
 *
 * @return string/html
 */
function EMBM_Output_Filter_content($content)
{
    // Get global post object
    global $post;

    // Get EMBM settings
    $options = get_option('embm_options');

    // Get beer profile content
    $profile = EMBM_Output_Content_profile($post->ID);

    // Get beer extras content
    $extras = EMBM_Output_Content_extras($post->ID);

    // Initialize output string
    $output = '';

    // Enter the post loop
    if (in_the_loop() && (is_singular('embm_beer') || is_tax('embm_style') || is_tax('embm_group'))) {

        // Display Untappd content
        $output .= EMBM_Output_Content_untappd($post->ID);

        // End post content div
        $output .= '</div>'."\n";

        // Set view defaults
        $show_profile = true;
        $show_extras = true;

        // Handle single beer posts
        if (is_singular('embm_beer')) {
            // Get single post profile setting
            if (isset($options['embm_profile_show_single']) && $options['embm_profile_show_single'] == '1') {
                $show_profile = false;
            }

            // Get single post extras setting
            if (isset($options['embm_extras_show_single']) && $options['embm_extras_show_single'] == '1') {
                $show_extras = false;
            }
        }

        // Handle beer style posts
        if (is_tax('embm_style')) {
            // Get style post profile setting
            if (isset($options['embm_profile_show_style']) && $options['embm_profile_show_style'] == '1') {
                $show_profile = false;
            }

            // Get style post extras setting
            if (isset($options['embm_extras_show_style']) && $options['embm_extras_show_style'] == '1') {
                $show_extras = false;
            }
        }

        // Handle beer group posts
        if (is_tax('embm_group')) {
            // Get group post profile setting
            if (isset($options['embm_profile_show_group']) && $options['embm_profile_show_group'] == '1') {
                $show_profile = false;
            }

            // Get group post extras setting
            if (isset($options['embm_extras_show_group']) && $options['embm_extras_show_group'] == '1') {
                $show_extras = false;
            }
        }

        // Display beer meta
        if ($show_profile || $show_extras) {
            // Start beer meta output
            $output .= '<div class="embm-beer--meta">'."\n";

            if ($show_profile && $profile != null) {
                $output .= $profile;
            }

            if ($show_extras && $extras != null) {
                $output .= $extras;
            }

            // End beer meta output
            $output .= '</div>'."\n";
        }

        // Initialize thumbnail string
        $thumb = '';

        // Show thumbnail
        if (has_post_thumbnail($post->ID)) {
            $thumb .= '<div class="embm-beer--image">'."\n";
            $thumb .= get_the_post_thumbnail($post->ID, 'full')."\n";
            $thumb .= '</div>'."\n";
        }

        // Get all content filters
        global $wp_filter;
        $content_filters = $wp_filter['the_content'];

        // Apply all content filters
        $filtered_content = $content;
        foreach ($content_filters as $content_filter) {
            foreach ($content_filter as $filter => $attributes) {
                if (($filter != __FUNCTION__) && (gettype($attributes['function']) == 'string')) {
                    $filtered_content = $attributes['function']($filtered_content);
                }
            }
        }

        // Set up content
        $content = sprintf('%s<div class="embm-beer--description">%s', $thumb, $filtered_content);
        $content .= $output;
    }

    // Return HTML content
    return $content;
}

// Load custom content filter
add_filter('the_content', 'EMBM_Output_Filter_content', -1);


/**
 * Adds custom EMBM class to EMBM pages
 *
 * @param array $classes List of existing body classes
 *
 * @return array
 */
function EMBM_Output_Filter_classes($classes)
{
    if (is_singular('embm_beer') || is_tax('embm_style') || is_tax('embm_group')) {
        $classes[] = 'embm-beer';
    }
    return $classes;
}

// Load custom body class filter
add_filter('body_class', 'EMBM_Output_Filter_classes');


/**
 * Adds the beer style to the beer post title HTML output
 *
 * @param string $title The beer post's title content
 * @param int    $id    WP post ID
 *
 * @return string/html
 */
function EMBM_Output_Filter_title($title, $id=null)
{
    // Load global post object
    global $post;

    // Display beer style
    if (EMBM_Core_Beer_style($id) && (is_singular('embm_beer') || is_tax('embm_group') ) && in_the_loop() && ($title == $post->post_title)) {
        $output = '';
        $output .= '</a><span class="embm-beer--header-style">(';
        $output .= '<a href="'.get_term_link(EMBM_Core_Beer_style($id), 'embm_style').'" title="View All '.EMBM_Core_Beer_style($id).'s">';
        $output .= EMBM_Core_Beer_style($id);
        $output .= '</a>)</span>'."\n";

        $title .= $output;
    }

    // Return updated title
    return $title;
}

// Load custom post title filter
add_filter('the_title', 'EMBM_Output_Filter_title', 10, 2);
