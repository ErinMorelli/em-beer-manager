=== EM Beer Manager ===
Contributors: ErinMorelli
Donate link: http://erinmorelli.com/wordpress/
Tags: beer, beers, brewery, untappd
Requires at least: 3.0.1
Tested up to: 3.6.0
Stable tag: 1.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage your beers with WordPress. Integrates simply with Untappd beer checkins. Great for everyone from home brewers to professional breweries!


== Description ==

This plugin allows beer creators from home brewers to professional breweries to easily manage and display their beers. Includes a comprehensive beer management section with a variety of options, including:

* A custom beer "style" taxonomy for classifying your beers pre-populated with styles from BeerPal
* A customizable "group" taxonomy for categorizing and grouping your beers
* Shortcodes and template tags for displaying all or a select number of beers
* Custom meta boxes to store detailed information about each beer, including ABV, IBU, and ingredients
* Simple beer checkin integration with Untappd
* A "Beer List" widget for simply displaying your beers in sidebars
* A "Recent Check-Ins" widget for displaying recent beer check-ins for your brewery on Untappd
* Custom page display for beers and styles



### Usage ###

Use these shortcodes to display beers in your posts or use the template tags in your theme files.


#### Single Beer Display ####

     [beer id={beer id}]

     <?php echo embm_beer_single( beer id (required), show_profile, show_extras ); ?>

This will display a single beer entry given it's ID number (found in "Beers" admin). Optional attributes for both shortcode and template tag:

* __show_profile=`"true/false"`__ (Default = `true`)
     
    *Displays or hides the "Beer Profile" information*

* __show_extras=`"true/false"`__ (Default = `true`)
     
    *Displays or hides the "More Information" section*


#### List All Beers ####


     [beer-list]

     <?php echo embm_beer_list( exclude, show_profile, show_extras, style, group, beers_per_page ); ?>
     
This will display a formatted listing of all beers in the database. Optional attributes for both shortcode and template tag:

* __exclude=`"beer ids"`__ (String separated by commas e.g. `"4,23,24"`)

    *Hides listed beers from output*

* __show_profile=`"true/false"`__ (Default = `true`)

    *Displays or hides the "Beer Profile" information for each listing*

* __show_extras=`"true/false"`__ (Default = `true`)

    *Displays or hides the "More Information" section for each listing*

* __style=`"style name"`__ (String e.g. `"India Pale Ale"`)

    *Displays only beers belonging to a specific beer style*
    
* __group=`"group name"`__ (String e.g. `"Seasonals"`)

    *Displays only beers belonging to a specific group*

* __beers\_per\_page=`"number"`__ (Default = `-1`, shows all beers on one page)

    *Paginates output and displays the given number of beers per page*




### Planned Features ###

* Post/Page "Add Beer" button to auto-generate shortcode input
* Possibly add a ratings/review system down-the-line


== Installation ==

1. Unzip the `em-beer-manager.zip` file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. See "Usage" section on the Description page to learn more.


== Frequently Asked Questions ==

= Nothing is working or there are errors after upgrading to version 1.7.0 = 
EM Beer Manager updated the beer database structure in v1.7.0 and should automatically make any necessary changes. However in the case that the automatic update does not work, you will need to uninstall EM Beer Manager and install the latest v1.7.x or higher release to maintain functionality. You will not lose any of your Beer or Styles data when uninstalling the older version.


= How do I display an image of my beer next to its name and description? =
When creating your new beer entry, set the "featured image" option in the sidebar to the beer image you wish to use, it will display alongside the entry when the beer is displayed on your site. 


= How do I display a single beer on a page? =

Use the `[beer id=#]` shortcode inside the WordPress page editor to add a beer to any page. Replace the "#" with the ID of the beer you wish to display, which is listed on the "Beers" admin page.


= How do I display a list of all my beers? =

Use the `[beer-list]` shortcode inside the WordPress page editor to add a list of all your beers to any page.


= I don't want to show that big grey box of information, how do I get rid of it? =

For both the `[beer id=#]` and `[beer-list]` shortcodes there are 2 optional attributes of `show_profile` and `show_extras`. Set both of these to `false` to hide the grey box. 

Example: `[beer-list show_profile="false" show_extras="false"]`


= What's the difference between `show_profile` and `show_extras`? =

The `show_profile` setting refers to all the content in the "Beer Profile" information stored for each beer. This includes ABV, IBU, Hops, Malts, Additions, and Yeast. The `show_extras` setting refers to the "Additional Notes" and "Availability" information stored for each beer. 


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