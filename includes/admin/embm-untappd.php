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
define('EMBM_UNTAPPD_RETURN_URL', 'options-general.php?page=embm-settings&embm-import-%s=%d#labs');
define('EMBM_UNTAPPD_API_URL', 'https://api.untappd.com/v4/%s?access_token=');

// Set cache names
$GLOBALS['EMBM_UNTAPPD_CACHE'] = array(
    'beer_list'     => 'embm_untappd_beer_list',
    'brewery'       => 'embm_untappd_brewery_info',
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
        $response = file_get_contents($request_url);
    } catch (Exception $e) {
        // Return to EMBM settings page to show error & exit
        wp_redirect(get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1)));
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
 * Retrieves Untappd beer data from the API.
 *
 * @param string $api_root A templated string for the Untappd API root URL
 * @param int    $beer_id  Untappd beer ID
 * @param int    $post_id  The beer's post ID
 *
 * @return object Object of the beer's data
 */
function EMBM_Admin_Untappd_beer($api_root, $beer_id, $post_id)
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
        error_log('delta: '.$delta);

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
        $beer_url = sprintf($api_root, 'beer/info/'.$beer_id);
        $beer_res = EMBM_Admin_Untappd_request($beer_url);
        $beer = $beer_res->response->beer;

        // Remove unneeded data
        unset($beer->similar);
        unset($beer->friends);

        // Set up data for storage
        $fresh_data = array(
            'beer'      => $beer,
            'cached'    => $now
        );

        // Store for 6 hours
        update_post_meta($post_id, 'embm_untappd_data', $fresh_data);
    }
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
