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
 * @param int   $beer_id WP post ID for single beer
 * @param array $options Extra display variables
 *
 * @return string/html
 */
function EMBM_Output_beer($beer_id, $options)
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

    // Show rating
    if (isset($options['rating']) && $options['rating']) {
        $rating = EMBM_Output_rating($beer_id);
        if ($rating != null) {
            $output .= $rating;
        }
    }

    // End beer description
    $output .= EMBM_Output_untappd($beer_id);
    $output .= '</div>'."\n";

    // Beer meta
    if ((isset($options['profile']) && $options['profile'])
        || (isset($options['extras']) && $options['extras'])
    ) {
        // Begin beer meta output
        $output .= '<div class="embm-beer--meta">'."\n";

        // Display beer profile
        if (isset($options['profile']) && $options['profile']) {
            $profile = EMBM_Output_profile($beer_id);
            if ($profile != null) {
                $output .= $profile;
            }
        }

        // Display beer extras
        if (isset($options['extras']) && $options['extras']) {
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

    // Display reviews
    if (isset($options['reviews']) && $options['reviews']) {
        $reviews = EMBM_Output_reviews($beer_id, $options['reviews_count']);
        if ($reviews != null) {
            $output .= $reviews;
        }
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
    $options = get_option('embm_options');
    $output = '';

    // Don't show if Untappd is disabled
    if (EMBM_Core_Beer_disabled()) {
        return null;
    }

    // Get raw Untappd value from DB
    $untap = get_post_meta($beer_id, 'embm_untappd', true);

    // Bail if we don't have an ID
    if (!$untap || $untap == '') {
        return null;
    }

    // Set up translatable title text
    $untap_title = __('Check in on Untappd', 'embm');

    // Get icon set
    $untap_icon = $options['embm_untappd_icons'];

    // Set up icon image URL
    $untap_img = EMBM_PLUGIN_URL.'assets/img/checkin-button-'.$untap_icon.'.png';

    // If an Untappd value is set for this beer, display the link
    $output = '<div class="embm-beer--untappd">';
    $output .= '<a href="'.EMBM_Core_Beer_attr($beer_id, 'untappd').'" target="_blank" title="'.$untap_title.'">';
    $output .= '<img src="'.$untap_img.'" alt="'.$untap_title.'" border="0" />';
    $output .= '</a></div>';

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
    $options = get_option('embm_options');

    // Get global profile display setting
    $hide_profile = null;
    if (isset($options['embm_profile_show'])) {
        $hide_profile = $options['embm_profile_show'];
    }

    // If global display is not set, don't show the content
    if ($hide_profile == '1') {
        return null;
    }

    // Set up beer profile attributes
    $abv = EMBM_Core_Beer_attr($beer_id, 'abv');
    $ibu = EMBM_Core_Beer_attr($beer_id, 'ibu');
    $malts = EMBM_Core_Beer_attr($beer_id, 'malts');
    $hops = EMBM_Core_Beer_attr($beer_id, 'hops');
    $adds = EMBM_Core_Beer_attr($beer_id, 'adds');
    $yeast = EMBM_Core_Beer_attr($beer_id, 'yeast');

    // If none of the values are set, don't show the content
    if (($abv == '0%') && ($ibu == '0') && ($malts == '') && ($hops == '') && ($adds ==' ') && ($yeast =='')) {
        return null;
    }

    // Start profile output
    $output = '<div class="embm-beer--meta-profile">'."\n";

    // Display ABV
    if ($abv != '0%') {
        $output .= '<div class="abv"><span class="label">';
        $output .= __('ABV', 'embm').':';
        $output .= '</span><span class="value">'.$abv.'</span></div>'."\n";
    }
    // Display IBU
    if ($ibu != '0') {
        $output .= '<div class="ibu"><span class="label">';
        $output .= __('IBU', 'embm').':';
        $output .= '</span><span class="value">'.$ibu.'</span></div>'."\n";
    }
    // Display Malts
    if ($malts != '') {
        $output .= '<div class="malts"><span class="label">';
        $output .= __('Malts', 'embm').':';
        $output .= '</span><span class="value">'.$malts.'</span></div>'."\n";
    }
    // Display Hops
    if ($hops != '') {
        $output .= '<div class="hops"><span class="label">';
        $output .= __('Hops', 'embm').':';
        $output .= '</span><span class="value">'.$hops.'</span></div>'."\n";
    }
    // Display Additions
    if ($adds != '') {
        $output .= '<div class="other"><span class="label">';
        $output .= __('Other', 'embm').':';
        $output .= '</span><span class="value">'.$adds.'</span></div>'."\n";
    }
    // Display Yeast
    if ($yeast != '') {
        $output .= '<div class="yeast"><span class="label">';
        $output .= __('Yeast', 'embm').':';
        $output .= '</span><span class="value">'.$yeast.'</span></div>'."\n";
    }

    // End profile output
    $output .= '</div>'."\n";

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
    $options = get_option('embm_options');

    // Get global extras display setting
    $hide_extras = null;
    if (isset($options['embm_extras_show'])) {
        $hide_extras = $options['embm_extras_show'];
    }

    // If global display is not set, don't show the content
    if ($hide_extras == '1') {
        return null;
    }

    // Set up beer extras attributes
    $bnum = EMBM_Core_Beer_attr($beer_id, 'beer_num');
    $avail = EMBM_Core_Beer_attr($beer_id, 'avail');
    $notes = EMBM_Core_Beer_attr($beer_id, 'notes');

    // If none of the values are set, don't show the content
    if (($bnum == '#') && ($avail == '') && ($notes == '')) {
        return null;
    }

    // Start extras output
    $output = '<div class="embm-beer--meta-extras">'."\n";

    // Display beer number
    if ($bnum != '#') {
        $output .= '<div class="beer_num"><span class="label">';
        $output .= __('Beer Number', 'embm').':';
        $output .= '</span><span class="value">'.$bnum.'</span></div>'."\n";
    }
    // Display availability
    if ($avail != '') {
        $output .= '<div class="avail"><span class="label">';
        $output .= __('Availability', 'embm').':';
        $output .= '</span><span class="value">'.$avail.'</span></div>'."\n";
    }
    // Display notes
    if ($notes != '') {
        $output .= '<div class="notes"><span class="label">';
        $output .= __('Additional Notes', 'embm');
        $output .= '</span><span class="value">'.$notes.'</span></div>'."\n";
    }

    // End extras output
    $output .= '</div>'."\n";

    // Return HTML content
    return $output;
}

/**
 * Generate star HTML for a given beer
 *
 * @param int $beer_id WP post ID
 *
 * @return string/html
 */
function EMBM_Output_rating($beer_id)
{
    $options = get_option('embm_options');

    // Get global ratings display setting
    $hide_rating = null;
    if (isset($options['embm_rating_show'])) {
        $hide_rating = $options['embm_rating_show'];
    }

    // If global display is not set, show the content
    if ($hide_rating == '1') {
        return null;
    }

    // Get rating data
    $untappd_data = EMBM_Core_Beer_attr($beer_id, 'untappd_data');

    // Bail if we don't have any data
    if (!$untappd_data || $untappd_data == '' || !is_object($untappd_data)) {
        return null;
    }

    // Make sure the data is well-formatted
    if (!property_exists($untappd_data, 'rating_score') || !property_exists($untappd_data, 'rating_count')) {
        return null;
    }

    // Set score and count
    $rating_score = $untappd_data->rating_score;
    $rating_count = number_format($untappd_data->rating_count);

    // Get rating format
    $format_id = $options['embm_untappd_rating_format'];
    $formats = EMBM_Core_Beer_ratings();
    $format = $formats[$format_id];

    // Get stars
    $stars = EMBM_Output_Rating_stars($rating_score);

    // Generate HTML output
    $output = '<div class="embm-beer--rating">'."\n";
    $output .= sprintf($format['form'], $stars, $rating_score, $rating_count, __('Ratings', 'embm'));
    $output .= EMBM_Output_Rating_styles();
    $output .= '</div>'."\n";

    // Return HTML output
    return $output;
}

/**
 * Generate star HTML for a given rating
 *
 * @param float $rating_score Untappd beer rating value
 *
 * @return string/html
 */
function EMBM_Output_Rating_stars($rating_score)
{
    // Round to the nearest quarter
    $rating = (floor($rating_score * 4) / 4);

    // Calculate stars
    $full_count = floor($rating);
    $empty_count = (5 - $full_count);
    $fraction = (($rating - $full_count) * 100);
    if ($fraction) {
        $empty_count = (4 - $full_count);
    }

    // Generate stars
    $stars = '';

    if ($full_count > 0) {
        foreach (range(1, $full_count) as $full_star) {
            $stars .= file_get_contents(EMBM_PLUGIN_DIR.'assets/img/star-full.svg');
        }
    }

    if ($fraction) {
        $stars .= file_get_contents(EMBM_PLUGIN_DIR.'assets/img/star-'.$fraction.'.svg');
    }

    if ($empty_count > 0) {
        foreach (range(1, $empty_count) as $empty_star) {
            $stars .= file_get_contents(EMBM_PLUGIN_DIR.'assets/img/star-empty.svg');
        }
    }

    // Return HTML output
    return $stars;
}

/**
 * Generate star CSS
 *
 * @return string/html
 */
function EMBM_Output_Rating_styles()
{
    $options = get_option('embm_options');

    // Get styles from admin settings
    $star_color = $options['embm_untappd_rating_color'];
    $star_opacity = ($options['embm_untappd_rating_opacity'] / 100);

     // Generate styles based on admin settings
    $styles = '<style type="text/css">';
    $styles .= '.embm-beer--rating-stars polygon';
    $styles .= '{fill:'.$star_color.';stroke:'.$star_color.';}';
    $styles .= '.embm-beer--rating-stars .embm-rating-star--empty polygon,';
    $styles .= '.embm-beer--rating-stars .embm-rating-star--fraction polygon:nth-of-type(1)';
    $styles .= '{opacity:'.$star_opacity.'}';
    $styles .= '</style>';

    // Return CSS content
    return $styles;
}

/**
 * Generate reviews HTML for a given beer
 *
 * @param int $beer_id       WP post ID for beer entry
 * @param int $reviews_count Number of reviews to show (default: null)
 *
 * @return string/html
 */
function EMBM_Output_reviews($beer_id, $reviews_count = null)
{
    $options = get_option('embm_options');

    // Get global reviews display setting
    $hide_reviews = null;
    if (isset($options['embm_reviews_show'])) {
        $hide_reviews = $options['embm_reviews_show'];
    }

    // If global display is not set, show the content
    if ($hide_reviews == '1') {
        return null;
    }

    // Get review data
    $untappd_data = EMBM_Core_Beer_attr($beer_id, 'untappd_data');
    $untappd_url = EMBM_Core_Beer_attr($beer_id, 'untappd');

    // Bail if we don't have any data
    if (!$untappd_data || $untappd_data == '' || !is_object($untappd_data)) {
        return null;
    }

    // Make sure the data is well-formatted
    if (!property_exists($untappd_data, 'checkins')) {
        return null;
    }

    // Set review data
    $review_data = $untappd_data->checkins;
    $reviews = null;

    // Bail if we don't have any reviews
    if (is_null($review_data) && !property_exists($review_data, 'items')) {
        return null;
    }

    // Get review items
    $reviews = $review_data->items;

    // Get review count
    if (is_null($reviews_count)) {
        $reviews_count = $options['embm_reviews_count_single'];
        $local_count = EMBM_Core_Beer_attr($beer_id, 'reviews_count');
        if ($local_count !== $reviews_count) {
            $reviews_count = $local_count;
        }
    }

    // Start reviews output
    $output = '<div class="embm-beer--reviews">'."\n";
    $output .= '<h4 class="embm-beer--reviews-title">'.__('Recent Check-ins', 'embm').'</h4>'."\n";

    // Check that we have reviews
    if (count($reviews) > 0) {
        // Iterate over reviews
        foreach (range(0, ($reviews_count-1)) as $ix) {
            if (array_key_exists($ix, $reviews)) {
                $output .= EMBM_Output_Review_content($reviews[$ix]);
            }
        }
    } else {
        // Friendly text for when there are no reviews
        $output .= '<p class="embm-beer--reviews-empty">';
        $output .= __('This beer has no check-ins yet!', 'embm');
        $output .= '</p>';
    }

    // Add footer
    $output .= '<div class="embm-beer--reviews-footer">';

    // Add 'more' link
    if (count($reviews) > 0) {
        $more_text = __('View More', 'embm');
        $output .= '<div class="embm-beer--reviews-more">';
        $output .= '<a href="'.$untappd_url.'" target="_blank" title="' . $more_text . '">';
        $output .= '<span>' . $more_text . '</span>';
        $output .= '<span class="dashicons dashicons-arrow-right-alt"></span>';
        $output .= '</a></div>';
    }

    // Add Untappd credit
    $credit_text = __('Powered by Untappd', 'embm');
    $output .= '<div class="embm-beer--reviews-credit">';
    $output .= '<a href="https://untappd.com" target="_blank" rel="nofollow" title="' . $credit_text . '">';
    $output .= '<img src="' . EMBM_PLUGIN_URL .'/assets/img/ut-credit.png" alt="' . $credit_text . '" border="0" />';
    $output .= '</a></div></div>';

    // Get star styles
    $styles = EMBM_Output_Rating_styles();

    // End HTML content
    $output .= $styles;
    $output .= '</div>'."\n";

    // Return HTML output
    return $output;
}

/**
 * Generate review HTML for a given review
 *
 * @param object $review Beer review data
 *
 * @return string/html
 */
function EMBM_Output_Review_content($review)
{
    // Get review parts
    $user = $review->user;
    $venue = $review->venue;

    // Check in user URL
    $user_url = 'https://untappd.com/user/'.$user->user_name;

    // Start single review output
    $output = '<div class="embm-beer--review" id="embm-beer--review-'.$review->checkin_id.'">'."\n";

    // User avatar
    $output .= '<div class="embm-beer--review-thumb"><img src="'.$user->user_avatar.'" border="0" /></div>';

    // Main content
    $output .= '<div class="embm-beer--review-content">';

    // Author name
    $output .= '<h4 class="embm-beer--review-author">';
    $output .= '<a href="'.$user_url.'" target="_blank">';
    $output .= sprintf('%s %s', $user->first_name, $user->last_name);
    $output .= '</a></h4>';

    // Review stars
    if ($review->rating_score) {
        $output .= '<div class="embm-beer--rating-stars" title="'.$review->rating_score.'">';
        $output .= EMBM_Output_Rating_stars($review->rating_score);
        $output .= '</div>';
    }

    // Review comment
    if ($review->checkin_comment != '') {
        $output .= '<div class="embm-beer--review-comment">'.$review->checkin_comment.'</div>';
    }

    // Start review meta
    $output .= '<div class="embm-beer--review-meta">';

    // Show review date
    $output .= '<span class="embm-beer--review-date">';
    $output .= '<a href="'.$user_url.'/checkin/'.$review->checkin_id.'" target="_blank">';
    $output .= EMBM_Output_Review_date($review->created_at);
    $output .= '</a></span>';

    // Show review location
    if (is_object($venue) && property_exists($venue, 'venue_id')) {
        $output .= '<span class="embm-beer--review-venue">';
        $output .= '<a href="https://untappd.com/venue/'.$venue->venue_id.'" target="_blank">';
        $output .= $venue->venue_name;
        $output .= '</a></span>';
    }

    // Show review link
    $output .= '<span class="embm-beer--review-link">';
    $output .= '<a href="'.$user_url.'/checkin/'.$review->checkin_id.'" target="_blank">';
    $output .= __('View Full Check-in', 'embm');
    $output .= '</a></span>';

    // End review meta and content
    $output .= '</div></div>';

    // End review
    $output .= '</div>'."\n";

    // Return HTML output
    return $output;
}

/**
 * Display data using WP settings
 *
 * @param string $date The date to display
 *
 * @return string
 */
function EMBM_Output_Review_date($date)
{
    // Display date using WP timezone setting
    $offset = get_option('gmt_offset');
    $post_date = strtotime($date);
    $new_date = mktime(
        date('H', $post_date) + $offset,
        date('i', $post_date),
        0,
        date('n', $post_date),
        date('j', $post_date),
        date('y', $post_date)
    );

    // Output formatted date
    return date('j M y', $new_date);
}
