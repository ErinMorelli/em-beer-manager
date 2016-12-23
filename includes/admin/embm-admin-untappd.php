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
 * @package EMBM\Admin\Untappd
 */


// Set constants
define('EMBM_UNTAPPD_RETURN_URL', 'options-general.php?page=embm-settings&embm-import-%s=%d#%s');
define('EMBM_UNTAPPD_API_URL', 'https://api.untappd.com/v4/%s?access_token=');

// Set cache names
$GLOBALS['EMBM_UNTAPPD_CACHE'] = array(
    'beer_list'     => 'embm_untappd_beer_list',
    'brewery'       => 'embm_untappd_brewery_info',
    'checkins'      => 'embm_untappd_brewery_checkins',
    'user'          => 'embm_untappd_user_info'
);

/**
 * Makes a request to Untappd API and intercept any errors.
 *
 * @param string $request_url URL for an Untappd API endpoint
 * @param bool   $decode      Whether or not to decode JSON (default: true)
 *
 * @return array Decoded JSON API response or raw JSON string
 */
function EMBM_Admin_Untappd_request($request_url, $decode = true)
{
    // Force PHP to throw an exception on warnings
    set_error_handler(
        function ($errno, $errstr, $errfile, $errline, array $errcontext) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    );

    try {
        // Make GET request to API
        error_log($request_url);
        $response = file_get_contents($request_url);
    } catch (Exception $e) {
        // Return to EMBM settings page to show error & exit
        wp_redirect(get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1, '')));
        exit;
    }

    // Reset error handler
    restore_error_handler();

    // Return response
    if ($decode) {
        return json_decode($response);
    } else {
        return $response;
    }
}

/**
 * Retrieves Untappd brewery ID from the WP DB or website.
 *
 * @param string $untappd_url URL of an Untappd brewery page.
 *
 * @return int Untapped brewery ID
 */
function EMBM_Admin_Untappd_id($untappd_url)
{
    // Retrieve stored brewery ID
    $brewery_id = get_option('embm_untappd_brewery_id');

    // Get brewery ID from Untappd if not set
    if (!$brewery_id || $brewery_id == '') {
        // Set up RSS feed regex to retrieve brewery ID
        $rss_regex = '/<p class="rss"><a href="\/rss\/brewery\/(\d+)">/';

        // Get brewery page contents
        $brewery_page = EMBM_Admin_Untappd_request($untappd_url, false);

        // Look for ID
        preg_match($rss_regex, $brewery_page, $matches);

        // Store ID
        $brewery_id = $matches[1];
        update_option('embm_untappd_brewery_id', $brewery_id);
    }

    return $brewery_id;
}

/**
 * Retrieves Untappd user data from either the WP cache or API.
 *
 * @param string $api_root A templated string for the Untappd API root URL
 *
 * @return array Array of user data from Untappd
 */
function EMBM_Admin_Untappd_user($api_root)
{
    // Attempt to retrieve user info from cache
    $user = get_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['user']);

    // Get user info if it's not cached
    if (false === $user) {
        $user_info_url = sprintf($api_root, 'user/info');
        $user_res = EMBM_Admin_Untappd_request($user_info_url);
        $user = $user_res->response->user;

        // Store for 1 week
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['user'], $user, WEEK_IN_SECONDS);
    }

    return $user;
}

/**
 * Retrieves Untappd brewery data from either the WP cache or API.
 *
 * @param string $api_root   A templated string for the Untappd API root URL
 * @param int    $brewery_id Untappd brewery ID
 *
 * @return array Array of brewery data from Untappd
 */
function EMBM_Admin_Untappd_brewery($api_root, $brewery_id)
{
    // Attempt to retrieve brewery info from cache
    $brewery = get_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['brewery']);

    // Get brewery info if it's not cached
    if (false === $brewery) {
        $brewery_url = sprintf($api_root, 'brewery/info/'.$brewery_id);
        $brewery_res = EMBM_Admin_Untappd_request($brewery_url);
        $brewery = $brewery_res->response->brewery;

        // Store for 1 week
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['brewery'], $brewery, WEEK_IN_SECONDS);
    }

    return $brewery;
}

/**
 * Retrieves Untappd brewery check-in data from either the WP cache or API.
 *
 * @param string $api_root   A templated string for the Untappd API root URL
 * @param int    $brewery_id Untappd brewery ID
 *
 * @return array Array of brewery check-ins from Untappd
 */
