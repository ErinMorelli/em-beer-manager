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
        __('Extra Beer Information', 'em-beer-manager'),
        'EMBM_Admin_Metabox_Extras_content',
        EMBM_BEER,
        'normal',
        'core'
    );
}

// Add to beer post editor
add_action('add_meta_boxes_'.EMBM_BEER, 'EMBM_Admin_Metabox_extras');

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
    $beer_entry = get_post_meta($post->ID, EMBM_BEER_META, true);
    $beer_entry = (null == $beer_entry) ? array() : $beer_entry;

    // Set custom post data values
    $b_num = array_key_exists('beer_num', $beer_entry) ? esc_attr($beer_entry['beer_num']) : '';
    $b_avail = array_key_exists('avail', $beer_entry) ? esc_attr($beer_entry['avail']) : '';
    $b_notes = array_key_exists('notes', $beer_entry) ? esc_html($beer_entry['notes']) : '';

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
                <label for="embm_beer_num"><strong><?php _e('Beer Number', 'em-beer-manager'); ?></strong></label><br />
                <input type="number" name="embm_beer_num" id="embm_beer_num" min="0000" max="9999" step="1" value="<?php echo $b_num; ?>" />
            </p>
        </div>
        <div class="embm-metabox__field embm-metabox--extras-avail">
            <p>
                <label for="embm_avail"><strong><?php _e('Availability', 'em-beer-manager'); ?></strong></label><br />
                <input type="text" name="embm_avail" id="embm_avail" value="<?php echo $b_avail; ?>" />
            </p>
        </div>
    </div>
    <div class="embm-metabox__right">
        <div class="embm-metabox--extras-notes">
            <p class="embm-metabox--extras-notes-title">
                <label for="embm_notes"><strong><?php _e('Additional Notes/Food Pairings', 'em-beer-manager'); ?></strong></label>
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

    // Get current post meta
    $beer_meta = get_post_meta($post_id, EMBM_BEER_META, true);
    $beer_meta = (null == $beer_meta) ? array() : $beer_meta;

    // Get list of attrs
    $beer_attrs = array('beer_num', 'avail', 'notes');

    // Save inputs
    foreach ($beer_attrs as $beer_attr) {
        $beer_meta[$beer_attr] = isset($_POST['embm_'.$beer_attr]) ? esc_attr($_POST['embm_'.$beer_attr]) : null;
    }

    // Update post meta
    update_post_meta($post_id, EMBM_BEER_META, $beer_meta);
}

// Save Beer meta box inputs
add_action('save_post', 'EMBM_Admin_Metabox_Extras_save');
