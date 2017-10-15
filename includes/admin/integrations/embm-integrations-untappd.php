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
 * @package EMBM\Admin\Integrations\Untappd
 */

// Set constants
define('EMBM_UNTAPPD_RETURN_URL', 'options-general.php?page=embm-settings&embm-%s-%s=%d#%s');
define('EMBM_UNTAPPD_API_URL', 'https://api.untappd.com/v4/%s?access_token=');
define('EMBM_UNTAPPD_RSS_URL', 'https://untappd.com/rss/brewery/');
define('EMBM_UNTAPPD_CACHE', 'embm_untappd_cache');
define('EMBM_UNTAPPD_CACHE_TIME', 30 * MINUTE_IN_SECONDS);

// Set cache names
$GLOBALS[EMBM_UNTAPPD_CACHE] = array(
    'beer_list'      => 'embm_untappd_beer_list',
    'collaborations' => 'embm_untappd_collaborations',
    'brewery'        => 'embm_untappd_brewery_info',
    'checkins'       => 'embm_untappd_brewery_checkins_%s',
    'xml_checkins'   => 'embm_untappd_brewery_xml_checkins_%s',
    'user'           => 'embm_untappd_user_info',
    'save_errors'    => 'embm_untappd_save_errors'
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
    return __('Your Untappd API rate-limit has been reached for this hour. Please try again later.', 'em-beer-manager');
}

/**
 * Determines if a cached Untappd object needs reloading
 *
 * @param string $global_name Name of global hash key to check
 * @param string $cache_name  Name of cache object
 * @param int    $timeout     Timeout period in MS
 * @param int    $cache_id    Untappd cache object ID (Default: null)
 *
 * @return bool Whether or not cache has timed out
 */
function EMBM_Admin_Untappd_reload($global_name, $cache_name, $timeout, $cache_id = null)
{
    // Check cache name
    if (!array_key_exists($cache_name, $GLOBALS[$global_name])) {
        return false;
    }

    // Get transient name
    if (!is_null($cache_id)) {
        $transient_name = '_transient_timeout_' . sprintf($GLOBALS[$global_name][$cache_name], $cache_id);
    } else {
        $transient_name = '_transient_timeout_' . $GLOBALS[$global_name][$cache_name];
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
    $brewery_id = get_option(EMBM_UNTAPPD_BREWERY);

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
        update_option(EMBM_UNTAPPD_BREWERY, $brewery_id);
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
    $user = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['user']);

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Untappd_reload(EMBM_UNTAPPD_CACHE, 'user', HOUR_IN_SECONDS);

    // Get user info if it's not cached
    if (false === $user || $reload) {
        $user_info_url = sprintf($api_root, 'user/info');
        $res = EMBM_Admin_Untappd_request($user_info_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            if (false !== $user) {
                return $user;
            } elseif ($res['limit']) {
                return EMBM_Admin_Untappd_ratelimit();
            } else {
                return null;
            }
        }

        // Store for 24 hours (as per TOS)
        $user = $res['data']->response->user;
        set_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['user'], $user, DAY_IN_SECONDS);
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
    $brewery = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['brewery']);

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Untappd_reload(EMBM_UNTAPPD_CACHE, 'brewery', HOUR_IN_SECONDS);

    // Get brewery info if it's not cached
    if (false === $brewery || $reload) {
        $brewery_url = sprintf($api_root, 'brewery/info/'.$brewery_id);
        $res = EMBM_Admin_Untappd_request($brewery_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            if (false !== $brewery) {
                return $brewery;
            } elseif ($res['limit']) {
                return EMBM_Admin_Untappd_ratelimit();
            } else {
                return null;
            }
        }

        // Store for 24 hours (as per TOS)
        $brewery = $res['data']->response->brewery;
        set_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['brewery'], $brewery, DAY_IN_SECONDS);
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
    $checkins_cache_name = sprintf($GLOBALS[EMBM_UNTAPPD_CACHE]['checkins'], $brewery_id);
    $checkins = get_transient($checkins_cache_name);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UNTAPPD_CACHE, 'checkins', EMBM_UNTAPPD_CACHE_TIME, $brewery_id);

    // Get brewery checkins if it's not cached
    if (false == $checkins || $refresh || $reload) {
        $checkins_url = sprintf($api_root, 'brewery/checkins/'.$brewery_id);
        $res = EMBM_Admin_Untappd_request($checkins_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            if (false !== $checkins && !$refresh) {
                return $checkins;
            } elseif ($res['limit']) {
                return EMBM_Admin_Untappd_ratelimit();
            } else {
                return null;
            }
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
    $xml_cache_name = sprintf($GLOBALS[EMBM_UNTAPPD_CACHE]['xml_checkins'], $brewery_id);
    $xml_data = get_transient($xml_cache_name);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UNTAPPD_CACHE, 'xml_checkins', EMBM_UNTAPPD_CACHE_TIME, $brewery_id);

    // Get checkins info if it's not cached
    if (false === $xml_data || $refresh || $reload) {
        // Set Untappd brewery rss URL
        $feed_url = EMBM_UNTAPPD_RSS_URL.$brewery_id;

        // Extract Untappd xml feed data
        $res = EMBM_Admin_Untappd_request($feed_url, false);

        // Handle any errors or return cached data
        if (!$res['success']) {
            if (false !== $xml_data && !$refresh) {
                return $xml_data;
            } elseif ($res['limit']) {
                return EMBM_Admin_Untappd_ratelimit();
            } else {
                return null;
            }
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
 * @param string $api_root        A templated string for the Untappd API root URL
 * @param array  $brewery         Untappd brewery data
 * @param bool   $include_collabs Whether or not to include collaboration beers
 *
 * @return array Array of all beers for the given brewery
 */
function EMBM_Admin_Untappd_beers($api_root, $brewery, $include_collabs = false)
{
    // Attempt to retrieve beer list from cache
    $beer_list = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['beer_list']);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UNTAPPD_CACHE, 'beer_list', EMBM_UNTAPPD_CACHE_TIME);

    // Get beer list if it's not cached
    if (false === $beer_list || $reload) {
        $beer_list = array();
        $beer_offset = 0;
        $beer_count = $brewery->beer_count;
        $beers_root = sprintf($api_root, 'brewery/beer_list/'.$brewery->brewery_id) . '&offset=%d';

        while (count($beer_list) < $beer_count) {
            $beers_url = sprintf($beers_root, $beer_offset);
            $res = EMBM_Admin_Untappd_request($beers_url);

            // Handle any errors or return cached data
            if (!$res['success']) {
                if (false !== $beer_list) {
                    return $beer_list;
                } elseif ($res['limit']) {
                    return EMBM_Admin_Untappd_ratelimit();
                } else {
                    return null;
                }
            }

            $beers = $res['data']->response->beers;
            $beer_list = array_merge($beer_list, $beers->items);
            $beer_offset += $beers->count;
        }

        // Store for 24 hours (as per TOS)
        set_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['beer_list'], $beer_list, DAY_IN_SECONDS);
    }

    // Get any collaboration beers
    if ($include_collabs) {
        $collabs_list = EMBM_Admin_Untappd_Beers_collaborations($api_root, $brewery);

        // Merge the two arrays
        $beer_list = array_merge($beer_list, $collabs_list);
    }

    return $beer_list;
}

