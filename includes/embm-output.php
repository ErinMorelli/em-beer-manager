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


// Load other modules
require EMBM_PLUGIN_DIR.'includes/output/embm-output-shortcodes.php';
require EMBM_PLUGIN_DIR.'includes/output/embm-output-filters.php';


/**
 * Generate HTML output for a single beer entry
 *
 * @param int  $beer_id     WP post ID for single beer
 * @param bool $showprofile True/false value to show beer profile
 * @param bool $showextras  True/false value to show beer extras
 *
 * @return string/html
 */
function EMBM_Output_beer($beer_id, $showprofile=true, $showextras=true)
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
        $link_title = sprintf(__('View all %s beers', 'embm'), EMBM_Core_Beer_style($beer_id));

        $output .= '<span class="embm-beer--header-style">(';
        $output .= '<a href="'.get_term_link(EMBM_Core_Beer_style($beer_id), 'embm_style').'" title="'.$link_title.'">';
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

    // Get all content filters
    global $wp_filter;
    $content_filters = $wp_filter['the_content'];

    // Apply all content filters
    $filtered_content = get_the_content($beer_id);
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
    $output .= $filtered_content;

    // Set read more link
    if ((is_tax('embm_style') || is_archive()) && !is_tax('embm_group')) {
        $output .= ' <a class="read-more" href="'.get_permalink($beer_id).'">';
        $output .= __('More', 'embm').'...';
        $output .= '</a>';
    }

    // End beer description
    $output .= EMBM_Output_untappd($beer_id);
    $output .= '</div>'."\n";

    // Beer meta
    if ($showprofile || $showextras) {
        // Begin beer meta output
        $output .= '<div class="embm-beer--meta">'."\n";

        // Display beer profile
        if ($showprofile) {
            // Get profile HTML
            $profile = EMBM_Output_profile($beer_id);

            if ($profile != null) {
                $output .= $profile;
            }
        }

        // Display beer extras
        if ($showextras) {
            // Get extras HTML
            $extras = EMBM_Output_extras($beer_id);

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
function EMBM_Output_untappd($beer_id)
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
        $untap = get_post_meta($beer_id, 'embm_untappd', true);

        // Set up translatable title text
        $untap_title = __('Check in on Untappd', 'embm');

        // Get icon set
        $untap_icon = $ut_option['embm_untappd_icons'];

        // Set up icon image URL
        $untap_img = EMBM_PLUGIN_URL.'assets/img/checkin-button-'.$untap_icon.'.png';

        // If an Untappd value is set for this beer, display the link
        if ($untap != '') {
            $output = '<div class="untappd"><a href="'.EMBM_Core_Beer_attr($beer_id, 'untappd').'" target="_blank" title="'.$untap_title.'">';
            $output .= '<img src="'.$untap_img.'" alt="'.$untap_title.'" border="0" /></a></div>'."\n";
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
function EMBM_Output_profile($beer_id)
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
                $output .= __('ABV', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'abv').'</span></div>'."\n";
            }
            // Display IBU
            if ($ibu != '0') {
                $output .= '<div class="ibu"><span class="label">';
                $output .= __('IBU', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'ibu').'</span></div>'."\n";
            }
            // Display Malts
            if ($malts != '') {
                $output .= '<div class="malts"><span class="label">';
                $output .= __('Malts', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'malts').'</span></div>'."\n";
            }
            // Display Hops
            if ($hops != '') {
                $output .= '<div class="hops"><span class="label">';
                $output .= __('Hops', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'hops').'</span></div>'."\n";
            }
            // Display Additions
            if ($adds != '') {
                $output .= '<div class="other"><span class="label">';
                $output .= __('Other', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'adds').'</span></div>'."\n";
            }
            // Display Yeast
            if ($yeast != '') {
                $output .= '<div class="yeast"><span class="label">';
                $output .= __('Yeast', 'embm').':';
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
function EMBM_Output_extras($beer_id)
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
                $output .= __('Beer Number', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'beer_num').'</span></div>'."\n";
            }
            // Display availability
            if ($avail != '') {
                $output .= '<div class="avail"><span class="label">';
                $output .= __('Availability', 'embm').':';
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'avail').'</span></div>'."\n";
            }
            // Display notes
            if ($notes != '') {
                $output .= '<div class="notes"><span class="label">';
                $output .= __('Additional Notes', 'embm');
                $output .= '</span><span class="value">'.EMBM_Core_Beer_attr($beer_id, 'notes').'</span></div>'."\n";
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
 * Generate star HTML from a given rating
 *
 * @param float $rating Untappd beer rating value
 *
 * @return void
 */
function EMBM_Output_rating($rating)
{
    $full_count = floor($rating);
    $empty_count = (5 - $full_count);
    $has_half = (ceil($rating) > $full_count);

    if ($has_half) {
        $empty_count = 4 - $full_count;
    }

    $output = '';

    if ($full_count > 0) {
        foreach (range(1, $full_count) as $full_star) {
            $output .= '<span class="dashicons dashicons-star-filled"></span>';
        }
    }

    if ($has_half) {
        $output .= '<span class="dashicons dashicons-star-half"></span>';
    }

    if ($empty_count > 0) {
        foreach (range(1, $empty_count) as $empty_star) {
            $output .= '<span class="dashicons dashicons-star-empty"></span>';
        }
    }

    return $output;

    printf(
        '%s (%.2f) | %s %s',
        EMBM_Admin_Metabox_Untappd_stars($untappd_data->rating_score),
        $untappd_data->rating_score,
        number_format($untappd_data->rating_count),
        __('Ratings', 'embm')
    );
}


/**
 * Generate star HTML from a given rating
 *
 * @param float $rating Untappd beer rating value
 *
 * @return void
 */
function EMBM_Output_reviews()
{
}



