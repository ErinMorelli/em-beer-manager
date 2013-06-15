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
function em_beer_single($postid, $profile, $extras) {
	$args = array('id' => $postid, 'profile' => $profile, 'extras' => $extras);
	return em_beer_single_output ($args);
}
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
	
	if ( (get_beer($bid,'untappd') != '') && ($showextras == 'true') ) {
		$output .= '<div class="untappd"><a href="'.get_beer($bid,'untappd').'" target="_blank" title="Check In on Untappd"></a></div>'."\n";
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
function em_beer_list($exclude, $profile, $extras, $style, $pagenum) {
	$args = array('exclude' => $exclude, 'profile' => $profile, 'extras' => $extras, 'style' => $style, 'page_num' => $pagenum);
	return em_beer_list_output ($args);
}
function em_beer_list_output ($beers) {
	
	$excludes = explode(',', $beers['exclude']);

	$showprofile = $beers['profile'];
	echo $showprofile;
	$showextras = $beers['extras'];
	echo $showextras;
	$showstyle = $beers['style'];
	echo $showstyle;
	$showpages = $beers['page_num']; 
	echo $showpages;
	
	
	echo '<div class="beer-list">'."\n";
	
	wp_reset_postdata();
	
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
	global $post;
	
	$temp = $wp_query; 
	$wp_query = null; 
	$wp_query = new WP_Query(); 
	
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
		
	while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
	  
	  <div id="beer-<?php echo $post->ID; ?>'" class="single-beer beer beer-<?php echo $post->ID; ?>">

		<div class="beer-title">
			<h2><?php the_title(); ?></h2>
			<span class="beer-style">(<?php echo get_beer_style($post->ID); ?>)</span>
		</div>
		
		<?php if ( get_the_post_thumbnail($post->ID) != '' ) : ?>
			<div class="beer-image">
				<?php echo get_the_post_thumbnail($post->ID, 'full'); ?>
			</div>
		<?php endif; ?>
		
		<div class="beer-description">
		
			<?php the_content(); ?>
		
			<?php if ( (get_beer($post->ID,'untappd') != '') && ($showextras == 'true') ) : ?>
				<div class="untappd"><a href="<?php get_beer($post->ID,'untappd'); ?>" target="_blank" title="Check In on Untappd"></a></div>
			<?php endif; ?>
		
		</div>
		
		<?php if ( ($showprofile == 'true') || ($showextras == 'true') ) : ?>
			
			<div class="beer-meta">
			
			<?php if ($showprofile == 'true') : ?>
				
				<div class="beer-profile">
				
				<?php if (get_beer($post->ID,'abv') != '') {
					echo '<div class="abv"><span class="label">ABV:</span><span class="value">'.get_beer($post->ID,'abv').'</span></div>'."\n";
				} 
				if (get_beer($post->ID,'ibu') != '') {
					echo '<div class="ibu"><span class="label">IBU:</span><span class="value">'.get_beer($post->ID,'ibu').'</span></div>'."\n";
				} 
				if (get_beer($post->ID,'malts') != '') {
					echo '<div class="malts"><span class="label">Malts:</span><span class="value">'.get_beer($post->ID,'malts').'</span></div>'."\n";
				}
				if (get_beer($post->ID,'hops') != '') {
					echo '<div class="hops"><span class="label">Hops:</span><span class="value">'.get_beer($post->ID,'hops').'</span></div>'."\n";
				}
				if (get_beer($post->ID,'adds') != '') {
					echo '<div class="other"><span class="label">Other:</span><span class="value">'.get_beer($post->ID,'adds').'</span></div>'."\n";
				}
				if (get_beer($post->ID,'yeast') != '') {
					echo '<div class="yeast"><span class="label">Yeast:</span><span class="value">'.get_beer($post->ID,'yeast').'</span></div>'."\n";
				} ?>
				
				</div>
				
			<?php endif; ?>
			
			<?php if ($showextras == 'true') : ?>
				
				<div class="beer-extras">
				
				<?php if (get_beer($post->ID,'avail') != '') {
					echo '<div class="avail"><span class="label">Availability:</span><span class="value">'.get_beer($post->ID,'avail').'</span></div>'."\n";
				} 
				if (get_beer($post->ID,'notes') != '') {
					echo '<div class="notes"><span class="label">Additional Notes</span><span class="value">'.get_beer($post->ID,'notes').'</span></div>'."\n";
				} ?>
				
				</div>
				
			<?php endif; ?>
			
			</div>	
			
		<?php endif; ?>
		
	  </div>
		
	<?php endwhile; ?>
		
	<!-- Display navigation to next/previous pages when applicable -->
	<div class="nav-below">
	
		<div class="nav-previous"><?php next_posts_link('<span class="meta-nav">&larr;</span> Older brews'); ?></div>
		<div class="nav-next"><?php previous_posts_link('Newer brews <span class="meta-nav">&rarr;</span>'); ?></div>

	</div><!-- #nav-below -->
	
	<?php $wp_query = null; 
		  $wp_query = $temp;  // Reset

}


?>