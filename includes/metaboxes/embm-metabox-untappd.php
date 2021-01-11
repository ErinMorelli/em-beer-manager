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
 * @package EMBM\Admin\Metabox\Untappd
 */

/**
 * Add the Untappd metabox to the Beer post type
 *
 * @return void
 */
function EMBM_Admin_Metabox_untappd()
{
    // Don't load box if Untappd integration is disabled
    if (EMBM_Core_Beer_disabled()) {
        return;
    }

    // Add Untappd metabox to main content
    add_meta_box(
        'embm_beer_untappd',
        __('Untappd', 'em-beer-manager'),
        'EMBM_Admin_Metabox_Untappd_content',
        EMBM_BEER,
        'normal',
        'core'
    );
}

// Add to beer post editor
add_action('add_meta_boxes_'.EMBM_BEER, 'EMBM_Admin_Metabox_untappd');

/**
 * Outputs Untappd metabox content
 *
 * @return void
 */
function EMBM_Admin_Metabox_Untappd_content()
{
    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_meta($post->ID, EMBM_BEER_META, true);
    $beer_entry = (null == $beer_entry) ? array() : $beer_entry;
    $untappd_data = EMBM_Core_Beer_untappd($post->ID);
    $utfb_data = EMBM_Core_Beer_utfb($post->ID);

    // Set custom post data values
    $untappd_id = array_key_exists('untappd_id', $beer_entry) ? esc_attr($beer_entry['untappd_id']) : '';
    $hide_rating = array_key_exists('hide_rating', $beer_entry) ? esc_attr($beer_entry['hide_rating']) : '';
    $hide_reviews = array_key_exists('hide_reviews', $beer_entry) ? esc_attr($beer_entry['hide_reviews']) : '';
    $reviews_count = array_key_exists('reviews_count', $beer_entry) ? esc_attr($beer_entry['reviews_count']) : '5';
    $sync_exclude = array_key_exists('sync_exclude', $beer_entry) ? esc_attr($beer_entry['sync_exclude']) : '';

    // Brewery account status
    $is_brewery = false;
    $api_root = '';
    $beer_found = false;
    $show_api_error = (null !== $untappd_data && !is_object($untappd_data));

    // Check for UTFB account
    if (is_array($utfb_data) && !empty($utfb_data)) {
        // Mark beer as found
        $beer_found = true;

        // Get the UTFB menus
        $terms = wp_get_object_terms($post->ID, EMBM_MENU, array('order' => 'DESC'));

        // Get all of the top level menus
        $menus = array_filter(
            $terms, function ($term) {
                return !$term->parent;
            }
        );

        // Get child sections for each menu
        foreach ($menus as $menu) {
            $menu->sections = array_filter(
                $terms, function ($term) use ($menu) {
                    return $term->parent == $menu->term_id;
                }
            );
        }
    }

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Make sure we're authorized
    if (null !== $token && !$show_api_error) {
        // Set API Root
        $api_root = EMBM_UNTAPPD_API_URL.$token;

        // Get Untappd user info
        $user = EMBM_Admin_Untappd_user($api_root);
        $show_api_error = (is_null($user) || is_string($user));

        // Check for brewery account
        if ($user->account_type == 'brewery' && !$show_api_error) {
            $is_brewery = true;

            // Get Untappd brewery ID
            $brewery_id = EMBM_Admin_Untappd_id($user->untappd_url);

            // Get Untappd brewery info from API
            if ($brewery_id && !$show_api_error) {
                $brewery = EMBM_Admin_Untappd_brewery($api_root, $brewery_id);
                $show_api_error = (is_null($brewery) || is_string($brewery));

                // Make sure brewery is claimed by authorized user
                if (!$brewery->claimed_status->is_claimed || $brewery->claimed_status->uid != $user->uid) {
                    $is_brewery = false;
                } elseif (!$show_api_error) {
                    // Get all the Untappd beers for the brewery
                    $beer_list = EMBM_Admin_Untappd_beers($api_root, $brewery);
                    $show_api_error = (is_null($brewery) || is_string($brewery));

                    // Look for beer in list
                    if ($untappd_id !== '' && !$show_api_error) {
                        $beer_found = !is_null(EMBM_Admin_Untappd_find($untappd_id, $beer_list));
                    }
                }
            }
        }
    }

    // Get ratings formats
    $rating_formats = EMBM_Core_Beer_ratings();

    // Set reviews_count input
    $reviews_count_input = sprintf(
        __('Show %s checkins (max. %d)', 'em-beer-manager'),
        '<input
            id="embm_reviews_count"
            name="embm_reviews_count"
            class="small-text"
            type="number"
            min="1"
            max="15"
            value="'.$reviews_count.'"
        />', 15
    );

    // Setup nonce field for options
    wp_nonce_field('embm_untappd_save', '_embm_untappd_save_nonce');

?>
<div class="embm-metabox embm-metabox--untappd">
    <div class="embm-metabox__left">
        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
        <div class="embm-metabox__field embm-metabox--untappd-id">
            <p>
                <label for="embm_untappd"><strong><?php _e('Beer ID', 'em-beer-manager'); ?></strong></label><br />
                <input
                    type="number"
                    name="embm_untappd"
                    id="embm_untappd"
                    data-value="<?php echo $untappd_id; ?>"
                    value="<?php echo $untappd_id; ?>"
                <?php if (($is_brewery || $utfb_id !== '') && $beer_found && !$show_api_error) : ?>
                    readonly
                <?php endif; ?>
                />
                <a data-help="embm-untappd-beer-id" class="embm-settings--help">?</a>
            </p>
        </div>
        <div class="embm-metabox__field embm-metabox--untappd-select">
            <?php if ($is_brewery && !$show_api_error) : ?>
                <p>
                    <label for="untappd_id_select"><strong><?php _e('Brewery Beer', 'em-beer-manager'); ?></strong></label><br />
                    <select id="untappd_id_select" name="untappd_id_select">
                        <option value=""
                            <?php selected($beer_found, false); ?>
                        >-- <?php _e('Custom/Unaffiliated', 'em-beer-manager'); ?> --</option>
                    <?php foreach ($beer_list as $item) : $beer = $item->beer; ?>
                        <option
                            value="<?php echo $beer->bid; ?>"
                            <?php selected($untappd_id, $beer->bid); ?>
                        ><?php echo $beer->beer_name; ?></option>
                    <?php endforeach; ?>
                    </select>
                </p>
            <?php endif; ?>
        </div>
        <div class="embm-metabox__field embm-metabox--utfb">
            <?php if (null !== $utfb_data && !$show_api_error) : ?>
                <p>
                    <strong><?php _e('Untappd for Business Menus', 'em-beer-manager'); ?></strong><br />
                    <ul>
                        <?php foreach ($menus as $menu): ?>
                            <li>
                                <a
                                    href="<?php echo get_term_link($menu->slug, EMBM_MENU); ?>"
                                    title="<?php echo esc_html($menu->name); ?>"
                                ><?php echo esc_html($menu->name); ?></a>
                                <?php if (property_exists($menu, 'sections')) : ?>
                                    <ul>
                                        <?php foreach ($menu->sections as $section): ?>
                                            <li>
                                                <a
                                                    href="<?php echo get_term_link($section->slug, EMBM_MENU); ?>"
                                                    title="<?php echo esc_html($section->name); ?>"
                                                ><?php echo esc_html($section->name); ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <div class="embm-metabox__right">
    <?php if (null !== $token && $untappd_id !== '' && !$show_api_error) : ?>
        <div class="embm-metabox--untappd-checkboxes">
            <p>
                <strong><?php printf('Override Display Settings', 'em-beer-manager'); ?></strong>
            </p>
            <div class="embm-metabox--untappd-rating">
                <p>
                    <input
                        name="embm_hide_rating"
                        id="embm_hide_rating"
                        value="1"
                        type="checkbox"
                        <?php checked('1', $hide_rating); ?>
                    >
                    <label for="embm_hide_rating"><?php _e('Hide Untappd rating', 'em-beer-manager'); ?></label>
                </p>
            </div>
            <div class="embm-metabox--untappd-reviews">
                <p>
                    <input
                        name="embm_hide_reviews"
                        id="embm_hide_reviews"
                        value="1"
                        type="checkbox"
                        <?php checked('1', $hide_reviews); ?>
                    >
                    <label for="embm_hide_reviews"><?php _e('Hide Untappd checkins', 'em-beer-manager'); ?></label>
                </p>
                <p class="embm-metabox--untappd-review-count">
                    <label for="embm_reviews_count_style"><?php echo $reviews_count_input; ?></label>
                </p>
            </div>
        </div>
        <div class="embm-metabox--untappd-actions">
            <div class="embm-metabox--untappd-flush">
                <p>
                    <strong><?php _e('Refresh Untappd Beer Data', 'em-beer-manager'); ?></strong>
                </p>
                <p>
                    <a href="#" class="button-secondary" data-api-root="<?php echo $api_root; ?>">
                        <?php _e('Flush Cache', 'em-beer-manager'); ?>
                    </a>
                </p>
                <p class="description">
                    <?php _e('This is automatically done daily.', 'em-beer-manager'); ?>
                </p>
            </div>
            <div class="embm-metabox--untappd-sync">
                <p>
                    <strong><?php _e('Sync Untappd Beer Data', 'em-beer-manager'); ?></strong>
                </p>
                <p>
                    <a href="#" class="button-secondary" data-api-root="<?php echo $api_root; ?>">
                        <?php _e('Sync Data', 'em-beer-manager'); ?>
                    </a>
                </p>
                <p class="description">
                    <span class="warning"><?php _e('WARNING', 'em-beer-manager'); ?>:</span>
                    <?php _e('This will override any changes you have made to this beer.', 'em-beer-manager'); ?>
                </p>
                <p>
                    <input
                        name="embm_sync_exclude"
                        id="embm_sync_exclude"
                        value="1"
                        type="checkbox"
                        <?php checked('1', $sync_exclude); ?>
                    >
                    <label for="embm_sync_exclude">
                        <strong><?php _e('Exclude from Sync', 'em-beer-manager'); ?></strong>
                    </label>
                </p>
            </div>
        </div>
    <?php elseif ($show_api_error && null !== $token) : ?>
        <?php EMBM_Admin_Notices_ratelimit(null); ?>
    <?php elseif ($untappd_id == '') : ?>
        <p class="embm-metabox--untappd-empty">
            <?php _e('Set a valid Untappd Beer ID to access additional display options.', 'em-beer-manager'); ?>
        </p>
    <?php else : ?>
        <p class="embm-metabox--untappd-empty">
            <?php
                printf(
                    __('Log in to Untappd on the %s to access additional display options.', 'em-beer-manager'),
                    sprintf(
                        '<a href="%s">%s</a>',
                        get_admin_url(null, 'options-general.php?page=embm-settings'),
                        __('settings page', 'em-beer-manager')
                    )
                );
            ?>
        </p>
    <?php endif; ?>
    </div>
</div>
<?php
}

