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
 * @package EMBM\Admin\Upgrades
 */

// Set current upgrade version
define('EMBM_LEGACY_VERSION', '1.7.0');

// Set upgrade versions and functions
$GLOBALS['EMBM_UPGRADE_MAP'] = array(
    '1.7.0' => 'EMBM_Upgrade_v170',
    '3.0.0' => 'EMBM_Upgrade_v300'
);

/**
 * Perform v3.0.0 upgrades
 *
 * @return void
 */
function EMBM_Upgrade_check()
{
    // Get upgrade status
    $upgrade = get_option('embm_db_upgrade');

    // Stop if we've done this before
    if ($upgrade && $upgrade == EMBM_VERSION_NUM) {
        return;
    }

    // Get current version
    $curr_version = floatval(EMBM_VERSION_NUM);

    // Do legacy upgrades
    if ($curr_version >= floatval(EMBM_LEGACY_VERSION)) {
        EMBM_Upgrade_legacy();
    }

    // Update upgrade version
    $upgrade = get_option('embm_db_upgrade');
    $upgrade_version = floatval($upgrade);

    // Get upgrade map
    $upgrade_map = $GLOBALS['EMBM_UPGRADE_MAP'];

    // Iterate over upgrades
    foreach ($upgrade_map as $version => $do_upgrade) {
        // Only do the upgrade if we haven't done it already
        if (!$upgrade
            || ($curr_version >= floatval($version)
            && $upgrade_version < floatval($version))
        ) {
            $do_upgrade();
        }
    }

    // Save upgrade status to DB
    update_option('embm_db_upgrade', EMBM_VERSION_NUM);
}

/**
 * Perform v3.0.0 upgrades
 *
 * @return void
 */
function EMBM_Upgrade_v300()
{
    // Get global WP database reference
    global $wpdb;

    // List of attributes to update
    $attrs = array(
        'malts',
        'hops',
        'adds',
        'yeast',
        'ibu',
        'abv',
        'beer_num',
        'avail',
        'notes',
        'untappd'
    );

    // Update each attribute
    foreach ($attrs as $attr) {
        // New attribute name
        $new_attr = 'embm_' . $attr;

        // Update column names
        $wpdb->query(
            "
            UPDATE $wpdb->postmeta
            SET meta_key = '$new_attr'
            WHERE meta_key = '$attr'
            "
        );
    }

    // List of widgets to update
    $widgets = array('beer_list', 'recent_untappd');

    // Update each widget name
    foreach ($widgets as $widget) {
        // Set widget names
        $old_widget = 'widget_'.$widget.'_widget';
        $new_widget = 'widget_embm_'.$widget.'_widget';

        // Update column names
        $wpdb->query(
            "
            UPDATE $wpdb->options
            SET option_name = '$new_widget'
            WHERE option_name = '$old_widget'
            "
        );
    }
}

/**
 * Perform v1.7.0 upgrades
 *
 * @return void
 */
function EMBM_Upgrade_v170()
{
    // Get global WP database reference
    global $wpdb;

    // Taxonomies to update
    $tax_names = array('style', 'beer');

    // Rename taxonomies
    foreach ($tax_names as $tax_name) {
        // Set new tax name
        $new_tax_name = 'embm_' . $tax_name;

        // Update column names
        $wpdb->query(
            "
            UPDATE $wpdb->term_taxonomy
            SET taxonomy = '$new_tax_name'
            WHERE taxonomy = '$tax_name'
            "
        );
    }
}

/**
 * Perform legacy upgrade actions
 *
 * @return void
 */
function EMBM_Upgrade_legacy()
{
    // Get upgrade status
    $upgrade = get_option('embm_db_upgrade');

    // Check for old DB content
    delete_option('embm_comment_change');

    // Update DB data format for upgrade
    if ($upgrade == 'true') {
        update_option('embm_db_upgrade', EMBM_LEGACY_VERSION);
    } elseif (!$upgrade) {
        update_option('embm_db_upgrade', false);
    }

    // Get styles loaded option
    $loaded = get_option('embm_styles_loaded');

    // Update DB data format for styles
    if ($loaded == 'true') {
        update_option('embm_styles_loaded', true);
    } elseif (!$loaded) {
        update_option('embm_styles_loaded', false);
    }
}
