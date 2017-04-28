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
 * @package EMBM\Admin\Labs\UntappdImport
 */

/**
 * Displays an unauthorized error message
 *
 * @return void
 */
function EMBM_Admin_Labs_Import_error()
{
?>
    <p class="warning"><?php _e('Sorry, Untappd importing is only supported for brewery accounts.', 'embm'); ?></p>
    <p><?php _e('Please re-authorize with a brewery account to use this feature.', 'embm'); ?></p>
    <p>
        <button class="embm-labs--reauthorize button-secondary"><?php _e('Re-authorize with Untappd', 'embm'); ?></button><br />
        <small><em><?php _e('You will need to log out of the Untappd.com website before re-authorizing.', 'embm'); ?></em></small>
    <p>
<?php
}

/**
 * Displays a API rate-limiting error message based on response
 *
 * @param any $res Untappd API response
 *
 * @return boolean Whether or not an error was shown
 */
function EMBM_Admin_Labs_Api_error($res)
{
    if (is_null($res) || is_string($res)) {
        EMBM_Admin_Notices_ratelimit($res);
        return true;
    }
    return false;
}

// Show status
$shown = EMBM_Admin_Authorize_status();
if ($shown === 1) {
    return;
}

// Get token
$token = EMBM_Admin_Authorize_token();

// Make sure we're authorized
if (null == $token) {
    EMBM_Admin_Labs_Import_error();
    return;
}

// Set API Root
$api_root = EMBM_UNTAPPD_API_URL.$token;

// Get Untappd user info
$user = EMBM_Admin_Untappd_user($api_root);

// Check for error
if (is_null($user) || is_string($user)) {
    if ($shown === 2) {
        EMBM_Admin_Notices_ratelimit($user);
    }
    return;
}

// Check for brewery account
if ($user->account_type != 'brewery') {
    EMBM_Admin_Labs_Import_error();
    return;
}

// Get HTTPS user URL
$user_url = EMBM_Admin_Untappd_https($user->untappd_url);

// Get Untappd brewery ID
$brewery_id = EMBM_Admin_Untappd_id($user_url);
if (!$brewery_id) {
    EMBM_Admin_Labs_Import_error();
    return;
}

// Get Untappd brewery info from API
$brewery = EMBM_Admin_Untappd_brewery($api_root, $brewery_id);
if (EMBM_Admin_Labs_Api_error($brewery)) {
    return;
}

// Make sure brewery is claimed by authorized user
if (!$brewery->claimed_status->is_claimed || $brewery->claimed_status->uid != $user->uid) {
    EMBM_Admin_Labs_Import_error();
    return;
}

// Get all the Untappd beers for the brewery
$beer_list = EMBM_Admin_Untappd_beers($api_root, $brewery);
if (EMBM_Admin_Labs_Api_error($beer_list)) {
    return;
}

// Display import options
?>
<div id="embm-labs-untappd">
    <input type="hidden" id="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
    <input type="hidden" id="embm-untappd-brewery-id" value="<?php echo $brewery->brewery_id; ?>" />

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e('Import Specific Beers', 'embm'); ?></th>
                <td>
                    <p>
                        <select multiple id="embm-untappd-beer-ids" name="embm-untappd-beer-ids[]" class="embm-labs--import-select">
                            <?php foreach ($beer_list as $item) : $beer = $item->beer; ?>
                                <option value="<?php echo $beer->bid; ?>"><?php echo $beer->beer_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p>
                        <a class="button-primary embm-untappd--import" data-type="1"><?php _e('Import Selected Beer(s)', 'embm'); ?></a>
                    </p>
                    <p class="description">
                        <?php
                            printf(
                                __('Use the %s and %s/%s keys to select multiple beers.', 'embm'),
                                '<code>shift</code>',
                                '<code>ctrl</code>',
                                '<code>command</code>'
                            );
                        ?>
                    </p>
                    </form>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Import All Beers', 'embm'); ?></th>
                <td>
                    <p>
                        <a class="button-primary embm-untappd--import" data-type="3">
                            <?php echo __('Import All Beers', 'embm').' ('.count($beer_list).')'; ?>
                        </a>
                    </p>
                    <p class="description"><?php _e('If you have a lot of beers, this could take a while.', 'embm'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Import Beer By ID', 'embm'); ?></th>
                <td>
                    <p>
                        <input
                            id="embm-untappd-beer-id"
                            name="embm-untappd-beer-id"
                            type="number"
                            placeholder="<?php _e('Untappd Beer ID', 'embm'); ?>"
                        />
                        <a class="button-primary embm-untappd--import" data-type="2"><?php _e('Import Beer', 'embm'); ?></a>
                        <a data-help="embm-untappd-beer-id" class="embm-settings--help">?</a>
                    </p>
                    <p class="description">
                        <?php _e('Import beers from your brewery account that are not accessible in the features above.', 'embm'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Refresh Untappd Data', 'embm'); ?></th>
                <td>
                    <p><a href="#" class="embm-untappd--flush button-secondary"><?php _e('Flush Cache', 'embm'); ?></a></p>
                    <p class="description">
                        <?php _e('Update the data from Untappd used in the above features. This is automatically done daily.', 'embm'); ?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