/**
 * Retrieves Untappd brewery collaboration beer data from either the WP cache or API.
 *
 * @param string $api_root A templated string for the Untappd API root URL
 * @param array  $brewery  Untappd brewery data
 *
 * @return array Array of all beers for the given brewery
 */
function EMBM_Admin_Untappd_Beers_collaborations($api_root, $brewery)
{
    // Attempt to retrieve beer list from cache
    $collabs_list = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['collaborations']);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UNTAPPD_CACHE, 'collaborations', EMBM_UNTAPPD_CACHE_TIME);

    // Get beer list if it's not cached
    if (false === $collabs_list || $reload) {
        $collabs_list = array();
        $collabs_offset = 0;
        $more_collabs = true;
        $collabs_root = sprintf($api_root, 'brewery/collaborations/'.$brewery->brewery_id) . '&offset=%d';

        while (true == $more_collabs) {
            $collabs_url = sprintf($collabs_root, $collabs_offset);
            $res = EMBM_Admin_Untappd_request($collabs_url);

            // Handle any errors or return cached data
            if (!$res['success']) {
                if (false !== $collabs_list) {
                    return $collabs_list;
                } elseif ($res['limit']) {
                    return EMBM_Admin_Untappd_ratelimit();
                } else {
                    return null;
                }
            }

            // Get beer data from response
            $collabs = $res['data']->response->beers;

            // Check count to determine if there are more
            if ($collabs->count > 0) {
                $collabs_list = array_merge($collabs_list, $collabs->items);
                if ($collabs->count < 25) {  // 25 is the current max returned by API
                    $more_collabs = false;
                } else {
                    $collabs_offset += $collabs->count;
                }
            } else {
                $more_collabs = false;
            }
        }

        // Add collaboration flag to beers
        foreach ($collabs_list as $collab) {
            $collab->is_collab = true;
        }

        // Store for 24 hours (as per TOS)
        set_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['collaborations'], $collabs_list, DAY_IN_SECONDS);
    }

    return $collabs_list;
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
    $beer_data = null;
    $beer_cache = null;
    $reload = false;
    $expired = false;
    $now = time();
    $cache_time = EMBM_UNTAPPD_CACHE_TIME;
    $store_time = DAY_IN_SECONDS;

    // Attempt to retrieve beer data from cache
    $untappd_data = get_post_meta($post_id, EMBM_BEER_META_UNTAPPD, true);

    // Check for data
    if (!is_null($untappd_data) && is_array($untappd_data)) {
        $beer = $untappd_data['beer'];
        $beer_cache = $untappd_data['cached'];
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
                $untappd_data = null;
                update_post_meta($post_id, EMBM_BEER_META_UNTAPPD, $untappd_data);
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
        $untappd_data = array(
            'beer'      => $beer_res,
            'cached'    => $now
        );

        // Store for 6 hours
        update_post_meta($post_id, EMBM_BEER_META_UNTAPPD, $untappd_data);
    } elseif ($expired) {
        // Remove data if it has expired (as per TOS)
        $untappd_data = null;
        update_post_meta($post_id, EMBM_BEER_META_UNTAPPD, $untappd_data);
    }

    return $untappd_data;
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
        return $res['limit'] ? EMBM_Admin_Untappd_ratelimit() : 2;
    }

    // Check for success
    if (property_exists($res['data'], 'response') && property_exists($res['data']->response, 'beer')) {
        return $res['data']->response->beer;
    } else {
        return 3;
    }
}

