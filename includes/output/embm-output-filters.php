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
 * @package EMBM\Output\Filters
 */

/**
 * Adds extra HTML content to beer posts
 *
 * @param string $content WP post content
 *
 * @return string/html
 */
function EMBM_Output_Filters_content($content)
{
    // Get global post object
    global $post;

    // Get EMBM settings
    $options = get_option('embm_options');

    // Initialize output string
    $output = '';

    // Enter the post loop
    if (in_the_loop() && (is_singular('embm_beer') || is_tax('embm_style') || is_tax('embm_group'))) {

        // Set view defaults
        $show_profile = true;
        $show_extras = true;
        $show_rating = true;
        $show_reviews = false;

        // Handle single beer posts
        if (is_singular('embm_beer')) {
            if (isset($options['embm_profile_show_single']) && $options['embm_profile_show_single'] == '1') {
                $show_profile = false;
            }
            if (isset($options['embm_extras_show_single']) && $options['embm_extras_show_single'] == '1') {
                $show_extras = false;
            }
            if (isset($options['embm_rating_show_single']) && $options['embm_rating_show_single'] == '1') {
                $show_rating = false;
            }

            // Handle reviews separately
            $show_reviews = true;
            if (isset($options['embm_reviews_show_single']) && $options['embm_reviews_show_single'] == '1') {
                $show_reviews = false;
            }
        }

        // Handle beer style posts
        if (is_tax('embm_style')) {
            if (isset($options['embm_profile_show_style']) && $options['embm_profile_show_style'] == '1') {
                $show_profile = false;
            }
            if (isset($options['embm_extras_show_style']) && $options['embm_extras_show_style'] == '1') {
                $show_extras = false;
            }
            if (isset($options['embm_rating_show_style']) && $options['embm_rating_show_style'] == '1') {
                $show_rating = false;
            }
        }

        // Handle beer group posts
        if (is_tax('embm_group')) {
            if (isset($options['embm_profile_show_group']) && $options['embm_profile_show_group'] == '1') {
                $show_profile = false;
            }
            if (isset($options['embm_extras_show_group']) && $options['embm_extras_show_group'] == '1') {
                $show_extras = false;
            }
            if (isset($options['embm_rating_show_group']) && $options['embm_rating_show_group'] == '1') {
                $show_rating = false;
            }
        }

        // Show rating
        if ($show_rating) {
            $rating = EMBM_Output_rating($post->ID);
            if ($rating != null) {
                $output .= $rating;
            }
        }

        // Display Untappd content
        $output .= EMBM_Output_untappd($post->ID);

        // End post content div
        $output .= '</div>'."\n";

        // Display beer meta
        if ($show_profile || $show_extras) {
            // Start beer meta output
            $output .= '<div class="embm-beer--meta">'."\n";

            // Display beer profile
            if ($show_profile) {
                $profile = EMBM_Output_profile($post->ID);
                if ($profile != null) {
                    $output .= $profile;
                }
            }

            // Display beer extras
            if ($show_extras) {
                $extras = EMBM_Output_extras($post->ID);
                if ($extras != null) {
                    $output .= $extras;
                }
            }

            // End beer meta output
            $output .= '</div>'."\n";
        }

        // Display reviews
        if ($show_reviews) {
            $reviews = EMBM_Output_reviews($post->ID);
            if ($reviews != null) {
                $output .= $reviews;
            }
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
                if (($filter != __FUNCTION__)
                    && (gettype($attributes['function']) == 'string')
                    && (function_exists($attributes['function']))
                ) {
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
add_filter('the_content', 'EMBM_Output_Filters_content', -1);

/**
 * Adds custom EMBM class to EMBM pages
 *
 * @param array $classes List of existing body classes
 *
 * @return array
 */
function EMBM_Output_Filters_classes($classes)
{
    if (is_singular('embm_beer') || is_tax('embm_style') || is_tax('embm_group')) {
        $classes[] = 'embm-beer';
    }
    return $classes;
}

// Load custom body class filter
add_filter('body_class', 'EMBM_Output_Filters_classes');

/**
 * Adds the beer style to the beer post title HTML output
 *
 * @param string $title The beer post's title content
 * @param int    $id    WP post ID
 *
 * @return string/html
 */
function EMBM_Output_Filters_title($title, $id=null)
{
    // Load global post object
    global $post;

    // Get style for post
    $style = EMBM_Core_Beer_style($id);

    // Display beer style
    if ($style && (is_singular('embm_beer') || is_tax('embm_group') ) && in_the_loop() && ($title == $post->post_title)) {
        // Check for the_title() call
        $is_the_title = false;
        $backtrace = debug_backtrace();
        foreach ($backtrace as $call) {
            if ($call['function'] == 'the_title') {
                $is_the_title = true;
                break;
            }
        }

        // Bail if this isn't a the_title() call
        if (!$is_the_title) {
            return $title;
        }

        // Add formatting
        $output = !is_singular('embm_beer') ? '</a>' : '';
        $output .= '<span class="embm-beer--header-style">(';
        $output .= '<a href="'.get_term_link($style, 'embm_style').'" title="';
        $output .= sprintf(__('View all %s beers', 'embm'), $style).'">';
        $output .= $style;
        $output .= '</a>)</span>'."\n";

        $title .= $output;
    }

    // Return updated title
    return $title;
}

// Load custom post title filter
add_filter('the_title', 'EMBM_Output_Filters_title', 10, 2);
