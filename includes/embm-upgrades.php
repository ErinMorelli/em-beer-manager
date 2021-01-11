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
 * @package EMBM\Admin\Upgrades
 */

// Set current upgrade version
define('EMBM_LEGACY_VERSION', '1.7.0');

// Set upgrade versions and functions
$GLOBALS['EMBM_UPGRADE_MAP'] = array(
    '1.7.0' => 'EMBM_Upgrade_v170',
    '3.0.0' => 'EMBM_Upgrade_v300',
    '3.2.0' => 'EMBM_Upgrade_v320'
);

/**
 * Perform v3.0.0 upgrades
 *
 * @return void
 */
function EMBM_Upgrade_check()
{
    // Get upgrade status
    $upgrade = get_option(EMBM_DB_VERSION);

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
    $upgrade = get_option(EMBM_DB_VERSION);
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
    update_option(EMBM_DB_VERSION, EMBM_VERSION_NUM);
}

/**
 * Perform v3.2.0 upgrades
 *  - Adds UTFB menu/section IDs to embm_menu taxonomy terms
 *  - Condense existing beer post meta into one of 3 new rows
 *      1. embm_meta (profile, extras, ids)
 *      2. embm_meta_untappd (timestamped, raw Untappd API data)
 *      3. embm_meta_utfb (timestamped, raw UTFB API data)
 *  - Fixes UTFB beers that were imported with only one Menu association
 *  - Update styles with new additions
 *
 * @return void
 */
