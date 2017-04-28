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
define('EMBM_UNTAPPD_RSS_URL', 'https://untappd.com/rss/brewery/');

// Set cache names
$GLOBALS['EMBM_UNTAPPD_CACHE'] = array(
    'beer_list'     => 'embm_untappd_beer_list',
    'brewery'       => 'embm_untappd_brewery_info',
    'checkins'      => 'embm_untappd_brewery_checkins_%s',
    'xml_checkins'  => 'embm_untappd_brewery_xml_checkins_%s',
    'user'          => 'embm_untappd_user_info',
    'save_errors'   => 'embm_untappd_save_errors'
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
    // Set up response object
    $response = array(
        'success'   => false,
        'limit'     => false,
        'data'      => null,
        'headers'   => null,
        'errors'    => null
    );

    // Open cURL connection
    $ch = curl_init($request_url);

    // Set up cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    // Set response header handler
    curl_setopt(
        $ch,
        CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$response) {
            $len = strlen($header);
            $header = explode(':', $header, 2);

            if (count($header) < 2) {
                return $len;
            }

            $response['headers'][strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        }
    );

    // Make GET request to API
    $response['data'] = curl_exec($ch);
    $response['errors'] = curl_error($ch);

    // Close cURL connection
    curl_close($ch);

    // Check response headers for ratelimit
    if (!is_null($response['headers'])
        && isset($response['headers']['x-ratelimit-remaining'])
        && intval($response['headers']['x-ratelimit-remaining']) <= 1
    ) {
        $response['limit'] = true;
        return $response;
    }

    // Check for valid response
    if ($response['data'] === false || $response['errors'] !== '') {
        return $response;
    }

    // Try to decode JSON (if needed)
    if ($decode) {
        $json_data = @json_decode($response['data']);

        // Check for any JSON decoding errors
        if ($json_data === false || is_null($json_data)) {
            return $response;
        } else {
            $response['data'] = $json_data;
        }
    }

    // Set success
    $response['success'] = true;

    // Return result
    return $response;
}

/**
 * Returns a rate-limit reached response
 *
 * @return string Rate limit error message
 */
function EMBM_Admin_Untappd_ratelimit()
{
    return __('Your Untappd API rate-limit has been reached for this hour. Please try again later.', 'embm');
}

/**
 * Determines if a cached Untappd object needs reloading
 *
 * @param string $cache_name Name of cache object
 * @param int    $timeout    Timeout period in MS
 * @param int    $cache_id   Untappd cache object ID (Default: null)
 *
 * @return bool Whether or not cache has timed out
 */
function EMBM_Admin_Untappd_reload($cache_name, $timeout, $cache_id = null)
{
    // Check cache name
    if (!array_key_exists($cache_name, $GLOBALS['EMBM_UNTAPPD_CACHE'])) {
        return false;
    }

    // Get transient name
    if (!is_null($cache_id)) {
        $transient_name = '_transient_timeout_' . sprintf($GLOBALS['EMBM_UNTAPPD_CACHE'][$cache_name], $cache_id);
    } else {
        $transient_name = '_transient_timeout_' . $GLOBALS['EMBM_UNTAPPD_CACHE'][$cache_name];
    }

    // Get transient timeout
    $transient_timeout = get_option($transient_name);

    // Return if this has already expired
    if (!$transient_timeout) {
        return true;
    }

    // Check for expiration
    return ((time() - $transient_timeout) >= $timeout);
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
        $res = EMBM_Admin_Untappd_request($untappd_url, false);

        // Handle any errors
        if (!$res['success']) {
            return null;
        }

        // Look for ID
        preg_match($rss_regex, $res['data'], $matches);

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

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Untappd_reload('user', HOUR_IN_SECONDS);

    // Get user info if it's not cached
    if (false === $user || $reload) {
        $user_info_url = sprintf($api_root, 'user/info');
        $res = EMBM_Admin_Untappd_request($user_info_url);

        // Handle any errors
        if (!$res['success']) {
            return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : null;
        }

        // Store for 24 hours (as per TOS)
        $user = $res['data']->response->user;
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['user'], $user, DAY_IN_SECONDS);
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

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Untappd_reload('brewery', HOUR_IN_SECONDS);

    // Get brewery info if it's not cached
    if (false === $brewery || $reload) {
        $brewery_url = sprintf($api_root, 'brewery/info/'.$brewery_id);
        $res = EMBM_Admin_Untappd_request($brewery_url);

        // Handle any errors
        if (!$res['success']) {
            return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : null;
        }

        // Store for 24 hours (as per TOS)
        $brewery = $res['data']->response->brewery;
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['brewery'], $brewery, DAY_IN_SECONDS);
    }

    return $brewery;
}

