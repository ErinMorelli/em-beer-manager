<?php
/*
Copyright (c) 2013, Erin Morelli.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
*
* EM Beer Manager 'Recent Untappd Check-Ins' widget options & display
*
*/

$ut_option = get_option('embm_options');

if ( isset($ut_option['embm_untappd_check']) ) {
	$use_untappd = $ut_option['embm_untappd_check'];
} else {
	$use_untappd = null;
}

if ( $use_untappd != '1' ) {
	// Define Beer List widget constuctor
	class EMBM_Recent_Untappd_Widget extends WP_Widget {

		function EMBM_Recent_Untappd_Widget() {
			$widget_options = array(
				'classname'	=> 'recent_untappd_widget',
				'description'	=> __('Displays a list of recent Untappd brewery check-ins', 'embm')
			);
			parent::__construct("recent_untappd_widget", "Recent Untappd Check-Ins", $widget_options);
		}

		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array(
				'title'		=> '',
				'items'		=> 3,
				'brewery'	=> ''
			) );

			$title = $instance['title'];
			$items = $instance['items'];
			$brewery = $instance['brewery'];

			?>
			<script type="text/javascript">
				function untappdHelp() {
					window.open("<?php echo EMBM_PLUGIN_URL; ?>assets/embm-help.php#untappd","Finding Your Untappd Brewery ID","menubar=no,width=460,height=360,toolbar=no");
				}
			</script>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'embm'); ?></label><br />
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" style="width: 100%;" value="<?php echo $title; ?>"   />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('brewery'); ?>"><?php _e('Brewery ID: ', 'embm'); ?></label>
				<input id="<?php echo $this->get_field_id('brewery'); ?>" name="<?php echo $this->get_field_name('brewery'); ?>" type="text" style="width: 30%;" value="<?php echo $brewery; ?>" />
				<span><a href="javascript:untappdHelp()" id="embm-help-link" onclick="createPopup();"><small><?php _e("What's this?", "embm"); ?></small></a></span>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Number of items to show: ', 'embm'); ?></label>
				<input id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" type="number" min="1" step="1" style="width: 20%;" value="<?php echo $items; ?>" />
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title'] = $new_instance['title'];
			$instance['items'] = $new_instance['items'];
			$instance['brewery'] = $new_instance['brewery'];

			return $instance;
		}

		public function widget( $args, $instance ) {
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
			$items = apply_filters( 'widget_items', $instance['items'] );
			$brewery = apply_filters( 'widget_brewery', $instance['brewery'] );

			echo $before_widget;

			echo embm_display_untappd_widget( array (
				'title'		=> $title,
				'items'		=> $items,
				'brewery'	=> $brewery
			) );

			echo $after_widget;
		}
	}

	add_action( 'widgets_init', create_function('', 'return register_widget("EMBM_Recent_Untappd_Widget");') );

	// Generate HTML display of recent untappd widget content
	function embm_display_untappd_widget($beers) {

		// Widget variables
		$title = $beers['title'];
		$items = $beers['items'];
		$brewery = $beers['brewery'];

		$feed_url = 'https://untappd.com/rss/brewery/'.$brewery; // Get Untappd deed

		$output = '';
		$output .= "\n".'<h3 class="widget-title">'.$title.'</h3>'."\n";
		$output .= '<ul class="embm-untappd-list">'."\n";


		if ( !$brewery ) {
			$output .= '<li class="embm-untappd-list-item">';
			$output .= __('A brewery ID number has not been set.', 'embm');
			$output .= '</li>'."\n";
		} else {
			// Extract Untappd xml feed data
			$content = file_get_contents($feed_url);
			$x = new SimpleXmlElement($content);

			$i = 0; // Initiate iterator

			foreach( $x->channel->item as $entry ) {
				if ( $i < $items ) {
					$output .= '<li class="embm-untappd-list-item">';
					$output .= $entry->title."\n";
					$output .= '<a class="embm-checkin-date" href="'.$entry->link.'" title="'.$entry->title.'">';

					// Display date using WP timezone setting
					$offset = get_option('gmt_offset');
					$postDate = strtotime($entry->pubDate);
					$newDate = mktime(date('H', $postDate)+$offset,date('i', $postDate),0,date('n', $postDate),date('j', $postDate),date('y', $postDate));

					$output .= date('g:i A - j M y', $newDate);

					$output .= '</a></li>'."\n";
					$i++;
				}
			}
		}

		$output .= '</ul>'."\n";

		return $output;
	}
}

?>