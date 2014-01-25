<?php 
/*
Copyright (c) 2014, Erin Morelli. 

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
* EM Beer Manager shortcodes and template tags
*
*/
 

// Single Beer shortcode
function embm_single_beer($atts) {
   extract(shortcode_atts(array(
      'id' => 0,
      'show_profile' => 'true',
      'show_extras' => 'true',
   ), $atts));
   
   return embm_beer_single($id, $show_profile, $show_extras);
}
add_shortcode('beer', 'embm_single_beer');


// Single beer template code
function embm_beer_single($postid, $profile = 'true', $extras = 'true') {
	$args = array('id' => $postid, 'profile' => $profile, 'extras' => $extras);
	return embm_beer_single_output ($args);
}

// Single beer display
function embm_beer_single_output ($beer) {

	$bid = $beer['id'];
	$showprofile = $beer['profile'];
	$showextras = $beer['extras'];

	$output = '';
	
	// Set up new loop data
	global $post;
	$wp_query = new WP_Query(); 
	
	// The query
	$args = array (
		'post_type' => 'embm_beer',
		'page_id' => $bid
	);
	
	$wp_query->query($args);
	
		
	while ($wp_query->have_posts()) : $wp_query->the_post();
	  
	  $output .= embm_display_beer($post->ID, $showprofile, $showextras);
		
	endwhile;

	wp_reset_query();
	wp_reset_postdata(); //reset
	
	return $output;
}



// Beer list shortcode
function embm_all_beers($atts) {
   extract(shortcode_atts(array(
      'exclude' => '',
      'show_profile' => 'true',
      'show_extras' => 'true',
      'style' => '',
      'group' => '',
      'beers_per_page' => -1,
      'paginate' => 'true',
      'orderby' => '',
      'order' => ''
   ), $atts));
   
   return embm_beer_list($exclude, $show_profile, $show_extras, $style, $group, $beers_per_page, $paginate, $orderby, $order);
}
add_shortcode('beer-list', 'embm_all_beers');


// Beer list template code
function embm_beer_list($exclude = '', $profile = 'true', $extras = 'true', $style = '', $group = '', $pagenum = -1, $usepages = 'true', $sortby = '', $sort = '') {

	$args = array(
		'exclude' => $exclude, 
		'profile' => $profile, 
		'extras' => $extras, 
		'style' => $style, 
		'group' => $group, 
		'page_num' => $pagenum, 
		'use_pages' => $usepages, 
		'sortby' => $sortby, 
		'sort' => $sort
	);
	
	return embm_beer_list_output ($args);
}

// Beer list display
function embm_beer_list_output ($beers) {
	
	// Declared shortcode variables
	$excludes = explode(',', $beers['exclude']);
	$showprofile = $beers['profile'];
	$showextras = $beers['extras'];
	$showstyle = $beers['style'];
	$showgroup = $beers['group'];
	$showpages = $beers['page_num'];
	$usepages = $beers['use_pages'];
	$sortby = $beers['sortby'];
	$sort = strtoupper($beers['sort']);
	
	$output = '';	
	
	// Set up new loop data
	global $post;
	
	if ( get_query_var('paged') ) {
	    $paged = get_query_var('paged');
	} else if ( get_query_var('page') ) {
	    $paged = get_query_var('page');
	} else {
	    $paged = 1;
	}

	$wp_query = new WP_Query(); 
	
	// The query
	$args = array (
		'post_type' => 'embm_beer',
		'showposts' => $showpages,
		'paged' => $paged,
		
	);
	
	// Add styles filter
	if ($showstyle != '') {
		$style_slug = get_term_by('name', $showstyle, 'embm_style', 'ARRAY_A');
		$args['embm_style'] = $style_slug['slug'];
	}
	// Add groups filter
	if ($showgroup != '') {
		$group_slug = get_term_by('name', $showgroup, 'embm_group', 'ARRAY_A');
		$args['embm_group'] = $group_slug['slug'];
	}
	
	// Add id filter
	if ($excludes) {
		$args['post__not_in'] = $excludes;
	}
	
	// Add sortby filter
	if ($sortby != '') {
		$args['orderby'] = $sortby;
	}
	
	// Add sort filter
	if ($sort != '') {
		$args['order'] = $sort;
	}

	$wp_query->query($args);
	
	$output .= '<div class="beer-list">'."\n";
		
	while ($wp_query->have_posts()) : $wp_query->the_post();
	  
	  $output .= embm_display_beer($post->ID, $showprofile, $showextras);
		
	endwhile;
		
	// Display navigation to next/previous pages when applicable
	if ($usepages == 'true') {
	
		$output .= '<div class="nav-below">'."\n";
		
			$big = 999999999; // need an unlikely integer
			$output .= paginate_links( array(
			  	'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			  	'format' => '?paged=%#%',
			  	'current' => max( 1, $paged ),
			  	'total' => $wp_query->max_num_pages
			  	) );
		
		$output .= '</div>'."\n";
	}
	
	$output .= '</div>'."\n";
	
	wp_reset_query();
	wp_reset_postdata(); //reset

	return $output;
	
}


