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
 * @package EMBM\Uninstall
 */


// If uninstall not called from WP exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

?>
    <p>Would you like to keep the options configured by this plugin?</p>
    <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
        <select name="act">
            <option>Select choice..</option>
            <option value="keep">Keep options</option>
            <option value="delete">Delete options</option>
        </select>
        <input type="submit" value="Go" />
    </form>
<?php

/**
 * Plugin uninstall setup
 *
 * @return void
 */
function EMBM_Plugin_uninstall()
{
    // Get currently installed version
    $curr_version = floatval(get_option('embm_version'));

    // Old version requiring update
    $new_version = 1.7;

    // Keep beer data saved for those upgrading from 1.6 or earlier
    if ($curr_version >= $new_version) {

        // Get global post types variable
        global $wp_post_types;

        // Remove EMBM post type
        if (isset($wp_post_types['embm_beer'])) {
            unset($wp_post_types['embm_beer']);
        }

        // Set up EMBM post query
        $args = array(
            'post_type'     =>'embm_beer',
            'post_status'   => array(
                'publish',
                'pending',
                'draft',
                'auto-draft',
                'future',
                'private',
                'inherit',
                'trash'
            )
        );

        // Get all existing EMBM posts
        $posts = get_posts($args);

        // Iteratively remove existing EMBM posts
        if (is_array($posts)) {
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }

        // Set EMBM taxonomies
        $tax = array('embm_group', 'embm_style');

        // Get global WP taxonomies
        global $wp_taxonomies;

        // Remove all EMBM taxonomies
        foreach ($tax as $taxonomy) {
            // Set taxonomy
            register_taxonomy($taxonomy);

            // Fina all terms for taxonomy
            $terms = get_terms($taxonomy, array('hide_empty' => 0));

            // Iteratively remove all terms for taxonomy
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }

            // Remove taxonomy from WP
            unset($wp_taxonomies[$taxonomy]);
        }
    }

    // Remove EMBM widget CSS
    wp_deregister_style('embm-widget', EMBM_PLUGIN_URL.'assets/css/widget.css');
    wp_dequeue_style('embm-widget');

    // Remove EMBM output CSS
    wp_deregister_style('embm-output', EMBM_PLUGIN_URL.'assets/css/output.css');
    wp_dequeue_style('embm-output');

    // Remove EMBM admin CSS
    wp_dequeue_style('embm-admin');

    // Retrieve custom CSS info
    $get_style_option = get_option('embm_options');
    $get_custom_css = $style_option['embm_css_url'];

    // Remove custom CSS
    wp_deregister_style('custom-embm-output', $get_custom_css);
    wp_dequeue_style('custom-embm-output');

    // Remove EMBM settings
    delete_option('embm_version');
    delete_option('embm_options');
    delete_option('embm_db_upgrade');
    delete_option('embm_styles_loaded');
    delete_option('widget_beer_list_widget');
    delete_option('widget_recent_untappd_widget');
}
