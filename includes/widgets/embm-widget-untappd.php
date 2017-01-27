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
        $screen->add_help_tab($default_help['untappd_limit']);

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
                'brewery'   => '',
                'rating'    => '1',
                'comment'   => '1',
                'venue'   => '1'
            )
        );

        // Set up args
        $title = $instance['title'];
        $items = $instance['items'];
        $brewery = $instance['brewery'];
        $rating = $instance['rating'];
        $comment = $instance['comment'];
        $venue = $instance['venue'];

        // Get brewery ID from Labs
        $brewery_id = get_option('embm_untappd_brewery_id');

        // If an ID was found and one is not set, auto-populate the field
        if (($brewery_id && $brewery_id != '') && (!$brewery || $brewery == '')) {
            $brewery = $brewery_id;
        }

?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'embm'); ?>:</label><br />
            <input
                id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>"
                type="text"
                style="width:100%;"
                value="<?php echo $title; ?>"
            />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('brewery'); ?>"><?php _e('Brewery ID', 'embm'); ?>: </label>
            <input
                id="<?php echo $this->get_field_id('brewery'); ?>"
                name="<?php echo $this->get_field_name('brewery'); ?>"
                type="number"
                style="width:30%;"
                value="<?php echo $brewery; ?>"
            />
            <a data-help="embm-untappd-brewery-id" class="embm-settings--help">?</a>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Number of items to show', 'embm'); ?>: </label>
            <input
                id="<?php echo $this->get_field_id('items'); ?>"
                name="<?php echo $this->get_field_name('items'); ?>"
                type="number"
                min="1"
                max="15"
                step="1"
                style="width:50px;"
                value="<?php echo $items; ?>"
            /><br />
            <small><?php printf(__('(max. %d)', 'embm'), 15); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show check-in ratings', 'embm'); ?>:</label>
            <input
                id="<?php echo $this->get_field_id('rating'); ?>"
                name="<?php echo $this->get_field_name('rating'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $rating); ?>
            />

        </p>
        <p>
            <label for="<?php echo $this->get_field_id('comment'); ?>"><?php _e('Show check-in comments', 'embm'); ?>:</label>
            <input
                id="<?php echo $this->get_field_id('comment'); ?>"
                name="<?php echo $this->get_field_name('comment'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $comment); ?>
            />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('venue'); ?>"><?php _e('Show check-in venue', 'embm'); ?>:</label>
            <input
                id="<?php echo $this->get_field_id('venue'); ?>"
                name="<?php echo $this->get_field_name('venue'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $venue); ?>
            />
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
        $instance['rating'] = $new_instance['rating'];
        $instance['comment'] = $new_instance['comment'];
        $instance['venue'] = $new_instance['venue'];

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
        $rating = apply_filters('widget_rating', $instance['rating']);
        $comment = apply_filters('widget_comment', $instance['comment']);
        $venue = apply_filters('widget_venue', $instance['venue']);

        // Output pre-widget content
        echo $before_widget;

        // Output widget content
        echo EMBM_Widget_Untappd_Recent_display(
            array(
                'title'     => $title,
                'items'     => $items,
                'brewery'   => $brewery,
                'rating'    => $rating,
                'comment'   => $comment,
                'venue'   => $venue
            )
        );

        // Out put post-widget content
        echo $after_widget;
    }
}

/**
 * Set up and register Untappd widget
 *
 * @return void
 */
function EMBM_Widget_Untappd_Recent_register()
{
    // Get EMBM settings
    $ut_option = get_option('embm_options');

    // Get Untappd global settings
    $use_untappd = isset($ut_option['embm_untappd_check']) ? $ut_option['embm_untappd_check'] : null;

    // If Untappd is disabled, exit
    if ($use_untappd == '1') {
        return;
    }

    // Include Untappd functions
    if (!function_exists('EMBM_Admin_Untappd_checkins')) {
        include_once EMBM_PLUGIN_DIR.'includes/admin/embm-admin-untappd.php';
    }
    if (!function_exists('EMBM_Admin_Authorize_token')) {
        include_once EMBM_PLUGIN_DIR.'includes/admin/embm-admin-authorize.php';
    }

    // Register widget
    return register_widget('EMBM_Widget_Untappd_Recent');
}

