<?php 
/*
 * EM Beer Manager shortcodes
 *
 */
 

// Single Beer shortcode
function single_beer($atts) {
   extract(shortcode_atts(array(
      'id' => 0,
      'show_profile' => 'true',
      'show_extras' => 'true',
   ), $atts));
   
   return em_beer_single($id, $show_profile, $show_extras);
}
add_shortcode('beer', 'single_beer');


// Single beer template code
function em_beer_single($postid, $profile = 'true', $extras = 'true') {
	$args = array('id' => $postid, 'profile' => $profile, 'extras' => $extras);
	return em_beer_single_output ($args);
}

// Single beer display
function em_beer_single_output ($beer) {

	$bid = $beer['id'];
	$showprofile = $beer['profile'];
	$showextras = $beer['extras'];
	
	$the_beer = get_post($bid);

	$output = '';
	$output .= '<div id="beer-'.$bid.'" class="single-beer beer beer-'.$bid.'">'."\n";

	$output .= '<div class="beer-title"><h2>'.$the_beer->post_title.'</h2>';
	$output .= '<span class="beer-style">('.get_beer_style($bid).')</span></div>'."\n";
	
	if ( get_the_post_thumbnail($bid) != '' ) {
		$output .= '<div class="beer-image">';
		$output .= get_the_post_thumbnail($bid, 'full');
		$output .= '</div>'."\n";
	}
	
	$output .= '<div class="beer-description">'."\n";
	$output .= $the_beer->post_content."\n";
	
	$ut_option = get_option('embm_options');
	$use_untappd = $ut_option['embm_untappd_check'];
	
	if ($use_untappd != "1") {
		if ( (get_beer($bid,'untappd') != '') ) {
			$output .= '<div class="untappd"><a href="'.get_beer($bid,'untappd').'" target="_blank" title="Check In on Untappd"></a></div>'."\n";
		}
	}
	
	$output .= '</div>'."\n";
	
	if ( ($showprofile == 'true') || ($showextras == 'true') ) {
		$output.= '<div class="beer-meta">'."\n";
		
		if ($showprofile == 'true') {
			$output .= '<div class="beer-profile">'."\n";
			if (get_beer($bid,'abv') != '') {
				$output .= '<div class="abv"><span class="label">ABV:</span><span class="value">'.get_beer($bid,'abv').'</span></div>'."\n";
			} 
			if (get_beer($bid,'ibu') != '') {
				$output .= '<div class="ibu"><span class="label">IBU:</span><span class="value">'.get_beer($bid,'ibu').'</span></div>'."\n";
			} 
			if (get_beer($bid,'malts') != '') {
				$output .= '<div class="malts"><span class="label">Malts:</span><span class="value">'.get_beer($bid,'malts').'</span></div>'."\n";
			}
			if (get_beer($bid,'hops') != '') {
				$output .= '<div class="hops"><span class="label">Hops:</span><span class="value">'.get_beer($bid,'hops').'</span></div>'."\n";
			}
			if (get_beer($bid,'adds') != '') {
				$output .= '<div class="other"><span class="label">Other:</span><span class="value">'.get_beer($bid,'adds').'</span></div>'."\n";
			}
			if (get_beer($bid,'yeast') != '') {
				$output .= '<div class="yeast"><span class="label">Yeast:</span><span class="value">'.get_beer($bid,'yeast').'</span></div>'."\n";
			}
			$output .= '</div>'."\n";
		}
		
		if ($showextras == 'true') {
			$output .= '<div class="beer-extras">'."\n";
			if (get_beer($bid,'avail') != '') {
				$output .= '<div class="avail"><span class="label">Availability:</span><span class="value">'.get_beer($bid,'avail').'</span></div>'."\n";
			} 
			if (get_beer($bid,'notes') != '') {
				$output .= '<div class="notes"><span class="label">Additional Notes</span><span class="value">'.get_beer($bid,'notes').'</span></div>'."\n";
			} 
			$output .= '</div>'."\n";
		}
		
		$output .= '</div>'."\n";	
	}
	
	$output .= '</div>'."\n";
	
	return $output;
}



// Beer list shortcode
function all_beers($atts) {
   extract(shortcode_atts(array(
      'exclude' => '',
      'show_profile' => 'true',
      'show_extras' => 'true',
      'style' => '',
      'beers_per_page' => -1,
   ), $atts));
   
   return em_beer_list($exclude, $show_profile, $show_extras, $style, $beers_per_page);
}
add_shortcode('beer-list', 'all_beers');


// Beer list template code
function em_beer_list($exclude = '', $profile = 'true', $extras = 'true', $style = '', $pagenum = -1) {
	$args = array('exclude' => $exclude, 'profile' => $profile, 'extras' => $extras, 'style' => $style, 'page_num' => $pagenum);
	return em_beer_list_output ($args);
}

