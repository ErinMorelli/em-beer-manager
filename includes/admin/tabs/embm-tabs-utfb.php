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
 * @package EMBM\Admin\Labs\UTFB
 */

include EMBM_PLUGIN_DIR.'includes/admin/tabs/embm-tabs-labs.php';

/**
 *
 */
function EMBM_Admin_Labs_Utfb_credentials($credentials)
{
?>
    <div id="embm-labs-utfb">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('API Key', 'embm'); ?></th>
                    <td>
                        <p>
                            <input value="<?php echo $credentials['apikey']; ?>" id="embm-utfb--apikey" type="text">
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Account Email', 'embm'); ?></th>
                    <td>
                        <p>
                            <input value="<?php echo $credentials['email']; ?>" id="embm-utfb--email" type="text">
                        </p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <p>
                            <a href="#" class="embm-utfb--connect button-primary"><?php _e('Connect Account', 'embm'); ?></a>
                        </p>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}

// Check for existing UTFB credentials
$credentials = get_option('embm_utfb_options');
if (!$credentials){
    EMBM_Admin_Labs_Utfb_credentials($credentials);
    return;
}

// Get account information
$account = EMBM_Admin_Utfb_account($credentials);

// Get all locations
$locations = EMBM_Admin_Utfb_locations($credentials);

?>
<div id="embm-labs-utfb">
    <input type="hidden" id="embm-utfb-authorization" value="<?php echo $credentials['encoded']; ?>" />

    <div class="embm-settings--status">
        <p>
            <?php _e('You are connected to Untappd for Business as', 'embm'); ?>:
            <a
                href="https://untappd.com/<?php echo $account->untappd_username; ?>"
                target="_blank"
                class="embm-utfb--account-link"
                title="<?php echo $account->untappd_username . ' (' . $account->role . ')'; ?>"
            ><span
                class="dashicons dashicons-admin-users"
            ></span><strong><?php echo $account->name; ?></strong></a>
            <a href="#" class="embm-utfb--disconnect button button-small"><?php _e('Disconnect', 'embm'); ?></a>
        </p>
    </div>

    <table class="form-table">
        <tbody>
            <tr class="embm-utfb-section embm-utfb-section--location">
                <th scope="row"><?php _e('Select a Location', 'embm'); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-location-id"
                            name="embm-utfb-location-id"
                            class="embm-labs--location-select"
                        >
                            <option value="">-- <?php _e('Select', 'embm'); ?> --</option>
                            <?php foreach ($locations as $location) :  ?>
                                <option value="<?php echo $location->id; ?>"><?php echo $location->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </td>
            </tr>
            <tr class="embm-utfb-section embm-utfb-section--menu">
                <th scope="row"><?php _e('Select a Menu', 'embm'); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-menu-id"
                            name="embm-utfb-menu-id"
                            class="embm-labs--menu-select"
                        >
                            <option value="">-- <?php _e('Select', 'embm'); ?> --</option>
                        </select>
                    </p>
                </td>
            </tr>
            <tr class="embm-utfb-section embm-utfb-section--section">
                <th scope="row"><?php _e('Select a Section', 'embm'); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-section-id"
                            name="embm-utfb-section-id"
                            class="embm-labs--section-select"
                        >
                            <option value="">-- <?php _e('Select', 'embm'); ?> --</option>
                        </select>
                    </p>
                </td>
            </tr>
            <tr class="embm-utfb-section embm-utfb-section--beer">
                <th scope="row"><?php _e('Select a Beer', 'embm'); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-beer-id"
                            name="embm-utfb-beer-id"
                            class="embm-labs--beer-select"
                        >
                            <option value="">-- <?php _e('Select', 'embm'); ?> --</option>
                        </select>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>