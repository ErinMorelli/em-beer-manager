<?php
/**
 * Copyright (c) 2013-2021, Erin Morelli.
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
 * @package EMBM\Admin\Metabox\Menus
 */

/**
 * Display extra Menu fields
 *
 * @param object $taxonomy WP term taxonomy data
 *
 * @return string/html
 */
function EMBM_Admin_Metabox_menus($taxonomy)
{
    // Add UTFB metabox, if enabled
    if (EMBM_Core_Beer_disabled()) {
        return;
    }
?>
    <div class="form-field term-utfb-id">
        <label for="embm_utfb_id">
            <?php _e('Untappd for Business ID', 'em-beer-manager'); ?>
        </label>
        <input type="number" name="embm_utfb_id" id="embm_utfb_id" value="" />
    </div>
    <div class="form-field term-sync-exclude">
        <label for="embm_sync_exclude">
            <input
                name="embm_sync_exclude"
                id="embm_sync_exclude"
                value="1"
                type="checkbox"
            >
            <span><?php _e('Exclude from Sync', 'em-beer-manager'); ?></span>
        </label>
    </div>
<?php
}

// Add extra fields to form
add_action(EMBM_MENU.'_add_form_fields', 'EMBM_Admin_Metabox_menus');

/**
 * Display extra Menu fields
 *
 * @param object $term WP term data
 *
 * @return string/html
 */
function EMBM_Admin_Metabox_Menus_edit($term)
{
    // Add UTFB metabox, if enabled
    if (EMBM_Core_Beer_disabled()) {
        return;
    }

    // Get current term meta data
    $menu_meta = get_term_meta($term->term_id, EMBM_BEER_META, true);
    $menu_meta = (null == $menu_meta) ? array() : $menu_meta;

    // Parse attributes
    $utfb_id = array_key_exists('utfb_id', $menu_meta) ? esc_attr($menu_meta['utfb_id']) : '';
    $sync_exclude = array_key_exists('sync_exclude', $menu_meta) ? esc_attr($menu_meta['sync_exclude']) : '';

?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="embm_utfb_id">
                <?php _e('Untappd for Business ID', 'em-beer-manager'); ?>
            </label>
        </th>
        <td>
            <input type="number" name="embm_utfb_id" id="embm_utfb_id" value="<?php echo $utfb_id; ?>" />
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="embm_sync_exclude">
                <strong><?php _e('Exclude from Sync', 'em-beer-manager'); ?></strong>
            </label>
        </th>
        <td>
            <input
                name="embm_sync_exclude"
                id="embm_sync_exclude"
                value="1"
                type="checkbox"
                <?php checked('1', $sync_exclude); ?>
            />
        </td>
    </tr>
<?php
}

// Add extra fields to form
add_action(EMBM_MENU.'_edit_form_fields', 'EMBM_Admin_Metabox_Menus_edit');

/**
 * Save or update metadata for the Menu
 *
 * @param int $term_id WP term ID
 *
 * @return void
 */
