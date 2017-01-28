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
        // Global error tracker
        $this->widget_errors = array();

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
                'items'     => '3',
                'brewery'   => '',
                'rating'    => '1',
                'comment'   => '1',
                'venue'     => '1',
                'thumb'     => '1',
                'more'      => '1'
            )
        );

        // Set up args
        $title = $instance['title'];
        $items = $instance['items'];
        $brewery = $instance['brewery'];
        $rating = $instance['rating'];
        $comment = $instance['comment'];
        $venue = $instance['venue'];
        $more = $instance['more'];
        $thumb = $instance['thumb'];
        $errors = '';

        // Get brewery ID from Labs
        $brewery_id = get_option('embm_untappd_brewery_id');

        // If an ID was found and one is not set, auto-populate the field
        if (($brewery_id && $brewery_id != '') && (!$brewery || $brewery == '')) {
            $brewery = $brewery_id;
        }

        // Get any errors
        foreach ($this->widget_errors as $error) {
            if (!array_key_exists($error, $GLOBALS['EMBM_NOTICE_MAP']['widget-error'])) {
                continue;
            }

            // Get error content
            $notice = $GLOBALS['EMBM_NOTICE_MAP']['widget-error'][$error];

            // Add error to output
            $errors .= sprintf(
                '<p class="notice notice-%s" style="font-size:12px;background:#fafafa;"><strong>%s</strong> %s</p>',
                $notice['type'],
                $notice['title'],
                $notice['message']
            );
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
            <label for="<?php echo $this->get_field_id('brewery'); ?>"><?php _e('Brewery ID', 'embm'); ?>&nbsp;</label>
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
            <label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Number of items to show', 'embm'); ?>:&nbsp;</label>
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
            <small><em>(<?php printf(__('Max. %d', 'embm'), 15); ?></em>)</small>
        </p>
        <p>
            <input
                id="<?php echo $this->get_field_id('rating'); ?>"
                name="<?php echo $this->get_field_name('rating'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $rating); ?>
            />
            <label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show check-in ratings', 'embm'); ?></label>
        </p>
        <p>
            <input
                id="<?php echo $this->get_field_id('comment'); ?>"
                name="<?php echo $this->get_field_name('comment'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $comment); ?>
            />
            <label for="<?php echo $this->get_field_id('comment'); ?>"><?php _e('Show check-in comments', 'embm'); ?></label>
        </p>
        <p>
            <input
                id="<?php echo $this->get_field_id('venue'); ?>"
                name="<?php echo $this->get_field_name('venue'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $venue); ?>
            />
            <label for="<?php echo $this->get_field_id('venue'); ?>"><?php _e('Show check-in venue', 'embm'); ?></label>
        </p>
        <p style="margin-bottom:0;">
            <input
                id="<?php echo $this->get_field_id('thumb'); ?>"
                name="<?php echo $this->get_field_name('thumb'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $thumb); ?>
            />
            <label for="<?php echo $this->get_field_id('thumb'); ?>"><?php _e('Show check-in avatar', 'embm'); ?></label>
        </p>
        <p style="line-height:1;margin-top:0;padding-left:22px;">
            <small><em>(<?php _e('Only shows default generic avatar when not authenticated with Untappd.', 'embm'); ?>)</em></small>
        </p>
        <p>
            <input
                id="<?php echo $this->get_field_id('more'); ?>"
                name="<?php echo $this->get_field_name('more'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $more); ?>
            />
            <label for="<?php echo $this->get_field_id('more'); ?>"><?php _e('Show "View More" link', 'embm'); ?></label>
        </p>
        <?php echo $errors; ?>
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

        // Save new settings
        $instance['title'] = isset($new_instance['title']) ? $new_instance['title'] : $old_instance['title'];
        $instance['items'] = isset($new_instance['items']) ? $new_instance['items'] : $old_instance['items'];
        $instance['brewery'] = isset($new_instance['brewery']) ? $new_instance['brewery'] : $old_instance['brewery'];
        $instance['rating'] = isset($new_instance['rating']) ? '1' : '';
        $instance['comment'] = isset($new_instance['comment']) ? '1' : '';
        $instance['venue'] = isset($new_instance['venue']) ? '1' : '';
        $instance['thumb'] = isset($new_instance['thumb']) ? '1' : '';
        $instance['more'] = isset($new_instance['more']) ? '1' : '';

        // Fallback to default count of 3 if not set
        if (!isset($instance['items']) || $instance['items'] == '') {
            $instance['items'] = '3';
        }

        // Check if we need to refresh cached data
        if ($old_instance['brewery'] !== $new_instance['brewery']) {
            $has_errors = false;

            // Refresh XML data
            $xml_res = EMBM_Admin_Untappd_Checkins_xml($new_instance['brewery'], true);

            // Check for errors
            if (!is_object($xml_res)) {
                $has_errors = true;
            }

            // Get token
            $token = EMBM_Admin_Authorize_token();

            // Check for token
            if (null !== $token) {
                // Update data from API
                $api_res = EMBM_Admin_Untappd_checkins(EMBM_UNTAPPD_API_URL.$token, $new_instance['brewery'], true);

                // Check for errors
                if (!is_object($api_res)) {
                    $has_errors = true;
                } elseif ($has_errors) {
                    $has_errors = false;
                }
            }

            // Log any errors
            if ($has_error && !array_key_exists('1', $this->widget_errors)) {
                array_push($this->widget_errors, '1');
            }
        }

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

        // Output pre-widget content
        echo $before_widget;

        // Output widget content
        echo EMBM_Widget_Untappd_Recent_display(
            array(
                'title'     => apply_filters('widget_title', $instance['title']),
                'items'     => apply_filters('widget_items', $instance['items']),
                'brewery'   => apply_filters('widget_brewery', $instance['brewery']),
                'rating'    => apply_filters('widget_rating', $instance['rating']),
                'comment'   => apply_filters('widget_comment', $instance['comment']),
                'venue'     => apply_filters('widget_venue', $instance['venue']),
                'thumb'     => apply_filters('widget_thumb', $instance['thumb']),
                'more'      => apply_filters('widget_more', $instance['more'])
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
 * @param array $beers     Widget options
 * @param bool  $force_xml Whether or not to force XML output
 *
 * @return string/html
 */
function EMBM_Widget_Untappd_Recent_display($beers, $force_xml = false)
{
    // Set widget options
    $title = $beers['title'];
    $count = intval($beers['items']);
    $brewery_id = $beers['brewery'];
    $show_more = $beers['more'];

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

    // Display widget content from Untappd
    if (null !== $token && !$force_xml) {
        // Get API content
        $output .= EMBM_Widget_Untappd_Recent_Display_api($token, $beers);
    } else {
        // Fall back to XML output
        $output .= EMBM_Widget_Untappd_Recent_Display_xml($beers);
    }

    // Start footer
    $output .= '<li><span class="embm-untappd-list--footer">';

    // Add 'more' link
    if ($show_more) {
        $more_text = __('View More', 'embm');
        $output .= '<span class="embm-untappd-list--more">';
        $output .= '<a href="https://untappd.com/brewery/'.$brewery_id.'" target="_blank" title="' . $more_text . '">';
        $output .= '<span>' . $more_text . '</span>';
        $output .= '<span class="dashicons dashicons-arrow-right-alt"></span>';
        $output .= '</a></span>';
    }

    // Add Untappd credit
    $credit_text = __('Powered by Untappd', 'embm');
    $credit_class = $show_more ? '' : ' embm-untappd-list--credit__left';
    $output .= '<span class="embm-untappd-list--credit'.$credit_class.'">';
    $output .= '<a href="https://untappd.com" target="_blank" rel="nofollow" title="' . $credit_text . '">';
    $output .= '<img src="' . EMBM_PLUGIN_URL .'/assets/img/ut-credit.png" alt="' . $credit_text . '" border="0" />';
    $output .= '</a></span>';

    // End footer
    $output.= '</span></li>';

    // End check-in list
    $output .= '</ul>'."\n";

    // Get star styles
    $output .= EMBM_Output_Rating_styles();

    // Return HTML content
    return $output;
}

/**
 * Generate HTML content of Untappd Recent Check-ins widget from the API
 *
 * @param string $token Untappd API token
 * @param int    $beers Widget options
 *
 * @return string/html
 */
function EMBM_Widget_Untappd_Recent_Display_api($token, $beers)
{
    // Set API Root
    $api_root = EMBM_UNTAPPD_API_URL.$token;

    // Get widget options
    $count = intval($beers['items']);
    $brewery_id = $beers['brewery'];
    $show_rating = ($beers['rating'] == '1');
    $show_comment = ($beers['comment'] == '1');
    $show_venue = ($beers['venue'] == '1');
    $show_thumb = ($beers['thumb'] == '1');

    // Get checkins
    $checkins = EMBM_Admin_Untappd_checkins($api_root, $brewery_id);

    // Check for errors
    if (!is_object($checkins)) {
        // Force XML output as fall back
        return EMBM_Widget_Untappd_Recent_display($beers, true);
    }

    /// Start output
    $output = '';

    // Iterate over XML output
    foreach (range(0, $count-1) as $i) {
        // Reset outputs
        $title_output = null;
        $venue_output = null;
        $comment_output = null;
        $rating_output = null;

        // Get checkin
        $checkin = $checkins->items[$i];
        $user = $checkin->user;
        $beer = $checkin->beer;
        $venue = $checkin->venue;

        // Set link format
        $link_format = '<a class="embm-untappd-list--item-link" href="%s" target="_blank">%s</a>';

        // Set user & checkin links
        $user_link = 'https://untappd.com/user/'.$user->user_name;
        $checkin_link = $user_link.'/'.$checkin->checkin_id;

        // Start check-in entry
        $output .= '<li class="embm-untappd-list--item">';

        // Set title output
        $title_output = '<span class="embm-untappd-list--item-title">';
        $title_output .= sprintf($link_format, $user_link, $user->first_name.' '.$user->last_name);
        $title_output .= ' '.__('is drinking a', 'embm').' ';
        $title_output .= sprintf($link_format, 'https://untappd.com/beer/'.$beer->bid, $beer->beer_name);
        $title_output .= '</span>';

        // Set optional location
        if ($venue && is_object($venue) && property_exists($venue, 'venue_id')) {
            $venue_output = '<span class="embm-untappd-list--item-venue">';
            $venue_output .= sprintf($link_format, 'https://untappd.com/venue/'.$venue->venue_id, $venue->venue_name);
            $venue_output .= '</span>';
        }

        // Set description
        if (property_exists($checkin, 'checkin_comment') && $checkin->checkin_comment != '') {
            $comment_output = '<span class="embm-untappd-list--item-comment">'.$checkin->checkin_comment.'</span>';
        }

        // Set rating
        if (property_exists($checkin, 'rating_score') && $checkin->rating_score != 0) {
            $rating_output = '<span class="embm-untappd-list--item-rating embm-beer--rating-stars" title="'.$checkin->rating_score.'">';
            $rating_output .= EMBM_Output_Rating_stars(floatval($checkin->rating_score));
            $rating_output .= '</span>';
        }

        // Show thumbnail
        if ($show_thumb) {
            $avatar_url = 'https://i1.wp.com/untappd.akamaized.net/site/assets/images/default_avatar_v2.jpg';
            if (property_exists($user, 'user_avatar') && $user->user_avatar != '') {
                $avatar_url = $user->user_avatar;
            }
            $output .= '<span class="embm-untappd-list--item-thumb">';
            $output .= sprintf('<img src="%s" border="0" alt="%s">', $avatar_url, $user->user_name);
            $output .= '</span>';
        }

        // Start content
        $content_class = $show_thumb ? ' embm-untappd-list--item-content__thumb' : '';
        $output .= '<span class="embm-untappd-list--item-content'.$content_class.'">';

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

        // Output formatted date
        $output .= '<span class="embm-untappd-list--item-meta">';
        $output .= '<span class="embm-untappd-list--item-date">';
        $output .= sprintf($link_format, $checkin_link, EMBM_Widget_Untappd_Recent_Display_date($checkin->created_at));
        $output .= '</a></span>';

        // Optional venue output
        if (isset($venue_output) && $show_venue) {
            $output .= $venue_output;
        }

        // Out put link to full check-in
        $output .= sprintf($link_format, $checkin_link, __('View Full Check-in', 'embm'));

        // End meta & content
        $output .= '</span></span>';

        // End check-in entry
        $output .= '</li>'."\n";
    }

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
    $count = intval($beers['items']);
    $brewery_id = $beers['brewery'];
    $show_rating = ($beers['rating'] == '1');
    $show_comment = ($beers['comment'] == '1');
    $show_venue = ($beers['venue'] == '1');
    $show_thumb = ($beers['thumb'] == '1');

    // Set default avatar URL
    $avatar_url = 'https://i1.wp.com/untappd.akamaized.net/site/assets/images/default_avatar_v2.jpg';

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

        // Set up check-in link
        $link = '<a class="embm-untappd-list--item-link" href="'.$entry->link.'" target="_blank">';

        // Parse title
        if (preg_match($title_regex, $entry->title, $title)) {
            // Get data
            $user = $title[1];
            $beer = (array_key_exists(6, $title) && !preg_match('/^\s+$/', $title[6])) ? $title[6] : $title[3];
            $venue = (array_key_exists(4, $title) && !preg_match('/^\s+$/', $title[4])) ? $title[4] : null;

            // Set output
            $title_output = '<span class="embm-untappd-list--item-title">';
            $title_output .= $link.$user.'</a> ';
            $title_output .= __('is drinking a', 'embm');
            $title_output .= ' '.$link.$beer.'</a>';
            $title_output .= '</span>';

            // Set optional location
            if ($venue && !is_null($venue)) {
                $venue_output = '<span class="embm-untappd-list--item-venue">'.$link.$venue.'</a></span>';
            }
        }

        // Parse description
        if (property_exists($entry, 'description') && is_object($entry->description)) {
            if (preg_match('/^(.*)\((\d(\.\d{1,2})?)\/5 stars\)$/i', $entry->description[0], $description)) {
                // Get rating
                if (array_key_exists(2, $description)) {
                    $rating_output = '<span class="embm-untappd-list--item-rating embm-beer--rating-stars" title="'.$description[2].'">';
                    $rating_output .= EMBM_Output_Rating_stars(floatval($description[2]));
                    $rating_output .= '</span>';
                }
                if (array_key_exists(1, $description) && !preg_match('/^\s+$/', $description[1])) {
                    $comment_output = '<span class="embm-untappd-list--item-comment">'.$description[1].'</span>';
                }
            }
        }

        // Show thumbnail
        if ($show_thumb) {
            $output .= '<span class="embm-untappd-list--item-thumb">';
            $output .= sprintf('<img src="%s" border="0" alt="%s">', $avatar_url, $user);
            $output .= '</span>';
        }

        // Start content
        $content_class = $show_thumb ? ' embm-untappd-list--item-content__thumb' : '';
        $output .= '<span class="embm-untappd-list--item-content'.$content_class.'">';

        // Put the rest together
        if (isset($title_output)) {
            $output .= $title_output;
        }
        if (isset($rating_output) && $show_rating) {
            $output .= $rating_output;
        }
        if (isset($comment_output) && $show_comment) {
            $output .= $comment_output;
        }

        // Output formatted date
        $output .= '<span class="embm-untappd-list--item-meta">';
        $output .= '<span class="embm-untappd-list--item-date">';
        $output .= $link.EMBM_Widget_Untappd_Recent_Display_date($entry->pubDate);
        $output .= '</a></span>';

        // Optional venue output
        if (isset($venue_output) && $show_venue) {
            $output .= $venue_output;
        }

        // Out put link to full check-in
        $output .= $link.__('View Full Check-in', 'embm').'</a>';

        // End meta & content
        $output .= '</span></span>';

        // End check-in entry
        $output .= '</li>'."\n";
    }

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
