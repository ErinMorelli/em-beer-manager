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
 * @package EMBM\Output\Menus
 */

/**
 * Display a given UTFB menu
 *
 * @param array $args Menu display data
 *
 * @return string/html
 */
function EMBM_Output_Menus_display($args)
{
    // Get UTFB credentials
    $credentials = get_option(EMBM_UTFB_CREDENTIALS);

    // Make sure we're authorized
    if (null == $credentials) {
        return __('Please log in to Untappd for Business to use this shortcode.', 'em-beer-manager');
    }

    // Set up display options
    $menu = $args['id'];
    $showrating = $args['rating'];
    $showupdated = $args['updated'];
    $showthumbnail = $args['thumbnail'];
    $showdescription = $args['description'];

    // Get menu meta
    $menu_meta = get_term_meta($menu->term_id, EMBM_BEER_META, true);

    // Check that ID exists
    if (null == $menu_meta || !array_key_exists('utfb_id', $menu_meta)) {
        return __(
            'Could not locate Untappd for Business data for this Menu. '.
            'Please make sure an ID is set in the Menus admin',
            'em-beer-manager'
        );
    }

    // Get menu UTFB data
    $menu_data = EMBM_Admin_Utfb_Menu_term($credentials, $menu->term_id, $menu_meta['utfb_id']);

    // Get last updated
    $menu_updated = new DateTime($menu_data->updated_at);
    $menu_updated_formatted = $menu_updated->format('l, M d, g:i A'); // Saturday, Oct 07, 10:32 AM

    // Begin menu output
    $output = '<div id="embm-beer-menu-'.$menu->term_id.'" class="embm-beer-menu embm-beer embm-beer-menu-'.$menu->term_id.'">'."\n";

    // Initialize output string
    $output .= '<h2 class="embm-beer-menu--header">'.$menu->name.'</h2>'."\n";

    // Display menu description
    if ($showdescription && !is_null($menu->description) && $menu->description != '') {
        $output .= '<p class="embm-beer-menu--description">'.$menu->description.'</p>';
    }

    // Display menu last updated
    if ($showupdated) {
        $output .= '<p class="embm-beer-menu--updated">';
        $output .= __('Last updated', 'em-beer-manager').': ';
        $output .= $menu_updated_formatted.'</p>';
    }

    // Get all menu objects (sections, beer)
    $sections =  EMBM_Output_Menus_sections($credentials, $menu->term_id);

    // Iterate over sections
    foreach ($sections as $section) {
        // Make sure the section has beers to show
        if (null == $section->beers) {
            continue;
        }

        // Start section output
        $output .= '<div id="embm-beer-menu--section-'.$section->term_id.'" class="embm-beer-menu--section">'."\n";
        $output .= '<h3 class="embm-beer-menu--section-header">'.$section->name.'</h3>'."\n";

        // Display description
        if ($showdescription && !is_null($section->description) && $section->description != '') {
            $output .= '<p class="embm-beer-menu--section-description">'.$section->description.'</p>'."\n";
        }

        // Start beer count
        $beer_count = 1;

        // Iterate over beers for the section
        foreach ($section->beers as $beer) {
            // Set up beer data
            $beer_style = EMBM_Core_Beer_style($beer->ID);
            $beer_abv = EMBM_Core_Beer_meta($beer->ID, 'abv');
            $beer_ibu = EMBM_Core_Beer_meta($beer->ID, 'ibu');
            $beer_rating = $beer->utfb_data->rating;

            // Get ratings stars
            $beer_rating_stars = EMBM_Output_Rating_stars($beer_rating);

            // Display beer
            $output .= '<div id="embm-beer-menu--beer-'.$beer->ID;
            $output .= '" class="embm-beer-menu--beer embm-beer-menu--beer-'.$beer_count.'">';

            // Show thumbnail for beer
            if ($showthumbnail && has_post_thumbnail($beer->ID)) {
                $output .= '<div class="embm-beer-menu--beer-image">';
                $output .= '<a href="'.get_permalink($beer->ID).'">';
                $output .= get_the_post_thumbnail($beer->ID, array(50, 50)).'</a></div>';
            }

            // Display title and style
            $output .= '<div class="embm-beer-menu--beer-profile">';
            $output .= '<p><a class="embm-beer-menu--beer-title" href="'.get_permalink($beer->ID).'">';
            $output .= $beer->post_title.'</a>';
            $output .= '<a class="embm-beer-menu--beer-style" href="'.get_term_link($beer_style, EMBM_STYLE).'">';
            $output .= $beer_style.'</a></p>';

            // Display beer data
            $output .= '<p class="embm-beer-menu--beer-data">';
            if ($beer_abv != '') {
                $output .= '<span class="embm-beer-menu--beer-abv">'.$beer_abv.' '.__('ABV', 'em-beer-manager').'</span>';
            }
            if ($beer_ibu != '') {
                $output .= '<span class="embm-beer-menu--beer-ibu">'.$beer_ibu.' '.__('IBU', 'em-beer-manager').'</span>';
            }

            // Optionally display rating
            if ($showrating) {
                $output .= '<span class="embm-beer-menu--beer-rating embm-beer--rating-stars">';
                $output .= $beer_rating_stars.'</span>';
            }

            // End beer output
            $output .= '</p></div></div>';

            // Increment beer counter
            $beer_count += 1;
        }

        // End section output
        $output .= '</div>';
    }

    // Add rating styles if needed
    if ($showrating) {
        $styles = EMBM_Output_Rating_styles();
        $output .= $styles;
    }

    // End menu output
    $output .= '</div>';

    // Return HTML content
    return $output;
}

