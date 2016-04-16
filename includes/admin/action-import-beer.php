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
 * @package EMBM\Admin\ImportBeer
 */


// Load WP Functions
require '../../../../../wp-load.php';

// Check that user is logged in
if (!is_user_logged_in()) {
    wp_redirect(get_admin_url());
    exit;
}

// Check that the current user has permission to access this page
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'embm'));
}

/**
 * Insert post from Untappd
 *
 * @param string $beer_url   Untappd API URI to GET beer
 * @param string $brewery_id Untappd brewery account ID
 *
 * @return void
 */
function EMBM_Admin_Import_beer($beer_url, $brewery_id)
{
    // Force PHP to throw an exception on warnings
    set_error_handler(
        function ($errno, $errstr, $errfile, $errline, array $errcontext) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    );

    try {
        // Make GET request to API
        $beer_contents = file_get_contents($beer_url);
    } catch (Exception $e) {
        // Return to EMBM settings page to show error & exit
        wp_redirect(get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-error=1#labs'));
        exit;
    }

    // Reset error handler
    restore_error_handler();

    // Decode API request from JSON
    $beer_res = json_decode($beer_contents);

    // Extract beer info from results
    $beer = $beer_res->response->beer;

    // Check that beer is owned by brewery, if needed
    if (isset($_POST['embm-untappd-brewery-check'])) {
        // Compare beer's brewery ID to user's brewery ID
        if ($beer->brewery->brewery_id != intval($brewery_id)+1) {
            // Return to EMBM settings page to show error & exit
            wp_redirect(get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-error=3#labs'));
            exit;
        }
    }

    // Set beer slug
    $beer_slug = sanitize_title($beer->beer_name);

    // Set up args for duplicate check
    $dup_args = array(
        'name'           => $beer_slug,
        'post_type'      => 'embm_beer',
        'post_status'    => 'publish',
        'posts_per_page' => 1
    );

    // Run post query
    $beer_exists = get_posts($dup_args);

    // If we found a beer, exit
    if ($beer_exists) {
        return;
    }

    // Set post publish date from Untappd created date
    $beer_date = date('Y-m-d H:i:s', strtotime($beer->created_at));

    // Set up post array
    $new_beer_post = array(
        'post_author'   => get_current_user_id(),
        'post_title'    => $beer->beer_name,
        'post_name'     => $beer_slug,
        'post_content'  => $beer->beer_description,
        'post_date'     => $beer_date,
        'post_status'   => 'publish',
        'post_type'     => 'embm_beer',
        'tax_input'     => array(
            'embm_style'    => $beer->beer_style
        ),
        'meta_input'    => array(
            'abv'       => $beer->beer_abv,
            'ibu'       => $beer->beer_ibu,
            'untappd'   => $beer->bid
        )
    );

    // Insert post
    $post_id = wp_insert_post($new_beer_post, true);

    // Add post image
    EMBM_Admin_Import_Beer_image($post_id, $beer);
}


/**
 * Upload and set beer featured image
 *
 * @param int    $post_id The beer post ID
 * @param object $beer    Beer object from Untappd API
 *
 * @return void
 */
function EMBM_Admin_Import_Beer_image($post_id, $beer)
{
    // Set beer slug
    $img_slug = sanitize_title($beer->beer_slug);

    // Set up args for duplicate check
    $dup_args = array(
        'name'           => $img_slug,
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1
    );

    // Run post query
    $img_exists = get_posts($dup_args);

    // Handle if we found the image
    if ($img_exists) {
        // Set as post image and exit
        set_post_thumbnail($post_id, $img_exists[0]->ID);
        return;
    }

    // Get WP upload dir info
    $upload_dir = wp_upload_dir();

    // Get image type from URL
    $img_parts = explode('.', $beer->beer_label_hd);
    $img_type = end($img_parts);

    // Set file save path
    $filename = $upload_dir['path'] . '/' . $beer->beer_slug . '.' . $img_type;

    // Save image from URL
    file_put_contents($filename, file_get_contents($beer->beer_label_hd));

    // Check the type of file
    $filetype = wp_check_filetype(basename($filename), null);

    // Prepare an post data for attachment
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title'     => $beer->beer_slug,
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert the attachment
    $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    include_once ABSPATH . 'wp-admin/includes/image.php';

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Set as thumbnail for beer
    set_post_thumbnail($post_id, $attach_id);
}


// Check for a valid POST request
if ($_SERVER['REQUEST_METHOD'] && isset($_POST['embm-labs-untappd-import'])) {
    // Import single beer
    if ($_POST['embm-labs-untappd-import'] == '1') {
        // Get imported vars
        $api_root = $_POST['embm-untappd-api-root'];
        $beer_id = $_POST['embm-untappd-beer-id'];
        $brewery_id = $_POST['embm-untappd-brewery-id'];

        // Set up beer request URL
        $beer_url = sprintf($api_root, 'beer/info/'.$beer_id);

        // Run import
        EMBM_Admin_Import_beer($beer_url, $brewery_id);

        // Return to EMBM settings page & exit
        wp_redirect(get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-success=1#labs'));
        exit;
    } elseif ($_POST['embm-labs-untappd-import'] == '2') {  // Import all beers
        // Get imported vars
        $api_root = $_POST['embm-untappd-api-root'];
        $brewery_id = $_POST['embm-untappd-brewery-id'];

        // Make GET request to API
        $brewery_url = sprintf($api_root, 'brewery/info/'.$brewery_id);
        $brewery_res = json_decode(file_get_contents($brewery_url));
        $brewery = $brewery_res->response->brewery;

        // Iteratively add beers
        foreach ($brewery->beer_list->items as $item) {
            // Set up beer request URL
            $beer_url = sprintf($api_root, 'beer/info/'.$item->beer->bid);

            // Run import
            EMBM_Admin_Import_beer($beer_url, $brewery_id);
        }

        // Return to EMBM settings page & exit
        wp_redirect(get_admin_url(null, 'options-general.php?page=embm-settings&embm-import-success=2#labs'));
        exit;
    }
} else {
    // Die if this isn't a valid POST request
    wp_die(__('You do not have sufficient permissions to access this page.', 'embm'));
}