function EMBM_Admin_Untappd_checkins($api_root, $brewery_id)
{
    // Attempt to retrieve brewery checkins from cache
    $checkins = get_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['checkins']);

    // Get brewery checkins if it's not cached
    if (false === $checkins) {
        $checkins_url = sprintf($api_root, 'brewery/checkins/'.$brewery_id);
        $checkins_res = EMBM_Admin_Untappd_request($checkins_url);
        $checkins = $checkins_res->response->checkins;

        // Store for 1 hour
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['checkins'], $checkins, HOUR_IN_SECONDS);
    }

    return $checkins;
}

/**
 * Retrieves Untappd brewery beer data from either the WP cache or API.
 *
 * @param string $api_root A templated string for the Untappd API root URL
 * @param array  $brewery  Untappd brewery data
 *
 * @return array Array of all beers for the given brewery
 */
function EMBM_Admin_Untappd_beers($api_root, $brewery)
{
    // Attempt to retrieve beer list from cache
    $beer_list = get_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['beer_list']);

    // Get beer list if it's not cached
    if (false === $beer_list) {
        $beer_list = [];
        $beer_offset = 0;
        $beer_count = $brewery->beer_count;
        $beers_root = sprintf($api_root, 'brewery/beer_list/'.$brewery->brewery_id) . '&offset=%d';

        while (count($beer_list) < $beer_count) {
            $beers_url = sprintf($beers_root, $beer_offset);
            $beers_res = EMBM_Admin_Untappd_request($beers_url);
            $beers = $beers_res->response->beers;

            $beer_list = array_merge($beer_list, $beers->items);
            $beer_offset += $beers->count;
        }

        // Store for 1 week
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['beer_list'], $beer_list, WEEK_IN_SECONDS);
    }

    return $beer_list;
}

/**
 * Retrieves Untappd beer data from the API or DB
 *
 * @param string $api_root A templated string for the Untappd API root URL
 * @param int    $beer_id  Untappd beer ID
 * @param int    $post_id  The beer's post ID
 * @param bool   $refresh  Forces a refresh of beer data (Default: false)
 *
 * @return object Object of the beer's data
 */
function EMBM_Admin_Untappd_beer($api_root, $beer_id, $post_id, $refresh = false)
{
    // Set vars
    $beer = null;
    $beer_cache = null;
    $refresh = false;
    $now = time();
    $six_hours = 6 * HOUR_IN_SECONDS;

    // Attempt to retrieve beer data from cache
    $beer_data = get_post_meta($post_id, 'embm_untappd_data', true);

    // Check for data
    if ($beer_data) {
        $beer = $beer_data['beer'];
        $beer_cache = $beer_data['cached'];
    }

    // Check cached time
    if (!is_null($beer_cache)) {
        // Get time delta
        $delta = $now - $beer_cache;

        // Check for
        if ($delta >= $six_hours) {
            $refresh = true;
        }
    }

    // Check for beer data
    if (is_null($beer) || false == $beer) {
        $refresh = true;
    }

    // Get fresh beer data from API
    if ($refresh) {
        $beer = EMBM_Admin_Untappd_Beer_get($api_root, $beer_id);

        // Remove unneeded data
        unset($beer->similar);
        unset($beer->friends);
        unset($beer->media);
        unset($beer->vintages);

        // Set up data for storage
        $beer_data = array(
            'beer'      => $beer,
            'cached'    => $now
        );

        // Store for 6 hours
        update_post_meta($post_id, 'embm_untappd_data', $beer_data);
    }

    return $beer_data;
}

/**
 * Retrieves Untappd beer data from the API.
 *
 * @param string $api_root A templated string for the Untappd API root URL
 * @param int    $beer_id  Untappd beer ID
 *
 * @return object Object of the beer's data
 */
function EMBM_Admin_Untappd_Beer_get($api_root, $beer_id)
{
    $beer_url = sprintf($api_root, 'beer/info/'.$beer_id);
    $beer_res = EMBM_Admin_Untappd_request($beer_url);
    $beer = $beer_res->response->beer;

    return $beer;
}

/**
 * Flushes the cached labs data
 *
 * @param string $key Optional. Name of cached item to flush.
 *
 * @return void
 */
function EMBM_Admin_Untappd_flush($key = null)
{
    // Check for specified key
    if (!is_null($key)) {
        delete_transient($GLOBALS['EMBM_UNTAPPD_CACHE'][$key]);
    } else {
        // Iteratively remove items
        foreach ($GLOBALS['EMBM_UNTAPPD_CACHE'] as $name => $value) {
            delete_transient($value);
        }
    }
}

/**
 * Search for a given Untappd beer ID in an array of beers.
 *
 * @param int   $beer_id   Untappd beer ID
 * @param array $beer_list Array of Untappd beers
 *
 * @return array Array of beer data
 */
