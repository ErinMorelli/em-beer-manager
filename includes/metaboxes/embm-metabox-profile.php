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
 * @package EMBM\Admin\Metabox\Profile
 */

/**
 * Add the Beer Profile metabox to the Beer post type
 *
 * @return void
 */
function EMBM_Admin_Metabox_profile()
{
    // Add Beer Profile metabox to sidebar
    add_meta_box(
        'embm_beer_profile',
        __('Beer Profile', 'embm'),
        'EMBM_Admin_Metabox_Profile_content',
        'embm_beer',
        'side',
        'core'
    );
}

// Add to beer post editor
add_action('add_meta_boxes_embm_beer', 'EMBM_Admin_Metabox_profile');

/**
 * Outputs Beer Profile metabox content
 *
 * @return void
 */
function EMBM_Admin_Metabox_Profile_content()
{
    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_custom($post->ID);

    // Set custom post data values
    $b_malts = isset($beer_entry['embm_malts']) ? esc_attr($beer_entry['embm_malts'][0]) : '';
    $b_hops = isset($beer_entry['embm_hops']) ? esc_attr($beer_entry['embm_hops'][0]) : '';
    $b_adds= isset($beer_entry['embm_adds']) ? esc_attr($beer_entry['embm_adds'][0]) : '';
    $b_yeast = isset($beer_entry['embm_yeast']) ? esc_attr($beer_entry['embm_yeast'][0]) : '';
    $b_ibu = isset($beer_entry['embm_ibu']) ? esc_attr($beer_entry['embm_ibu'][0]) : '0';
    $b_abv = isset($beer_entry['embm_abv']) ? esc_attr($beer_entry['embm_abv'][0]) : '0';

    // Setup nonce field for options
    wp_nonce_field('embm_profile_save', '_embm_profile_save_nonce');

?>
<div class="embm-metabox embm-metabox--profile">
    <div class="embm-metabox__field embm-metabox--profile-malts">
        <p>
            <label for="embm_malts"><strong><?php _e('Malts', 'embm'); ?></strong></label><br />
            <input type="text" name="embm_malts" id="embm_malts" style="width:100%;" value="<?php echo $b_malts; ?>" />
        </p>
    </div>
    <div class="embm-metabox__field embm-metabox--profile-hops">
        <p>
            <label for="embm_hops"><strong><?php _e('Hops', 'embm'); ?></strong></label><br />
            <input type="text" name="embm_hops" id="embm_hops" style="width:100%;" value="<?php echo $b_hops; ?>" />
        </p>
    </div>
    <div class="embm-metabox__field embm-metabox--profile-adds">
        <p>
            <label for="embm_adds"><strong><?php _e('Additions/Spices', 'embm'); ?></strong></label><br />
            <input type="text" name="embm_adds" id="embm_adds" style="width:100%;" value="<?php echo $b_adds; ?>" />
        </p>
    </div>
    <div class="embm-metabox__field embm-metabox--profile-yeast">
        <p>
            <label for="embm_yeast"><strong><?php _e('Yeast', 'embm'); ?></strong></label><br />
            <input type="text" name="embm_yeast" id="embm_yeast" style="width:100%;" value="<?php echo $b_yeast; ?>" />
        </p>
    </div>
    <hr />
    <div class="embm-metabox__field embm-metabox--profile-abv">
        <p>
            <label for="embm_abv"><strong><?php _e('ABV', 'embm'); ?></strong></label><br />
            <input type="number" name="embm_abv" id="embm_abv" min="0.0" max="100.0" step="0.1" value="<?php echo $b_abv; ?>" /> %
        </p>
    </div>
    <div class="embm-metabox__field embm-metabox--profile-ibu">
        <p>
            <label for="embm_ibu"><strong><?php _e('IBU', 'embm'); ?></strong></label><br />
            <input type="number" name="embm_ibu" id="embm_style" min="0" max="100" step="1" value="<?php echo $b_ibu; ?>" />
        </p>
    </div>
</div>
<?php
}

/**
 * Save the options from the Beer Profile metabox
 *
 * @param int $post_id WP post ID
 *
 * @return void
 */
function EMBM_Admin_Metabox_Profile_save($post_id)
{
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Validate nonce
    if (!isset($_POST['_embm_profile_save_nonce']) || !wp_verify_nonce($_POST['_embm_profile_save_nonce'], 'embm_profile_save')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save input
    if (isset($_POST['embm_malts'])) {
        update_post_meta($post_id, 'embm_malts', esc_attr($_POST['embm_malts']));
    }
    if (isset($_POST['embm_hops'])) {
        update_post_meta($post_id, 'embm_hops', esc_attr($_POST['embm_hops']));
    }
    if (isset($_POST['embm_adds'])) {
        update_post_meta($post_id, 'embm_adds', esc_attr($_POST['embm_adds']));
    }
    if (isset($_POST['embm_yeast'])) {
        update_post_meta($post_id, 'embm_yeast', esc_attr($_POST['embm_yeast']));
    }
    if (isset($_POST['embm_ibu'])) {
        update_post_meta($post_id, 'embm_ibu', esc_attr($_POST['embm_ibu']));
    }
    if (isset($_POST['embm_abv'])) {
        update_post_meta($post_id, 'embm_abv', esc_attr($_POST['embm_abv']));
    }
}

// Save Beer Profile metabox inputs
add_action('save_post', 'EMBM_Admin_Metabox_Profile_save');
