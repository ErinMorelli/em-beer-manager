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
 * @package EMBM\Admin\Labs
 */


// Set constants
define('EMBM_RETURN_URL', 'options-general.php?page=embm-settings&embm-import-%s=%d#labs');
define('EMBM_API_URL_FORMAT', 'https://api.untappd.com/v4/%s?access_token=');


/**
 * Cleans up URL after return
 *
 * @return void
 */
function EMBM_Admin_Labs_urlclean()
{
?>
    <script type="text/javascript">EMBM_Labs_CleanURL();</script>
<?php
    return;
}


/**
 * Deauthorize user and display error message
 *
 * @return void
 */
function EMBM_Admin_Labs_deauthorize()
{
    // Deauthorize user
    delete_option('embm_untappd_token');
    delete_option('embm_untappd_brewery_id');

    // Display error message
?>
    <p class="warning"><?php _e('Sorry, Untappd importing is only supported for brewery accounts.', 'embm'); ?></p>
    <p>
        <button class="embm-labs--authorize-button button-secondary"><?php _e('Re-authorize with Untappd', 'embm'); ?></button><br />
        <small><em><?php _e('You will need to log out of Untappd before re-authorizing.', 'embm'); ?></em></small>
    <p>
<?php
    return;
}


/**
 * Makes a request to Untappd API and intercept any errors.
 *
 * @param string $request_url URL for an Untappd API endpoint
 * @param bool   $decode      Whether or not to decode JSON (default: True)
 *
 * @return array Decoded JSON API response or raw JSON string
 */
function EMBM_Admin_Labs_Untappd_request($request_url, $decode = true)
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
        wp_redirect(get_admin_url(null, sprintf(EMBM_RETURN_URL, 'error', 1)));
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
 * Retrieves Untappd user data from either the WP cache or API.
 *
 * @param string $api_root A templated string for the Untappd API root URL
 *
 * @return array Array of user data from Untappd
 */
function EMBM_Admin_Labs_Untappd_user($api_root)
{
    // Get API user info
    $user_info_cache = 'embm_untappd_user_info';
    $user = wp_cache_get($user_info_cache);

    // Get user info if it's not cached
    if (false === $user) {
        $user_info_url = sprintf($api_root, 'user/info');
        $user_res = EMBM_Admin_Labs_Untappd_request($user_info_url);
        $user = $user_res->response->user;
        wp_cache_set($user_info_cache, $user);
    }

    return $user;
}


/**
 * Retrieves Untappd brewery ID from the WP DB or website.
 *
 * @param string $untappd_url URL of an Untappd brewery page.
 *
 * @return int Untapped brewery ID
 */
function EMBM_Admin_Labs_Untappd_id($untappd_url)
{
    // Retrieve stored brewery ID
    $brewery_id = get_option('embm_untappd_brewery_id');

    // Get brewery ID from Untappd if not set
    if (!$brewery_id || $brewery_id == '') {
        // Set up RSS feed regex to retrieve brewery ID
        $rss_regex = '/<p class="rss"><a href="\/rss\/brewery\/(\d+)">/';

        // Get brewery page contents
        $brewery_page = EMBM_Admin_Labs_Untappd_request($untappd_url, false);

        // Look for ID
        preg_match($rss_regex, $brewery_page, $matches);

        // Store ID
        $brewery_id = $matches[1];
        update_option('embm_untappd_brewery_id', $matches[1]);
    }

    return $brewery_id;
}


/**
 * Retrieves Untappd brewery data from either the WP cache or API.
 *
 * @param string $api_root   A templated string for the Untappd API root URL
 * @param int    $brewery_id Untappd brewery ID
 *
 * @return array Array of brewery data from Untappd
 */
function EMBM_Admin_Labs_Untappd_brewery($api_root, $brewery_id)
{
    // Get brewery info from API
    $brewery_cache = 'embm_untappd_brewery_info';
    $brewery = wp_cache_get($brewery_cache);

    // Get brewery info if it's not cached
    if (false === $brewery) {
        $brewery_url = sprintf($api_root, 'brewery/info/'.$brewery_id);
        $brewery_res = EMBM_Admin_Labs_Untappd_request($brewery_url);
        $brewery = $brewery_res->response->brewery;
        wp_cache_set($brewery_cache, $brewery);
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
function EMBM_Admin_Labs_Untappd_beers($api_root, $brewery)
{
    $beer_list_cache = 'embm_untappd_beer_list';
    $beer_list = wp_cache_get($beer_list_cache);

    // Get beer list if it's not cached
    if (false === $beer_list) {
        $beer_list = [];
        $beer_offset = 0;
        $beer_count = $brewery->beer_count;
        $beers_root = sprintf($api_root, 'brewery/beer_list/'.$brewery->brewery_id) . '&offset=%d';

        while (count($beer_list) < $beer_count) {
            $beers_url = sprintf($beers_root, $beer_offset);
            $beers_res = EMBM_Admin_Labs_Untappd_request($beers_url);
            $beers = $beers_res->response->beers;

            $beer_list = array_merge($beer_list, $beers->items);
            $beer_offset += $beers->count;
        }

        wp_cache_add($beer_list_cache, $beer_list);
    }

    return $beer_list;
}


/**
 * Search for a given Untappd beer ID in an array of beers.
 *
 * @param int   $beer_id   Untappd beer ID
 * @param array $beer_list Array of Untappd beers
 *
 * @return array Array of beer data
 */
function EMBM_Admin_Labs_find($beer_id, $beer_list)
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

?>