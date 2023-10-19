#### NOTICE: _This plugin is no longer being actively developed or supported._ ####

---
# EM Beer Manager #

*by Erin Morelli*

Manage and display your beers with WordPress. Integrates simply with Untappd and Untappd for Business. Great for everyone from home brewers to professional breweries!

[![CodeFactor](https://www.codefactor.io/repository/github/erinmorelli/em-beer-manager/badge)](https://www.codefactor.io/repository/github/erinmorelli/em-beer-manager)

---

## Overview ###

This plugin allows beer creators from home brewers to professional breweries to easily manage and display their beers. Includes a comprehensive beer management section with a variety of options, including:

* A custom beer "style" taxonomy for classifying your beers pre-populated with styles from Untappd
* A customizable "group" taxonomy for categorizing and grouping your beers
* "Menu" taxonomy and shortcode for creating and displaying beer menus
* Shortcodes and template tags for displaying all or a select number of beers
* Custom meta boxes to store detailed information about each beer, including ABV, IBU, and ingredients
* Beer check-in and rating integration with Untappd
* A "Beer List" widget for simply displaying your beers in sidebars
* A "Recent Check-Ins" widget for displaying recent beer check-ins for your brewery on Untappd
* Custom page display for beers and styles
* [Beta] Import and sync your brewery's beers directly from Untappd
* [Beta] Import and sync your beers and menus from Untappd for Business


### Screenshots ###

1. [The beer post type list page](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-1.jpg)
2. [Extra beer post metaboxes](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-2.jpg)
3. [Pre-populated styles taxonomy](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-3.jpg)
4. [Plugin settings page](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-4.jpg)
5. [Single beer page display](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-5.jpg)
6. [Beer list widget options & display](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-6.jpg)
7. [Untappd check-in widget options & display](https://raw.githubusercontent.com/ErinMorelli/em-beer-manager/master/screenshot-7.jpg)



*****


## Latest Release ##

### [Version 3.2.3 - Bug Fix](https://github.com/ErinMorelli/em-beer-manager/releases/download/v3.2.3/em-beer-manager.3.2.3.zip) ###
* [FIXED] Issue with Untappd for Business import failing


### Installation ###

1. Unzip the `em-beer-manager.zip` file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. See below to learn more about usage.


*****

## Usage ##


Use these shortcodes to display beers in your posts or use the template tags in your theme files.


### Single Beer Display ###

These will display a single beer entry given it's ID number (found in "Beers" admin).

* __Shortcode:__

    `[beer id={beer id}]`

* __Template tag:__

    `<?php echo EMBM_Output_Shortcodes_Beer_display( $beer_id, $args ); ?>`

    Where `$beer_id` is required and `$args` is a PHP array of comma-separated `key => value` pairs. For example:

        <?php echo EMBM_Output_Shortcodes_Beer_display( 123, array(
            'show_profile'   => false,
            'show_extras'    => true,
            'show_rating'    => false,
            'show_checkins'  => true,
            'checkins_count' => 10
        ) ); ?>

* __Options__:

    For use with both the shortcode and template code.

    * __show_profile => `"true, false"`__ (Default = `true`)

        *Displays or hides the "Beer Profile" information section*

    * __show_extras => `"true, false"`__ (Default = `true`)

        *Displays or hides the "More Beer Information" section*

    * __show_rating => `"true, false"`__ (Default = `true`)

        *Displays or hides the Untappd beer rating*

    * __show_checkins => `"true, false"`__ (Default = `true`)

        *Displays or hides the Untappd check-ins section*

    * __checkins_count => `"number"`__ (Default = `5`, limit is `15`)

        *The number of recent Untappd check-ins to display*


### List All Beers ###

These will display a formatted listing of all beers.

* __Shortcode:__

    `[beer-list]`

* __Template tag:__

    `<?php echo EMBM_Output_Shortcodes_List_display( $args ); ?>`

    Where `$args` is a PHP array of comma-separated `key => value` pairs. For example:

        <?php echo EMBM_Output_Shortcodes_List_display( array(
            'show_extras'    => false,
            'show_rating'    => true,
            'beers_per_page' => 3,
            'orderby'        => 'name',
            'order'          => 'ASC'
        ) ); ?>

* __Options__:

    For use with both the shortcode and template code.

    * __show_profile => `"true, false"`__ (Default = `true`)

        *Displays or hides the "Beer Profile" information section*

    * __show_extras => `"true, false"`__ (Default = `true`)

        *Displays or hides the "More Beer Information" section*

    * __show_rating => `"true, false"`__ (Default = `true`)

        *Displays or hides the Untappd beer rating*

    * __style => `"style name"`__ (String e.g. `"india-pale-ale, pale-ale"`)

        *Displays only beers belonging to specific beer styles*

    * __group => `"group name"`__ (String e.g. `"Seasonal, Barrel-Aged"`)

        *Displays only beers belonging to specific groups*

    * __exclude => `"beer ids"`__ (Comma-separated list of beer IDs e.g. `"4,23,24"`)

        *Hides listed beers from output*

    * __beers\_per\_page => `"number"`__ (Default = `-1`, shows all beers on one page)

        *Paginates output and displays the given number of beers per page*

    * __offset => `"number"`__ (Default = `0`, starts at the first beer)

        *Offsets the output of beers by given number*

    * __paginate => `"true, false"`__ (Default = `true`)

        *Disables/enables pagination*

    * __orderby => `"string"`__ (Default = `date`, see [this list](http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters) for options)

        *Orders output by given paramater*

    * __order => `"DSC, ASC"`__ (Default = `DSC`)

        *Sorts beer list by `orderby` value in ascending or descending order*


### Beer Menus ###

These will display a beer menu given it's Name, Slug, or ID number.

* __Shortcode:__

    `[beer-menu menu={menu id}]`

* __Template tag:__

    `<?php echo EMBM_Output_Shortcodes_Menu_display( $menu_id, $args ); ?>`

    Where `$args` is a PHP array of comma-separated `key => value` pairs. For example:

        <?php echo EMBM_Output_Shortcodes_Menu_display(
            'Taproom Menu',
            array(
                'show_rating'       => false,
                'show_last_updated' => true,
                'show_thumbnail'    => true,
                'show_description'  => false,
            )
        ); ?>

* __Options__:

    For use with both the shortcode and template code.

    * __show_rating => `"true, false"`__ (Default = `true`)

        *Displays or hides the Untappd beer rating*

    * __show_last_updated => `"true, false"`__ (Default = `true`)

        *Displays or hides the menu's last updated timestamp*

    * __show_thumbnail => `"true, false"`__ (Default = `true`)

        *Displays or hides the beer featured image thumbnails*

    * __show_description => `"true, false"`__ (Default = `true`)

        *Displays or hides the menu section descriptions*


*****

### Translations ###

I would love to be able to expand this section - let me know if you are able to contribute!

* English
* Icelandic (is_IS) - *thanks to __[rodonmanes](http://bjorspjall.is)__*
* Norwegian Bokmål (nb_NO) - *thanks to __[Lars Kvisle](http://www.lars.kvisle.no)__*
* Brazilian Portuguese (pt_BR) - *thanks to __Lucas Alexandre__*
