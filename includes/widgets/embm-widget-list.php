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
 * @package EMBM\Widget\List
 */

/**
 * Add Beer List widget
 */
class EMBM_Widget_List extends WP_Widget
{
    /**
     * Define beer list widget construct
     *
     * @return void
     */
    public function __construct()
    {
        $widget_options = array(
            'classname'     => 'embm_beer_list_widget',
            'description'   => __('Displays a list of beers', 'embm')
        );
        parent::__construct(
            'embm_beer_list_widget',
            __('Beer List', 'embm'),
            $widget_options
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
                'title'         => '',
                'exclude'       => '',
                'count'         => '3',
                'summary'       => null,
                'sum_length'    => '100',
                'style'         => '',
                'group'         => ''
            )
        );

        // Set up arguments
        $title = $instance['title'];
        $exclude = $instance['exclude'];
        $count = $instance['count'];
        $summary = $instance['summary'];
        $sum_length = $instance['sum_length'];
        $style = $instance['style'];
        $group = $instance['group'];

?>
    <div class="embm-beer-list-widget">
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'embm'); ?>:</label><br />
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" style="width: 100%;" value="<?php echo $title; ?>"   />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude Beers', 'embm'); ?>: </label><br />
            <input id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" style="width: 100%;" value="<?php echo $exclude; ?>" /><br /><small><?php _e('Comma separated IDs', 'embm'); ?>, e.g. "1,2,3"</small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Beer Count', 'embm'); ?>: </label>
            <input id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="number" style="width: 25%;" value="<?php echo $count; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('summary'); ?>"><?php _e('Show Summary', 'embm'); ?>: </label>
            <input name="<?php echo $this->get_field_name('summary'); ?>" type="checkbox" id="<?php echo $this->get_field_id('summary'); ?>" value="1"<?php checked('1', $summary, true); ?> />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('sum_length'); ?>"><?php _e('Summary Length', 'embm'); ?>: </label>
            <input id="<?php echo $this->get_field_id('sum_length'); ?>" name="<?php echo $this->get_field_name('sum_length'); ?>" type="text" size="3" value="<?php echo $sum_length; ?>" /><small>&nbsp;<?php _e('Characters', 'embm'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('style'); ?>"><?php _e('Show Style', 'embm'); ?>: </label>
            <select name="<?php echo $this->get_field_name('style'); ?>" id="<?php echo $this->get_field_id('style'); ?>">
                <option value="all" <?php selected($style, 'all', true); ?>><?php _e('All Styles', 'embm'); ?></option>
                <?php $beer_styles = get_terms('embm_style'); foreach ($beer_styles as $beer_style) : ?>
                    <option value="<?php echo $beer_style->slug; ?>" <?php echo selected($style, $beer_style->slug, false); ?>><?php echo $beer_style->name; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('group'); ?>"><?php _e('Show Group', 'embm'); ?>: </label>
            <select name="<?php echo $this->get_field_name('group'); ?>" id="<?php echo $this->get_field_id('group'); ?>">
                <option value="all" <?php selected($group, 'all', true); ?>><?php _e('All Groups', 'embm'); ?></option>
                <?php $beer_groups = get_terms('embm_group'); foreach ($beer_groups as $beer_group) : ?>
                    <option value="<?php echo $beer_group->slug; ?>" <?php echo selected($group, $beer_group->slug, false); ?>><?php echo $beer_group->name; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
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

        $instance['title'] = isset($new_instance['title']) ? $new_instance['title'] : $old_instance['title'];
        $instance['exclude'] = isset($new_instance['exclude']) ? $new_instance['exclude'] : $old_instance['exclude'];
        $instance['count'] = isset($new_instance['count']) ? $new_instance['count'] : $old_instance['count'];
        $instance['summary'] = isset($new_instance['summary']) ? '1' : '';
        $instance['sum_length'] = isset($new_instance['sum_length']) ? $new_instance['sum_length'] : $old_instance['sum_length'];
        $instance['style'] = isset($new_instance['style']) ? $new_instance['style'] : $old_instance['style'];
        $instance['group'] = isset($new_instance['group']) ? $new_instance['group'] : $old_instance['group'];

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
        echo EMBM_Widget_List_display(
            array(
                'title'         => apply_filters('widget_title', $instance['title']),
                'exclude'       => apply_filters('widget_exclude', $instance['exclude']),
                'count'         => apply_filters('widget_count', $instance['count']),
                'summary'       => apply_filters('widget_summary', $instance['summary']),
                'sum_length'    => apply_filters('widget_sum_length', $instance['sum_length']),
                'style'         => apply_filters('widget_style', $instance['style']),
                'group'         => apply_filters('widget_group', $instance['group'])
            )
        );

        // Out put post-widget content
        echo $after_widget;
    }
}

// Load the widget
add_action('widgets_init', create_function('', 'return register_widget("EMBM_Widget_List");'));

/**
 * Generate HTML content of Beer List widget
 *
 * @param array $beers Widget options
 *
 * @return string/html
 */
function EMBM_Widget_List_display($beers)
{
    // Set widget options
    $title = $beers['title'];
    $exclude = explode(',', $beers['exclude']);
    $count = $beers['count'];
    $summary = $beers['summary'];
    $sum_length = $beers['sum_length'];
    $style = $beers['style'];
    $group = $beers['group'];

    // Initialize output string
    $output = '';

    // Widget title
    $output = "\n".'<h3 class="widget-title">'.$title.'</h3>'."\n";

    // Get global post object
    global $post;
    $tmp_post = $post;

    // Initialize query args
    $args = array(
        'post_type' => 'embm_beer'
    );

    // Add count filter
    if ($count != '') {
        $args['posts_per_page'] = $count;
    }
    // Add styles filter
    if ($style != 'all') {
        $args['embm_style'] = $style;
    }
    // Add group filter
    if ($group != 'all') {
        $args['embm_group'] = $group;
    }
    // Add id filter
    if ($exclude) {
        $args['post__not_in'] = $exclude;
    }

    // Get list of beers
    $beerlist = get_posts($args);

    if ($beerlist) {
        // Start beer list
        $output .= '<ul class="embm-beer-list-widget">'."\n";

        // Iterate over beers in list
        foreach ($beerlist as $post) {
            // Set up post data for beer
            setup_postdata($post);

            // Start beer item
            $output .= '<li class="embm-beer-list-widget-item" id="embm-beer-'.$post->ID.'">';
            $output .= '<a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">'.get_the_title($post->ID).'</a>';

            // Show beer summary, if enabled
            if ($summary == '1') {
                $output .= '<span class="embm-beer-summary">';

                $beer_summary = get_the_content($post->ID);
                $beer_sum_end = intval($sum_length);

                // Only display number of characters specified
                $output .= substr($beer_summary, 0, $beer_sum_end).'...';
                $output .= '<a class="embm-read-more" href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
                $output .= __('More', 'embm');
                $output .= '</a>';
                $output .= '</span>';
            }

            // End beer item
            $output .= '</li>'."\n";
        }

        // Reset post data
        $post = $tmp_post;

        // End beer list
        $output .= '</ul>'."\n";
    } else {
        // Fall back message for when no beers are found
        $error = __('No beers found.', 'embm');
        return $error;
    }

    // Return HTML content
    return $output;
}
