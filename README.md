# EM Beer Manager #

*by Erin Morelli*

Manage your beers with WordPress. Integrates simply with Untappd beer checkins. Great for everyone from home brewers to professional breweries!


## Overview ###

This plugin allows beer creators from home brewers to professional breweries to easily manage and display their beers. Includes a comprehensive beer management section with a variety of options, including:

* A custom beer "style" taxonomy for classifying your beers pre-populated with styles from BeerAdvocate
* A customizable "group" taxonomy for categorizing and grouping your beers
* Shortcodes and template tags for displaying all or a select number of beers
* Custom meta boxes to store detailed information about each beer, including ABV, IBU, and ingredients
* Simple beer checkin integration with Untappd
* A "Beer List" widget for simply displaying your beers in sidebars
* A "Recent Check-Ins" widget for displaying recent beer check-ins for your brewery on Untappd
* Custom page display for beers and styles
* [Experimental] Import your brewery's beers directly from Untappd


### Screenshots ###

1. [The "Beer" management screen](https://bitbucket.org/repo/jbBK97/images/2094199255-screenshot-1.jpg)
2. [Beer profile information](https://bitbucket.org/repo/jbBK97/images/3661523039-screenshot-2.jpg)
3. [Extra Beer information](https://bitbucket.org/repo/jbBK97/images/489714407-screenshot-3.jpg)
4. [Special groups taxonomy](https://bitbucket.org/repo/jbBK97/images/1441219431-screenshot-4.jpg)
5. [Pre-populated styles taxonomy](https://bitbucket.org/repo/jbBK97/images/124939507-screenshot-5.jpg)
6. [Single beer front-end display (with all options enabled)](https://bitbucket.org/repo/jbBK97/images/4234449303-screenshot-6.jpg)
7. [Untappd check-in widget options & display](https://bitbucket.org/repo/jbBK97/images/924423916-screenshot-7.jpg)
8. [Beer List widget options & display](https://bitbucket.org/repo/jbBK97/images/2556482217-screenshot-8.jpg)



*****


## Latest Release ##


### [Version 2.1.1 - Minor Update](https://bitbucket.org/ErinMorelli/em-beer-manager/downloads/em-beer-manager.2.1.1.zip) ###
* Added PUT/POST support for beer metadata via the Wordpress API
* Further improvements to the 'Import from Untappd' Labs feature


*****


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

    `<?php echo EMBM_Output_Beer_display( $beer_id, $args ); ?>`

    Where `$beer_id` is required and `$args` is a PHP array of comma-separated `key => value` pairs. For example:

        <?php echo EMBM_Output_Beer_display( 123, array(
            'show_profile' => false,
            'show_extras' => true
        ) ); ?>

* __Options__:

    For use with both the shortcode and template code.

    * __show_profile => `"true, false"`__ (Default = `true`)

        *Displays or hides the "Beer Profile" information section*

    * __show_extras => `"true, false"`__ (Default = `true`)

        *Displays or hides the "More Beer Information" section*


### List All Beers ###

These will display a formatted listing of all beers.

* __Shortcode:__

    `[beer-list]`

* __Template tag:__

    `<?php echo EMBM_Output_List_display( $args ); ?>`

    Where `$args` is a PHP array of comma-separated `key => value` pairs. For example:

        <?php echo EMBM_Output_List_display( array(
            'show_extras' => false,
            'beers_per_page' => 3,
            'orderby' => 'name',
            'order' => 'ASC'
        ) ); ?>

* __Options__:

    For use with both the shortcode and template code.

    * __show_profile => `"true, false"`__ (Default = `true`)

        *Displays or hides the "Beer Profile" information section*

    * __show_extras => `"true, false"`__ (Default = `true`)

        *Displays or hides the "More Beer Information" section*

    * __style => `"style name"`__ (String e.g. `"India Pale Ale"`)

        *Displays only beers belonging to a specific beer style*

    * __group => `"group name"`__ (String e.g. `"Seasonal Beers"`)

        *Displays only beers belonging to a specific group*

    * __exclude => `"beer ids"`__ (Comma-separated list of beer IDs e.g. `"4,23,24"`)

        *Hides listed beers from output*

    * __beers\_per\_page => `"number"`__ (Default = `-1`, shows all beers on one page)

        *Paginates output and displays the given number of beers per page*

    * __paginate => `"true, false"`__ (Default = `true`)

        *Disables/enables pagination*

    * __orderby => `"string"`__ (Default = `date`, see [this list](http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters) for options)

        *Orders output by given paramater*

    * __order => `"DSC, ASC"`__ (Default = `DSC`)

        *Sorts beer list by `orderby` value in ascending or descending order*



*****

### Translations ###

I would love to be able to expand this section - let me know if you are able to contribute!

* English
* Icelandic - *thanks to __rodonmanes__*


### Planned Features ###

* Post/Page “Add Beer” page/post editor button to auto-generate shortcode input
* Customization for “Beer Profile” input fields (e.g. allow users to remove “Additions/Spices” or add “OG”)
* Allow users to select additional fields to show in the beer list widget (e.g. "ABV")
