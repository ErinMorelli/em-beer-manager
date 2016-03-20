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
 * @package EMBM\Admin\ResetStyles
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

// Check for a valid POST request
if ($_SERVER['REQUEST_METHOD'] && isset($_POST['embm-styles-reset-request']) && $_POST['embm-styles-reset-request'] == '1') {
    // Soft reset all styles
    EMBM_Core_Styles_populate();

    // Return to EMBM settings page
    wp_redirect(get_admin_url(null, 'options-general.php?page=embm-settings&embm-styles-reset=2'));
    exit;
} else {
    // Die if this isn't a valid POST request
    wp_die(__('You do not have sufficient permissions to access this page.', 'embm'));
}
