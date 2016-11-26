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
 * @package EMBM\Admin\Labs\Import
 */


// Import shared labs functions
require 'labs.php';

// Handle token return
if (isset($_GET['embm-untappd-token'])) {
    // Store token
    $new_token = $_GET['embm-untappd-token'];
    update_option('embm_untappd_token', $new_token);

     // Clean up URL
    EMBM_Admin_Labs_urlclean();
}

// Handle Untappd deauthorization
if (isset($_GET['embm-untappd-deauthorize']) && $_GET['embm-untappd-deauthorize'] == '1') {
    // Delete Untappd records from db
    delete_option('embm_untappd_brewery_id');
    delete_option('embm_untappd_token');

    // Clean up URL
    EMBM_Admin_Labs_urlclean();
}

// Get API token
$token = get_option('embm_untappd_token');

// Check for a token
if (!$token || $token == '') {
?>
    <p>
        <button class="embm-labs--authorize-button button-secondary"><?php _e('Log In to Authorize Untappd', 'embm'); ?></button>
    </p>
<?php
    return;
}

// Set API Root
$api_root = EMBM_API_URL_FORMAT.$token;

// Get Untappd user info
$user = EMBM_Admin_Labs_Untappd_user($api_root);

// Check for brewery account
if ($user->account_type != 'brewery') {
    // Deauthorize user
    EMBM_Admin_Labs_deauthorize();
    return;
}

// Get Untappd brewery ID
$brewery_id = EMBM_Admin_Labs_Untappd_id($user->untappd_url);

// Get Untappd brewery info from API
$brewery = EMBM_Admin_Labs_Untappd_brewery($api_root, $brewery_id);

// Make sure brewery is claimed by authorized user
if (!$brewery->claimed_status->is_claimed || $brewery->claimed_status->uid != $user->uid) {
    // Deauthorize user
    EMBM_Admin_Labs_deauthorize();
    return;
}

// Get all the Untappd beers for the brewery
$beer_list = EMBM_Admin_Labs_Untappd_beers($api_root, $brewery);


// Display import options
?>
    <p>
        <?php _e('You are authorized as', 'embm'); ?>:
        <a href="<?php echo $user->untappd_url; ?>" target="_blank" class="embm-untappd--user-link">
            <strong><?php echo $user->first_name; ?></strong>
            <small>(<a href="#" class="embm-untappd--deauthorize"><?php _e('Deauthorize', 'embm'); ?></a>)</small>
        </a>
    </p>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e('Import single beer', 'embm'); ?></th>
                <td>
                    <form method="post" action="<?php echo EMBM_PLUGIN_URL.'includes/admin/action-import-beer.php'; ?>" class="embm-labs--import-form">
                        <input type="hidden" name="embm-labs-untappd-import" value="1" />
                        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
                        <input type="hidden" name="embm-untappd-brewery-id" value="<?php echo $brewery->brewery_id; ?>" />
                        <p>
                            <select id="embm-untappd-beer-id" name="embm-untappd-beer-id" class="embm-labs--import-select">
                                <?php foreach ($beer_list as $item) : $beer = $item->beer; ?>
                                    <option value="<?php echo $beer->bid; ?>"><?php echo $beer->beer_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input name="import" type="submit" class="button-secondary" value="<?php _e('Import', 'embm'); ?>" />
                        </p>
                    </form>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Import all beers', 'embm'); ?></th>
                <td>
                    <form method="post" action="<?php echo EMBM_PLUGIN_URL.'includes/admin/action-import-beer.php'; ?>" class="embm-labs--import-form">
                        <input type="hidden" name="embm-labs-untappd-import" value="2" />
                        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
                        <input type="hidden" name="embm-untappd-brewery-id" value="<?php echo $brewery->brewery_id; ?>" />
                        <p>
                            <input
                                name="import"
                                type="submit"
                                class="button-primary"
                                value="<?php echo __('Import All', 'embm') . ' (' . count($beer_list) . ')'; ?>"
                        </p>
                        <p class="description">(<?php _e('If you have a lot of beers, this could take a while.', 'embm'); ?>)</p>
                    </form>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Import single beer by ID', 'embm'); ?></th>
                <td>
                    <form method="post" action="<?php echo EMBM_PLUGIN_URL.'includes/admin/action-import-beer.php'; ?>" class="embm-labs--import-form">
                        <input type="hidden" name="embm-labs-untappd-import" value="1" />
                        <input type="hidden" name="embm-untappd-brewery-check" value="1" />
                        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
                        <input type="hidden" name="embm-untappd-brewery-id" value="<?php echo $brewery->brewery_id; ?>" />
                        <p><?php _e('Import beers that are not accessible in the features above.', 'embm'); ?></p>
                        <p>
                            <input
                                id="embm-untappd-beer-id"
                                name="embm-untappd-beer-id"
                                class="embm-labs--import-id"
                                type="number"
                                placeholder="<?php _e('Untappd Beer ID', 'embm'); ?>"
                            />
                            <input name="import" type="submit" class="button-secondary" value="<?php _e('Import', 'embm'); ?>" />
                            <a data-help="embm-untappd-beer-id" class="embm-settings--help">?</a>
                        </p>
                        <p class="description">(<?php _e('You can only import beers that your brewery account owns.', 'embm'); ?>)</p>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
