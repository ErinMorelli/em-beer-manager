<?php 
/*
 * EM Beer Manager shortcodes
 *
 */
 

// Single Beer shortcode
function single_beer($atts) {
   extract(shortcode_atts(array(
      'id' => 0,
      'show_profile' => true,
      'show_extras' => true,
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
	$output .= '<div id="beer-'.$bid.'" class="single-beer beer beer-'.$bid.'">';
	$output .= '<h2 class="beer-title">'.$the_beer->post_title.'</h2>';
	$output .= '<div class="beer-description">'.$the_beer->post_content.'</div>';
	
	if ($showextras) {
		$output .= '';
	}
	
	if ($showprofile) {
		
	}
	
	$output .= '</div>';
	
	return $output;
}

// Beer list shortcode
function all_beers($atts) {
   extract(shortcode_atts(array(
      'exclude' => 0,
      'show_profile' => true,
      'show_extras' => true,
      'style' => '',
   ), $atts));
   
   return '<img src="http://lorempixel.com/'. $width . '/'. $height . '" />';
}
add_shortcode('beer-list', 'all_beers');


// Beer list template code
function em_beer_list($postid, $profile, $extras) {
	
}