function EMBM_Core_Metabox_Menus_save($term_id)
{
    // Get current term meta data
    $menu_meta = get_term_meta($term_id, EMBM_BEER_META, true);
    $menu_meta = (null == $menu_meta) ? array() : $menu_meta;

    // Store old UTFB ID
    $old_utfb_id = array_key_exists('utfb_id', $menu_meta) ? $menu_meta['utfb_id'] : null;

    // Get list of term attrs
    $menu_attrs = array('utfb_id', 'sync_exclude');

    // Save inputs
    foreach ($menu_attrs as $menu_attr) {
        $menu_meta[$menu_attr] = isset($_POST['embm_'.$menu_attr]) ? esc_attr($_POST['embm_'.$menu_attr]) : null;
    }

    // Update post meta
    update_term_meta($term_id, EMBM_BEER_META, $menu_meta);

    // Check if we need to get new UTFB data
    if (isset($_POST['embm_utfb_id']) && ($old_utfb_id !== $menu_meta['utfb_id'])) {
        // Get UTFB credentials
        $auth = get_option(EMBM_UTFB_CREDENTIALS);
        if (null == $auth) {
            return;
        }

        // Get UTFB query data
        $new_utfb_id = esc_attr($_POST['embm_utfb_id']);
        $is_section = ($_POST['parent'] > 0);

        // Get UFTB data based on Menu/Section
        $utfb_data = EMBM_Admin_Utfb_Menu_term($auth, $term_id, $new_utfb_id, $is_section, true);

        if (is_null($utfb_data) || is_string($utfb_data)) {
            $errors = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['save_errors']);
            if (is_array($errors) && !array_key_exists('2', $errors)) {
                array_push($errors, '2');
            } else {
                $errors = array('2');
            }
            set_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['save_errors'], $errors, HOUR_IN_SECONDS);
            return;
        }

        // Remove menu data if the ID is unset
        if ($new_utfb_id == '') {
            $menu_meta['utfb_id'] = null;
            update_term_meta($term_id, EMBM_BEER_META, $menu_meta);
        }
    }
}

// Add save and update action hooks
add_action('created_'.EMBM_MENU, 'EMBM_Core_Metabox_Menus_save', 10, 2);
add_action('edited_'.EMBM_MENU, 'EMBM_Core_Metabox_Menus_save', 10, 2);

/**
 * Add new columns to the Menu list view
 *
 * @param array $columns Existing Menu column data
 *
 * @return array
 */
function EMBM_Admin_Metabox_Menus_column($columns)
{
    // Add UTFB columns, if enabled
    if (!EMBM_Core_Beer_disabled()) {
        $columns['embm_utfb_id'] = __('Untappd for Business ID', 'em-beer-manager');
        $columns['embm_sync_exclude'] = __('Exclude from Sync', 'em-beer-manager');
    }
    return $columns;
}

// Add filter to Menu taxonomy page
add_filter('manage_edit-'.EMBM_MENU.'_columns', 'EMBM_Admin_Metabox_Menus_column');

/**
 * Populate custom Menu column data
 *
 * @param string $content     Content of the current column
 * @param string $column_name Name of the current column
 * @param int    $term_id     WP term ID for Menu
 *
 * @return string
 */
function EMBM_Admin_Metabox_Menus_Column_content($content, $column_name, $term_id)
{
    $term_id = absint($term_id);
    $menu_meta = get_term_meta($term_id, EMBM_BEER_META, true);
    $menu_meta = (null == $menu_meta) ? array() : $menu_meta;

    if ($column_name == 'embm_utfb_id') {
        $utfb_id = array_key_exists('utfb_id', $menu_meta) ? esc_attr($menu_meta['utfb_id']) : '';
        $content .= $utfb_id;
    }

    if ($column_name == 'embm_sync_exclude') {
        $sync_exclude = array_key_exists('sync_exclude', $menu_meta) ? esc_attr($menu_meta['sync_exclude']) : '';
        $content .= (null == $sync_exclude) ? '' : '<span class="dashicons dashicons-yes"></span>';
    }

    return $content;
}

// Add filter to Menu taxonomy page
add_filter('manage_'.EMBM_MENU.'_custom_column', 'EMBM_Admin_Metabox_Menus_Column_content', 10, 3);

/**
 * Make new Menu columns sortable
 *
 * @param array $sortable Array of sortable column names
 *
 * @return array
 */
function EMBM_Admin_Metabox_Menus_Column_sortable($sortable)
{
    // Add sortable UTFB columns, if enabled
    if (!EMBM_Core_Beer_disabled()) {
        $sortable['embm_utfb_id'] = 'embm_utfb_id';
        $sortable['embm_sync_exclude'] = 'embm_sync_exclude';
    }
    return $sortable;
}

// Add filter to Menu taxonomy page
add_filter('manage_edit-'.EMBM_MENU.'_sortable_columns', 'EMBM_Admin_Metabox_Menus_Column_sortable');
