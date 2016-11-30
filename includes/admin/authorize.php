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
 * @package EMBM\Admin\Authorize
 */

// Set authorization url
define('EMBM_UNTAPPD_AUTH_URL', 'https://wp.erinmorelli.com/embm/untappd');


/**
 * Deauthorize user
 *
 * @return void
 */
function EMBM_Admin_Authorize_deauthorize()
{
    // Deauthorize user
    delete_option('embm_untappd_token');
    delete_option('embm_untappd_brewery_id');

    // Flush cache
    EMBM_Admin_Untappd_flush();
}


/**
 * Retrieves a user's Untappd token
 *
 * @return string
 */
function EMBM_Admin_Authorize_token()
{
    // Get API token
    $token = get_option('embm_untappd_token');

    // Check for a token
    if (!$token || $token == '') {
        return null;
    }

    // Return token
    return $token;
}


/**
 *
 */
function EMBM_Admin_Authorize_status()
{
    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Check for authorization
    if (null == $token) {
?>
    <p>
        <button class="embm-labs--authorize button-secondary"><?php _e('Log In to Authorize Untappd', 'embm'); ?></button>
    </p>
<?php
        return;
    }

    // Get Untappd user info
    $user = EMBM_Admin_Untappd_user(EMBM_UNTAPPD_API_URL.$token);

?>
    <div class="embm-settings--status">
        <p>
            <?php _e('You are logged in as', 'embm'); ?>:
            <a href="<?php echo $user->untappd_url; ?>" target="_blank" class="embm-untappd--user-link"><strong><?php echo $user->first_name; ?></strong></a>
            &nbsp;<a href="#" class="embm-untappd--deauthorize button button-small"><?php _e('Log Out', 'embm'); ?></a>
        </p>
    </div>
<?php
}


// Handle token return
if (isset($_GET['embm-untappd-token'])) {
    // Store token
    $new_token = $_GET['embm-untappd-token'];
    update_option('embm_untappd_token', $new_token);
}

// Handle Untappd deauthorization
if (isset($_GET['embm-untappd-deauthorize']) && $_GET['embm-untappd-deauthorize'] == '1') {
    // Deauthorize the user
    EMBM_Admin_Authorize_deauthorize();
}

// Handle Untappd reauthorization
if (isset($_GET['embm-untappd-reauthorize']) && $_GET['embm-untappd-reauthorize'] == '1') {
    // Deauthorize the user
    EMBM_Admin_Authorize_deauthorize();

    // Return URL
    $return_url = get_admin_url(null, 'options-general.php?page=embm-settings');

    // Set up auth URL params
    $auth_params = http_build_query(array('embm-origin' => $return_url));

    // Set up full auth URL
    $auth_url = EMBM_UNTAPPD_AUTH_URL . '?' . $auth_params;

    // Redirect for authorization
    wp_redirect($auth_url);
}
