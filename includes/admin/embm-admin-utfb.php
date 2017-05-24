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

// TODO: Remove when integration is complete
include_once EMBM_PLUGIN_DIR.'includes/admin/embm-admin-utfb-dummy.php';

define('EMBM_UTFB_RETURN_URL', 'options-general.php?page=embm-settings&embm-utfb-%s=%d#%s');
define('EMBM_UTFB_API_URL', 'https://business.untappd.com/api/v1/%s');

// Set cache names
$GLOBALS['EMBM_UTFB_CACHE'] = array(
    'account'   => 'embm_utfb_account',
    'locations' => 'embm_utfb_locations',
    'menus'     => 'embm_utfb_menus_%s',
    'sections'  => 'embm_utfb_section_%s',
    'items'     => 'embm_utfb_items_%s'
);

// Resource mapping
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

/*
 *
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

/*
 * TODO: Remove when integration is complete
 */
function EMBM_Admin_Utfb_dummyrequest($request_url, $decode = true)
{
    // Set up response object
    $response = array(
        'success'   => false,
        'data'      => null,
        'errors'    => null
    );

    // Make GET request to API
    $response['data'] = EMBM_Admin_Utfb_Dummy_response($request_url);

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

/*
 *
 */
function EMBM_Admin_Utfb_validate($auth)
{
    return true;
}

/*
 *
 */
function EMBM_Admin_Utfb_account($auth, $refresh = false)
{
    // Attempt to retrieve account info from cache
    $account = get_transient($GLOBALS['EMBM_UTFB_CACHE']['account']);

    // Get account info if it's not cached
    if (false === $account || $refresh) {
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

/*
 *
 */
function EMBM_Admin_Utfb_locations($auth, $refresh = false)
{
    // Attempt to retrieve locations from cache
    $locations = get_transient($GLOBALS['EMBM_UTFB_CACHE']['locations']);

    // Get locations if not cached
    if (false === $locations || $refresh) {
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

/*
 *
 */
function EMBM_Admin_Utfb_location($auth, $location_id, $refresh = false)
{
    // Get all locations
    $locations = EMBM_Admin_Utfb_locations($auth, $location_id, $refresh);

    // Find location
    return EMBM_Admin_Utfb_find($locations, $location_id);
}

/*
 *
 */
function EMBM_Admin_Utfb_menus($auth, $location_id, $refresh = false)
{
    // Attempt to retrieve menu from cache
    $menus_cache_name = sprintf($GLOBALS['EMBM_UTFB_CACHE']['menus'], $location_id);
    $menus = get_transient($menus_cache_name);

    // Get menus if not cached
    if (false === $menus || $refresh) {
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

/*
 *
 */
function EMBM_Admin_Utfb_menu($auth, $location_id, $menu_id, $refresh = false)
{
    // Get all menus
    $menus = EMBM_Admin_Utfb_menus($auth, $location_id, $refresh);

    // Find menu
    return EMBM_Admin_Utfb_find($menus, $menu_id);
}

/*
 *
 */
function EMBM_Admin_Utfb_sections($auth, $menu_id, $refresh = false)
{
    // Attempt to retrieve sections from cache
    $sections_cache_name = sprintf($GLOBALS['EMBM_UTFB_CACHE']['sections'], $menu_id);
    $sections = get_transient($sections_cache_name);

    // Get sections if not cached
    if (false === $sections || $refresh) {
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

/*
 *
 */
function EMBM_Admin_Utfb_section($auth, $menu_id, $section_id, $refresh = false)
{
    // Get all sections
    $sections = EMBM_Admin_Utfb_sections($auth, $menu_id, $refresh);

    // Find section
    return EMBM_Admin_Utfb_find($sections, $section_id);
}

/*
 *
 */
function EMBM_Admin_Utfb_beers($auth, $section_id, $refresh = false)
{
    // Attempt to retrieve beers from cache
    $beers_cache_name = sprintf($GLOBALS['EMBM_UTFB_CACHE']['sections'], $section_id);
    $beers = get_transient($beers_cache_name);

    // Get beers if not cached
    if (false === $beers || $refresh) {
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

/*
 *
 */
function EMBM_Admin_Utfb_beer($auth, $section_id, $beer_id, $refresh = false)
{
    // Get all beers
    $beers = EMBM_Admin_Utfb_beers($auth, $section_id, $refresh);

    // Find beer
    return EMBM_Admin_Utfb_find($beers, $beer_id);
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
    $response = null;

    // Iterate over objects
    foreach ($resources as $resource => $data) {
        // Import a given type
        switch ($resource) {

        // Import menus as categories
        case 'menu':
            // Iterate over menus
            foreach ($data as $menu) {
                // Check if menu exists
                $exists = term_exists($menu->name, 'embm_menu');

                // Store term ID and continue
                if ($exists) {
                    $menu->term_id = $exists['term_id'];
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
                $menu->term_id = $term['term_id'];
            }
            break;

        // Import sections as sub-categories
        case 'section':
            // Iterate over sections
            foreach ($data as $section) {
                // Get menu from ID
                $menu = EMBM_Admin_Utfb_find($objects['menu'], $section->menu_id);

                // Check if section  exists
                $exists = term_exists($section->name, 'embm_menu', $menu->term_id);

                // Store term ID and continue
                if ($exists) {
                    $section->term_id = $exists['term_id'];
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
                $section->term_id = $term['term_id'];
            }
            break;

        // Import beers
        case 'beer':
            // Set error tracker
            $has_errors = false;

            // Iterate over beers
            foreach ($data as $beer) {
                // Get section from ID
                $section = EMBM_Admin_Utfb_find($objects['section'], $beer->section_id);

                // Import beer
                $res = EMBM_Admin_Utfb_Import_beer($beer, $section->term_id);

                // Check response
                if (!is_null($res)) {
                    $has_errors = true;
                }
            }

            // Check for errors
            if ($has_errors) {
                $response = get_admin_url(null, sprintf(EMBM_UTFB_RETURN_URL, 'error', 3, 'utfb'));
            }
            break;

        // Fallback
        default:
            $response = get_admin_url(null, sprintf(EMBM_UTFB_RETURN_URL, 'error', 2, 'utfb'));
        }
    }

    return $response;
}

/**
 * Insert post from UTFB
 *
 * @param array $beer            UTFB beer data
 * @param int   $section_term_id UTFB section WP taxonomy term ID
 *
 * @return void
 */
function EMBM_Admin_Utfb_Import_beer($beer, $section_term_id)
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
            'embm_style'   => array($beer->style),
            'embm_menu'    => array($section_term_id)
        ),
        'meta_input'    => array(
            'embm_abv'              => $beer->abv,
            'embm_ibu'              => $beer->ibu,
            'embm_untappd'          => $beer->untappd_id,
            'embm_untappd_data'     => $beer_data,
            'embm_reviews_count'    => 5
        )
    );

    // Insert post
    $post_id = wp_insert_post($new_beer_post, true);

    // Add post image
    if (property_exists($beer, 'label_image')) {
        EMBM_Admin_Untappd_Import_image($post_id, $beer->label_image, $beer->untappd_beer_slug);
    }

    return null;
}
