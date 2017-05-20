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
 * @package EMBM\Admin\Actions
 */

// Define AJAX Nonce
define('EMBM_AJAX_NONCE', '_embm_ajax_request_nonce');

/**
 * Reset Untappd beer styles
 *
 * @return void
 */
function EMBM_Admin_Actions_Styles_reset()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Soft reset all styles
    EMBM_Core_Styles_populate();

    // Set up redirect URL
    $redirect_url = get_admin_url(null, 'options-general.php?page=embm-settings&embm-styles-reset=1');

    // Set up response
    $response = array(
        'redirect' => $redirect_url
    );

    // Send response
    wp_send_json($response);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-styles-reset', 'EMBM_Admin_Actions_Styles_reset');

/**
 * Deauthorize the current user from Untappd
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_deauthorize()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Deauthorize the user
    EMBM_Admin_Authorize_deauthorize();

    // Send response
    wp_die();
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-deauthorize', 'EMBM_Admin_Actions_Untappd_deauthorize');

/**
 * Authorize the current user with Untappd
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_authorize()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Return URL
    $return_url = get_admin_url(null, 'options-general.php?page=embm-settings');

    // Set up auth URL params
    $auth_params = http_build_query(array('embm-origin' => $return_url));

    // Set up full auth URL
    $auth_url = EMBM_UNTAPPD_AUTH_URL . '?' . $auth_params;

    // Set up response
    $response = array(
        'redirect' => $auth_url
    );

    // Send response
    wp_send_json($response);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-authorize', 'EMBM_Admin_Actions_Untappd_authorize');

/**
 * Reauthorize the current user with Untappd
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_reauthorize()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Deauthorize the user
    EMBM_Admin_Authorize_deauthorize();

    // Return URL
    $return_url = get_admin_url(null, 'options-general.php?page=embm-settings');

    // Set up auth URL params
    $auth_params = http_build_query(array('embm-origin' => $return_url));

    // Set up full auth URL
    $auth_url = EMBM_UNTAPPD_AUTH_URL . '?' . $auth_params;

    // Set up response
    $response = array(
        'redirect' => $auth_url
    );

    // Send response
    wp_send_json($response);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-reauthorize', 'EMBM_Admin_Actions_Untappd_reauthorize');

/**
 * Flush the Untappd cache
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_flush()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Flush the transient cache
    EMBM_Admin_Untappd_flush();

    // Send response
    wp_die();
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-flush', 'EMBM_Admin_Actions_Untappd_flush');

/**
 * Remove Untappd cached data to force refresh
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_Flush_beer()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get POST data
    $post_id = intval($_POST['post_id']);
    $beer_id = intval($_POST['beer_id']);
    $api_root = $_POST['api_root'];

    // Refresh Untappd data
    $beer = EMBM_Admin_Untappd_beer($api_root, $beer_id, $post_id, true);

    // Send response
    wp_send_json($beer);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-flush-beer', 'EMBM_Admin_Actions_Untappd_Flush_beer');

/**
 * Remove Untappd cached check-in data to force refresh
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_Flush_checkins()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get POST data
    $brewery_id = intval($_POST['brewery_id']);
    $api_root = $_POST['api_root'];

    // Set up response
    $response = array(
        'xml'   => null,
        'api'   => null,
        'error' => $GLOBALS['EMBM_NOTICE_MAP']['widget-error']['2']
    );

    // Refresh XML data
    $response['xml'] = EMBM_Admin_Untappd_Checkins_xml($brewery_id, true);

    // Check for api_root
    if (null !== $api_root && $api_root !== '') {
        // Update data from API
        $response['api'] = EMBM_Admin_Untappd_checkins($api_root, $brewery_id, true);
    }

    // Send response
    wp_send_json($response);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-flush-checkins', 'EMBM_Admin_Actions_Untappd_Flush_checkins');

/**
 * Import beers from Untappd to WP
 *
 * @return void
 */