function EMBM_Upgrade_v320()
{
    // Unset styles loaded so they will refresh
    update_option(EMBM_STYLES_LOADED, false);

    // Get global WP database reference
    global $wpdb;

    // Load our admin data functions if they haven't already been loaded
    if (!function_exists('EMBM_Admin_Utfb_Resources_all')) {
        include_once EMBM_PLUGIN_DIR.'includes/embm-admin.php';
    }

    // Get all available UTFB resources
    $resources = EMBM_Admin_Utfb_Resources_all();

    // Make sure we got resources back
    if (!is_null($resources)) {
        // Get all existing Menus and Sections
        $menu_terms = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT t.term_id, t.name, tt.parent
                FROM $wpdb->term_taxonomy as tt
                JOIN $wpdb->terms as t
                     ON t.term_id = tt.term_id
                WHERE tt.taxonomy = %s
                ",
                EMBM_MENU
            )
        );

        // Iterate over menu terms
        foreach ($menu_terms as $menu_term) {
            // Determine if this is a Menu or a Section
            $menu_type = ($menu_term->parent == 0) ? 'menu' : 'section';

            // Get all UTFB resources for Menu/Section
            $utfb_menu_res = $resources[$menu_type];

            // Iterate over UTFB items
            foreach ($utfb_menu_res as $item) {
                // We only care about items that match
                if (strtolower(trim($item->name)) == strtolower(trim($menu_term->name))) {
                    // Set up new term meta data
                    $term_meta = array(
                        'utfb_id'      => $item->id,
                        'sync_exclude' => ''
                    );

                    // Update term with meta data
                    add_term_meta($menu_term->term_id, EMBM_BEER_META, $term_meta, true);

                    // Set up UTFB data
                    $utfb_data = array(
                        $menu_type => $item,
                        'cached'   => time()
                    );

                    // Update term with raw UTFB data
                    add_term_meta($menu_term->term_id, EMBM_BEER_META_UTFB, $utfb_data, true);
                    break;
                }
            }
        }

        // Get all existing beers
        $beers = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT *
                FROM $wpdb->posts
                WHERE post_type = %s
                ",
                EMBM_BEER
            )
        );

        // Iterate over each beer
        foreach ($beers as $beer) {
            // Set up new meta for beer
            $new_meta = array();

            // Get all EMBM post meta for this beer
            $beer_meta = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT meta_key, meta_value
                    FROM $wpdb->postmeta
                    WHERE post_id = %d
                            AND meta_key LIKE 'embm_%'
                    ",
                    $beer->ID
                ),
                OBJECT_K
            );

            // Only do this if there's meta for the post
            if (empty($beer_meta)) {
                continue;
            }

            // Set profile meta with keys
            $profile_meta = array(
                'abv'           => null,
                'adds'          => null,
                'avail'         => null,
                'beer_num'      => null,
                'hops'          => null,
                'ibu'           => null,
                'malts'         => null,
                'notes'         => null,
                'yeast'         => null,
                'hide_rating'   => null,
                'hide_reviews'  => null,
                'reviews_count' => null
            );

            // Set new profile values
            foreach ($profile_meta as $profile_key => $value) {
                $old_key = 'embm_'.$profile_key;
                if (array_key_exists($old_key, $beer_meta)) {
                    $profile_meta[$profile_key] = $beer_meta[$old_key]->meta_value;
                }
            }

            // Handle Untappd ID
            $profile_meta['untappd_id'] = array_key_exists('embm_untappd', $beer_meta) ? $beer_meta['embm_untappd']->meta_value : null;

            // Handle Untappd data
            $untappd_meta = array_key_exists('embm_untappd_data', $beer_meta) ? unserialize($beer_meta['embm_untappd_data']->meta_value) : null;

            // Get current UTFB ID for beer
            $utfb_id = array_key_exists('embm_utfb', $beer_meta) ? $beer_meta['embm_utfb']->meta_value : null;

            // Set up new UTFB beer meta data
            $profile_meta['utfb_ids'] = array();
            $utfb_meta = array();

            // Only handle beers with existing UTFB associations
            if (!is_null($utfb_id) && $utfb_id !== '') {
                // Look for beer in UTFB resources
                foreach ($resources['beer'] as $utfb_beer) {
                    // Only handle beers that match
                    if (strtolower(trim($utfb_beer->name)) !== strtolower(trim($beer->post_title))) {
                        continue;
                    }

                    // Add to list of UTFB IDs for beer
                    array_push($profile_meta['utfb_ids'], $utfb_beer->id);

                    // Add UTFB data to list for beer
                    array_push(
                        $utfb_meta,
                        array(
                            'beer'   => $utfb_beer,
                            'cached' => time()
                        )
                    );

                    // Find section and menu for beer
                    $section = EMBM_Admin_Utfb_find($resources['section'], $utfb_beer->section_id);
                    $menu = EMBM_Admin_Utfb_find($resources['menu'], $section->menu_id);

                    // Locate section and menu in WP terms list
                    $section_term = term_exists($section->name, EMBM_MENU);
                    $menu_term = term_exists($menu->name, EMBM_MENU);

                    // Only continue if we found both a menu and a section
                    if (!is_array($section_term) || !is_array($menu_term)) {
                        continue;
                    }

                    // Set list of term IDs to add
                    $term_ids = array($menu_term['term_taxonomy_id'], $section_term['term_taxonomy_id']);

                    // Update the post with the term IDs
                    wp_set_post_terms($beer->ID, $term_ids, EMBM_MENU, true);
                }
            }

            // Save new meta values for beer
            update_post_meta($beer->ID, EMBM_BEER_META, $profile_meta);
            update_post_meta($beer->ID, EMBM_BEER_META_UNTAPPD, $untappd_meta);
            update_post_meta($beer->ID, EMBM_BEER_META_UTFB, $utfb_meta);

            // Only remove old meta if all 3 passed
            if (metadata_exists('post', $beer->ID, EMBM_BEER_META)
                && metadata_exists('post', $beer->ID, EMBM_BEER_META_UNTAPPD)
                && metadata_exists('post', $beer->ID, EMBM_BEER_META_UTFB)
            ) {
                // Iterate over the beer meta we queried earlier
                foreach ($beer_meta as $old_meta) {
                    // Delete old post meta values
                    delete_post_meta($beer->ID, $old_meta->meta_key);
                }
            }
        }
    }
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
    $tax_names = array(
        'style' => EMBM_STYLE,
        'beer'  => EMBM_BEER
    );

    // Rename taxonomies
    foreach ($tax_names as $tax_name => $new_tax_name) {
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
    $upgrade = get_option(EMBM_DB_VERSION);

    // Check for old DB content
    delete_option('embm_comment_change');

    // Update DB data format for upgrade
    if ($upgrade == 'true') {
        update_option(EMBM_DB_VERSION, EMBM_LEGACY_VERSION);
    } elseif (!$upgrade) {
        update_option(EMBM_DB_VERSION, false);
    }

    // Get styles loaded option
    $loaded = get_option(EMBM_STYLES_LOADED);

    // Update DB data format for styles
    if ($loaded == 'true') {
        update_option(EMBM_STYLES_LOADED, true);
    } elseif (!$loaded) {
        update_option(EMBM_STYLES_LOADED, false);
    }
}
