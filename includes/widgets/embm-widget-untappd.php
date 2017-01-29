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
            'classname'     => 'embm_recent_untappd_widget',
            'description'   => __('Displays a list of recent Untappd brewery check-ins', 'embm')
        );

        // Add contextual help for widget
        add_action('load-widgets.php', array($this, 'helpLoad'));

        // Call parent construct
        parent::__construct(
            'embm_recent_untappd_widget',
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

        // Get token
        $token = EMBM_Admin_Authorize_token();
        $api_root = null;

        // Check for token
        if (null !== $token) {
            $api_root = EMBM_UNTAPPD_API_URL.$token;
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
    <div class="embm-untappd-widget">
        <p class="embm-untappd-widget--title">
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'embm'); ?>:</label><br />
            <input
                id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>"
                type="text"
                style="width:100%;"
                value="<?php echo $title; ?>"
            />
        </p>
        <p class="embm-untappd-widget--brewery">
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
        <p class="embm-untappd-widget--count">
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
        <hr />
        <p class="embm-untappd-widget--rating">
            <input
                id="<?php echo $this->get_field_id('rating'); ?>"
                name="<?php echo $this->get_field_name('rating'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $rating); ?>
            />
            <label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show check-in ratings', 'embm'); ?></label>
        </p>
        <p class="embm-untappd-widget--comment">
            <input
                id="<?php echo $this->get_field_id('comment'); ?>"
                name="<?php echo $this->get_field_name('comment'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $comment); ?>
            />
            <label for="<?php echo $this->get_field_id('comment'); ?>"><?php _e('Show check-in comments', 'embm'); ?></label>
        </p>
        <p class="embm-untappd-widget--venue">
            <input
                id="<?php echo $this->get_field_id('venue'); ?>"
                name="<?php echo $this->get_field_name('venue'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $venue); ?>
            />
            <label for="<?php echo $this->get_field_id('venue'); ?>"><?php _e('Show check-in venue', 'embm'); ?></label>
        </p>
        <p class="embm-untappd-widget--thumb" style="margin-bottom:0;">
            <input
                id="<?php echo $this->get_field_id('thumb'); ?>"
                name="<?php echo $this->get_field_name('thumb'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $thumb); ?>
            />
            <label for="<?php echo $this->get_field_id('thumb'); ?>"><?php _e('Show check-in avatar', 'embm'); ?></label>
        </p>
        <p class="embm-untappd-widget--thumb-note" style="line-height:1;margin-top:0;padding-left:22px;">
            <small><em>(<?php _e('Only shows default generic avatar when not authenticated with Untappd.', 'embm'); ?>)</em></small>
        </p>
        <p class="embm-untappd-widget--more">
            <input
                id="<?php echo $this->get_field_id('more'); ?>"
                name="<?php echo $this->get_field_name('more'); ?>"
                type="checkbox"
                value="1"'
                <?php checked('1', $more); ?>
            />
            <label for="<?php echo $this->get_field_id('more'); ?>"><?php _e('Show "View More" link', 'embm'); ?></label>
        </p>
        <hr />
        <p class="embm-untappd-widget--refresh" style="margin-bottom:5px;">
            <strong><?php _e('Refresh Untappd Check-in Data', 'embm'); ?></strong>
        </p>
        <p class="embm-untappd-widget--refresh-button" style="margin-top:0;margin-bottom:4px;">
            <a href="#" class="button-secondary" data-api-root="<?php echo $api_root; ?>"><?php _e('Flush Cache', 'embm'); ?></a>
        </p>
        <p class="embm-untappd-widget--refresh-note" style="margin-top:0;">
            <small><em><?php _e('This is automatically done daily.', 'embm'); ?></em></small>
        </p>
        <?php echo $errors; ?>
    </div>
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
        if (!isset($old_instance['brewery']) || $old_instance['brewery'] !== $instance['brewery']) {
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
            if ($has_errors && !array_key_exists('1', $this->widget_errors)) {
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
    // If Untappd is disabled, exit
    if (EMBM_Core_Beer_disabled()) {
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
    if (null !== $token) {
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
 * @param array  $beers Widget options
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

    // Get checkins
    $checkins = EMBM_Admin_Untappd_checkins($api_root, $brewery_id);

    // Check for errors
    if (!is_object($checkins)) {
        // Force XML output as fall back
        return EMBM_Widget_Untappd_Recent_Display_xml($beers, true);
    }

    // Start output
    $output = '';

    // Make sure we have check-ins to show
    if (!$checkins->count || $checkins->count < 1) {
        $error = __('This brewery has no recent check-ins!', 'embm');
        return sprintf('<p class="embm-untappd-list--empty">%s</p>', $error);
    }

    // Iterate over XML output
    foreach (range(0, $count-1) as $i) {
        // Get checkin
        $checkin = $checkins->items[$i];
        $user = $checkin->user;
        $beer = $checkin->beer;
        $venue = $checkin->venue;

        // Set user & checkin links
        $user_link = 'https://untappd.com/user/'.$user->user_name;
        $checkin_link = $user_link.'/'.$checkin->checkin_id;

        // Set up new entry content
        $entry = array(
            'user'      => array(
                'link'  => $user_link,
                'name'  => sprintf('%s %s', $user->first_name, $user->last_name)
            ),
            'beer'      => array(
                'link'  => sprintf('https://untappd.com/beer/%s', $beer->bid),
                'name'  => $beer->beer_name
            ),
            'venue'     => array(
                'link'  => null,
                'name'  => null
            ),
            'avatar'    => null,
            'link'      => $checkin_link,
            'rating'    => null,
            'comment'   => null,
            'date'      => $checkin->created_at
        );

        // Set venue
        if ($venue && is_object($venue) && property_exists($venue, 'venue_id')) {
            $entry['venue']['link'] = sprintf('https://untappd.com/venue/%s', $venue->venue_id);
            $entry['venue']['name'] = $venue->venue_name;
        }

        // Set comment
        if (property_exists($checkin, 'checkin_comment') && $checkin->checkin_comment != '') {
            $entry['comment'] = $checkin->checkin_comment;
        }

        // Set rating
        if (property_exists($checkin, 'rating_score') && $checkin->rating_score != 0) {
            $entry['rating'] = $checkin->rating_score;
        }

        // Set thumbnail
        if (property_exists($user, 'user_avatar') && $user->user_avatar != '') {
            $entry['avatar'] = $user->user_avatar;
        }

        // Get entry content
        $output .= EMBM_Widget_Untappd_Recent_Display_entry($beers, $entry);
    }

    // Return HTML output
    return $output;
}

/**
 * Generate HTML content of Untappd Recent Check-ins widget from XML
 *
 * @param array $beers Widget options
 *
 * @return string/html
 */
function EMBM_Widget_Untappd_Recent_Display_xml($beers)
{
    // Get widget options
    $count = intval($beers['items']);
    $brewery_id = $beers['brewery'];

    // Get XML content
    $xml = EMBM_Admin_Untappd_Checkins_xml($brewery_id);

    // Check for errors
    if (!is_object($xml)) {
        $error = __('There was a problem retrieving Untappd check-ins. Please try again later.', 'embm');
        return sprintf('<p class="embm-untappd-list--empty">%s</p>', $error);
    }

    // Start output
    $output = '';

    // Make sure we have check-ins to show
    if (!$xml->channel->item->count() || $xml->channel->item->count() < 1) {
        $error = __('This brewery has no recent check-ins!', 'embm');
        return sprintf('<p class="embm-untappd-list--empty">%s</p>', $error);
    }

    // Iterate over XML output
    foreach (range(0, $count-1) as $i) {
        // Get checkin
        $item = $xml->channel->item[$i];

        // Set up new entry content
        $entry = array(
            'user'      => array(
                'link'  => (string) $item->link,
                'name'  => null
            ),
            'beer'      => array(
                'link'  => (string) $item->link,
                'name'  => null
            ),
            'venue'     => array(
                'link'  => (string) $item->link,
                'name'  => null
            ),
            'avatar'    => null,
            'link'      => (string) $item->link,
            'rating'    => null,
            'comment'   => null,
            'date'      => (string) $item->pubDate
        );

        // Set up title regex
        $title_regex = '/^(.*) is drinking a[n]? (?(?=.* at .*$)((.*) at (.*)$)|((.*)$))/i';

        // Parse title
        if (preg_match($title_regex, (string) $item->title, $title)) {
            // Set data
            $entry['user']['name'] = $title[1];
            $entry['beer']['name'] = (array_key_exists(6, $title) && !preg_match('/^\s+$/', $title[6])) ? $title[6] : $title[3];
            $entry['venue']['name'] = (array_key_exists(4, $title) && !preg_match('/^\s+$/', $title[4])) ? $title[4] : null;
        }

        // Parse description
        if (property_exists($item, 'description') && is_object($item->description)) {
            if (preg_match('/^(.*)\((\d(\.\d{1,2})?)\/5 stars\)$/i', (string) $item->description[0], $description)) {
                // Get rating
                if (array_key_exists(2, $description)) {
                    $entry['rating'] = $description[2];
                }
                // Get comment
                if (array_key_exists(1, $description) && !preg_match('/^\s+$/', $description[1])) {
                    $entry['comment'] = $description[1];
                }
            }
        }

        // Get entry content
        $output .= EMBM_Widget_Untappd_Recent_Display_entry($beers, $entry);
    }

    // Return HTML output
    return $output;
}

/**
 * Generate HTML content for a given XML or API entry
 *
 * @param array $beers Widget options
 * @param array $entry Beer entry values
 *
 * @return string/html
 */
function EMBM_Widget_Untappd_Recent_Display_entry($beers, $entry)
{
    // Get widget options
    $show_rating = ($beers['rating'] == '1');
    $show_comment = ($beers['comment'] == '1');
    $show_venue = ($beers['venue'] == '1');
    $show_thumb = ($beers['thumb'] == '1');

    // Set default avatar URL
    $default_avatar_url = 'https://i1.wp.com/untappd.akamaized.net/site/assets/images/default_avatar_v2.jpg';

    // Set link format
    $link_format = '<a class="embm-untappd-list--item-link" href="%s" target="_blank">%s</a>';

    // Start check-in entry
    $output = '<li class="embm-untappd-list--item">';

    // Show thumbnail
    if ($show_thumb) {
        // Set avatar URL
        $avatar_url = !is_null($entry['avatar']) ? $entry['avatar'] : $default_avatar_url;

        // Display avatar
        $output .= '<span class="embm-untappd-list--item-thumb">';
        $output .= sprintf('<img src="%s" border="0" alt="%s">', $avatar_url, $entry['user']['name']);
        $output .= '</span>';
    }

    // Start entry content
    $content_class = $show_thumb ? ' embm-untappd-list--item-content__thumb' : '';
    $output .= '<span class="embm-untappd-list--item-content'.$content_class.'">';

    // See if we need to use 'a' or 'an'
    $determiner = preg_match('/^[aeiou]/i', $entry['beer']['name']) ? __('an', 'embm') : __('a', 'embm');

    // Entry title
    $output .= '<span class="embm-untappd-list--item-title">';
    $output .= sprintf($link_format, $entry['user']['link'], $entry['user']['name']);
    $output .= ' '.sprintf(__('is drinking %s', 'embm'), $determiner).' ';
    $output .= sprintf($link_format, $entry['beer']['link'], $entry['beer']['name']);
    $output .= '</span>';

    // Entry rating
    if ($show_rating && !is_null($entry['rating'])) {
        $output .= sprintf(
            '<span class="embm-untappd-list--item-rating embm-beer--rating-stars" title="%s">%s</span>',
            $entry['rating'],
            EMBM_Output_Rating_stars(floatval($entry['rating']))
        );
    }

    // Entry comment
    if ($show_comment && !is_null($entry['comment'])) {
        $output .= sprintf('<span class="embm-untappd-list--item-comment">%s</span>', $entry['comment']);
    }

    // Start entry meta
    $output .= '<span class="embm-untappd-list--item-meta">';

    // Output formatted entry date
    $output .= '<span class="embm-untappd-list--item-date">';
    $output .= sprintf($link_format, $entry['link'], EMBM_Output_Review_date($entry['date']));
    $output .= '</span>';

    // Entry venue
    if ($show_venue && !is_null($entry['venue']['name'])) {
        $output .= '<span class="embm-untappd-list--item-venue">';
        $output .= sprintf($link_format, $entry['venue']['link'], $entry['venue']['name']);
        $output .= '</span>';
    }

    // Link to full check-in entry
    $output .= sprintf($link_format, $entry['link'], __('View Full Check-in', 'embm'));

    // End meta & content
    $output .= '</span></span>';

    // End check-in entry
    $output .= '</li>'."\n";

    // Return HTML output
    return $output;
}
