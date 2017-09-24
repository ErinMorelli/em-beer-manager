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

require EMBM_PLUGIN_DIR.'includes/admin/tabs/embm-tabs-labs.php';

/**
 * Displays the UTFB API credentials form
 *
 * @return void
 */
function EMBM_Admin_Labs_Utfb_credentials()
{
?>
    <div id="embm-labs-utfb">
        <hr />
        <h3><?php _e('Connect your Untappd for Business account', EMBM_DOMAIN); ?>:</h3>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('API Key', EMBM_DOMAIN); ?></th>
                    <td>
                        <p>
                            <input value="" id="embm-utfb--apikey" type="text">
                            <a data-help="embm-utfb-integration" class="embm-settings--help">?</a>
                        </p>
                        <p class="description">
                            <?php printf(
                                __('You can find your API key under the %s section %s.', EMBM_DOMAIN),
                                sprintf('<strong>"%s"</strong>', __('API Access Tokens', EMBM_DOMAIN)),
                                sprintf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    'https://business.untappd.com/api_tokens',
                                    __('here', EMBM_DOMAIN)
                                )
                            ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Account Email', EMBM_DOMAIN); ?></th>
                    <td>
                        <p><input value="" id="embm-utfb--email" type="text"></p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <p>
                            <a href="#" class="embm-utfb--connect button-primary"><?php _e('Connect Account', EMBM_DOMAIN); ?></a>
                        </p>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}

// Show status
$shown = EMBM_Admin_Authorize_status();
if ($shown !== 2) {
    printf(
        '<p class="description">%s%s</p>',
        __('You will be asked to connect your Untappd for Business account after logging in to your Untappd account.', EMBM_DOMAIN),
        '<a data-help="embm-utfb-integration" class="embm-settings--help">?</a>'
    );
    return;
}

// Check for existing UTFB credentials
$credentials = get_option(EMBM_UTFB_CREDENTIALS);

// Make sure we're authorized
if (null == $credentials) {
    EMBM_Admin_Labs_Utfb_credentials();
    return;
}

// Get account information
$account = EMBM_Admin_Utfb_account($credentials);

// Check for error
if (is_null($account) || is_string($account)) {
    EMBM_Admin_Labs_Utfb_credentials();
    return;
}

// Set account URL and title
if (property_exists($account, 'untappd_username')) {
    $title = sprintf('%s (%s)', $account->untappd_username, $account->role);
    $href = sprintf('https://untappd.com/%s', $account->untappd_username);
} else {
    $title = sprintf('%s (%s)', $account->name, $account->role);
    $href = 'https://business.untappd.com';
}

// Get all locations
$locations = EMBM_Admin_Utfb_locations($credentials);

