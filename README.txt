=== EM Beer Manager ===
Contributors: ErinMorelli
Donate link: http://www.erinmorelli.com/projects/em-beer-manager/
Tags: beer, beers, brewery, untappd
Requires at least: 3.0.1
Tested up to: 4.4.2
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage your beers with WordPress. Integrates simply with Untappd beer checkins. Great for everyone from home brewers to professional breweries!


== Description ==

This plugin allows beer creators from home brewers to professional breweries to easily manage and display their beers. Includes a comprehensive beer management section with a variety of options, including:

* A custom beer "style" taxonomy for classifying your beers pre-populated with styles from BeerAdvocate
* A customizable "group" taxonomy for categorizing and grouping your beers
* Shortcodes and template tags for displaying all or a select number of beers
* Custom meta boxes to store detailed information about each beer, including ABV, IBU, and ingredients
* Simple beer check-in integration with Untappd
* A "Beer List" widget for simply displaying your beers in sidebars
* A "Recent Check-Ins" widget for displaying recent beer check-ins for your brewery on Untappd
* Custom page display for beers and styles
* [Experimental] Import your brewery's beers directly from Untappd

= Usage =
Use these shortcodes to display beers in your posts or use the template tags in your theme files:

__Single Beer Display__

These will display a single beer entry given it's ID number (found in "Beers" admin).

* __Shortcode__:

    `[beer id={beer id}]`

* __Template tag__:

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



__List All Beers__

These will display a formatted listing of all beers.

* __Shortcode__:

    `[beer-list]`

* __Template tag__:

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


== Installation ==

1. Unzip the `em-beer-manager.zip` file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. See "Usage" section on the Description page to learn more.


== Frequently Asked Questions ==

= What is the 'Labs' section and how risky is it to use? =

New in v2.1.0 is the EM Beer Manager 'Labs'. This is a section where we plan to introduce new and experimental features for users to test. We do test all of the lab features before making them available, but cannot guarantee that there won't be any issues or bugs when using them, since they are still being worked on. If you experience any issues while using a Labs feature, please contact [labs@wp.erinmorelli.com](mailto:labs@wp.erinmorelli.com).

= How do I access EM Beer Manager beers, styles, and groups in the WordPress API? =