/**
 * Get sorted section and beer data for a menu
 *
 * @param array $auth    UTFB authentication data
 * @param int   $menu_id WP term ID of menu
 *
 * @return object
 */
function EMBM_Output_Menus_sections($auth, $menu_id)
{
    // Get all section IDs for menu
    $section_ids = get_term_children($menu_id, EMBM_MENU);

    // Get all the section data
    $sections = array_map(
        function ($section_id) use ($auth, $menu_id) {
            // Get section data
            $section = get_term($section_id);

            // Get section data
            $section_meta = get_term_meta($section_id, EMBM_BEER_META, true);

            // Get section UTFB data
            $section->utfb_data = EMBM_Admin_Utfb_Menu_term($auth, $section->term_id, $section_meta['utfb_id'], true);

            // Get all beers for section
            $beer_ids = get_objects_in_term($section->term_id, EMBM_MENU);

            // Get all beer posts
            $section->beers = array_map(
                function ($beer_id) use ($menu_id, $section_meta) {
                    // Get WP post for beer
                    $beer = get_post($beer_id);

                    // Get raw UTFB data
                    $utfb_data = EMBM_Core_Beer_utfb($beer_id);

                    // Locate beer in data
                    $beer->utfb_data = EMBM_Output_Menus_find($utfb_data, $section_meta['utfb_id']);

                    // Return beer data
                    return $beer;
                },
                $beer_ids
            );

            // Sort the beers by menu position
            usort($section->beers, 'EMBM_Output_Menus_sort');

            // Return section data
            return $section;
        },
        $section_ids
    );

    // Sort the sections by position
    usort($sections, 'EMBM_Output_Menus_sort');

    // Return all section data
    return $sections;
}

/**
 * Locate beer data based on the UTFB section
 *
 * @param array $beers      Array of UTFB beer data to search
 * @param int   $section_id UTFB section ID
 *
 * @return object
 */
function EMBM_Output_Menus_find($beers, $section_id)
{
    foreach ($beers as $beer) {
        if ($beer['beer']->section_id == $section_id) {
            return $beer['beer'];
        }
    }
    return null;
}

/**
 * Function to sort UTFB data by position number
 *
 * @param object $a First item to compare
 * @param object $b Second item to compare
 *
 * @return int
 */
function EMBM_Output_Menus_sort($a, $b)
{
    if ($a->utfb_data->position == $b->utfb_data->position) {
        return 0;
    }
    return ($a->utfb_data->position < $b->utfb_data->position) ? -1 : 1;
}
