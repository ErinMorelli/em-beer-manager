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
 * @package EMBM\Admin\Utfb
 */

// Set constants
define('EMBM_UTFB_RETURN_URL', 'options-general.php?page=embm-settings&embm-utfb-%s=%d#%s');
define('EMBM_UTFB_API_URL', 'https://business.untappd.com/api/v1/%s');

// Set cache names
$GLOBALS['EMBM_UTFB_CACHE'] = array(
    'account'   => 'embm_utfb_account',
    'locations' => 'embm_utfb_locations',
    'menus'     => 'embm_utfb_menus_%s',
    'sections'  => 'embm_utfb_section_%s',
    'beers'     => 'embm_utfb_beers_%s'
);

// UTFB resource function mapping
$GLOBALS['EMBM_UTFB_RESOURCE_MAP'] = array(
    'location' => array(
        'single' => 'EMBM_Admin_Utfb_location',
        'plural' => 'EMBM_Admin_Utfb_locations'
    ),
    'menu'     => array(
        'single' => 'EMBM_Admin_Utfb_menu',
        'plural' => 'EMBM_Admin_Utfb_menus'
    ),
    'section'  => array(
        'single' => 'EMBM_Admin_Utfb_section',
        'plural' => 'EMBM_Admin_Utfb_sections'
    ),
    'beer'     => array(
        'single' => 'EMBM_Admin_Utfb_beer',
        'plural' => 'EMBM_Admin_Utfb_beers'
    )
);

/**
 * Makes a request to UTFB API and intercept any errors.
 *
 * @param array  $auth        API authentication credentials
 * @param string $request_url URL for an UTFB API endpoint
 * @param bool   $decode      Whether or not to decode JSON (default: true)
 *
 * @return array Decoded JSON API response or raw JSON string
 */
function EMBM_Admin_Utfb_request($auth, $request_url, $decode = true)
{
    // Set up response object
    $response = array(
        'success'   => false,
        'data'      => null,
        'errors'    => null
    );

    // Open cURL connection
    $ch = curl_init($request_url);

    // Set authorization
    curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $auth['email'], $auth['apikey']));

    // Set up cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    // Make GET request to API
    $response['data'] = curl_exec($ch);
    $response['errors'] = curl_error($ch);

    // Close cURL connection
    curl_close($ch);

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
 * Determines if a cached UTFB object needs reloading
 *
 * @param string $cache_name Name of cache object
 * @param int    $timeout    Timeout period in MS
 * @param int    $cache_id   UTFB cache object ID (Default: null)
 *
 * @return bool Whether or not cache has timed out
 */
