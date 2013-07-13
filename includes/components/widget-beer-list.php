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
* EM Beer Manager 'Beer List' widget options & display
*
*/

// Define Beer List widget constuctor 
class EMBM_Beer_List_Widget extends WP_Widget {

  function EMBM_Beer_List_Widget() {
  
      $widget_options = array(
      	'classname' => 'beer_list_widget',
      	'description' => __('Displays a list of beers', 'embm')
      );
      
      $this->WP_Widget("beer_list_widget", "Beer List", $widget_options);
  }
 
  public function form( $instance ) {
  
	  $instance = wp_parse_args( (array) $instance, array( 
	    'title' => '',
      	'exclude' => '',
      	'summary' => null,
      	'sum_length' => '100',
      	'style' => '',
      	'group' => ''
	  ) );
	  $title = $instance['title'];
	  $exclude = $instance['exclude'];
	  $summary = $instance['summary'];
	  $sum_length = $instance['sum_length'];
	  $style = $instance['style'];
	  $group = $instance['group'];
 
	  ?>
		  <p>
		  	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'embm'); ?></label><br />
		  	<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" style="width: 100%;" value="<?php echo $title; ?>"   />
		  </p>
		  <p>
		  	<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude Beers: ', 'embm'); ?></label><br />
		  	<input id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" style="width: 100%;" value="<?php echo $exclude; ?>" /><br /><small><?php _e('Comma separated IDs, e.g. "1,2,3"', 'embm'); ?></small>
		  </p>
		  <p>
		  	<label for="<?php echo $this->get_field_id('summary'); ?>"><?php _e('Show Summary: ', 'embm'); ?></label>
		  	<input name="<?php echo $this->get_field_name('summary'); ?>" type="checkbox" id="<?php echo $this->get_field_id('summary'); ?>" value="1"<?php checked('1', $summary, true); ?> />
		  </p>
		  <p>
		  	<label for="<?php echo $this->get_field_id('sum_length'); ?>"><?php _e('Summary Length: ', 'embm'); ?></label>
		  	<input id="<?php echo $this->get_field_id('sum_length'); ?>" name="<?php echo $this->get_field_name('sum_length'); ?>" type="text" size="3" value="<?php echo $sum_length; ?>" /><small><?php _e(' Characters', 'embm'); ?></small>
		  </p>
		  <p>
		  	<label for="<?php echo $this->get_field_id('style'); ?>"><?php _e('Show Style: ', 'embm'); ?></label>
		  	<select name="<?php echo $this->get_field_name('style'); ?>" id="<?php echo $this->get_field_id('style'); ?>">
		  		<option value="all" <?php selected($style, 'all', true); ?>><?php _e('All Styles', 'embm'); ?></option>
	        <?php $beer_styles = get_terms('embm_style');
	        	  foreach ( $beer_styles as $beer_style) { 
                   echo '<option value="';
                   echo $beer_style->slug;
                   echo '" '.selected($style, $beer_style->slug, false).'>';
                   echo $beer_style->name;
                   echo '</option>';
            }?>
            </select> 
		  </p>
		  <p>
		  	<label for="<?php echo $this->get_field_id('group'); ?>"><?php _e('Show Group: ', 'embm'); ?></label>
		  	<select name="<?php echo $this->get_field_name('group'); ?>" id="<?php echo $this->get_field_id('group'); ?>">
		  		<option value="all" <?php selected($group, 'all', true); ?>><?php _e('All Groups', 'embm'); ?></option>
	        <?php $beer_groups = get_terms('embm_group');
	        	  foreach ( $beer_groups as $beer_group) { 
                   echo '<option value="';
                   echo $beer_group->slug;
                   echo '" '.selected($group, $beer_group->slug, false).'>';
                   echo $beer_group->name;
                   echo '</option>';
            }?>
            </select> 
		  </p>
  	 <?php
  }
 
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance; 
    
    $instance['title'] = $new_instance['title'];
  	$instance['exclude'] = $new_instance['exclude'];
  	$instance['summary'] = $new_instance['summary'];
  	$instance['sum_length'] = $new_instance['sum_length'];
  	$instance['style'] = $new_instance['style'];
  	$instance['group'] = $new_instance['group'];
  	
  	return $instance;
  }

  public function widget( $args, $instance ) {  
  	extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );
    $exclude = apply_filters( 'widget_exclude', $instance['exclude'] );
    $summary = apply_filters( 'widget_summary', $instance['summary'] );
    $sum_length = apply_filters( 'widget_sum_length', $instance['sum_length'] );
    $style = apply_filters( 'widget_style', $instance['style'] );
    $group = apply_filters( 'widget_group', $instance['group'] );
 
    echo $before_widget;

    echo embm_display_list_widget( array (
    	'title' => $title,
    	'exclude' => $exclude,
    	'summary' => $summary,
    	'sum_length' => $sum_length,
    	'style' => $style,
    	'group' => $group
    ) );
    
    echo $after_widget;
  }
}

add_action( 'widgets_init', create_function('', 'return register_widget("EMBM_Beer_List_Widget");') );


// Generate HTML display of beer list widget content
function embm_display_list_widget($beers) {
	
	// Widget variables
	$title = $beers['title'];
	$exclude = explode(',', $beers['exclude']);
	$summary = $beers['summary'];
	$sum_length = $beers['sum_length'];
	$style = $beers['style']; 
	$group = $beers['group']; 
	
	$output = '';	
	$output = "\n".'<h3 class="widget-title">'.$title.'</h3>'."\n";
	
	// The query
	global $post;
	$tmp_post = $post;
	
	$args = array (
		'post_type' => 'embm_beer'
	);
	
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
	
	$beerlist = get_posts($args);

	if ($beerlist) {
	
		$output .= '<ul class="embm-beer-list">'."\n";
	
		foreach ($beerlist as $post ) : setup_postdata($post); 
		
			$output .= '<li class="embm-beer-list-item" id="embm-beer-'.$post->ID.'">';
			$output .= '<a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">'.get_the_title($post->ID).'</a>';
			
			if ($summary == '1') {
				$output .= '<span class="embm-beer-summary">';
				
				$beer_summary = get_the_content($post->ID); 
				$beer_sum_end = intval($sum_length);
				
				$output .= substr($beer_summary, 0, $beer_sum_end).'...';
				$output .= '<a class="embm-read-more" href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
				$output .= __('More', 'embm');
				$output .= '</a>';
				$output .= '</span>';
			}
			
			$output .= '</li>'."\n";
		
		endforeach; 
		$post = $tmp_post; //reset
	
		$output .= '</ul>'."\n";
		
	} else {
		$error = __('No beers found.', 'embm'); 
		return $error;
	}
	
	return $output;

}

?>