/**
 * Retrieves Untappd brewery check-in data from either the WP cache or API.
 *
 * @param string $api_root   A templated string for the Untappd API root URL
 * @param int    $brewery_id Untappd brewery ID
 * @param bool   $refresh    Forces a refresh of check-in data (Default: false)
 *
 * @return array Array of brewery check-ins from Untappd
 */
function EMBM_Admin_Untappd_checkins($api_root, $brewery_id, $refresh = false)
{
    // Attempt to retrieve brewery check-ins from cache
    $checkins_cache_name = sprintf($GLOBALS['EMBM_UNTAPPD_CACHE']['checkins'], $brewery_id);
    $checkins = get_transient($checkins_cache_name);

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Untappd_reload('checkins', 15 * MINUTE_IN_SECONDS, $brewery_id);

    // Get brewery checkins if it's not cached
    if (false == $checkins || $refresh || $reload) {
        $checkins_url = sprintf($api_root, 'brewery/checkins/'.$brewery_id);
        $res = EMBM_Admin_Untappd_request($checkins_url);

        // Handle any errors
        if (!$res['success']) {
            return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : null;
        }

        // Store for 24 hours (as per TOS)
        $checkins = $res['data']->response->checkins;
        set_transient($checkins_cache_name, $checkins, DAY_IN_SECONDS);
    }

    return $checkins;
}

/**
 * Retrieves and parses Untappd brewery check-in data from either the WP cache or XML.
 *
 * @param int  $brewery_id Untappd brewery ID
 * @param bool $refresh    Forces a refresh of check-in data (Default: false)
 *
 * @return object Parse XML object
 */