// Generate HTML output
function embm_display_beer($beer_id, $showprofile='true', $showextras='true') {
	
  $output = '';	
  
  $profile = embm_display_beer_profile($beer_id);
  $extras = embm_display_beer_extras($beer_id);
  
  $output .= '<div id="beer-'.$beer_id.'" class="single-beer beer beer-'.$beer_id.'">'."\n";

	$output .= '<div class="beer-title">'."\n";
	
	if (is_page() || is_archive() || is_tax('embm_group')) {
		$output .= '<a href="'.get_permalink($beer_id).'" titile="'.get_the_title($beer_id).'">';
		$output .= '<h2>'.get_the_title($beer_id).'</h2>';
		$output .= '</a>'."\n";
	} else {
		$output .= '<h1>'.get_the_title($beer_id).'</h1>'."\n";
	}
	
	if (embm_get_beer_style($beer_id)) {
		$output .= '<span class="beer-style">(';
		$output .= '<a href="'.get_term_link(embm_get_beer_style($beer_id), 'embm_style').'" title="View All '.embm_get_beer_style($beer_id).'s">';
		$output .= embm_get_beer_style($beer_id);
		$output .= '</a>)</span>'."\n";
	}
	
	$output .= '</div>'."\n";
	
	if (!is_archive()) {
		if ( get_the_post_thumbnail($beer_id) != '' ) {
			$output .= '<div class="beer-image">'."\n";
			$output .= get_the_post_thumbnail($beer_id, 'full')."\n";
			$output .= '</div>'."\n";
		}
	}
	
	$output .= '<div class="beer-description">'."\n";
	
		$output .= apply_filters('the_content', get_the_content($beer_id) );
		
		if ( (is_tax('embm_style') || is_archive()) && !is_tax('embm_group') ) {
			$output .= ' <a class="read-more" href="'.get_permalink($beer_id).'">';
			$output .= __('More...', 'embm');
			$output .= '</a>';
		}
	
		$output .= embm_display_untappd($beer_id);
	
	$output .= '</div>'."\n";
	
	if ( ($showprofile == 'true') || ($showextras == 'true') ) {
	
		if( ($profile != null) || ($extras != null) ) {
		
			$output .= '<div class="beer-meta">'."\n";
			
			if ( ($showprofile == 'true') && ($profile != null) ) {
				$output .= $profile;
			}
			
			if ( ($showextras == 'true') && ($extras != null) ) {
				$output .= $extras;
			}
						
			$output .= '</div>'."\n";
		}
		
	} else {
		$output .= '<div class="embm-clear"></div>'."\n";
	}
	
 $output .= '</div>'."\n";
	
 return $output;	

}


function embm_display_untappd($beer_id) {

	$output = '';
	
	$ut_option = get_option('embm_options');
	if (isset($ut_option['embm_untappd_check'])) {
		$use_untappd = $ut_option['embm_untappd_check']; 
	} else {
		$use_untappd = null;
	}
	
	if ($use_untappd != "1") {
		if ( (embm_get_beer($beer_id,'untappd') != '') ) {
			$output = '<div class="untappd"><a href="'.embm_get_beer($beer_id,'untappd').'" target="_blank" title="Check In on Untappd"></a></div>'."\n";
		}
	}
	
	return $output; 
}
		