// Load the widget
add_action('widgets_init', 'EMBM_Widget_Untappd_Recent_register');

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
    $brewery_id = $beers['brewery'];

    // Initialize output string
    $output = '';

    // Widget title
    $output .= "\n".'<h3 class="widget-title">'.$title.'</h3>'."\n";

    // Start check-in list
    $output .= '<ul class="embm-untappd-list">'."\n";

    // Fall back content for when a brewery is not defined
    if (!$brewery_id) {
        $output .= '<li class="embm-untappd-list-item">';
        $output .= __('A brewery ID number has not been set.', 'embm');
        $output .= '</li>'."\n";
        $output .= '</ul>'."\n";

        // Return
        return $output;
    }

    // Get token
    $token = EMBM_Admin_Authorize_token();

    // // Make sure we're authorized
    // if (null !== $token) {
    //     // Get API content
    //     $output .= EMBM_Widget_Untappd_Recent_Display_api($token, $brewery_id, $count);
    // } else {

    // Fall back to XML output
    $output .= EMBM_Widget_Untappd_Recent_Display_xml($beers);

    // }

    // End check-in list
    $output .= '</ul>'."\n";

    // Return HTML content
    return $output;
}

/**
 * Generate HTML content of Untappd Recent Check-ins widget from the API
 *
 * @param string $token   Untappd API token
 * @param int    $brewery Untappd brewery ID
 * @param int    $count   The number of checkins to display
 *
 * @return string/html
 */
function EMBM_Widget_Untappd_Recent_Display_api($token, $brewery, $count)
{
    // Set API Root
    $api_root = EMBM_UNTAPPD_API_URL.$token;

    // Get checkins
    $checkins = EMBM_Admin_Untappd_checkins($api_root, $brewery);

    /// Start output
    $output = '';

    // // Iterate over XML output
    // foreach (range(0, $count-1) as $i) {
    //     // Get checkin
    //     $checkin = $checkins->items[$i];
    //     $user = $checkin->user;
    //     $beer = $checkin->beer;
    //     $venue = $checkin->venue;

    //     // Start check-in
    //     $output .= '<li class="embm-untappd-list-item">';

    //     // Formate text
    //     $output .= sprintf(
    //         '%s %s is drinking a %s at %s',
    //         $user->first_name,
    //         $user->last_name,
    //         $beer->beer_name,
    //         $venue->venue_name
    //     );

    //     $output .= '<a class="embm-checkin-date" href="';
    //     $output .= sprintf(
    //         'https://untappd.com/user/%s/checkin/%s',
    //         $user->user_name,
    //         $checkin->checkin_id
    //     );
    //     $output .= '">';

    //     // Output formatted date
    //     $output .= EMBM_Widget_Untappd_Recent_Display_date($checkin->created_at);

    //     // End check-in entrt
    //     $output .= '</a></li>'."\n";
    // }

    // Return HTML output
    return $output;
}

/**
 * Generate HTML content of Untappd Recent Check-ins widget from XML
 *
 * @param array $beers Untappd widget options
 *
 * @return string/html
 */