function EMBM_Admin_Untappd_Checkins_xml($brewery_id, $refresh = false)
{
    // Get XML content
    $xml_cache_name = sprintf($GLOBALS['EMBM_UNTAPPD_CACHE']['xml_checkins'], $brewery_id);
    $xml_data = get_transient($xml_cache_name);

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Untappd_reload('xml_checkins', 15 * MINUTE_IN_SECONDS, $brewery_id);

    // Get checkins info if it's not cached
    if (false === $xml_data || $refresh || $reload) {
        // Set Untappd brewery rss URL
        $feed_url = EMBM_UNTAPPD_RSS_URL.$brewery_id;

        // Extract Untappd xml feed data
        $res = EMBM_Admin_Untappd_request($feed_url, false);

        // Handle any errors
        if (!$res['success']) {
            return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : null;
        }

        // Set XML data to parse
        $xml_data = $res['data'];
    }

    // Force PHP to throw an exception on warnings
    set_error_handler(
        function ($errno, $errstr, $errfile, $errline, array $errcontext) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    );

    // Attempt to parse XML
    try {
        $xml = new SimpleXmlElement($xml_data);
    } catch (Exception $e) {
        return null;
    }

    // Reset error handler
    restore_error_handler();

    // Save as transient for 24 hours (per TOS)
    set_transient($xml_cache_name, $xml_data, DAY_IN_SECONDS);

    // Return result
    return $xml;
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

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Untappd_reload('beer_list', 15 * MINUTE_IN_SECONDS);

    // Get beer list if it's not cached
    if (false === $beer_list || $reload) {
        $beer_list = array();
        $beer_offset = 0;
        $beer_count = $brewery->beer_count;
        $beers_root = sprintf($api_root, 'brewery/beer_list/'.$brewery->brewery_id) . '&offset=%d';

        while (count($beer_list) < $beer_count) {
            $beers_url = sprintf($beers_root, $beer_offset);
            $res = EMBM_Admin_Untappd_request($beers_url);

            // Handle any errors
            if (!$res['success']) {
                return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : null;
            }

            $beers = $res['data']->response->beers;
            $beer_list = array_merge($beer_list, $beers->items);
            $beer_offset += $beers->count;
        }

        // Store for 24 hours (as per TOS)
        set_transient($GLOBALS['EMBM_UNTAPPD_CACHE']['beer_list'], $beer_list, DAY_IN_SECONDS);
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
    $cache_name = 'embm_untappd_data';
    $beer = null;
    $beer_cache = null;
    $reload = false;
    $expired = false;
    $now = time();
    $cache_time = 15 * MINUTE_IN_SECONDS;
    $store_time = DAY_IN_SECONDS;

    // Attempt to retrieve beer data from cache
    $beer_data = get_post_meta($post_id, $cache_name, true);

    // Check for data
    if ($beer_data && is_array($beer_data)) {
        $beer = $beer_data['beer'];
        $beer_cache = $beer_data['cached'];
    }

    // Check cached time
    if (isset($beer_cache) && !is_null($beer_cache)) {
        // Get time delta
        $delta = $now - $beer_cache;

        // Check for expired cache
        $reload = ($delta >= $cache_time);

        // If cache is over a day, remove it (as per TOS)
        $expired = ($delta >= $store_time);
    }

    // Check for beer data
    if (!is_object($beer) || false == $beer) {
        $refresh = true;
    }

    // Get fresh beer data from API
    if ($refresh || $reload) {
        $beer_res = EMBM_Admin_Untappd_Beer_get($api_root, $beer_id);

        // If there was a problem, return the cached data
        if (!is_object($beer_res) || !property_exists($beer_res, 'bid')) {
            // Remove data if it has expired (as per TOS)
            if ($expired) {
                delete_post_meta($post_id, $cache_name);
            }

            // Return cached data
            return $beer;
        }

        // Remove unneeded data
        unset($beer_res->similar);
        unset($beer_res->friends);
        unset($beer_res->media);
        unset($beer_res->vintages);

        // Set up data for storage
        $beer_data = array(
            'beer'      => $beer_res,
            'cached'    => $now
        );

        // Store for 6 hours
        update_post_meta($post_id, $cache_name, $beer_data);
    } elseif ($expired) {
        // Remove data if it has expired (as per TOS)
        delete_post_meta($post_id, $cache_name);
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
    $res = EMBM_Admin_Untappd_request($beer_url);

    // Handle any errors
    if (!$res['success']) {
        return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : null;
    }

    return $res['data']->response->beer;
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
 * @param int $beer_id Untappd beer ID
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
            return get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-error=3#labs');
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
        return null;
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
    EMBM_Admin_Untappd_Import_image($post_id, $beer);

    return null;
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

    // Check for beer image
    if (!property_exists($beer, 'beer_label_hd') || $beer->beer_label_hd == '') {
        return;
    }

    // Get WP upload dir info
    $upload_dir = wp_upload_dir();

    // Get image type from URL
    $img_parts = explode('.', $beer->beer_label_hd);
    $img_type = end($img_parts);

    // Set file save path
    $filename = $upload_dir['path'] . '/' . $beer->beer_slug . '.' . $img_type;

    // Get image file contents
    $img_res = EMBM_Admin_Untappd_request($beer->beer_label_hd, false);
    if (!$img_res['success']) {
        return;
    }

    // Save image data to file
    file_put_contents($filename, $img_res['data']);

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

/**
 * Format URLs to use HTTPS instead of HTTP
 *
 * @param str $url The URL to be formatted
 *
 * @return str Formatted URL
 */
function EMBM_Admin_Untappd_https($url)
{
    return preg_replace('/^http:/i', 'https:', $url);
}