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


/**
 * Cleans up URL after return
 *
 * @return void
 */
function EMBM_Admin_Labs_Import_urlclean()
{
?>
    <script type="text/javascript">EMBM_Labs_CleanURL();</script>
<?php
    return;
}


/**
 * Deauthorize user and display error message
 *
 * @return void
 */
function EMBM_Admin_Labs_Import_deauthorize()
{
    // Deauthorize user
    delete_option('embm_untappd_token');
    delete_option('embm_untappd_brewery_id');

    // Display error message
?>
    <p class="warning"><?php _e('Sorry, Untappd importing is only supported for brewery accounts.', 'embm'); ?></p>
    <p>
        <button class="embm-labs--authorize-button button-secondary"><?php _e('Re-authorize with Untappd', 'embm'); ?></button><br />
        <small><em><?php _e('You will need to log out of Untappd before re-authorizing.', 'embm'); ?></em></small>
    <p>
<?php
    return;
}


// Handle token return
if (isset($_GET['embm-untappd-token'])) {
    // Store token
    $new_token = $_GET['embm-untappd-token'];
    update_option('embm_untappd_token', $new_token);

     // Clean up URL
    EMBM_Admin_Labs_Import_urlclean();
}

// Handle Untappd deauthorization
if (isset($_GET['embm-untappd-deauthorize']) && $_GET['embm-untappd-deauthorize'] == '1') {
    // Delete Untappd records from db
    delete_option('embm_untappd_brewery_id');
    delete_option('embm_untappd_token');

    // Clean up URL
    EMBM_Admin_Labs_Import_urlclean();
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
$api_root = 'https://api.untappd.com/v4/%s?access_token='.$token;

// Get API user info
$user_info_url = sprintf($api_root, 'user/info');
$user_res = json_decode(file_get_contents($user_info_url));
$user = $user_res->response->user;

// Check for brewery account
if ($user->account_type != 'brewery') {
    // Deauthorize user
    EMBM_Admin_Labs_Import_deauthorize();
    return;
}

// Get brewery ID
$brewery_id = get_option('embm_untappd_brewery_id');

if (!$brewery_id || $brewery_id == '') {
    // Set up RSS feed regex to retrieve brewery ID
    $rss_regex = '/<p class="rss"><a href="\/rss\/brewery\/(\d+)">/';

    // Get brewery page contents
    $brewery_page = file_get_contents($user->untappd_url);

    // Look for ID
    preg_match($rss_regex, $brewery_page, $matches);

    // Store ID
    $brewery_id = $matches[1];
    update_option('embm_untappd_brewery_id', $matches[1]);
}

// Get brewery info from API
$brewery_url = sprintf($api_root, 'brewery/info/'.$brewery_id);
$brewery_res = json_decode(file_get_contents($brewery_url));
$brewery = $brewery_res->response->brewery;

// Make sure brewery is claimed by authorized user
if (!$brewery->claimed_status->is_claimed || $brewery->claimed_status->uid != $user->uid) {
    // Deauthorize user
    EMBM_Admin_Labs_Import_deauthorize();
    return;
}

// Display import options
?>
    <p>
        <?php _e('You are authorized as', 'embm'); ?>:
        <a href="<?php echo $user->untappd_url; ?>" target="_blank" class="embm-untappd--user-link">
            <strong><?php echo $user->first_name; ?></strong>
            <small>(<a href="#" class="embm-untappd--deauthorize"><?php _e('Deauthorize', 'embm'); ?></a>)</small>
        </a>
    </p>

     <p>
        <strong>
            <?php printf(
                __('NOTE: At this time, Untappd limits their API to only return %s beers per brewery.', 'embm'),
                '<span class="emphasis">15</span>'
            ); ?>
        </strong>
        <br />
        <em>
            <?php printf(
                __("If you'd like to see this change, please %s and let them know you'd like API access to all of your brewery's beers.", 'embm'),
                sprintf(
                    '<a href="mailto:info@untappd.com" target="_blank">%s</a>',
                    __('contact Untappd', 'embm')
                )
            ); ?>
        </em>
    </p>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e('Import single beer', 'embm'); ?></th>
                <td>
                    <form method="post" action="<?php echo EMBM_PLUGIN_URL.'includes/admin/action-import-beer.php'; ?>" class="embm-labs--import-form">
                        <input type="hidden" name="embm-labs-untappd-import" value="1" />
                        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
                        <p>
                            <select id="embm-untappd-beer-id" name="embm-untappd-beer-id" class="embm-labs--import-select">
                                <?php foreach ($brewery->beer_list->items as $item) : $beer = $item->beer; ?>
                                    <option value="<?php echo $beer->bid; ?>"><?php echo $beer->beer_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input name="import" type="submit" class="button-secondary" value="<?php _e('Import', 'embm'); ?>" />
                        </p>
                    </form>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Import single beer by ID', 'embm'); ?></th>
                <td>
                    <form method="post" action="<?php echo EMBM_PLUGIN_URL.'includes/admin/action-import-beer.php'; ?>" class="embm-labs--import-form">
                        <input type="hidden" name="embm-labs-untappd-import" value="1" />
                        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
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
                            <input name="import" type="submit" class="button-primary" value="<?php _e('Import All', 'embm'); ?>" />
                        </p>
                        <p class="description">(<?php _e('If you have a lot of beers, this could take a while.', 'embm'); ?>)</p>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
