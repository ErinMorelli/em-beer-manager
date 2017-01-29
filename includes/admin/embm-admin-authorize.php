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

// Set authorization URL
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

    // Get global WP database reference
    global $wpdb;

    // Remove individual beer Untappd data
    $wpdb->query(
        "
        UPDATE
            $wpdb->postmeta
        SET
            meta_value = NULL
        WHERE
            meta_key = 'embm_untappd_data'
        "
    );
    $wpdb->query(
        "
        UPDATE
            $wpdb->postmeta
        SET
            meta_value = '1'
        WHERE
            meta_key IN (
                'embm_show_rating',
                'embm_show_reviews'
            )
        "
    );
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
        return 1;
    }

    // Set API Root
    $api_root = EMBM_UNTAPPD_API_URL.$token;

    // Get Untappd user info
    $user = EMBM_Admin_Untappd_user($api_root);

    // Check for error
    if (is_null($user) || is_string($user)) {
        EMBM_Admin_Notices_ratelimit($user);
        return 0;
    } else {
        // Get brewery status
        $is_brewery = ($user->account_type == 'brewery');

?>
    <div class="embm-settings--status">
        <p>
            <?php _e('You are logged in as', 'embm'); ?>:
            <a
                href="<?php echo $user->untappd_url; ?>"
                target="_blank"
                class="embm-untappd--user-link"
                title="<?php echo $user->user_name . ' (' . $user->account_type . ')'; ?>"
            ><span
                class="dashicons dashicons-<?php echo ($is_brewery ? 'groups' : 'admin-users'); ?>"
            ></span><strong><?php echo $user->first_name . ' ' . $user->last_name; ?></strong></a>
            <a href="#" class="embm-untappd--deauthorize button button-small"><?php _e('Log Out', 'embm'); ?></a>
        </p>
    </div>
<?php
        return 2;
    }
}

// Handle token return
if (isset($_GET['embm-untappd-token'])) {
    // Store token
    $new_token = $_GET['embm-untappd-token'];
    update_option('embm_untappd_token', $new_token);
}