function EMBM_Admin_Actions_Untappd_import()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get imported vars
    $import_type = intval($_POST['import_type']);
    $brewery_id = intval($_POST['brewery_id']);
    $api_root = $_POST['api_root'];

    // Set up response
    $response = array(
        'redirect' => get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'success', 2, 'import'))
    );

    // Get Untappd brewery info from API
    $brewery = EMBM_Admin_Untappd_brewery($api_root, $brewery_id);

    // Check for error
    if (!is_object($brewery)) {
        $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1, 'import'));
        wp_send_json($response);
        return;
    }

    // Get all the Untappd beers for the brewery
    $beer_list = EMBM_Admin_Untappd_beers($api_root, $brewery);

    // Check for error
    if (!is_array($beer_list)) {
        $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1, 'import'));
        wp_send_json($response);
        return;
    }

    // Handle import types
    switch ($import_type) {

    // Import specific beers
    case 1:
        // Get beer IDs
        $beer_ids = $_POST['beer_ids'];

        // Set error tracker
        $has_errors = false;

        // Make sure we have IDs
        if (!$beer_ids) {
            wp_die();
        }

        // Iteratively add beers
        foreach ($beer_ids as $beer_id) {
            // Check for duplicate
            $exists = EMBM_Admin_Untappd_exists($beer_id);
            if (!is_null($exists)) {
                continue;
            }

            // Locate beer in cached array
            $beer = EMBM_Admin_Untappd_Beer_get($api_root, $beer_id);

            // Check for error
            if (!is_object($beer)) {
                $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1, 'import'));
                break;
            }

            // Run import
            $res = EMBM_Admin_Untappd_import($beer, $brewery_id);

            // Check response
            if (!is_null($res)) {
                $has_errors = true;
            }
        }

        // Check for errors
        if ($has_errors) {
            $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 5, 'import'));
        }
        break;

    // Import beer by ID
    case 2:
        // Get beer id
        $beer_id = $_POST['beer_id'];

        // Make sure we have an ID
        if (!$beer_id) {
            wp_die();
        }

        // Check for duplicate
        $exists = EMBM_Admin_Untappd_exists($beer_id);
        if (!is_null($exists)) {
            break;
        }

        // Get beer from API
        $beer = EMBM_Admin_Untappd_Beer_get($api_root, $beer_id);

        // Check for error
        if (!is_object($beer)) {
            $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1, 'import'));
            break;
        }

        // Run import with brewery check
        $res = EMBM_Admin_Untappd_import($beer, $brewery_id, true);

        // Check response
        if (!is_null($res)) {
            $response['redirect'] = $res;
        } else {
            $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'success', 1, 'import'));
        }
        break;

    // Import all beers
    case 3:
        // Set error tracker
        $has_errors = false;

        // Iteratively add beers
        foreach ($beer_list as $item) {
            // Check for duplicate
            $exists = EMBM_Admin_Untappd_exists($item->beer->bid);
            if (!is_null($exists)) {
                continue;
            }

            // Get beer from API
            $beer = EMBM_Admin_Untappd_Beer_get($api_root, $item->beer->bid);

            // Check for error
            if (!is_object($beer)) {
                $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 1, 'import'));
                break;
            }

            // Run import
            $res = EMBM_Admin_Untappd_import($beer, $brewery_id);

            // Check response
            if (!is_null($res)) {
                $has_errors = true;
            }
        }

        // Check for errors
        if ($has_errors) {
            $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 5, 'import'));
        }
        break;

    // Fallback
    default:
        // Setup return URL
        $response['redirect'] = get_admin_url(null, sprintf(EMBM_UNTAPPD_RETURN_URL, 'error', 4, 'import'));
    }

    // Send response
    wp_send_json($response);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-import', 'EMBM_Admin_Actions_Untappd_import');

/**
 * Connect to a UTFB account
 *
 * @return void
 */
function EMBM_Admin_Actions_Utfb_connect()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get POST data
    $api_key = $_POST['api_key'];
    $email = $_POST['email'];

    // Base64-encode credentials
    $encoded = base64_encode(sprintf('%s:%s', $email, $api_key));

    // Test for validity
    $is_valid = EMBM_Admin_Utfb_validate($encoded);

    // Respond to validity
    if ($is_valid) {
        // Store credentials
        update_option(
            'embm_utfb_options',
            array(
                'apikey'  => $api_key,
                'email'   => $email,
                'encoded' => $encoded
            )
        );

        // Set up redirect URL
        $redirect_url = get_admin_url(null, sprintf(EMBM_UTFB_RETURN_URL, 'success', 1, 'utfb'));
    } else {
        // Set up redirect URL
        $redirect_url = get_admin_url(null, sprintf(EMBM_UTFB_RETURN_URL, 'error', 1, 'utfb'));
    }

    // Set up response
    $response = array(
        'redirect' => $redirect_url
    );

    // Send response
    wp_send_json($response);
}

// Add UTFB connect action to AJAX
add_action('wp_ajax_embm-utfb-connect', 'EMBM_Admin_Actions_Utfb_connect');

/**
 * Get UTFB menus for a locations
 *
 * @return void
 */
function EMBM_Admin_Actions_Utfb_menus()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get POST data
    $authorization = $_POST['authorization'];
    $location_id = $_POST['location_id'];

    // Get menus
    $menus = EMBM_Admin_Utfb_menus($authorization, $location_id);

    // Set up response
    $response = array(
        'menus' => $menus
    );

    // Send response
    wp_send_json($response);
}

// Add UTFB menus action to AJAX
add_action('wp_ajax_embm-utfb-menus', 'EMBM_Admin_Actions_Utfb_menus');

/**
 * Get UTFB sections for a menu
 *
 * @return void
 */
function EMBM_Admin_Actions_Utfb_sections()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get POST data
    $authorization = $_POST['authorization'];
    $menu_id = $_POST['menu_id'];

    // Get sections
    $sections = EMBM_Admin_Utfb_sections($authorization, $menu_id);

    // Set up response
    $response = array(
        'sections' => $sections
    );

    // Send response
    wp_send_json($response);
}

// Add UTFB sections action to AJAX
add_action('wp_ajax_embm-utfb-sections', 'EMBM_Admin_Actions_Utfb_sections');

/**
 * Get UTFB beers for a section
 *
 * @return void
 */
function EMBM_Admin_Actions_Utfb_beers()
{
    // Check AJAX referrer
    check_ajax_referer(EMBM_AJAX_NONCE, '_nonce');

    // Get POST data
    $authorization = $_POST['authorization'];
    $section_id = $_POST['section_id'];

    // Get beers
    $beers = EMBM_Admin_Utfb_beers($authorization, $section_id);

    // Set up response
    $response = array(
        'beers' => $beers
    );

    // Send response
    wp_send_json($response);
}

// Add UTFB connect action to AJAX
add_action('wp_ajax_embm-utfb-beers', 'EMBM_Admin_Actions_Utfb_beers');