/**
 * Flushes the cached labs data
 *
 * @param string $global_name Name of global hash key to use
 * @param string $key         Optional. Name of cached item to flush.
 *
 * @return void
 */
function EMBM_Admin_Untappd_flush($global_name, $key = null)
{
    // Check for specified key
    if (!is_null($key)) {
        delete_transient($GLOBALS[$global_name][$key]);
    } else {
        // Iteratively remove items
        foreach ($GLOBALS[$global_name] as $name => $value) {
            // Check for ID values
            preg_match('/^(.*)_%s$/', $value, $transient_name);

            // Remove all ID-specific transients
            if ($transient_name) {
                global $wpdb;

                // Run DELETE query
                $wpdb->query(
                    "
                    DELETE
                    FROM $wpdb->options
                    WHERE option_name LIKE '%$transient_name[1]%'
                    "
                );
            } else {
                // Delete transient
                delete_transient($value);
            }
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
 * Search for a given Untappd brewery ID in an array of breweries
 *
 * @param int   $brewery_id  Untappd brewery ID
 * @param array $collab_list Array of Untappd breweries
 *
 * @return array Array of brewery data
 */
function EMBM_Admin_Untappd_Find_collab($brewery_id, $collab_list)
{
    // Iteratively search breweries
    foreach ($collab_list->items as $collab) {
        $brewery = $collab->brewery;
        if ($brewery->brewery_id == $brewery_id) {
            return $brewery;
        }
    }

    // Return null if not found
    return null;
}

/**
 * Retrieve Untappd ID for a given beer
 *
 * @param object $beer WP beer post object
 *
 * @return int
 */
function EMBM_Admin_Untappd_Find_id($beer)
{
    // Set meta key
    $meta_key = EMBM_BEER_META;

    // Return null if ID does not exist
    if (!$beer->$meta_key
        || !array_key_exists('untappd_id', $beer->$meta_key)
        || !$beer->$meta_key['untappd_id']
    ) {
        return null;
    }

    // Return Untappd ID
    return $beer->$meta_key['untappd_id'];
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

    // Set up like statement for query
    $like_query = '%"'.$beer_id.'"%';

    // Remove individual beer Untappd data
    return $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT post_id
            FROM $wpdb->postmeta
            WHERE meta_key = %s
                AND meta_value LIKE %s
            ",
            EMBM_BEER_META,
            $like_query
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
    // Set initial collaboration status
    $beer->is_collab = false;

    // Check for any collaborations
    if (property_exists($beer, 'collaborations_with')) {
        $beer->is_collab = !is_null(
            EMBM_Admin_Untappd_Find_collab($brewery_id, $beer->collaborations_with)
        );
    }

    // Check that beer is owned by brewery, if needed
    if ($check) {
        // Compare beer's brewery ID to user's brewery ID
        if ($beer->brewery->brewery_id != intval($brewery_id) && !$beer->is_collab) {
            return get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-error=3#untappd');
        }
    }

    // Set beer slug
    $beer_slug = sanitize_title($beer->beer_name);

    // Set up duplicate check args
    $dup_args = array(
        'name'           => $beer_slug,
        'post_type'      => EMBM_BEER,
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

    // Set up post array
    $new_beer_post = array(
        'post_author'   => get_current_user_id(),
        'post_title'    => $beer->beer_name,
        'post_name'     => $beer_slug,
        'post_content'  => $beer->beer_description,
        'post_date'     => $beer_date,
        'post_status'   => 'publish',
        'post_type'     => EMBM_BEER,
        'tax_input'     => array(
            EMBM_STYLE  => $beer->beer_style
        ),
        'meta_input'    => array(
            EMBM_BEER_META         => array(
                'abv'              => $beer->beer_abv,
                'ibu'              => $beer->beer_ibu,
                'untappd_id'       => $beer->bid,
                'reviews_count'    => 5
            ),
            EMBM_BEER_META_UNTAPPD => array(
                'beer'             => $beer,
                'cached'           => time()
            )
        )
    );

    // Insert post
    $post_id = wp_insert_post($new_beer_post, true);

    // Add post image
    if (property_exists($beer, 'beer_label_hd')) {
        EMBM_Admin_Untappd_Import_image($post_id, $beer->beer_label_hd, $beer->beer_slug);
    }

    return null;
}

/**
 * Upload and set beer featured image
 *
 * @param int    $post_id   The beer post ID
 * @param string $image_url The URL of the image to upload
 * @param string $slug      The slug name of the beer post
 *
 * @return void
 */
function EMBM_Admin_Untappd_Import_image($post_id, $image_url, $slug)
{
    // Set beer slug
    $img_slug = sanitize_title($slug);

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
    if (!$image_url || $image_url == '') {
        return;
    }

    // Get WP upload dir info
    $upload_dir = wp_upload_dir();

    // Get image type from URL
    $img_parts = explode('.', $image_url);
    $img_type = end($img_parts);

    // Set file save path
    $filename = $upload_dir['path'] . '/' . $slug . '.' . $img_type;

    // Get image file contents
    $img_res = EMBM_Admin_Untappd_request($image_url, false);
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
        'post_title'     => $slug,
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
 * Update post from Untappd
 *
 * @param int   $post_id        WP post ID
 * @param array $beer           Untappd beer data
 * @param bool  $delete_missing Whether or not to remove missing data
 *
 * @return void
 */
function EMBM_Admin_Untappd_sync($post_id, $beer, $delete_missing = false)
{
    // Set initial collaboration status
    $beer->is_collab = false;

    // Get brewery ID
    $brewery_id = get_option(EMBM_UNTAPPD_BREWERY);

    // Check for any collaborations
    if (property_exists($beer, 'collaborations_with')) {
        $beer->is_collab = !is_null(
            EMBM_Admin_Untappd_Find_collab($brewery_id, $beer->collaborations_with)
        );
    }

    // Unset beer style taxonomy
    wp_delete_object_term_relationships($post_id, EMBM_STYLE);

    // Set up post array
    $updated_beer_post = array(
        'ID'            => $post_id,
        'post_title'    => $beer->beer_name,
        'post_content'  => $beer->beer_description,
        'tax_input'     => array(
            EMBM_STYLE  => $beer->beer_style
        )
    );

    // Update post
    $response = wp_update_post($updated_beer_post, true);

    // Check for success
    if (is_wp_error($response)) {
        return 'error';
    }

    // Get current beer post meta
    $post_beer_meta = get_post_meta($post_id, EMBM_BEER_META, true);

    // Update beer post meta values
    $post_beer_meta['abv'] = $beer->beer_abv;
    $post_beer_meta['ibu'] = $beer->beer_ibu;
    $post_beer_meta['untappd_id'] = $beer->bid;

    // Save new beer post meta
    update_post_meta($post_id, EMBM_BEER_META, $post_beer_meta);

    // Update untappd post meta
    update_post_meta(
        $post_id,
        EMBM_BEER_META_UNTAPPD,
        array(
            'beer'   => $beer,
            'cached' => time()
        )
    );

    // Update post image
    if (property_exists($beer, 'beer_label_hd')) {
        // Get image ID
        $image_id = get_post_thumbnail_id($post_id);

        // Continue if we got an image ID
        if (!is_null($image_id)) {
            // Delete image
            wp_delete_attachment($image_id, true);

            // Upload more recent image
            EMBM_Admin_Untappd_Import_image($post_id, $beer->beer_label_hd, $beer->beer_slug);
        }
    }

    return null;
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
