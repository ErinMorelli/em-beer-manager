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

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'menus', 15 * MINUTE_IN_SECONDS, $location_id);

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

    // Check if we should attempt a reload (every 15 mins)
    $reload = EMBM_Admin_Untappd_reload(EMBM_UTFB_CACHE, 'sections', 15 * MINUTE_IN_SECONDS, $menu_id);

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

    // Check if we should attempt a reload (every 15 mins)
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
    $term_data = array(
        'description' => $menu->description
    );

    // Check for parent
    if (!is_null($parent)) {
        // Check if tax exists or create it
        $parent_term = EMBM_Admin_Utfb_taxonomy($parent);

        // Update term data
        $term_data['parent'] = $parent_term['term_id'];

        // Check if section  exists
        $exists = term_exists($menu->name, 'embm_menu', $parent_term['term_id']);
    } else {
        // Check if tax exists
        $exists = term_exists($menu->name, 'embm_menu');
    }

    // Return if exists
    if ($exists) {
        return $exists;
    }

    // Add term
    $term = wp_insert_term($menu->name, 'embm_menu', $term_data);

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
 * Retrieve UTFB resource objects from the API
 *
 * @param array  $auth       API authentication credentials
 * @param array  $resources  Array of UTFB resource names and IDs
 * @param string $resource   Name of UTFB resource to retrieve
 * @param bool   $import_all Whether or not to import all objects the resource
 *
 * @return array Array of objects from UTFB API
 */
function EMBM_Admin_Utfb_resources($auth, $resources, $resource, $import_all)
{
    // Set up resource tracking objects
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
                        'call_type'     => 'plural'
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
                    'call_type'     => $call_type
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
        // Get or add section term
        $section->term = EMBM_Admin_Utfb_taxonomy($section, $menu);
    }

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

    // Check for errors
    if ($has_errors) {
        return 3;
    }

    // Return success
    return 0;
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

    // Check for duplicate
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
    $untappd_beer_data = EMBM_Admin_Untappd_Beer_get($api_root, $beer->untappd_id);

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
 * Sync beers with UTFB data
 *
 * @param array $resources Array of UTFB resources to sync
 *
 * @return int Notice status code
 */
function EMBM_Admin_Utfb_sync($resources)
{
    // Get all WP beers
    $beers = get_posts(
        array(
            'post_type'   => 'embm_beer',
            'numberposts' => -1,
        )
    );

    // Track errors
    $has_errors = false;

    // Iterate over beers
    foreach ($beers as $beer) {
        // Skip beers that don't have Untappd IDs
        if (!$beer->embm_untappd) {
            continue;
        }

        // Find beer in resources
        $beer_res = null;
        foreach ($resources['beer'] as $resource) {
            if ($resource->untappd_id == $beer->embm_untappd) {
                $beer_res = $resource;
                break;
            }
        }

        // Skip this beer if it's not in the resources
        if (is_null($beer_res)) {
            continue;
        }

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
            'embm_menu'
        );

        // Check for errors
        if (!$result) {
            $has_errors = true;
        }
    }

    // Check for failures
    if ($has_errors) {
        return 4;
    }

    // Return success
    return 0;
}
