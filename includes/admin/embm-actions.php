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
 * @package EMBM\Admin\Actions
 */


// Define AJAX Nonce
define('EMBM_AJAX_NONCE', '_embm_ajax_request_nonce');


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
 * Remove Untappd cached data to force refesh
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

    // Refresh untappd data
    $beer = EMBM_Admin_Untappd_beer($api_root, $beer_id, $post_id, true);

    // Send response
    wp_send_json($beer);
}

// Add flush beer action to AJAX
add_action('wp_ajax_embm-untappd-flush-beer', 'EMBM_Admin_Actions_Untappd_Flush_beer');
