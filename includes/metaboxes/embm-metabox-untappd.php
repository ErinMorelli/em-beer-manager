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
 * @package EMBM\Admin\Metabox\Untappd
 */


/**
 * Outputs Untappd metabox content
 *
 * @return void
 */
function EMBM_Admin_Metabox_untappd()
{
    // Don't load box if Untappd integration is disabled
    $ut_option = get_option('embm_options');
    if (isset($ut_option['embm_untappd_check']) && $ut_option['embm_untappd_check'] == '1') {
        return;
    }

    // Get global post object
    global $post;

    // Get current post custom data
    $beer_entry = get_post_custom($post->ID);
    $untappd_data = EMBM_Core_Beer_attr($post->ID, 'untappd_data');

    // Set custom post data values
    $untappd_id = isset($beer_entry['embm_untappd']) ? esc_attr($beer_entry['embm_untappd'][0]) : '';
    $show_rating = isset($beer_entry['embm_show_rating']) ? esc_attr($beer_entry['embm_show_rating'][0]) : false;
    $show_reviews = isset($beer_entry['embm_show_reviews']) ? esc_attr($beer_entry['embm_show_reviews'][0]) : false;
    $review_count = isset($beer_entry['embm_review_count']) ? esc_attr($beer_entry['embm_review_count'][0]) : 5;

    // Brewery account status
    $is_brewery = false;
    $api_root = '';
    $beer_found = false;

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // Make sure we're authorized
    if (null !== $token) {
        // Set API Root
        $api_root = EMBM_UNTAPPD_API_URL.$token;

        // Get Untappd user info
        $user = EMBM_Admin_Untappd_user($api_root);

        // Check for brewery account
        if ($user->account_type == 'brewery') {
            $is_brewery = true;

            // Get Untappd brewery ID
            $brewery_id = EMBM_Admin_Untappd_id($user->untappd_url);

            // Get Untappd brewery info from API
            $brewery = EMBM_Admin_Untappd_brewery($api_root, $brewery_id);

            // Make sure brewery is claimed by authorized user
            if (!$brewery->claimed_status->is_claimed || $brewery->claimed_status->uid != $user->uid) {
                $is_brewery = false;
            } else {
                // Get all the Untappd beers for the brewery
                $beer_list = EMBM_Admin_Untappd_beers($api_root, $brewery);

                // Look for beer in list
                if ($untappd_id !== '') {
                    $beer_found = !is_null(EMBM_Admin_Untappd_find($untappd_id, $beer_list));
                }
            }
        }
    }

    // Setup nonce field for options
    wp_nonce_field('embm_untappd_save', 'embm_untappd_save_nonce');

?>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td valign="top" <?php if (!$is_brewery) echo 'colspan="2"'; ?>>
                    <div class="embm-untappd--id">
                        <input type="hidden" name="embm-untappd-api-root" value="<?php echo $api_root; ?>" />
                        <p><label for="embm_untappd"><strong><?php _e('Beer ID', 'embm'); ?></strong></label><br />
                        <input
                            type="number"
                            name="embm_untappd"
                            id="embm_untappd"
                            data-value="<?php echo $untappd_id; ?>"
                            value="<?php echo $untappd_id; ?>"
                            <?php if ($is_brewery && $beer_found) echo 'readonly'; ?>
                        />
                        <a data-help="embm-untappd-beer-id" class="embm-settings--help">?</a></p>
                    </div>
                </td>
                <td valign="top" style="width:50%">
                <?php if ($is_brewery): ?>
                    <div class="embm-untappd--select">
                        <p><label for="untappd_id_select"><strong><?php _e('Brewery Beer', 'embm'); ?></strong></label><br />
                        <select id="untappd_id_select" name="untappd_id_select">
                            <option value=""
                                <?php selected($beer_found, false); ?>
                            >-- <?php _e('Custom/Unaffiliated', 'embm'); ?> --</option>
                        <?php foreach ($beer_list as $item) : $beer = $item->beer; ?>
                            <option
                                value="<?php echo $beer->bid; ?>"
                                <?php selected($untappd_id, $beer->bid); ?>
                            ><?php echo $beer->beer_name; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <p>
                        <input name="embm_show_reviews" id="embm_show_reviews" value="1" type="checkbox">
                        <label for="embm_show_reviews"><?php _e('Display Untappd reviews', 'embm'); ?></label>
                    </p>
                    <p>
                        <input type="number" name="embm_review_count" id="embm_review_count" value="<?php echo $review_count; ?>">
                        <label for="embm_review_count"><strong><?php _e('Number of reviews to display', 'embm'); ?></strong></label
                    </p>
                </td>
                <td valign="top">
                    <p>
                        <input name="embm_show_rating" id="embm_show_rating" value="1" type="checkbox">
                        <label for="embm_show_rating"><?php _e('Display Untappd rating', 'embm'); ?></label>
                    </p>
                    <p><?php
                        if ($untappd_data) {
                            $stars = EMBM_Admin_Metabox_Untappd_stars($untappd_data->rating_score);
                            printf('%s (%.2f) | %s Ratings', $stars, $untappd_data->rating_score, number_format($untappd_data->rating_count));
                        }
                    ?></p>
                </td>
            </tr>
        <?php if (!$untappd_data) : ?>
            <tr>
                <td valign="top" colspan="2">
                    <ul>
                        <li><?php echo $untappd_data->beer_name; ?></li>
                        <li><?php echo $untappd_data->beer_style; ?></li>
                        <li>ABV: <?php echo $untappd_data->beer_abv; ?>%</li>
                        <li>IBU: <?php echo $untappd_data->beer_ibu; ?></li>
                        <li>Rating: <?php printf('%.1f', $untappd_data->rating_score); ?> (<?php echo $untappd_data->rating_count; ?> ratings)</li>
                        <li>Brewery: <?php echo $untappd_data->brewery->brewery_name; ?></li>
                        <li>Check-ins: <?php echo $untappd_data->stats->total_count; ?> (<?php echo $untappd_data->stats->total_user_count; ?> users)</li>
                    </ul>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php
}