function EMBM_Admin_Utfb_reload($cache_name, $timeout, $cache_id = null)
{
    // Check cache name
    if (!array_key_exists($cache_name, $GLOBALS['EMBM_UTFB_CACHE'])) {
        return false;
    }

    // Get transient name
    if (!is_null($cache_id)) {
        $transient_name = '_transient_timeout_' . sprintf($GLOBALS['EMBM_UTFB_CACHE'][$cache_name], $cache_id);
    } else {
        $transient_name = '_transient_timeout_' . $GLOBALS['EMBM_UTFB_CACHE'][$cache_name];
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
 * Checks whether or not UTFB API credentials are valid
 *
 * @param array $auth API authentication credentials
 *
 * @return book If the credentials provided are valid
 */
function EMBM_Admin_Utfb_validate($auth)
{
    // Attempt to get account data
    $account = EMBM_Admin_Utfb_account($auth);

    // Check for success
    return !is_null($account);
}

/**
 * Retrieves UTFB user account data from either the WP cache or API.
 *
 * @param array $auth    API authentication credentials
 * @param bool  $refresh Forces a refresh of API data (Default: false)
 *
 * @return array Array of user account data from UTFB
 */
function EMBM_Admin_Utfb_account($auth, $refresh = false)
{
    // Attempt to retrieve account info from cache
    $account = get_transient($GLOBALS['EMBM_UTFB_CACHE']['account']);

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Utfb_reload('account', HOUR_IN_SECONDS);

    // Get account info if it's not cached
    if (false === $account || $reload || $refresh) {
        $account_url = sprintf(EMBM_UTFB_API_URL, 'current_user');
        $res = EMBM_Admin_Utfb_request($auth, $account_url);

        // Handle any errors
        if (!$res['success']) {
            return null;
        }

        // Store for 24 hours (as per TOS)
        $account = $res['data']->current_user;
        set_transient($GLOBALS['EMBM_UTFB_CACHE']['account'], $account, DAY_IN_SECONDS);
    }

    return $account;
}

/**
 * Retrieves UTFB locations from either the WP cache or API.
 *
 * @param array $auth    API authentication credentials
 * @param bool  $refresh Forces a refresh of API data (Default: false)
 *
 * @return array Array of locations from UTFB
 */
function EMBM_Admin_Utfb_locations($auth, $refresh = false)
{
    // Attempt to retrieve locations from cache
    $locations = get_transient($GLOBALS['EMBM_UTFB_CACHE']['locations']);

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Utfb_reload('locations', HOUR_IN_SECONDS);

    // Get locations if not cached
    if (false === $locations || $reload || $refresh) {
        $locations_url = sprintf(EMBM_UTFB_API_URL, 'locations');
        $res = EMBM_Admin_Utfb_request($auth, $locations_url);

        // Handle any errors
        if (!$res['success']) {
            return null;
        }

        // Store for 24 hours (as per TOS)
        $locations = $res['data']->locations;
        set_transient($GLOBALS['EMBM_UTFB_CACHE']['locations'], $locations, DAY_IN_SECONDS);
    }

    return $locations;
}

/**
 * Retrieves a UTFB location from either the WP cache or API.
 *
 * @param array $auth        API authentication credentials
 * @param int   $location_id UTFB location ID
 * @param bool  $refresh     Forces a refresh of API data (Default: false)
 *
 * @return array Array of location data from UTFB
 */
function EMBM_Admin_Utfb_location($auth, $location_id, $refresh = false)
{
    // Get all locations
    $locations = EMBM_Admin_Utfb_locations($auth, $location_id, $refresh);

    // Find location
    return EMBM_Admin_Utfb_find($locations, $location_id);
}

/**
 * Retrieves UTFB menus from either the WP cache or API.
 *
 * @param array $auth        API authentication credentials
 * @param int   $location_id UTFB location ID
 * @param bool  $refresh     Forces a refresh of API data (Default: false)
 *
 * @return array Array of menus from UTFB
 */
function EMBM_Admin_Utfb_menus($auth, $location_id, $refresh = false)
{
    // Attempt to retrieve menu from cache
    $menus_cache_name = sprintf($GLOBALS['EMBM_UTFB_CACHE']['menus'], $location_id);
    $menus = get_transient($menus_cache_name);

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Utfb_reload('menus', 15 * MINUTE_IN_SECONDS, $location_id);

    // Get menus if not cached
    if (false === $menus || $reload || $refresh) {
        $menus_url = sprintf(EMBM_UTFB_API_URL, 'locations/'.$location_id.'/menus');
        $res = EMBM_Admin_Utfb_request($auth, $menus_url);

        // Handle any errors
        if (!$res['success']) {
            return null;
        }

        // Store for 24 hours (as per TOS)
        $menus = $res['data']->menus;
        set_transient($menus_cache_name, $menus, DAY_IN_SECONDS);
    }

    return $menus;
}

/**
 * Retrieves a UTFB menu from either the WP cache or API.
 *
 * @param array $auth        API authentication credentials
 * @param int   $location_id UTFB location ID
 * @param int   $menu_id     UTFB menu ID
 * @param bool  $refresh     Forces a refresh of API data (Default: false)
 *
 * @return array Array of menu data from UTFB
 */
function EMBM_Admin_Utfb_menu($auth, $location_id, $menu_id, $refresh = false)
{
    // Get all menus
    $menus = EMBM_Admin_Utfb_menus($auth, $location_id, $refresh);

    // Find menu
    return EMBM_Admin_Utfb_find($menus, $menu_id);
}

/**
 * Retrieves UTFB sections from either the WP cache or API.
 *
 * @param array $auth    API authentication credentials
 * @param int   $menu_id UTFB menu ID
 * @param bool  $refresh Forces a refresh of API data (Default: false)
 *
 * @return array Array of sections from UTFB
 */
function EMBM_Admin_Utfb_sections($auth, $menu_id, $refresh = false)
{
    // Attempt to retrieve sections from cache
    $sections_cache_name = sprintf($GLOBALS['EMBM_UTFB_CACHE']['sections'], $menu_id);
    $sections = get_transient($sections_cache_name);

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Utfb_reload('sections', 15 * MINUTE_IN_SECONDS, $menu_id);

    // Get sections if not cached
    if (false === $sections || $reload || $refresh) {
        $sections_url = sprintf(EMBM_UTFB_API_URL, 'menus/'.$menu_id.'/sections');
        $res = EMBM_Admin_Utfb_request($auth, $sections_url);

        // Handle any errors
        if (!$res['success']) {
            return null;
        }

        // Store for 24 hours (as per TOS)
        $sections = $res['data']->sections;
        set_transient($sections_cache_name, $sections, DAY_IN_SECONDS);
    }

    return $sections;
}

/**
 * Retrieves a UTFB section from either the WP cache or API.
 *
 * @param array $auth       API authentication credentials
 * @param int   $menu_id    UTFB menu ID
 * @param int   $section_id UTFB section ID
 * @param bool  $refresh    Forces a refresh of API data (Default: false)
 *
 * @return array Array of section data from UTFB
 */
function EMBM_Admin_Utfb_section($auth, $menu_id, $section_id, $refresh = false)
{
    // Get all sections
    $sections = EMBM_Admin_Utfb_sections($auth, $menu_id, $refresh);

    // Find section
    return EMBM_Admin_Utfb_find($sections, $section_id);
}

/**
 * Retrieves UTFB beers from either the WP cache or API.
 *
 * @param array $auth       API authentication credentials
 * @param int   $section_id UTFB section ID
 * @param bool  $refresh    Forces a refresh of API data (Default: false)
 *
 * @return array Array of beers from UTFB
 */
function EMBM_Admin_Utfb_beers($auth, $section_id, $refresh = false)
{
    // Attempt to retrieve beers from cache
    $beers_cache_name = sprintf($GLOBALS['EMBM_UTFB_CACHE']['beers'], $section_id);
    $beers = get_transient($beers_cache_name);

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Utfb_reload('beers', 15 * MINUTE_IN_SECONDS, $section_id);

    // Get beers if not cached
    if (false === $beers || $reload || $refresh) {
        $beers_url = sprintf(EMBM_UTFB_API_URL, 'sections/'.$section_id.'/items');
        $res = EMBM_Admin_Utfb_request($auth, $beers_url);

        // Handle any errors
        if (!$res['success']) {
            return null;
        }

        // Store for 24 hours (as per TOS)
        $beers = $res['data']->items;
        set_transient($beers_cache_name, $beers, DAY_IN_SECONDS);
    }

    return $beers;
}

/**
 * Retrieves a UTFB beer from either the WP cache or API.
 *
 * @param array $auth       API authentication credentials
 * @param int   $section_id UTFB section ID
 * @param int   $beer_id    UTFB beer ID
 * @param bool  $refresh    Forces a refresh of API data (Default: false)
 *
 * @return array Array of beer data from UTFB
 */
function EMBM_Admin_Utfb_beer($auth, $section_id, $beer_id, $refresh = false)
{
    // Get all beers
    $beers = EMBM_Admin_Utfb_beers($auth, $section_id, $refresh);

    // Find beer
    return EMBM_Admin_Utfb_find($beers, $beer_id);
}

/**
 * Retrieves a UTFB beer item from either the WP cache or API.
 *
 * @param array $auth    API authentication credentials
 * @param int   $beer_id UTFB beer ID
 *
 * @return array Array of beer item data from UTFB
 */
function EMBM_Admin_Utfb_Beer_item($auth, $beer_id)
{
    $beer_url = sprintf(EMBM_UTFB_API_URL, 'items/'.$beer_id);
    $res = EMBM_Admin_Utfb_request($auth, $beer_url);

    // Handle any errors
    if (!$res['success']) {
        return null;
    }

    return $res['data']->item;
}

/**
 * Search for a given UTFB object by ID.
 *
 * @param array $objects   Array of UTFB objects
 * @param int   $object_id UTFB object ID
 *
 * @return array Array of found object data
 */
function EMBM_Admin_Utfb_find($objects, $object_id)
{
    // Iteratively search objects
    foreach ($objects as $object) {
        if ($object->id == $object_id) {
            return $object;
        }
    }

    // Return null if not found
    return null;
}

/**
 * Retrieve a UTFB resource from the API
 *
 * @param array  $auth          API authentication credentials
 * @param string $resource_name Name of UTFB resource
 * @param int    $resource_id   UTFB ID of resource
 * @param int    $parent_id     UTFB ID of parent resource
 * @param string $call_type     Either 'single' or 'plural'
 *
 * @return array Results of UTFB API call
 */
function EMBM_Admin_Utfb_resource($auth, $resource_name, $resource_id, $parent_id, $call_type)
{
    // Get resource map
    $resource_map = $GLOBALS['EMBM_UTFB_RESOURCE_MAP'];

    // Get resource function
    $resource_func = $resource_map[$resource_name][$call_type];

    // Build params
    $params = array($auth, $parent_id);

    // Handle single calls
    if ($call_type == 'single') {
        // Add additional ID
        array_push($params, $resource_id);

        // Return result as array
        return array(call_user_func_array($resource_func, $params));
    } else {
        // Return plural call
        return call_user_func_array($resource_func, $params);
    }
}


/**
 * Import resources from UTFB
 *
 * @param array $resources Array of UTFB resources to import
 *
 * @return null if successful, else string of redirect URL
 */
function EMBM_Admin_Utfb_import($resources)
{
    $response = 0;

    // Iterate over resources
    foreach ($resources as $resource => $resource_data) {
        // Import a given type
        switch ($resource) {

        // Import menus as categories
        case 'menu':
            // Iterate over menus
            foreach ($resource_data as $menu) {
                // Check if menu exists
                $exists = term_exists($menu->name, 'embm_menu');

                // Store term ID and continue
                if ($exists) {
                    $menu->term = $exists;
                    continue;
                }

                // Add term
                $term = wp_insert_term(
                    $menu->name,
                    'embm_menu',
                    array(
                        'description' => $menu->description
                    )
                );

                // Store ID
                $menu->term = $term;
            }
            break;

        // Import sections as sub-categories
        case 'section':
            // Iterate over sections
            foreach ($resource_data as $section) {
                // Get menu from ID
                $menu = EMBM_Admin_Utfb_find($resources['menu'], $section->menu_id);

                // Check if section  exists
                $exists = term_exists($section->name, 'embm_menu', $menu->term['term_id']);

                // Store term ID and continue
                if ($exists) {
                    $section->term = $exists;
                    continue;
                }

                // Add term
                $term = wp_insert_term(
                    $section->name,
                    'embm_menu',
                    array(
                        'description' => $section->description,
                        'parent'      => $menu->term_id
                    )
                );

                // Store ID
                $section->term = $term;
            }
            break;

        // Import beers
        case 'beer':
            // Set error tracker
            $has_errors = false;

            // Iterate over beers
            foreach ($resource_data as $beer) {
                // Get section from ID
                $section = EMBM_Admin_Utfb_find($resources['section'], $beer->section_id);
                $menu = EMBM_Admin_Utfb_find($resources['menu'], $section->menu_id);

                // Import beer
                $res = EMBM_Admin_Utfb_Import_beer($beer, $section->term, $menu->term);

                // Check response
                if (!is_null($res)) {
                    $has_errors = true;
                }
            }

            // Check for errors
            if ($has_errors) {
                $response = 3;
            }
            break;

        // Fallback
        default:
            $response = 2;
        }
    }

    return $response;
}

/**
 * Insert post from UTFB
 *
 * @param array $beer         UTFB beer data
 * @param int   $section_term UTFB section WP taxonomy term data
 * @param int   $menu_term    UTFB menu WP taxonomy term data
 *
 * @return void
 */
function EMBM_Admin_Utfb_Import_beer($beer, $section_term, $menu_term)
{
    // Set beer slug
    $beer_slug = sanitize_title($beer->name);

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

    // Set up data for storage
    $beer_data = array(
        'beer'      => $beer,
        'cached'    => time()
    );

    // Find beer style
    $style = term_exists($beer->style, 'embm_style');

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Get beer data
    $api_root = EMBM_UNTAPPD_API_URL.$token;
    $untappd_beer_data = EMBM_Admin_Untappd_Beer_get($api_root, $beer_id);

    // Set up post array
    $new_beer_post = array(
        'post_author'   => get_current_user_id(),
        'post_title'    => $beer->name,
        'post_name'     => $beer_slug,
        'post_content'  => $beer->description,
        'post_date'     => $beer_date,
        'post_status'   => 'publish',
        'post_type'     => 'embm_beer',
        'tax_input'     => array(
            'embm_style'   => array(
                $beer->style
            ),
            'embm_menu'    => array(
                $menu_term['term_taxonomy_id'],
                $section_term['term_taxonomy_id']
            )
        ),
        'meta_input'    => array(
            'embm_abv'              => intval($beer->abv),
            'embm_ibu'              => intval($beer->ibu),
            'embm_untappd'          => intval($beer->untappd_id),
            'embm_utfb'             => intval($beer->id),
            'embm_utfb_data'        => $beer,
            'embm_reviews_count'    => 5,
        )
    );

    // Insert post
    $post_id = wp_insert_post($new_beer_post, true);

    // Add post image
    if (property_exists($beer, 'label_image')) {
        EMBM_Admin_Untappd_Import_image($post_id, $beer->label_image, $beer->untappd_beer_slug);
    }

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Get beer data
    $api_root = EMBM_UNTAPPD_API_URL.$token;
    EMBM_Admin_Untappd_beer($api_root, $beer->untappd_id, $post_id, true);

    return null;
}

/**
 * Flushes the cached UTFB data
 *
 * @param string $key Optional. Name of cached item to flush.
 *
 * @return void
 */
function EMBM_Admin_Utfb_flush($key = null)
{
    // Check for specified key
    if (!is_null($key)) {
        delete_transient($GLOBALS['EMBM_UTFB_CACHE'][$key]);
    } else {
        // Iteratively remove items
        foreach ($GLOBALS['EMBM_UTFB_CACHE'] as $name => $value) {
            delete_transient($value);
        }
    }
}