?>
<div id="embm-labs-utfb">
    <hr />

    <div class="embm-settings--status">
        <p>
            <?php _e('You are connected to Untappd for Business as', EMBM_DOMAIN); ?>:
            <a
                href="<?php echo $href; ?>"
                target="_blank"
                class="embm-utfb--account-link"
                title="<?php echo $title; ?>"
            ><span
                class="dashicons dashicons-admin-users"
            ></span><strong><?php echo $account->name; ?></strong></a>
            <a href="#" class="embm-utfb--disconnect button button-small"><?php _e('Disconnect', EMBM_DOMAIN); ?></a>
        </p>
    </div>

    <table class="form-table">
        <tbody>
            <tr class="embm-utfb-section embm-utfb-section--location">
                <th scope="row"><?php _e('Select a Location', EMBM_DOMAIN); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-location-id"
                            name="embm-utfb-location-id"
                            class="embm-utfb--dropdown"
                            data-action="menu"
                        >
                            <option value="">-- <?php _e('Select', EMBM_DOMAIN); ?> --</option>
                            <?php foreach ($locations as $location) :  ?>
                                <option value="<?php echo $location->id; ?>"><?php echo $location->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a data-help="embm-utfb-integration" class="embm-settings--help">?</a>
                    </p>
                    <p class="description"><?php _e('Select a location to import data from.', EMBM_DOMAIN); ?></p>
                </td>
            </tr>
            <tr class="embm-utfb-section embm-utfb-section--menu">
                <th scope="row"><?php _e('Select or Import a Menu', EMBM_DOMAIN); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-menu-id"
                            name="embm-utfb-menu-id"
                            class="embm-utfb--dropdown"
                            data-action="section"
                        >
                            <option value="">-- <?php _e('Select', EMBM_DOMAIN); ?> --</option>
                        </select>
                        <button
                            type="button"
                            class="button button-primary embm-utfb--import"
                            data-resource="menu"
                        ><?php _e('Import Selected Menu', EMBM_DOMAIN); ?></button>
                    </p>
                    <p class="description"><?php _e('Import all beers in the selected menu.', EMBM_DOMAIN); ?></p>
                    <p class="embm-utfb-section--import-all">
                        <button
                            type="button"
                            class="button button-secondary embm-utfb--import"
                            data-resource="menu"
                        ><?php _e('Import All Menus', EMBM_DOMAIN); ?><span></span></button>
                    </p>
                    <p class="description"><?php _e('Imports all beers in all menus for the selected location.', EMBM_DOMAIN); ?></p>
                </td>
            </tr>
            <tr class="embm-utfb-section embm-utfb-section--section">
                <th scope="row"><?php _e('Select or Import a Section', EMBM_DOMAIN); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-section-id"
                            name="embm-utfb-section-id"
                            class="embm-utfb--dropdown"
                            data-action="beer"
                        >
                            <option value="">-- <?php _e('Select', EMBM_DOMAIN); ?> --</option>
                        </select>
                        <button
                            type="button"
                            class="button button-primary embm-utfb--import"
                            data-resource="section"
                        ><?php _e('Import Selected Section', EMBM_DOMAIN); ?></button>
                    </p>
                    <p class="description"><?php _e('Import all beers in the selected section.', EMBM_DOMAIN); ?></p>
                    <p class="embm-utfb-section--import-all">
                        <button
                            type="button"
                            class="button button-secondary embm-utfb--import"
                            data-resource="section"
                        ><?php _e('Import All Sections', EMBM_DOMAIN); ?><span></span></button>
                    </p>
                    <p class="description"><?php _e('Imports all beers in all sections for the selected menu.', EMBM_DOMAIN); ?></p>
                </td>
            </tr>
            <tr class="embm-utfb-section embm-utfb-section--beer">
                <th scope="row"><?php _e('Import Beer', EMBM_DOMAIN); ?></th>
                <td>
                    <p>
                        <select
                            id="embm-utfb-beer-id"
                            name="embm-utfb-beer-id"
                            class="embm-utfb--dropdown"
                        >
                            <option value="">-- <?php _e('Select', EMBM_DOMAIN); ?> --</option>
                        </select>
                        <button
                            type="button"
                            class="button button-primary embm-utfb--import"
                            data-resource="beer"
                        ><?php _e('Import Selected Beer', EMBM_DOMAIN); ?></button>
                    </p>
                    <p class="description"><?php _e('Imports only the selected beer.', EMBM_DOMAIN); ?></p>
                    <p class="embm-utfb-section--import-all">
                        <button
                            type="button"
                            class="button button-secondary embm-utfb--import"
                            data-resource="beer"
                        ><?php _e('Import All Beers', EMBM_DOMAIN); ?><span></span></button>
                    </p>
                    <p class="description"><?php _e('Imports all beers in the selected section.', EMBM_DOMAIN); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Sync Untappd for Business Data', EMBM_DOMAIN); ?></th>
                <td>
                    <p>
                        <button
                            type="button"
                            class="embm-utfb--sync button-secondary"
                        ><?php _e('Sync Data', EMBM_DOMAIN); ?></button>
                    </p>
                    <p>
                        <input
                            id="embm-utfb--sync-delete"
                            name="embm-utfb--sync-delete"
                            type="checkbox"
                        />
                        <label for="embm-utfb--sync-delete">
                            <?php _e('Delete Missing', EMBM_DOMAIN); ?>
                        </label>
                        <a data-help="embm-untappd-api-sync" class="embm-settings--help">?</a>
                    </p>
                    <p class="description">
                        <?php _e('A location must be selected to use this feature.', EMBM_DOMAIN); ?><br />
                        <?php _e('Associate existing beers with your Untappd for Business menus and update menu data.', EMBM_DOMAIN); ?><br />
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Refresh Untappd for Business Data', EMBM_DOMAIN); ?></th>
                <td>
                    <p><a href="#" class="embm-utfb--flush button-secondary"><?php _e('Flush Cache', EMBM_DOMAIN); ?></a></p>
                    <p class="description">
                        <?php _e('Update the data from Untappd for Business used in the above features. This is automatically done daily.', EMBM_DOMAIN); ?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>