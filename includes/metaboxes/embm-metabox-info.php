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
 * @package EMBM\Admin\Metabox\Info
 */


/**
 * Outputs More Beer Information metabox content
 *
 * @return void
 */
function EMBM_Admin_Metabox_info()
{
    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_custom($post->ID);

    // Set custom post data values
    $b_num = isset($beer_entry['embm_beer_num']) ? esc_attr($beer_entry['embm_beer_num'][0]) : '';
    $b_avail = isset($beer_entry['embm_avail']) ? esc_attr($beer_entry['embm_avail'][0]) : '';
    $b_notes = isset($beer_entry['embm_notes']) ? esc_attr($beer_entry['embm_notes'][0]) : '';

    // Setup nonce field for options
    wp_nonce_field('embm_info_save', 'embm_info_save_nonce');

?>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td valign="top" style="width:40%">
                    <div class="embm-more-info">
                        <p><label for="embm_beer_num"><strong><?php _e('Beer Number', 'embm'); ?></strong></label><br />
                        <input type="number" name="embm_beer_num" id="embm_beer_num" min="000" max="999" step="1" value="<?php echo $b_num; ?>" /></p>
                    </div>
                </td>
                <td valign="top" rowspan="3" style="width:60%">
                    <p><label for="embm_notes"><strong><?php _e('Additional Notes/Food Pairings', 'embm'); ?></strong></label><br />
                    <textarea name="embm_notes" id="embm_notes" rows="7" style="width:100%"><?php echo $b_notes; ?></textarea></p>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <div class="embm-more-info">
                        <p><label for="embm_avail"><strong><?php _e('Availability', 'embm'); ?></strong></label><br />
                        <input type="text" name="embm_avail" id="embm_avail" value="<?php echo $b_avail; ?>" /></p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}

// Add More Beer Information metabox to main content
add_meta_box(
    'beer-info',
    __('More Beer Information', 'embm'),
    'EMBM_Admin_Metabox_info',
    'embm_beer',
    'normal',
    'core'
);


/**
 * Save the options from the More Beer Information metabox
 *
 * @param int $post_id WP post ID
 *
 * @return void
 */
function EMBM_Admin_Metabox_Info_save($post_id)
{
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Validate nonce
    if (!isset($_POST['embm_info_save_nonce']) || !wp_verify_nonce($_POST['embm_info_save_nonce'], 'embm_info_save')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post')) {
        return;
    }

    // Save input
    if (isset($_POST['embm_beer_num'])) {
        update_post_meta($post_id, 'embm_beer_num', esc_attr($_POST['embm_beer_num']));
    }
    if (isset($_POST['embm_avail'])) {
        update_post_meta($post_id, 'embm_avail', esc_attr($_POST['embm_avail']));
    }
    if (isset($_POST['embm_notes'])) {
        update_post_meta($post_id, 'embm_notes', esc_attr($_POST['embm_notes']));
    }
}

// Save Beer meta box inputs
add_action('save_post', 'EMBM_Admin_Metabox_Info_save');