// Beer list display
function em_beer_list_output ($beers) {
	
	// Declared shortcode variables
	$excludes = explode(',', $beers['exclude']);
	$showprofile = $beers['profile'];
	$showextras = $beers['extras'];
	$showstyle = $beers['style'];
	$showpages = $beers['page_num']; 
	
	$output = '';	
	
	// Set up new loop data
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	global $post;
	$wp_query = new WP_Query(); 
	
	// The query
	$args = array (
		'post_type' => 'beer',
		'showposts' => $showpages,
		'paged' => $paged,
		
	);
	
	// Add styles filter
	if ($showstyle != '') {
		$style_slug = get_term_by('name', $showstyle, 'style', 'ARRAY_A');
		$args['style'] = $style_slug['slug'];
	}
	
	// Add id filter
	if ($excludes) {
		$args['post__not_in'] = $excludes;
	}

	$wp_query->query($args);
	
	$output .= '<div class="beer-list">'."\n";
		
	while ($wp_query->have_posts()) : $wp_query->the_post();
	  
	  $output .= '<div id="beer-'.$post->ID.'" class="single-beer beer beer-'.$post->ID.'">'."\n";

		$output .= '<div class="beer-title">'."\n";
		$output .= '<h2>'.get_the_title($post->ID).'</h2>'."\n";
		$output .= '<span class="beer-style">('.get_beer_style($post->ID).')</span>'."\n";
		$output .= '</div>'."\n";
		
		if ( get_the_post_thumbnail($post->ID) != '' ) {
			$output .= '<div class="beer-image">'."\n";
			$output .= get_the_post_thumbnail($post->ID, 'full')."\n";
			$output .= '</div>'."\n";
		}
		
		$output .= '<div class="beer-description">'."\n";
		
			$output .= get_the_content($post->ID);
		
			$ut_option = get_option('embm_options');
			$use_untappd = $ut_option['embm_untappd_check'];
			
			if ($use_untappd != "1") {
				if ( (get_beer($bid,'untappd') != '') ) {
					$output .= '<div class="untappd"><a href="'.get_beer($bid,'untappd').'" target="_blank" title="Check In on Untappd"></a></div>'."\n";
				}
			}
		
		$output .= '</div>'."\n";
		
		if ( ($showprofile == 'true') || ($showextras == 'true') ) {
			
			$output .= '<div class="beer-meta">'."\n";
			
			if ($showprofile == 'true') {
				
				$output .= '<div class="beer-profile">'."\n";
				
				if (get_beer($post->ID,'abv') != '') {
					$output .= '<div class="abv"><span class="label">ABV:</span><span class="value">'.get_beer($post->ID,'abv').'</span></div>'."\n";
				} 
				if (get_beer($post->ID,'ibu') != '') {
					$output .= '<div class="ibu"><span class="label">IBU:</span><span class="value">'.get_beer($post->ID,'ibu').'</span></div>'."\n";
				} 
				if (get_beer($post->ID,'malts') != '') {
					$output .= '<div class="malts"><span class="label">Malts:</span><span class="value">'.get_beer($post->ID,'malts').'</span></div>'."\n";
				}
				if (get_beer($post->ID,'hops') != '') {
					$output .= '<div class="hops"><span class="label">Hops:</span><span class="value">'.get_beer($post->ID,'hops').'</span></div>'."\n";
				}
				if (get_beer($post->ID,'adds') != '') {
					$output .= '<div class="other"><span class="label">Other:</span><span class="value">'.get_beer($post->ID,'adds').'</span></div>'."\n";
				}
				if (get_beer($post->ID,'yeast') != '') {
					$output .= '<div class="yeast"><span class="label">Yeast:</span><span class="value">'.get_beer($post->ID,'yeast').'</span></div>'."\n";
				}
				
				$output .= '</div>'."\n";
				
			}
			
			if ($showextras == 'true') {
				
				$output .= '<div class="beer-extras">';
				
				if (get_beer($post->ID,'avail') != '') {
					$output .= '<div class="avail"><span class="label">Availability:</span><span class="value">'.get_beer($post->ID,'avail').'</span></div>'."\n";
				} 
				if (get_beer($post->ID,'notes') != '') {
					$output .= '<div class="notes"><span class="label">Additional Notes</span><span class="value">'.get_beer($post->ID,'notes').'</span></div>'."\n";
				}
				
				$output .= '</div>'."\n";
				
			}
						
			$output .= '</div>'."\n";
			
		}
		
	 $output .= '</div>'."\n";
		
	endwhile;
		
	// Display navigation to next/previous pages when applicable
	$output .= '<div class="nav-below">'."\n";
	
		$big = 999999999; // need an unlikely integer
		$output .= paginate_links( array(
		  	'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		  	'format' => '?paged=%#%',
		  	'current' => max( 1, get_query_var('paged') ),
		  	'total' => $wp_query->max_num_pages
		  	) );
	
	$output .= '</div>'."\n";
	$output .= '</div>'."\n";
	
	wp_reset_query();
	wp_reset_postdata(); //reset

	return $output;
	
}


?>