/**
 * Save the options from the Untappd metabox
 *
 * @param int $post_id WP post ID
 *
 * @return void
 */
function EMBM_Admin_Metabox_Untappd_save($post_id)
{
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Validate nonce
    if (!isset($_POST['_embm_untappd_save_nonce']) || !wp_verify_nonce($_POST['_embm_untappd_save_nonce'], 'embm_untappd_save')) {
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
    $beer_attrs = array('hide_rating', 'rating_format', 'hide_reviews', 'reviews_count', 'sync_exclude');

    // Save inputs
    foreach ($beer_attrs as $beer_attr) {
        $beer_meta[$beer_attr] = isset($_POST['embm_'.$beer_attr]) ? esc_attr($_POST['embm_'.$beer_attr]) : null;
    }

    // Update post meta
    update_post_meta($post_id, EMBM_BEER_META, $beer_meta);

    // Handle new Untappd ID separately
    if (isset($_POST['embm_untappd'])) {
        $beer_id = esc_attr($_POST['embm_untappd']);
        $old_id = array_key_exists('untappd_id', $beer_meta) ? $beer_meta['untappd_id'] : null;

        // Skip if this is not a new ID
        if ($beer_id !== $old_id) {
            // Save new ID
            $beer_meta['untappd_id'] = $beer_id;
            update_post_meta($post_id, EMBM_BEER_META, $beer_meta);

            // Get beer data from Untappd API
            if (isset($_POST['embm-untappd-api-root']) && $_POST['embm-untappd-api-root'] !== '' && $beer_id !== '') {
                $res = EMBM_Admin_Untappd_beer($_POST['embm-untappd-api-root'], $beer_id, $post_id, true);
                if (is_null($res) || is_string($res)) {
                    $errors = get_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['save_errors']);
                    if (is_array($errors) && !array_key_exists('1', $errors)) {
                        array_push($errors, '1');
                    } else {
                        $errors = array('1');
                    }
                    set_transient($GLOBALS[EMBM_UNTAPPD_CACHE]['save_errors'], $errors, HOUR_IN_SECONDS);
                    return;
                }
            }

            // Remove beer data if the ID is unset
            if ($beer_id == '') {
                $beer_meta['untappd_id'] = null;
                update_post_meta($post_id, EMBM_BEER_META, $beer_meta);
            }
        }
    }
}

// Save untappd box inputs
add_action('save_post', 'EMBM_Admin_Metabox_Untappd_save');
