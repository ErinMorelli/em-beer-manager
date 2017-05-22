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
    error_log($location_id);
    // Get all locations
    $locations = EMBM_Admin_Utfb_locations($auth, $location_id, $refresh);
    error_log($locations);

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

/*
 *
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

/*
 *
 */
function EMBM_Admin_Utfb_params($function_name, $get_optionals = false)
{
    // Set up params
    $params = array();

    // Check that function exists
    if(function_exists($function_name)) {
        // Parse function
        $fx = new ReflectionFunction($function_name);

        // Iterate over function params
        foreach ($fx->getParameters() as $param) {
            // Store required params
            if (!$param->isOptional()) {
                $params[$param->name] = null;
            }

            // Store optional params
            elseif ($param->isOptional() && $get_optionals) {
                $params[$param->name] = $param->getDefaultValue();
            }
        }
    }

    // Return params
    return $params;
}

/*
 *
 */
function EMBM_Admin_Utfb_import($auth, $objects)
{
    // Iterate over objects
    foreach ($objects as $resource => $data) {
        // Import a given type
        switch ($resource) {

        // Import menus as categories
        case 'menu':
            # code...
            break;

        // Import sections as sub-categories
        case 'section':
            # code...
            break;

        // Import beers
        case 'beer':
            # code...
            break;

        default:
            # code...
            break;
        }
    }

    return;
}
