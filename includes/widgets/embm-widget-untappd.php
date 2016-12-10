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
 * @package EMBM\Widget\Untappd
 */


// Get EMBM settings
$ut_option = get_option('embm_options');

// Get Untappd global settings
$use_untappd = isset($ut_option['embm_untappd_check']) ? $ut_option['embm_untappd_check'] : null;

// If Untappd is enabled, load widget
if ($use_untappd != '1') {

    /**
     * Add Recent Untappd Check-ins widget
     */
    class EMBM_Widget_Untappd_Recent extends WP_Widget
    {
        /**
         * Define Untappd widget construct
         *
         * @return void
         */
        public function __construct()
        {
            // Base widget options
            $widget_options = array(
                'classname'     => 'recent_untappd_widget',
                'description'   => __('Displays a list of recent Untappd brewery check-ins', 'embm')
            );

            // Add contextual help for widget
            add_action('load-widgets.php', array($this, 'helpLoad'));

            // Call parent construct
            parent::__construct(
                'recent_untappd_widget',
                __('Recent Untappd Check-ins', 'embm'),
                $widget_options
            );
        }

        /**
         * Add contextual help after default help loads
         *
         * @return void
         */
        public function helpLoad()
        {
            // Add contextual help
            add_action('admin_head', array($this, 'helpTabs'));
        }

        /**
         * Add custom contextual help to widgets page
         *
         * @return void
         */
        public function helpTabs()
        {
            // Get the current screen
            $screen = get_current_screen();

            // Check if current screen is admin page
            if ($screen->id != 'widgets') {
                return;
            }

            // Get default help data
            $default_help = EMBM_Plugin_help();

            // Untappd Integration help tab
            $screen->add_help_tab($default_help['untappd']);

            // Untappd Integration help
            $screen->add_help_tab(
                array(
                    'id'       => 'embm-untappd-brewery-id',
                    'title'    => __('Untappd Beer ID', 'embm'),
                    'content'  => '<p>'.
                        __('Find your Untappd brewery ID number by going to your brewery\'s official page', 'embm').
                        ' (i.e. <code>https://untappd.com/BreweryName</code>). '.
                        sprintf(
                            __('Click on the %s link in the right-hand sidebar. The link\'s URL will be formatted like this', 'embm'),
                            '"Brewery Feed (RSS)"'
                        ).':</p><p><code>https://untappd.com/rss/brewery/<strong>64324</strong></code></p><p>'.
                        __('The string of numbers at the end of the URL is your brewery ID number.', 'embm').
                        __('If you have authenticated with Untappd in the Labs section, your brewery\'s ID will automatically populate the field.', 'embm').
                        '</p>'
                )
            );
        }

        /**
         * Outputs the options form on admin
         *
         * @param array $instance The widget options
         *
         * @return void
         */
        public function form($instance)
        {
            // Parse widget instance args
            $instance = wp_parse_args(
                (array) $instance,
                array(
                    'title'     => '',
                    'items'     => 3,
                    'brewery'   => ''
                )
            );

            // Set up args
            $title = $instance['title'];
            $items = $instance['items'];
            $brewery = $instance['brewery'];

            // Get brewery ID from Labs
            $brewery_id = get_option('embm_untappd_brewery_id');

            // If an ID was found and one is not set, auto-populate the field
            if (($brewery_id && $brewery_id != '') && (!$brewery || $brewery == '')) {
                $brewery = $brewery_id;
            }

?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'embm'); ?>:</label><br />
                <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" style="width: 100%;" value="<?php echo $title; ?>"   />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('brewery'); ?>"><?php _e('Brewery ID', 'embm'); ?>: </label>
                <input id="<?php echo $this->get_field_id('brewery'); ?>" name="<?php echo $this->get_field_name('brewery'); ?>" type="text" style="width: 30%;" value="<?php echo $brewery; ?>" />
                <a data-help="embm-untappd-brewery-id" class="embm-settings--help">?</a>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Number of items to show', 'embm'); ?>: </label>
                <input id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" type="number" min="1" step="1" style="width: 20%;" value="<?php echo $items; ?>" />
            </p>
<?php
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance The new options
         * @param array $old_instance The old options
         *
         * @return array
         */
        public function update($new_instance, $old_instance)
        {
            $instance = $old_instance;

            $instance['title'] = $new_instance['title'];
            $instance['items'] = $new_instance['items'];
            $instance['brewery'] = $new_instance['brewery'];

            return $instance;
        }

        /**
         * Outputs the content of the widget
         *
         * @param array $args     The widget arguments
         * @param array $instance The widget options
         *
         * @return void
         */
        public function widget($args, $instance)
        {
            // Extract arguments
            extract($args);

            // Set widget options
            $title = apply_filters('widget_title', $instance['title']);
            $items = apply_filters('widget_items', $instance['items']);
            $brewery = apply_filters('widget_brewery', $instance['brewery']);

            // Output pre-widget content
            echo $before_widget;

            // Output widget content
            echo EMBM_Widget_Untappd_Recent_display(
                array(
                    'title'     => $title,
                    'items'     => $items,
                    'brewery'   => $brewery
                )
            );

            // Out put post-widget content
            echo $after_widget;
        }
    }

    // Load the widget
    add_action('widgets_init', create_function('', 'return register_widget("EMBM_Widget_Untappd_Recent");'));


    /**
     * Generate HTML content of Untappd Recent Check-ins widget
     *
     * @param array $beers Widget options
     *
     * @return string/html
     */
    function EMBM_Widget_Untappd_Recent_display($beers)
    {
        // Set widget options
        $title = $beers['title'];
        $items = $beers['items'];
        $brewery = $beers['brewery'];

        // Set Untappd brewery rss URL
        $feed_url = 'https://untappd.com/rss/brewery/'.$brewery;

        // Initialize output string
        $output = '';

        // Widget title
        $output .= "\n".'<h3 class="widget-title">'.$title.'</h3>'."\n";

        // Start check-in list
        $output .= '<ul class="embm-untappd-list">'."\n";

        // Fall back content for when a brewery is not defined
        if (!$brewery) {
            $output .= '<li class="embm-untappd-list-item">';
            $output .= __('A brewery ID number has not been set.', 'embm');
            $output .= '</li>'."\n";
        } else {
            // Extract Untappd xml feed data
            $content = file_get_contents($feed_url);
            $x = new SimpleXmlElement($content);

            // Initiate iterator
            $i = 0;

            // Iterate over XML output
            foreach ($x->channel->item as $entry) {
                // Make sure we only show the number of items specified
                if ($i < $items) {
                    // Start check-in entry
                    $output .= '<li class="embm-untappd-list-item">';
                    $output .= $entry->title."\n";
                    $output .= '<a class="embm-checkin-date" href="'.$entry->link.'" title="'.$entry->title.'">';

                    // Display date using WP timezone setting
                    $offset = get_option('gmt_offset');
                    $postDate = strtotime($entry->pubDate);
                    $newDate = mktime(
                        date('H', $postDate) + $offset,
                        date('i', $postDate),
                        0,
                        date('n', $postDate),
                        date('j', $postDate),
                        date('y', $postDate)
                    );

                    // Output formatted date
                    $output .= date('g:i A - j M y', $newDate);

                    // End check-in entry
                    $output .= '</a></li>'."\n";

                    // Increase counter
                    $i++;
                }
            }
        }

        // End check-in list
        $output .= '</ul>'."\n";

        // Return HTML content
        return $output;
    }
}