function EMBM_Widget_Untappd_Recent_Display_xml($beers)
{
    // Get widget options
    $count = $beers['items'];
    $brewery_id = $beers['brewery'];
    $show_rating = ($beers['rating'] == '1');
    $show_comment = ($beers['comment'] == '1');
    $show_venue = ($beers['venue'] == '1');

    // Get XML content
    $xml = EMBM_Admin_Untappd_Checkins_xml($brewery_id);

    // Check for errors
    if (!is_object($xml)) {
        $error = __('There was a problem retrieving Untappd check-ins. Please try again later.', 'embm');
        return sprintf('<p class="embm-untappd-list--empty">%s</p>', $error);
    }

    // Start output
    $output = '';

    // Iterate over XML output
    foreach (range(0, $count-1) as $i) {
        // Get checkin
        $entry = $xml->channel->item[$i];

        // Start check-in entry
        $output .= '<li class="embm-untappd-list--item">';

        // Set up title regex
        $title_regex = '/^(.*) is drinking a (?(?=.* at .*$)((.*) at (.*)$)|((.*)$))/i';

        // Parse title
        if (preg_match($title_regex, $entry->title, $title)) {
            // Get data
            $user = $title[1];
            $beer = (array_key_exists(6, $title) && !preg_match('/^\s+$/', $title[6])) ? $title[6] : $title[3];
            $venue = (array_key_exists(4, $title) && !preg_match('/^\s+$/', $title[4])) ? $title[4] : null;

            // Set output
            $title_output = '<span class="embm-untappd-list--item-title">';
            $title_output .= '<strong>'.$user.'</strong> ';
            $title_output .= __('is drinking a', 'embm');
            $title_output .= ' <strong>'.$beer.'</strong>';
            $title_output .= '</span>';

            // Set optional location
            if ($venue && !is_null($venue)) {
                $venue_output = '<span class="embm-untappd-list--item-venue">'.$venue.'</span>';
            }
        }

        // Parse description
        if (property_exists($entry, 'description') && is_object($entry->description)) {
            if (preg_match('/^(.*)\((\d(\.\d{1,2})?)\/5 stars\)$/i', $entry->description[0], $description)) {
                // Get rating
                if (array_key_exists(2, $description)) {
                    $rating_output = '<span class="embm-untappd-list--item-rating embm-beer--rating-stars">';
                    $rating_output .= EMBM_Output_Rating_stars(floatval($description[2]));
                    $rating_output .= '</span>';
                }
                if (array_key_exists(1, $description) && !preg_match('/^\s+$/', $description[1])) {
                    $comment_output = '<span class="embm-untappd-list--item-comment">'.$description[1].'</span>';
                }
            }
        }

        // Put it all together
        if (isset($title_output)) {
            $output .= $title_output;
        }
        if (isset($rating_output) && $show_rating) {
            $output .= $rating_output;
        }
        if (isset($comment_output) && $show_comment) {
            $output .= $comment_output;
        }
        if (isset($venue_output) && $show_venue) {
            $output .= $venue_output;
        }

        // Output formatted date
        $output .= '<span class="embm-untappd-list--item-meta">';
        $output .= '<a class="embm-untappd-list--item-date" href="'.$entry->link.'" target="_blank">';
        $output .= EMBM_Widget_Untappd_Recent_Display_date($entry->pubDate);
        $output .= '</a>';
        $output .= '<a class="embm-untappd-list--item-link" href="'.$entry->link.'" target="_blank">';
        $output .= '<span>'.__('View Full Check-in', 'embm').'</span>';
        $output .= '</a>';

        // End check-in entry
        $output .= '</li>'."\n";
    }

    // Get star styles
    $output .= EMBM_Output_Rating_styles();

    // Return HTML output
    return $output;
}

/**
 * Display data using WP settings
 *
 * @param string $date The date to display
 *
 * @return string
 */
function EMBM_Widget_Untappd_Recent_Display_date($date)
{
    // Display date using WP timezone setting
    $offset = get_option('gmt_offset');
    $post_date = strtotime($date);
    $new_date = mktime(
        date('H', $post_date) + $offset,
        date('i', $post_date),
        0,
        date('n', $post_date),
        date('j', $post_date),
        date('y', $post_date)
    );

    // Output formatted date
    return date('j M y', $new_date);
}
