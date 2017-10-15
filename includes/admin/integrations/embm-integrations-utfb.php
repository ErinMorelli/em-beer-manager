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
 * @package EMBM\Admin\Integrations\Utfb
 */

// Set constants
define('EMBM_UTFB_RETURN_URL', 'options-general.php?page=embm-settings&embm-utfb-%s=%d#%s');
define('EMBM_UTFB_API_URL', 'https://business.untappd.com/api/v1/%s');
define('EMBM_UTFB_CACHE', 'embm_utfb_cache');
define('EMBM_UTFB_CACHE_TIME', 30 * MINUTE_IN_SECONDS);

// Set cache names
$GLOBALS[EMBM_UTFB_CACHE] = array(
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
    $account = get_transient($GLOBALS[EMBM_UTFB_CACHE]['account']);

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'account', HOUR_IN_SECONDS);

    // Get account info if it's not cached
    if (false === $account || $reload || $refresh) {
        $account_url = sprintf(EMBM_UTFB_API_URL, 'current_user');
        $res = EMBM_Admin_Utfb_request($auth, $account_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            return (false !== $account && !$refresh) ? $account : null;
        }

        // Store for 24 hours (as per TOS)
        $account = $res['data']->current_user;
        set_transient($GLOBALS[EMBM_UTFB_CACHE]['account'], $account, DAY_IN_SECONDS);
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
    $locations = get_transient($GLOBALS[EMBM_UTFB_CACHE]['locations']);

    // Check if we should attempt a reload (every hour)
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'locations', HOUR_IN_SECONDS);

    // Get locations if not cached
    if (false === $locations || $reload || $refresh) {
        $locations_url = sprintf(EMBM_UTFB_API_URL, 'locations');
        $res = EMBM_Admin_Utfb_request($auth, $locations_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            return (false !== $locations && !$refresh) ? $locations : null;
        }

        // Store for 24 hours (as per TOS)
        $locations = $res['data']->locations;
        set_transient($GLOBALS[EMBM_UTFB_CACHE]['locations'], $locations, DAY_IN_SECONDS);
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
    $menus_cache_name = sprintf($GLOBALS[EMBM_UTFB_CACHE]['menus'], $location_id);
    $menus = get_transient($menus_cache_name);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'menus', EMBM_UTFB_CACHE_TIME, $location_id);

    // Get menus if not cached
    if (false === $menus || $reload || $refresh) {
        $menus_url = sprintf(EMBM_UTFB_API_URL, 'locations/'.$location_id.'/menus');
        $res = EMBM_Admin_Utfb_request($auth, $menus_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            return (false !== $menus && !$refresh) ? $menus : null;
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
 * Retrieves a UTFB menu from either the WP cache or API.
 *
 * @param array $auth       API authentication credentials
 * @param int   $term_id    WP term ID for the Menu/Section
 * @param int   $menu_id    UTFB menu ID
 * @param bool  $is_section Whether or not to get a Menu or Section
 * @param bool  $refresh    Forces a refresh of API data (Default: false)
 *
 * @return array Array of menu data from UTFB
 */
function EMBM_Admin_Utfb_Menu_term($auth, $term_id, $menu_id, $is_section = false, $refresh = false)
{
    // Set vars
    $type = $is_section ? 'section' : 'menu';
    $menu = null;
    $menu_cache = null;
    $reload = false;
    $expired = false;
    $now = time();
    $cache_time = EMBM_UTFB_CACHE_TIME;
    $store_time = DAY_IN_SECONDS;

    // Attempt to retrieve menu data from cache
    $utfb_data = get_term_meta($term_id, EMBM_BEER_META_UTFB, true);

    // Make sure we actually got data
    if (null == $utfb_data && is_array($utfb_data)) {
        $menu = array_key_exists($type, $utfb_data) ? $utfb_data[$type] : null;
        $menu_cache = array_key_exists('cached', $utfb_data) ? $utfb_data['cached'] : null;
    }

    // Check cached time
    if (!is_null($menu_cache)) {
        // Get time delta
        $delta = $now - $menu_cache;

        // Check for expired cache
        $reload = ($delta >= $cache_time);

        // If cache is over a day, remove it (as per TOS)
        $expired = ($delta >= $store_time);
    }

    // Check for menu data
    if (!is_object($menu) || false == $menu) {
        $refresh = true;
    }

    // Get fresh menu data from API
    if ($refresh || $reload) {
        $menu_url = sprintf(EMBM_UTFB_API_URL, $type.'s/'.$menu_id);
        $res = EMBM_Admin_Utfb_request($auth, $menu_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            return (false !== $menu && !$refresh) ? $menu : null;
        }

        // Store for 24 hours (as per TOS)
        $menu = $res['data']->$type;

        // Set up data for storage
        $utfb_data = array(
            $type       => $menu,
            'cached'    => $now
        );

        // Store for 1 day
        update_term_meta($term_id, EMBM_BEER_META_UTFB, $utfb_data);
    } elseif ($expired) {
        // Remove data if it has expired (as per TOS)
        $utfb_data = null;
        update_term_meta($term_id, EMBM_BEER_META_UTFB, $utfb_data);
    }

    return $menu;
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
    $sections_cache_name = sprintf($GLOBALS[EMBM_UTFB_CACHE]['sections'], $menu_id);
    $sections = get_transient($sections_cache_name);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'sections', EMBM_UTFB_CACHE_TIME, $menu_id);

    // Get sections if not cached
    if (false === $sections || $reload || $refresh) {
        $sections_url = sprintf(EMBM_UTFB_API_URL, 'menus/'.$menu_id.'/sections');
        $res = EMBM_Admin_Utfb_request($auth, $sections_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            return (false !== $sections && !$refresh) ? $sections : null;
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
    $beers_cache_name = sprintf($GLOBALS[EMBM_UTFB_CACHE]['beers'], $section_id);
    $beers = get_transient($beers_cache_name);

    // Check if we should attempt a reload
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'beers', 15 * MINUTE_IN_SECONDS, $section_id);

    // Get beers if not cached
    if (false === $beers || $reload || $refresh) {
        $beers_url = sprintf(EMBM_UTFB_API_URL, 'sections/'.$section_id.'/items');
        $res = EMBM_Admin_Utfb_request($auth, $beers_url);

        // Handle any errors or return cached data
        if (!$res['success']) {
            return (false !== $beers && !$refresh) ? $beers : null;
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
 * Search for a given UTFB object by Name.
 *
 * @param array  $objects     Array of UTFB objects
 * @param string $object_name UTFB object name
 *
 * @return array Array of found object data
 */
function EMBM_Admin_Utfb_Find_name($objects, $object_name)
{
    $found = array();

    // Iteratively search objects
    foreach ($objects as $object) {
        if (strtolower(trim($object->name)) == strtolower(trim($object_name))) {
            array_push($found, $object);
        }
    }

    // Return found items
    return $found;
}

/**
 * Retrieves or adds a Menu taxonomy item
 *
 * @param object $menu   Menu data from UTFB
 * @param object $parent Parent menu item data from UTFB
 *
 * @return array Array of found object data
 */
function EMBM_Admin_Utfb_taxonomy($menu, $parent = null)
{
    // Set up term data
    $term_data = array('description' => $menu->description);

    // Check for parent
    if (!is_null($parent)) {
        // Check if tax exists or create it
        $parent_term = EMBM_Admin_Utfb_taxonomy($parent);

        // Update term data
        $term_data['parent'] = $parent_term['term_id'];

        // Check if section exists
        $exists = term_exists($menu->name, EMBM_MENU, $parent_term['term_id']);
    } else {
        // Check if tax exists
        $exists = term_exists($menu->name, EMBM_MENU);
    }

    // Set as term if it exists & update data
    if ($exists) {
        $term = $exists;
        wp_update_term($term['term_id'], EMBM_MENU, $term_data);
    } else {
        // Remove parent, if set
        if(array_key_exists('parent', $term_data)) {
            unset($term_data['parent']);
        }

        // Add term
        $term = wp_insert_term($menu->name, EMBM_MENU, $term_data);

        // Check for WP_Error
        if (is_wp_error($term)) {
            error_log('[EMBM] Error "'.$term->get_error_message().'" for menu "'.$menu->name.'"');
            return null;
        }
    }

    // Set term meta
    $term_meta = array(
        'utfb_id'      => $menu->id,
        'sync_exclude' => null
    );

    // Add term meta
    update_term_meta($term['term_id'], EMBM_BEER_META, $term_meta);

    // Return new term
    return $term;
}

/**
 * Retrieve a UTFB resource from the API
 *
 * @param array  $auth          API authentication credentials
 * @param string $resource_name Name of UTFB resource
 * @param int    $resource_id   UTFB ID of resource
 * @param int    $parent_id     UTFB ID of parent resource
 * @param string $call_type     Either 'single' or 'plural'
 * @param bool   $refresh       Forces a refresh of API data (Default: false)
 *
 * @return array Results of UTFB API call
 */
function EMBM_Admin_Utfb_resource($auth, $resource_name, $resource_id, $parent_id, $call_type, $refresh = false)
{
    // Get resource map
    $resource_map = $GLOBALS['EMBM_UTFB_RESOURCE_MAP'];

    // Get resource function
    $resource_func = $resource_map[$resource_name][$call_type];

    // Build params
    $params = array($auth, $parent_id);

    // Handle single calls
    if ($call_type == 'single') {
        // Add additional data
        array_push($params, $resource_id);
        array_push($params, $refresh);

        // Return result as array
        return array(call_user_func_array($resource_func, $params));
    } else {
        // Add refresh data
        array_push($params, $refresh);

        // Return plural call
        return call_user_func_array($resource_func, $params);
    }
}

/**
 * Retrieve UTFB resource objects from the API
 *
 * @param array  $auth       API authentication credentials
 * @param array  $resources  Array of UTFB resource names and IDs
 * @param string $resource   Name of UTFB resource to retrieve
 * @param bool   $import_all Whether or not to import all objects the resource
 * @param bool   $refresh    Forces a refresh of API data (Default: false)
 *
 * @return array Array of objects from UTFB API
 */
function EMBM_Admin_Utfb_resources($auth, $resources, $resource, $import_all, $refresh = false)
{
    // Set up resource tracking objects
    $object_list = array_keys($GLOBALS['EMBM_UTFB_RESOURCE_MAP']);
    $objects = array();
    $get_all = false;

    // Get objects to import
    foreach ($resources as $resource_name => $resource_id) {
        // Skip the location
        if ($resource_name == 'location') {
            // Set new parent
            $parent_name = $resource_name;
            $parent_id = $resource_id;

            // Move to the next resource
            continue;
        }

        // Check if this is our main resource
        $is_resource = ($resource_name == $resource);

        // Handle get all cases
        if ($get_all) {
            // Get parent objects
            $parent_objects = $objects[$parent_name];

            // Set up resource objects
            $resource_objects = array();

            // Iterate over parent objects
            foreach ($parent_objects as $parent_object) {
                // Get resources
                $new_resource_objects = call_user_func_array(
                    'EMBM_Admin_Utfb_resource',
                    array(
                        'auth'          => $auth,
                        'resource_name' => $resource_name,
                        'resource_id'   => $resource_id,
                        'parent_id'     => $parent_object->id,
                        'call_type'     => 'plural',
                        'refresh'       => $refresh
                    )
                );

                // Add to resource array
                $resource_objects = array_merge($resource_objects, $new_resource_objects);
            }

            // Store resource data
            $objects[$resource_name] = $resource_objects;
        } else {
            // Get call type
            $call_type = ($is_resource && $import_all) ? 'plural' : 'single';

            // Get resource
            $objects[$resource_name] = call_user_func_array(
                'EMBM_Admin_Utfb_resource',
                array(
                    'auth'          => $auth,
                    'resource_name' => $resource_name,
                    'resource_id'   => $resource_id,
                    'parent_id'     => $parent_id,
                    'call_type'     => $call_type,
                    'refresh'       => $refresh
                )
            );
        }

        // Set new parent
        $parent_name = $resource_name;
        $parent_id = $resource_id;

        // Set get all
        if ($is_resource) {
            $get_all = true;
        }
    }

    return $objects;
}

/**
 * Retrieves all UTFB data for all locations
 *
 * @param bool $refresh Forces a refresh of API data (Default: false)
 *
 * @return array
 */
function EMBM_Admin_Utfb_Resources_all($refresh = false)
{
    // Check for existing UTFB credentials
    $auth = get_option(EMBM_UTFB_CREDENTIALS);

    // Make sure we're authorized
    if (null == $auth) {
        return null;
    }

    // Set up UTFB objects
    $resources = array(
        'location' => EMBM_Admin_Utfb_locations($auth, $refresh),
        'menu'     => array(),
        'section'  => array(),
        'beer'     => array()
    );

    // Get all menus for location
    foreach ($resources['location'] as $location) {
        // Set up resources for request
        $query = array(
            'location' => $location->id,
            'menu'     => null,
            'section'  => null,
            'beer'     => null
        );

        // Get UTFB objects to import
        $objects = EMBM_Admin_Utfb_resources($auth, $query, 'menu', true, $refresh);

        // Iteratively merge
        foreach ($objects as $obj_type => $obj_data) {
            $resources[$obj_type] = array_merge($resources[$obj_type], $obj_data);
        }
    }

    // Return full array of all UTFB resources for the account
    return $resources;
}

/**
 * Import resources from UTFB
 *
 * @param array $resources Array of UTFB resources to import
 *
 * @return int Notice status code
 */
function EMBM_Admin_Utfb_import($resources)
{
    // Get import resources
    $menus = $resources['menu'];
    $sections = $resources['section'];
    $beers = $resources['beer'];

    // Iterate over menus
    foreach ($menus as $menu) {
        // Get or add menu term
        $menu->term = EMBM_Admin_Utfb_taxonomy($menu);
    }

    // Iterate over sections
    foreach ($sections as $section) {
        // Get menu for section
        $section_menu = EMBM_Admin_Utfb_find($menus, $section->menu_id);

        // Get or add section term
        $section->term = EMBM_Admin_Utfb_taxonomy($section, $section_menu);
    }

    // Update list of existing attachment data to the cache
    EMBM_Admin_attachments();
    error_log(print_r(get_transient(EMBM_ATTACHMENT_CACHE),true));

    // Set error tracker
    $has_errors = false;

    // Iterate over beers
    foreach ($beers as $beer) {
        // Get section from ID
        $section = EMBM_Admin_Utfb_find($sections, $beer->section_id);
        $menu = EMBM_Admin_Utfb_find($menus, $section->menu_id);

        // Import beer
        $res = EMBM_Admin_Utfb_Import_beer($beer, $section->term, $menu->term);

        // Check response
        if (!is_null($res)) {
            $has_errors = true;
        }
    }

    error_log(print_r(get_transient(EMBM_ATTACHMENT_CACHE),true));

    // Remove attachment cache
    // delete_transient(EMBM_ATTACHMENT_CACHE);

    // Return response code
    return $has_errors ? 3 : 0;
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
        'post_type'      => EMBM_BEER,
        'posts_per_page' => 1
    );

    // Check for duplicate
    $duplicate = get_posts($dup_args);

    // Check if we need to update the existing beer's menus and exit
    if ($duplicate) {
        EMBM_Admin_Utfb_Import_Beer_duplicate($duplicate[0], $beer, $section_term, $menu_term);
        return null;
    }

    // Set post publish date from Untappd created date
    $beer_date = date('Y-m-d H:i:s', strtotime($beer->created_at));

    // Find beer style
    $style = term_exists($beer->style, EMBM_STYLE);

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Get beer data
    $api_root = EMBM_UNTAPPD_API_URL.$token;
    $untappd_beer_data = EMBM_Admin_Untappd_Beer_get($api_root, $beer->untappd_id);

    // Set up post array
    $new_beer_post = array(
        'post_author'   => get_current_user_id(),
        'post_title'    => $beer->name,
        'post_name'     => $beer_slug,
        'post_content'  => $beer->description,
        'post_date'     => $beer_date,
        'post_status'   => 'publish',
        'post_type'     => EMBM_BEER,
        'tax_input'     => array(
            EMBM_STYLE   => array(
                $beer->style
            ),
            EMBM_MENU    => array(
                $menu_term['term_taxonomy_id'],
                $section_term['term_taxonomy_id']
            )
        ),
        'meta_input'    => array(
            EMBM_BEER_META         => array(
                'abv'              => intval($beer->abv),
                'ibu'              => intval($beer->ibu),
                'untappd_id'       => intval($beer->untappd_id),
                'utfb_ids'         => array($beer->id)
            ),
            EMBM_BEER_META_UNTAPPD => array(
                'beer'             => $untappd_beer_data,
                'cached'           => time()
            ),
            EMBM_BEER_META_UTFB    => array(
                array(
                    'beer'         => $beer,
                    'cached'       => time()
                )
            )
        )
    );

    // TODO: uncomment
    // Insert post
    // $post_id = wp_insert_post($new_beer_post, true);
    $post_id = 123;

    // Add post image
    if (property_exists($beer, 'label_image')) {
        EMBM_Admin_Untappd_Import_image($post_id, $beer->label_image, $beer->untappd_beer_slug);
    }

    return null;
}

/**
 * Update existing beers with new menu or section data during import
 *
 * @param object $post         WP beer post data
 * @param array  $beer         UTFB beer data
 * @param int    $section_term UTFB section WP taxonomy term data
 * @param int    $menu_term    UTFB menu WP taxonomy term data
 *
 * @return void
 */
function EMBM_Admin_Utfb_Import_Beer_duplicate($post, $beer, $section_term, $menu_term)
{
    // Set list of term IDs to add
    $term_ids = array($menu_term['term_taxonomy_id'], $section_term['term_taxonomy_id']);

    // Update the post with the term IDs
    wp_set_post_terms($post->ID, $term_ids, EMBM_MENU, true);

    // Get current post meta data
    $beer_meta = get_post_meta($post->ID, EMBM_BEER_META, true);
    $beer_meta = (null == $beer_meta) ? array() : $beer_meta;

    // Add new UTFB ID to array of IDs
    if (array_key_exists('utfb_ids', $beer_meta) && !in_array($beer->id, $beer_meta['utfb_ids'])) {
        array_push($beer_meta['utfb_ids'], $beer->id);
    } else {
        $beer_meta['utfb_ids'] = array($beer->id);
    }

    // Update post meta data
    update_post_meta($post->ID, EMBM_BEER_META, $beer_meta);

    // Get current post UTFB data
    $utfb_meta = get_post_meta($post->ID, EMBM_BEER_META_UTFB, true);

    // Set up data for storage
    $beer_data = array(
        'beer'      => $beer,
        'cached'    => time()
    );

    if (is_array($utfb_meta)) {
        array_push($utfb_meta, $beer_data);
    } else {
        $utfb_meta = array($beer_data);
    }

    // Update UTFB meta data
    update_post_meta($post->ID, EMBM_BEER_META_UTFB, $utfb_meta);
}

/**
 * Sync existing WP objects with UTFB data
 *
 * @param array $resources      Array of UTFB resources to sync
 * @param bool  $delete_missing Whether or not to remove missing data
 *
 * @return int Notice status code
 */
function EMBM_Admin_Utfb_sync($resources, $delete_missing = false)
{

    // First sync Menus
    $menu_errors = EMBM_Admin_Utfb_Sync_menus($resources, $delete_missing);

    // Next sync beers
    $beer_errors = EMBM_Admin_Utfb_Sync_beers($resources);

    // Return with appropriate response code
    return ($menu_errors || $beer_errors) ? 4 : 0;
}

/**
 * Sync Menus with UTFB data
 *
 * @param array $resources      Array of UTFB resources to sync
 * @param bool  $delete_missing Whether or not to remove missing data
 *
 * @return bool
 */
function EMBM_Admin_Utfb_Sync_menus($resources, $delete_missing)
{
    // Get all menus
    $menus = get_terms(
        array(
            'taxonomy'   => EMBM_MENU,
            'hide_empty' => false
        )
    );

    // Track errors
    $has_errors = false;

    // Iterate over menus
    foreach ($menus as $menu) {
        // Check if this is section or menu
        $menu_type = ($menu->parent == 0) ? 'menu' : 'section';
        $menu_resource = null;

        // Get menu meta
        $menu_meta = get_term_meta($menu->term_id, EMBM_BEER_META, true);

        // Check if we're skipping this menu
        $skip_menu = (array_key_exists('sync_exclude', $menu_meta) && $menu_meta['sync_exclude'] == '1');
        if ($skip_menu) {
            continue;
        }

        // Get UTFB ID
        $menu_utfb_id = array_key_exists('utfb_id', $beer_meta) ? $beer_meta['utfb_id'] : null;

        // Check if there is existing UTFB data
        $menu_meta_utfb = get_term_meta($menu->term_id, EMBM_BEER_META_UTFB, true);
        if ((null == $menu_meta_utfb || !is_array($menu_meta_utfb)) && null == $menu_utfb_id) {
            // Attempt to find in existing menus
            $found = EMBM_Admin_Utfb_Find_name($resources['menu'], $menu->name);
            $menu_type = 'menu';

            // If it wasn't in menus, try sections
            if (empty($found)) {
                $found = EMBM_Admin_Utfb_Find_name($resources['section'], $menu->name);
                $menu_type = 'section';
            }

            // If it still wasn't found, continue
            if (empty($found)) {
                continue;
            }

            // Set up new data
            $menu_resource = $found[0];
            $menu_utfb_id = $found[0]->id;
        }

        // Get ID from UTFB data if needed
        if (null == $menu_utfb_id) {
            $menu_utfb_id = $menu_meta_utfb[$menu_type]->id;
        }

        // Find this menu in UTFB resources
        if (is_null($menu_resource)) {
            $menu_resource = EMBM_Admin_Utfb_find($resources[$menu_type], $menu_utfb_id);
        }

        // Skip if this menu doesn't exist on UTFB
        if (is_null($menu_resource)) {
            // Delete menu for missing flag
            if ($delete_missing) {
                wp_delete_term($menu->term_id, EMBM_MENU);
            }
            continue;
        }

        // Update menu data
        $menu_data = array(
            'name'        => $menu_resource->name,
            'description' => $menu_resource->description,
            'parent'      => 0
        );

        // If this is a section, check that the parent is still the same
        if ($menu_type == 'section') {
            // Get the UFTB data for the section's menu
            $parent = EMBM_Admin_Utfb_find($resources['menu'], $menu_resource->menu_id);

            // Get WP term data for the menu
            $menu_parent = EMBM_Admin_Utfb_taxonomy($parent);

            // Update section parent
            $menu_data['parent'] = $menu_parent['term_id'];
        }

        // Update menu
        $result = wp_update_term($menu->term_id, EMBM_MENU, $menu_data);

        // Update menu meta
        $menu_meta['utfb_id'] = $menu_resource->id;

        // Save updated meta
        $meta_res = update_term_meta($menu->term_id, EMBM_BEER_META, $menu_meta);

        // Update UTFB menu data
        $menu_utfb_data = array(
            $menu_type => $menu_resource,
            'cached'   => time()
        );

        // Update UTFB data for menu
        $utfb_meta_res = update_term_meta($menu->term_id, EMBM_BEER_META_UTFB, $menu_utfb_data);

        // Check for errors
        if (false == $result || false == $meta_res || false == $utfb_meta_res) {
            $has_errors = true;
        }
    }

    // Return any errors
    return $has_errors;
}

/**
 * Sync WP beers with UTFB data
 *
 * @param array $resources Array of UTFB resources to sync
 *
 * @return bool
 */
function EMBM_Admin_Utfb_Sync_beers($resources)
{
    // Get all WP beers
    $beers = get_posts(
        array(
            'post_type'   => EMBM_BEER,
            'numberposts' => -1,
        )
    );

    // Track errors
    $has_errors = false;

    // Iterate over beers
    foreach ($beers as $beer) {
        // Get beer meta
        $beer_meta = get_post_meta($beer->ID, EMBM_BEER_META, true);
        $beer_resources = null;

        // Check if we're skipping this beer
        $skip_beer = (array_key_exists('sync_exclude', $beer_meta) && $beer_meta['sync_exclude'] == '1');
        if ($skip_beer) {
            continue;
        }

        // Get list of UTFB beer IDs
        $beer_utfb_ids = array_key_exists('utfb_ids', $beer_meta) ? $beer_meta['utfb_ids'] : null;

        // Check if there is existing UTFB data
        $beer_meta_utfb = get_post_meta($beer->ID, EMBM_BEER_META_UTFB, true);
        if ((null == $beer_meta_utfb || !is_array($beer_meta_utfb)) && null == $beer_utfb_ids) {
            // Look for beer in existing resources
            $found = EMBM_Admin_Utfb_Find_name($resources['beer'], $beer->post_title);

            // If it still wasn't found, continue
            if (empty($found)) {
                continue;
            }

            // Set up data from found
            $beer_resources = $found;
            $beer_utfb_ids = array_map(
                function ($utfb_data) {
                    return $utfb_data->id;
                },
                $found
            );
        }

        // Get list of UTFB beer IDs from UTFB, if needed
        if (null == $beer_utfb_ids || empty($beer_utfb_ids)) {
            $beer_utfb_ids = array_map(
                function ($utfb_data) {
                    return $utfb_data['beer']->id;
                },
                $beer_meta_utfb
            );
        }

        // Find all instances of this beer in resources
        if (null == $beer_resources || empty($beer_resources)) {
            $beer_resources = array_values(
                array_filter(
                    $resources['beer'],
                    function ($resource) use ($beer_utfb_ids) {
                        return in_array($resource->id, $beer_utfb_ids);
                    }
                )
            );
        }

        // Skip if this beer doesn't exist on UTFB
        if (empty($beer_resources)) {
            continue;
        }

        // Set up new UTFB data
        $new_beer_utfb_ids = array();
        $new_beer_utfb_data = array();

        // Set appending to false to initially override all menus
        $append = false;

        // Iterate over all instances of the beer
        foreach ($beer_resources as $beer_res) {
            // Set up new UTFB data
            $beer_res_data = array(
                'beer'   => $beer_res,
                'cached' => time()
            );

            // Append to array
            array_push($new_beer_utfb_data, $beer_res_data);

            // Append UFTB ID to array
            array_push($new_beer_utfb_ids, $beer_res->id);

            // Get beer menu info
            $section = EMBM_Admin_Utfb_find($resources['section'], $beer_res->section_id);
            $menu = EMBM_Admin_Utfb_find($resources['menu'], $section->menu_id);

            // Location menu taxonomy
            $menu_tax = EMBM_Admin_Utfb_taxonomy($menu);

            // Locate section taxonomy
            $section_tax = EMBM_Admin_Utfb_taxonomy($section, $menu);

            // Update section and menu taxonomies for beer
            $result = wp_set_post_terms(
                $beer->ID,
                array(
                    $menu_tax['term_taxonomy_id'],
                    $section_tax['term_taxonomy_id']
                ),
                EMBM_MENU,
                $append
            );

            // Check for errors
            if (false == $result) {
                $has_errors = true;
            }

            // Enable appending for subsequent additions
            $append = true;
        }

        // Updated meta data
        $beer_meta['utfb_ids'] = $new_beer_utfb_ids;

        // Save update beer post meta
        update_post_meta($beer->ID, EMBM_BEER_META, $beer_meta);
        update_post_meta($beer->ID, EMBM_BEER_META_UTFB, $new_beer_utfb_data);

        // Check for errors
        if (json_encode(get_post_meta($beer->ID, EMBM_BEER_META, true)) !== json_encode($beer_meta)
            || json_encode(get_post_meta($beer->ID, EMBM_BEER_META_UTFB, true)) !== json_encode($new_beer_utfb_data)
        ) {
            $has_errors = true;
        }
    }

    // Check for failures
    return $has_errors;
}

/**
 * Sync all beers with UTFB data
 *
 * @return int Notice status code
 */
function EMBM_Admin_Utfb_Sync_all()
{
    // Check for existing UTFB credentials
    $auth = get_option(EMBM_UTFB_CREDENTIALS);

    // Make sure we're authorized
    if (null == $auth) {
        return null;
    }

    // Get all locations
    $locations = EMBM_Admin_Utfb_locations($auth);

    // Sync objects for each location
    foreach ($locations as $location) {
        // Set up resources for request
        $resources = array(
            'location' => $location->id,
            'menu'     => null,
            'section'  => null,
            'beer'     => null
        );

        // Get UTFB objects to import
        $objects = EMBM_Admin_Utfb_resources($auth, $resources, 'menu', true);

        // Sync UTFB objects
        $res_code = EMBM_Admin_Utfb_sync($objects);
    }
}