// Add Untappd metabox to main content
add_meta_box(
    'beer-untappd',
    __('Untappd', 'embm'),
    'EMBM_Admin_Metabox_untappd',
    'embm_beer',
    'normal',
    'core'
);


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
    if (!isset($_POST['embm_untappd_save_nonce']) || !wp_verify_nonce($_POST['embm_untappd_save_nonce'], 'embm_untappd_save')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post')) {
        return;
    }

    // Save input
    if (isset($_POST['embm_untappd'])) {
        $beer_id = esc_attr($_POST['embm_untappd']);
        $old_id = get_post_meta($post_id, 'embm_untappd', true);

        // Skip if this is not a new ID
        if ($beer_id !== $old_id) {
            // Save new ID
            update_post_meta($post_id, 'embm_untappd', $beer_id);

            // Get beer data from Untappd API
            if (isset($_POST['embm-untappd-api-root']) && $_POST['embm-untappd-api-root'] !== '') {
                EMBM_Admin_Untappd_beer($_POST['embm-untappd-api-root'], $beer_id, $post_id);
            }
        }
    }
    if (isset($_POST['embm_show_rating'])) {
        update_post_meta($post_id, 'embm_show_rating', esc_attr($_POST['embm_show_rating']));
    }
    if (isset($_POST['embm_show_reviews'])) {
        update_post_meta($post_id, 'embm_show_reviews', esc_attr($_POST['embm_show_reviews']));
    }
    if (isset($_POST['embm_review_count'])) {
        update_post_meta($post_id, 'embm_review_count', esc_attr($_POST['embm_review_count']));
    }
}

// Save untappd box inputs
add_action('save_post', 'EMBM_Admin_Metabox_Untappd_save');


/**
 * Generate star HTML from a given rating
 *
 * @param float $rating Untappd beer rating value
 *
 * @return void
 */
function EMBM_Admin_Metabox_Untappd_stars($rating)
{
    $full_count = floor($rating);
    $empty_count = (5 - $full_count);
    $has_half = (ceil($rating) > $full_count);

    if ($has_half) {
        $empty_count = 4 - $full_count;
    }

    $output = '';

    if ($full_count > 0) {
        foreach (range(1, $full_count) as $full_star) {
            error_log($full_star);
            $output .= '<span class="dashicons dashicons-star-filled"></span>';
        }
    }

    if ($has_half) {
        $output .= '<span class="dashicons dashicons-star-half"></span>';
    }

    if ($empty_count > 0) {
        foreach (range(1, $empty_count) as $empty_star) {
            error_log($empty_star);
            $output .= '<span class="dashicons dashicons-star-empty"></span>';
        }
    }

    return $output;
}