Starting with v2.1.0 you can now access and update EM Beer Manager beers, styles, and groups from the [WordPress API](http://v2.wp-api.org/).

Beers can be accessed using `/wp-json/wp/v2/embm_beers` or individually at `/wp-json/wp/v2/embm_beers/<beer_id>`.

Styles can be accessed using `/wp-json/wp/v2/embm_styles` or individually at `/wp-json/wp/v2/embm_styles/<style_id>`.

Groups can be accessed using `/wp-json/wp/v2/embm_groups` or individually at `/wp-json/wp/v2/embm_groups/<group_id>`.


= I accidentally deleted some of the pre-loaded styles, how do I get them back? =

Starting with v2.0.0, users are now able to easily restore any missing styles. Go to the EM Beer Manager settings page. Under the "Settings" tab, click on the "Restore Styles" button. This will restore any missing styles from the pre-populated BeerAdvocate list. It will not affect any already existing or any custom styles.


= Nothing is working or there are errors after upgrading to version 1.7.0 =

EM Beer Manager updated the beer database structure in v1.7.0 and should automatically make any necessary changes. However in the case that the automatic update does not work, you will need to uninstall EM Beer Manager and install the latest v1.7.x or higher release to maintain functionality. You will not lose any of your Beer or Styles data when uninstalling the older version.


= How do I display an image of my beer next to its name and description? =

When creating your new beer entry, set the "featured image" option in the sidebar to the beer image you wish to use, it will display alongside the entry when the beer is displayed on your site.


= How do I display a single beer on a page? =

Use the `[beer id=#]` shortcode inside the WordPress page editor to add a beer to any page. Replace the "#" with the ID of the beer you wish to display, which is listed on the "Beers" admin page.


= How do I display a list of all my beers? =

Use the `[beer-list]` shortcode inside the WordPress page editor to add a list of all your beers to any page.

You can display only beers from a single group using the `group` option, e.g.: `[beer-list group="Seasonal Beers"]`

You can display only beers from a single style using the `style` option, e.g.: `[beer-list style="India Pale Ale"]`


= I don't want to show that big grey box of information, how do I get rid of it? =

For both the `[beer id=#]` and `[beer-list]` shortcodes there are 2 optional attributes of `show_profile` and `show_extras`. Set both of these to `false` to hide the grey box.

Example: `[beer-list show_profile="false" show_extras="false"]`


= What's the difference between `show_profile` and `show_extras`? =

The `show_profile` setting refers to all the content in the "Beer Profile" information stored for each beer. This includes ABV, IBU, Hops, Malts, Additions, and Yeast.

The `show_extras` setting refers to the "More Beer Information" content stored for each beer, excluding the Untappd check-in button, which is handled separately.


= Why isn't the Untappd checkin button hidden when I set `show_extras` to false? =

The Untappd checkin integration is handled separately from the `show_extras` setting. To hide the button for a single beer, make sure the "Untappd Check-in URL" box is empty - a square Untappd check-in icon will appear on the "Beers" admin page next to the beers where the button is active. You can also completely disable the Untappd options through the "EM Beer Manager" settings page.


= My beer, style, or group pages are not displaying or are showing a 404 error =

Try refreshing your permalinks by going to "Settings" -> "Permalinks" and clicking the "Save Settings" button. If you are running EM Beer Manager 1.7.1 or earlier, it may be due to your site's theme overriding the EM Beer Manager templates. We recommend updating to version 1.8.0 or higher, but you can  also edit the templates in the plugin file to suit your needs. They're located in wp-content -> plugins -> em-beer-manager -> templates.




== Screenshots ==

1. The "Beer" management screen
2. Beer profile information
3. Extra Beer information
4. Special groups taxonomy
5. Pre-populated styles taxonomy
6. Single beer front-end display (with all options enabled)
7. Untappd check-in widget options & display
8. Beer List widget options & display


== Changelog ==

= 2.1.1 =
* Added PUT/POST support for beer metadata via the Wordpress API
* Further improvements to the 'Import from Untappd' Labs feature

= 2.1.0 =
* Added new 'Labs' section with an experimental import from Untappd feature
* Added ability to select which set of Untappd icons to use
* Added integration with WordPress API - *thanks to __tlongren__ for his help with this!*
* Updated translation POT to latest version

= 2.0.1 =
* Fixed an issue with the `[beer]` shortcode where debugging output was being output
* Fixed issue where the option to enable/disable comments was not being saved in the admin settings

= 2.0.0 =
* Massive admin settings page layout overhaul
* Added new "Restore Styles" button to admin settings page
* Renamed template tag functions and restructured input format
* Improved overall CSS to be more compatible with custom themes
* Lots of under-the-hood code improvements and cleanup
* Updated localization POT

= 1.9.6 =
* Fixed 'Warning: Missing argument' error

= 1.9.5 =
* Added new "Beer Number" field to beers post
* Updated admin CSS to blend with WP 4.3+ styles
* Updated beer styles list to populate from BeerAdvocate

= 1.9.4 =
* Fixed localization and translation issues
* Updated .POT language file to latest version

= 1.9.3 =
* Fixed bug with beer-list shortcode pagination not working on index pages

= 1.9.2 =
* Fixed major issue with comments setting overriding site-wide comments

= 1.9.1 =
* Fixed issue with content filter being overridden by other plugins

= 1.9.0 =
* Fixed issue with language textdomain files not loading properly
* Fixed issue with p and br tags not displaying properly in beer posts
* Added new filter options to "beer-list" plugin: paginate, orderby, and order
* Added ability to enable/disable commenting on beers

= 1.8.1 =
* Fixed a bug with the Beer List shortcode not displaying groups properly
* Added "beer count" control to Beer List widget

= 1.8.0 =
* Updated compatibility with WP v3.6
* Removed template files & added filters to make single beer, group taxonomy, and style taxonomy displays integrate more universally with themes
* Added new settings to control how single, group, and style pages are displayed
* Styles will now populate with styles list courtesy of BeerPal

= 1.7.1 =
* Fixed a bug with the beer list shortcode/template code throwing a "Group" error

= 1.7.0 =
* Renamed all EMBM custom post types and taxonomies to include embm_ prefix
* Added new "Group" taxonomy with the ability to customize slug
* "Styles" taxonomy is no longer hierarchical
* Updated "Beer List" widget, shortcode, and template tag to include "Group" filters
* Added "Group" page template
* Fixed a number of escaped input errors being thrown on the settings page

= 1.6.1 =
* Fixed a bug that was throwing an invalid function warning on the settings page

= 1.6.0 =
* Added localization POT
* Added new "Recent Untappd Check-Ins" widget
* Added new settings option for Untappd brewery ID
* Updated settings page with help documentation

= 1.5 =
* Added translatable strings for localization
* Added new "Beer List" widget to display a list of beers with a number of display options
* Added themed template files for styles and single beer page display
* Fixed bug that caused plugin activation to throw a header error

= 1.0 =
* Initial plugin release



== Upgrade Notice ==

= 2.0.1 =
Fixed broken beer shortcode output

= 2.0.0 =
Added "Restore Styles" feature, improved template tag functions, improved CSS compatibility

= 1.9.4 =
Plugin localization and translation is now working properly

= 1.9.2 =
Fixed major issue with comments setting overriding site-wide comments

= 1.9.1 =
Minor bug fix for conflicts with other plugins overriding content filters

= 1.8.1 =
Minor bug fixes and new beer list widget options

= 1.8.0 =
Upgraded templates, permalinks need to be refreshed

= 1.7.1 =
Fixed beer list shortcode "Group" error

= 1.7.0 =
Major bug fixes and a new "Group" taxonomy

= 1.6.1 =
Fixed an invalid function warning bug

= 1.5 =
Fixed plugin activation error bug


== Translations ==

I would love to be able to expand this section - let me know if you are able to contribute!

* English
* Icelandic - *thanks to __rodonmanes__*


== Planned Features ==

* Post/Page “Add Beer” page/post editor button to auto-generate shortcode input
* Customization for “Beer Profile” input fields (e.g. allow users to remove “Additions/Spices” or add “OG”)
* Allow users to select additional fields to show in the beer list widget (e.g. "ABV")