function embm_display_beer_profile($beer_id) {


	$abv = embm_get_beer($beer_id,'abv');
	$ibu = embm_get_beer($beer_id,'ibu');
	$malts = embm_get_beer($beer_id,'malts');
	$hops = embm_get_beer($beer_id,'hops'); 
	$adds = embm_get_beer($beer_id,'adds');
	$yeast = embm_get_beer($beer_id,'yeast');
	
	$output = '';
	$options = get_option('embm_options');
	
	if (isset($options['embm_profile_show'])) {
		$view_profile = $options['embm_profile_show']; 
	} else {
		$view_profile = null;
	}
	
	if($view_profile != "1") {
	
		if (($abv!='0%')||($ibu!='0')||($malts!='')||($hops!='')||($adds!='')||($yeast!='')) {
			
			$output = '<div class="beer-profile">'."\n";
			
			if ($abv != '0%') {
				$output .= '<div class="abv"><span class="label">';
				$output .= __('ABV:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'abv').'</span></div>'."\n";
			} 
			if ($ibu != '0') {
				$output .= '<div class="ibu"><span class="label">';
				$output .= __('IBU:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'ibu').'</span></div>'."\n";
			} 
			if ($malts != '') {
				$output .= '<div class="malts"><span class="label">';
				$output .= __('Malts:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'malts').'</span></div>'."\n";
			}
			if ($hops != '') {
				$output .= '<div class="hops"><span class="label">';
				$output .= __('Hops:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'hops').'</span></div>'."\n";
			}
			if ($adds != '') {
				$output .= '<div class="other"><span class="label">';
				$output .= __('Other:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'adds').'</span></div>'."\n";
			}
			if ($yeast != '') {
				$output .= '<div class="yeast"><span class="label">';
				$output .= __('Yeast:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'yeast').'</span></div>'."\n";
			}
			
			$output .= '</div>'."\n";
		
		}
		else {
		
			$output = null;
			
		}
	}
		
	return  $output; 
}

function embm_display_beer_extras($beer_id) {

	$avail = embm_get_beer($beer_id,'avail');
	$notes = embm_get_beer($beer_id,'notes');
	
	$output = '';
	$options = get_option('embm_options');
	
	if (isset($options['embm_extras_show'])) {
		$view_extras = $options['embm_extras_show']; 
	} else {
		$view_extras = null;
	}
	
	if($view_extras != "1") {
		
		if(($avail!='')||($notes!='')) {
		
			$output = '<div class="beer-extras">';
			
			if ($avail != '') {
				$output .= '<div class="avail"><span class="label">';
				$output .= __('Availability:', 'embm');
				$output .= '</span><span class="value">'.embm_get_beer($beer_id,'avail').'</span></div>'."\n";
			} 
			if ($notes != '') {
				$output .= '<div class="notes"><span class="label">';
				$output .= __('Additional Notes', 'embm');
				$output .= '</span><span class="value">'.wpautop(embm_get_beer($beer_id,'notes')).'</span></div>'."\n";
			}
			
			$output .= '</div>'."\n";
		
		} else {
		
			$output = null;
			
		}
	}
	
	return $output;
}


function embm_content_filter( $content ) {

	global $post;
	
	$options = get_option('embm_options');
	$output = '';
	$thumb = '';
	
	$profile = embm_display_beer_profile($post->ID);
    $extras = embm_display_beer_extras($post->ID);
	
    if ( is_singular('embm_beer') && in_the_loop() ) {
    	
    	$output .= embm_display_untappd($post->ID);
        $output .= '</div>'."\n"; 	
    	
		if (isset($options['embm_profile_show_single'])) {$view_profile_single = $options['embm_profile_show_single']; } 
		else {$view_profile_single = null;}
		
		if (isset($options['embm_extras_show_single'])) {$view_extras_single = $options['embm_extras_show_single']; } 
		else {$view_extras_single = null;}
		
		if( ($view_profile_single != "1") || ($view_extras_single != "1") ) {
		
			if( ($profile != null) || ($extras != null) ) {
		
				$output .= '<div class="beer-meta">'."\n";   
				
				if( ($view_profile_single != "1") && ($profile != null) ) {
					$output .= $profile;
				}
				
				if( ($view_extras_single != "1") && ($extras != null) ) {
					$output .= $extras;
				}
				
				$output .= '</div>'."\n";
			}
		}
		
		if ( has_post_thumbnail($post->ID) ) {
			$thumb .= '<div class="beer-image">'."\n";
			$thumb .= get_the_post_thumbnail($post->ID, 'full')."\n";
			$thumb .= '</div>'."\n";
		}
        
        $content = sprintf('%s<div class="beer-description">%s',
        	$thumb,
            $content
        );
        
        $content .= $output;
    }
    if ( is_tax('embm_style') && in_the_loop() ) {
    	
    	$output .= embm_display_untappd($post->ID);
        $output .= '</div>'."\n";
    	
		if (isset($options['embm_profile_show_style'])) {$view_profile_style = $options['embm_profile_show_style']; } 
		else {$view_profile_style = null;}
		
		if (isset($options['embm_extras_show_style'])) {$view_extras_style = $options['embm_extras_show_style']; } 
		else {$view_extras_style = null;}
		
		if( ($view_profile_style != "1") || ($view_extras_style != "1") ) {
		
			if( ($profile != null) || ($extras != null) ) {
		
				$output .= '<div class="beer-meta">'."\n";   
				
				if( ($view_profile_style != "1") && ($profile != null) ) {
					$output .= $profile;
				}
				
				if( ($view_extras_style != "1") && ($extras != null) ) {
					$output .= $extras;
				}
				
				$output .= '</div>'."\n";
			}
		}
        
        if ( has_post_thumbnail($post->ID) ) {
			$thumb .= '<div class="beer-image">'."\n";
			$thumb .= get_the_post_thumbnail($post->ID, 'full')."\n";
			$thumb .= '</div>'."\n";
		}
        
        $content = sprintf('%s<div class="beer-description">%s',
        	$thumb,
            $content
        );
        
        $content .= $output;
    }
    if ( is_tax('embm_group') && in_the_loop() ) {
    	
    	$output .= embm_display_untappd($post->ID);
        $output .= '</div>'."\n";
		
		if (isset($options['embm_profile_show_group'])) {$view_profile_group = $options['embm_profile_show_group']; } 
		else {$view_profile_group = null;}
		
		if (isset($options['embm_extras_show_group'])) {$view_extras_group = $options['embm_extras_show_group']; } 
		else {$view_extras_group = null;}
		
		if( ($view_profile_group != "1") || ($view_extras_group != "1") ) {
		
			if( ($profile != null) || ($extras != null) ) {
		
				$output .= '<div class="beer-meta">'."\n";   
				
				if( ($view_profile_group != "1") && ($profile != null) ) {
					$output .= $profile;
				}
				
				if( ($view_extras_group != "1") && ($extras != null) ) {
					$output .= $extras;
				}
				
				$output .= '</div>'."\n";
			}
		}
		
		if ( has_post_thumbnail($post->ID) ) {
			$thumb .= '<div class="beer-image">'."\n";
			$thumb .= get_the_post_thumbnail($post->ID, 'full')."\n";
			$thumb .= '</div>'."\n";
		}
        
        $content = sprintf('%s<div class="beer-description">%s',
        	$thumb,
            $content
        );
        
        $content .= $output;
    }
    
    return $content;
}
add_filter( 'the_content', 'embm_content_filter', -1 );


function embm_body_classes($classes) {
	if( is_singular('embm_beer') || is_tax('embm_style') || is_tax('embm_group') ) {
		$classes[] = 'single-beer';
	}
	
	return $classes;
}
add_filter('body_class', 'embm_body_classes');


function embm_title_filter($title, $id) {
	global $post;
	
	if ( embm_get_beer_style($id) && ( is_singular('embm_beer') || is_tax('embm_group') ) && in_the_loop() && ($title == $post->post_title) ) {
		$output = '';
		$output .= '</a><span class="beer-style">(';
		$output .= '<a href="'.get_term_link(embm_get_beer_style($id), 'embm_style').'" title="View All '.embm_get_beer_style($id).'s">';
		$output .= embm_get_beer_style($id);
		$output .= '</a>)</span>'."\n";
		
        $title .= $output;
    } 
   
    return $title;
}
add_filter('the_title', 'embm_title_filter', 10, 2);

?>