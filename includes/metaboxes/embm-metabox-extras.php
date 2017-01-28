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
 * @package EMBM\Admin\Metabox\Extras
 */

/**
 * Add the Beer Information metabox to the Beer post type
 *
 * @return void
 */
function EMBM_Admin_Metabox_extras()
{
    // Add More Beer Information metabox to main content
    add_meta_box(
        'embm_beer_extras',
        __('Extra Beer Information', 'embm'),
        'EMBM_Admin_Metabox_Extras_content',
        'embm_beer',
        'normal',
        'core'
    );
}

// Add to beer post editor
add_action('add_meta_boxes_embm_beer', 'EMBM_Admin_Metabox_extras');

/**
 * Outputs More Beer Information metabox content
 *
 * @return void
 */
function EMBM_Admin_Metabox_Extras_content()
{
    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_custom($post->ID);

    // Set custom post data values
    $b_num = isset($beer_entry['embm_beer_num']) ? esc_attr($beer_entry['embm_beer_num'][0]) : '';
    $b_avail = isset($beer_entry['embm_avail']) ? esc_attr($beer_entry['embm_avail'][0]) : '';
    $b_notes = isset($beer_entry['embm_notes']) ? esc_html($beer_entry['embm_notes'][0]) : '';

    // Setup nonce field for options
    wp_nonce_field('embm_extras_save', '_embm_extras_save_nonce');

    // Set up notes field settings
    $notes_settings = array(
        'media_buttons' => false,
        'textarea_rows' => 7,
        'tinymce'       => false,
        'quicktags'     => true,
    );

?>
<div class="embm-metabox embm-metabox--extras">
    <div class="embm-metabox__left">
        <div class="embm-metabox__field embm-metabox--extras-num">
            <p>
                <label for="embm_beer_num"><strong><?php _e('Beer Number', 'embm'); ?></strong></label><br />
                <input type="number" name="embm_beer_num" id="embm_beer_num" min="0000" max="9999" step="1" value="<?php echo $b_num; ?>" />
            </p>
        </div>
        <div class="embm-metabox__field embm-metabox--extras-avail">
            <p>
                <label for="embm_avail"><strong><?php _e('Availability', 'embm'); ?></strong></label><br />
                <input type="text" name="embm_avail" id="embm_avail" value="<?php echo $b_avail; ?>" />
            </p>
        </div>
    </div>
    <div class="embm-metabox__right">
        <div class="embm-metabox--extras-notes">
            <p class="embm-metabox--extras-notes-title">
                <label for="embm_notes"><strong><?php _e('Additional Notes/Food Pairings', 'embm'); ?></strong></label>
            </p>
            <?php wp_editor($b_notes, 'embm_notes', $notes_settings); ?>
        </div>
    </div>
</div>
<?php
}

/**
 * Save the options from the More Beer Information metabox
 *
 * @param int $post_id WP post ID
 *
 * @return void
 */
function EMBM_Admin_Metabox_Extras_save($post_id)
{
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Validate nonce
    if (!isset($_POST['_embm_extras_save_nonce']) || !wp_verify_nonce($_POST['_embm_extras_save_nonce'], 'embm_extras_save')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
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
        update_post_meta($post_id, 'embm_notes', esc_html($_POST['embm_notes']));
    }
}

// Save Beer meta box inputs
add_action('save_post', 'EMBM_Admin_Metabox_Extras_save');
