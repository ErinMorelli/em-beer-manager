# EM Beer Manager #

*by Erin Morelli*

Manage your beers with WordPress. Integrates simply with Untappd beer checkins. Great for everyone from home brewers to professional breweries!


### Overview ####

This plugin allows beer creators from home brewers to professional breweries to easily manage and display their beers. Includes a comprehensive beer management section with a variety of options, including:

* A custom beer "style" taxonomy for classifying your beers pre-populated with styles from BeerPal
* A customizable "group" taxonomy for categorizing and grouping your beers
* Shortcodes and template tags for displaying all or a select number of beers
* Custom meta boxes to store detailed information about each beer, including abv, ibu, and ingredients
* Simple beer checkin integration with Untappd
* A "Beer List" widget for simply displaying your beers in sidebars
* A "Recent Check-Ins" widget for displaying recent beer check-ins for your brewery on Untappd
* Custom page display for beers and styles

#### Planned Features ####

* Add new sort options to beer list widget
* Post/Page “Add Beer” page/post editor button to auto-generate shortcode input
* Customization for “Beer Profile” input fields (e.g. allow users to remove “Additions/Spices” or add “OG”)
* Expand Untappd integration to include further brewery/beer options
* Add a ratings/review system (possibly as separate add-on)

#### Screenshots ####

1. [The "Beer" management screen](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-1.jpg)
2. [Beer profile information](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-2.jpg)
3. [Extra Beer information](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-3.jpg)
4. [Special groups taxonomy](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-4.jpg)
5. [Pre-populated styles taxonomy](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-5.jpg)
6. [Single beer front-end display (with all options enabled)](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-6.jpg)
7. [Untappd check-in widget options & display](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-7.jpg)
8. [Beer List widget options & display](https://raw.github.com/ErinMorelli/em-beer-manager/master/screenshot-8.jpg)



*****


### Latest Release ###


#### [Version 1.9.3 - Minor Bug Fix](https://github.com/ErinMorelli/em-beer-manager/releases/tag/v1.9.3) ###
* Fixed bug with beer-list shortcode pagination not working on index pages



*****


### Installation ###

1. Unzip the `em-beer-manager.zip` file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. See below to learn more about usage.


*****

### Usage ###


Use these shortcodes to display beers in your posts or use the template tags in your theme files.


#### Single Beer Display ####

`[beer id={beer id}]`

`<?php echo embm_beer_single( beer id (required), show_profile, show_extras ); ?>`

This will display a single beer entry given it's ID number (found in "Beers" admin). Optional attributes for both shortcode and template tag:

* __show_profile=`"true/false"`__ (Default = `true`) // *Displays or hides the "Beer Profile" information*

* __show_extras=`"true/false"`__ (Default = `true`) // *Displays or hides the "More Information" section*


#### List All Beers ####

`[beer-list]`

`<?php echo embm_beer_list( exclude, show_profile, show_extras, style, group,` 
`beers_per_page, paginate, orderby, order ); ?>`
     
This will display a formatted listing of all beers in the database. Optional attributes for both shortcode and template tag:

* __exclude=`"beer ids"`__ (String separated by commas e.g. `"4,23,24"`) // *Hides listed beers from output*

* __show_profile=`"true/false"`__ (Default = `true`) // *Displays or hides the "Beer Profile" information for each listing*

* __show_extras=`"true/false"`__ (Default = `true`) // *Displays or hides the "More Information" section for each listing*

* __style=`"style name"`__ (String e.g. `"India Pale Ale"`) // *Displays only beers belonging to a specific beer style*
    
* __group=`"group name"`__ (String e.g. `"Seasonals"`) // *Displays only beers belonging to a specific group*

* __beers\_per\_page=`"number"`__ (Default = `-1`, shows all beers on one page) // *Paginates output and displays the given number of beers per page*
   
* __paginate=`"true/false"`__ (Default = `true`) // *Disables/enables pagination*
    
* __orderby=`"string"`__ (Default = `date`, see [this list](http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters) for options) // *Orders output by given paramater*

* __order=`"desc/asc"`__ (Default = `desc`) // *List beer by `orderby` value in ascending or descending order*