function EMBM_Admin_Untappd_find($beer_id, $beer_list)
{
    // Iteratively search beers
    foreach ($beer_list as $item) {
        $beer = $item->beer;
        if ($beer->bid == $beer_id) {
            return $beer;
        }
    }

    // Return null if not found
    return null;
}

/**
 * Search for a given Untappd beer ID in DB
 *
 * @param int   $beer_id   Untappd beer ID
 *
 * @return int WP Post ID of beer
 */
function EMBM_Admin_Untappd_exists($beer_id)
{
    // Get global WP database reference
    global $wpdb;

    // Remove individual beer Untappd data
    return $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT
                post_id
            FROM
                $wpdb->postmeta
            WHERE
                meta_key = 'embm_untappd' &&
                meta_value = %s
            ",
            $beer_id
        )
    );
}

/**
 * Insert post from Untappd
 *
 * @param array $beer       Untappd beer data
 * @param int   $brewery_id Untappd brewery account ID
 * @param bool  $check      Whether or not to verify brewery (default: false)
 *
 * @return void
 */
function EMBM_Admin_Untappd_import($beer, $brewery_id, $check = false)
{
    // Check that beer is owned by brewery, if needed
    if ($check) {
        // Compare beer's brewery ID to user's brewery ID
        if ($beer->brewery->brewery_id != intval($brewery_id)) {
            // Return to EMBM settings page to show error & exit
            wp_redirect(get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-error=3#labs'));
            exit;
        }
    }

    // Set beer slug
    $beer_slug = sanitize_title($beer->beer_name);

    // Set up duplicate check args
    $dup_args = array(
        'name'           => $beer_slug,
        'post_type'      => 'embm_beer',
        'post_status'    => 'publish',
        'posts_per_page' => 1
    );

    // Check for duplicate (#2)
    $duplicate = get_posts($dup_args);
    if ($duplicate) {
        return;
    }

    // Set post publish date from Untappd created date
    $beer_date = date('Y-m-d H:i:s', strtotime($beer->created_at));

    // Remove unneeded data
    unset($beer->similar);
    unset($beer->friends);
    unset($beer->media);
    unset($beer->vintages);

    // Set up data for storage
    $beer_data = array(
        'beer'      => $beer,
        'cached'    => time()
    );

    // Set up post array
    $new_beer_post = array(
        'post_author'   => get_current_user_id(),
        'post_title'    => $beer->beer_name,
        'post_name'     => $beer_slug,
        'post_content'  => $beer->beer_description,
        'post_date'     => $beer_date,
        'post_status'   => 'publish',
        'post_type'     => 'embm_beer',
        'tax_input'     => array(
            'embm_style'    => $beer->beer_style
        ),
        'meta_input'    => array(
            'embm_abv'              => $beer->beer_abv,
            'embm_ibu'              => $beer->beer_ibu,
            'embm_untappd'          => $beer->bid,
            'embm_untappd_data'     => $beer_data,
            'embm_reviews_count'    => 5
        )
    );

    // Insert post
    $post_id = wp_insert_post($new_beer_post, true);

    // Add post image
    if (property_exists($beer, 'beer_label_hd')) {
        EMBM_Admin_Untappd_Import_image($post_id, $beer);
    }
}

/**
 * Upload and set beer featured image
 *
 * @param int    $post_id The beer post ID
 * @param object $beer    Beer object from Untappd API
 *
 * @return void
 */
function EMBM_Admin_Untappd_Import_image($post_id, $beer)
{
    // Set beer slug
    $img_slug = sanitize_title($beer->beer_slug);

    // Set up args for duplicate check
    $dup_args = array(
        'name'           => $img_slug,
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1
    );

    // Run post query
    $img_exists = get_posts($dup_args);

    // Handle if we found the image
    if ($img_exists) {
        // Set as post image and exit
        set_post_thumbnail($post_id, $img_exists[0]->ID);
        return;
    }

    // Get WP upload dir info
    $upload_dir = wp_upload_dir();

    // Get image type from URL
    $img_parts = explode('.', $beer->beer_label_hd);
    $img_type = end($img_parts);

    // Set file save path
    $filename = $upload_dir['path'] . '/' . $beer->beer_slug . '.' . $img_type;

    // Save image from URL
    file_put_contents($filename, EMBM_Admin_Untappd_request($beer->beer_label_hd, false));

    // Check the type of file
    $filetype = wp_check_filetype(basename($filename), null);

    // Prepare an post data for attachment
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title'     => $beer->beer_slug,
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert the attachment
    $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Set as thumbnail for beer
    set_post_thumbnail($post_id, $attach_id);
}
