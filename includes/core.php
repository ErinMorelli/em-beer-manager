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
* EM Beer Manager core functions
*
*/


add_theme_support( 'post-thumbnails', array('beer') );


// ==== CUSTOM BEER POST TYPE === //

function embm_custom_post_beer() {
	$labels = array(
		'name'               => __( 'Beers', 'embm' ),
		'singular_name'      => __( 'Beer', 'embm' ),
		'add_new'            => __( 'Add New', 'embm' ),
		'add_new_item'       => __( 'Add New Beer', 'embm' ),
		'edit_item'          => __( 'Edit Beer', 'embm' ),
		'new_item'           => __( 'New Beer', 'embm' ),
		'all_items'          => __( 'All Beers', 'embm' ),
		'view_item'          => null,
		'search_items'       => __( 'Search Beers', 'embm' ),
		'not_found'          => __( 'No beers found', 'embm' ),
		'not_found_in_trash' => __( 'No beers found in the Trash', 'embm' ), 
		'parent_ithwh_colon'  => '',
		'menu_name'          => __( 'Beers', 'embm' )
	);
	$args = array(
		'labels'        	=> $labels,
		'description'   	=> 'Holds beer specific data',
		'public'        	=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'taxonomies'		=> array('style'),
		'has-archive' 		=> true,
		'menu_position' 	=> 5,
		'rewrite' 			=> array( 'slug' => 'beers', 'with_front' => false),
		'supports'      	=> array( 'title', 'editor', 'thumbnail', 'revisions'),
	);
	register_post_type( 'beer', $args );	
}

add_action( 'init', 'embm_custom_post_beer' );




// ==== REGISTER BEER PROFILE META BOX === //

add_action( 'add_meta_boxes', 'embm_beer_specs_add' );  

function embm_beer_specs_add() {  
	add_meta_box( 'beer-specs', 'Beer Profile', 'embm_beer_specs_cb', 'beer', 'side', 'core' );
	add_meta_box( 'beer-info', 'More Information', 'embm_beer_info_cb', 'beer', 'normal', 'core' );
}

function embm_beer_specs_cb() {  
    // $post is already set, and contains an object: the WordPress post  
    global $post;  
    $beer_entry = get_post_custom( $post->ID );
	$b_malts = isset( $beer_entry['malts'] ) ? esc_attr( $beer_entry['malts'][0] ) : '';
	$b_hops = isset( $beer_entry['hops'] ) ? esc_attr( $beer_entry['hops'][0] ) : '';
	$b_adds= isset( $beer_entry['adds'] ) ? esc_attr( $beer_entry['adds'][0] ) : '';
	$b_yeast = isset( $beer_entry['yeast'] ) ? esc_attr( $beer_entry['yeast'][0] ) : '';
	$b_ibu = isset( $beer_entry['ibu'] ) ? esc_attr( $beer_entry['ibu'][0] ) : '0';
	$b_abv = isset( $beer_entry['abv'] ) ? esc_attr( $beer_entry['abv'][0] ) : '0';
    // We'll use this nonce field later on when saving.  
    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' ); 
    ?> 
    
    <table width="100%" cellpadding="0" cellspacing="0">
    <tbody>
    	<tr>
    	<td>
		    <p><label for="malts"><strong><?php _e('Malts: ', 'embm'); ?></strong></label><br />
		    	<input type="text" name="malts" id="malts" style="width:100%;" value="<?php echo $b_malts; ?>" /></p>
		    <p><label for="hops"><strong><?php _e('Hops: ', 'embm'); ?></strong></label><br />
		    	<input type="text" name="hops" id="hops" style="width:100%;" value="<?php echo $b_hops; ?>" /></p>
		    <p><label for="adds"><strong><?php _e('Additions/Spices: ', 'embm'); ?></strong></label><br />
		    	<input type="text" name="adds" id="adds" style="width:100%;" value="<?php echo $b_adds; ?>" /></p>
		    <p><label for="yeast"><strong><?php _e('Yeast: ', 'embm'); ?></strong></label><br />
		    	<input type="text" name="yeast" id="yeast" style="width:100%;" value="<?php echo $b_yeast; ?>" /></p>
    	<hr />
		    <p><label for="abv"><strong><?php _e('ABV: ', 'embm'); ?></strong></label>
		       <input type="number" name="abv" id="abv" min="0.0" max="100.0" step="0.1" value="<?php echo $b_abv; ?>" /> %</p>
		    <p><label for="ibu"><strong><?php _e('IBU: ', 'embm'); ?></strong></label>
		       <input type="number" name="ibu" id="style" min="0" max="100" step="1" value="<?php echo $b_ibu; ?>" /></p>
    	</td>
    	</tr>
    </tbody>
    </table>
          
    <?php  
}  

// Save Beer meta box inputs

add_action( 'save_post', 'embm_beer_specs_save' ); 
 
function embm_beer_specs_save( $post_id )  {  
   // Bail if we're doing an auto save  
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
    // if our nonce isn't there, or we can't verify it, bail 
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return; 
    // if our current user can't edit this post, bail  
    if( !current_user_can( 'edit_post' ) ) return;  
    // now we can actually save the data 
     
    // Make sure your data is set before trying to save it  
    if( isset( $_POST['malts'] ) )  
        update_post_meta( $post_id, 'malts', esc_attr( $_POST['malts'] ) );
    if( isset( $_POST['hops'] ) )  
        update_post_meta( $post_id, 'hops', esc_attr( $_POST['hops'] ) );
    if( isset( $_POST['adds'] ) )  
        update_post_meta( $post_id, 'adds', esc_attr( $_POST['adds'] ) );  
    if( isset( $_POST['yeast'] ) )  
        update_post_meta( $post_id, 'yeast', esc_attr( $_POST['yeast'] ) );  
    if( isset( $_POST['ibu'] ) )  
        update_post_meta( $post_id, 'ibu', esc_attr( $_POST['ibu'] ) );  
    if( isset( $_POST['abv'] ) )  
        update_post_meta( $post_id, 'abv', esc_attr( $_POST['abv'] ) );   
}



// ==== REGISTER MORE INFO META BOX === //

function embm_beer_info_cb() {  
    // $post is already set, and contains an object: the WordPress post  
    global $post;  
    $beer_entry = get_post_custom( $post->ID );
	$b_avail = isset( $beer_entry['avail'] ) ? esc_attr( $beer_entry['avail'][0] ) : '';
	$b_untap = isset( $beer_entry['untappd'] ) ? esc_attr( $beer_entry['untappd'][0] ) : '';
	$b_notes = isset( $beer_entry['notes'] ) ? esc_attr( $beer_entry['notes'][0] ) : '';
	
	$ut_option = get_option('embm_options');
	$use_untappd = $ut_option['embm_untappd_check'];
	
    // We'll use this nonce field later on when saving.  
    wp_nonce_field( 'my_meta_box_nonce_two', 'meta_box_nonce_two' ); 
    ?> 
    
    <table width="100%" cellpadding="0" cellspacing="0">
    <tbody>
    	<tr>
    	<td width="60%" valign="top">
		   <p><label for="notes"><strong><?php _e('Additional Notes/Food Pairings:', 'embm'); ?></strong></label><br />
		     <textarea name="notes" id="notes" rows="7" cols="70" style="width:95%;"><?php echo $b_notes; ?></textarea></p>
    	</td><td valign="top">
		    <p><label for="avail"><strong><?php _e('Availability: ','embm'); ?></strong></label><br />
		    	<input type="text" name="avail" id="avail" style="width:95%;" value="<?php echo $b_avail; ?>" /></p>
		    	
		    <?php if ( $use_untappd != "1" ) : ?>
		    	<p><label for="untappd"><strong><?php _e('Untappd Check-In URL:', 'embm'); ?></strong></label><br />
		    	<input type="url" name="untappd" id="untappd" style="width:95%;" value="<?php echo $b_untap; ?>" /></p>
		    <?php endif; ?>
    	</td>
    	</tr>
    </tbody>
    </table>
          
    <?php  
}  

// Save Beer meta box inputs

add_action( 'save_post', 'embm_beer_info_save' ); 
 
function embm_beer_info_save( $post_id )  {  
   // Bail if we're doing an auto save  
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
    // if our nonce isn't there, or we can't verify it, bail 
    if( !isset( $_POST['meta_box_nonce_two'] ) || !wp_verify_nonce( $_POST['meta_box_nonce_two'], 'my_meta_box_nonce_two' ) ) return; 
    // if our current user can't edit this post, bail  
    if( !current_user_can( 'edit_post' ) ) return;  
    // now we can actually save the data 
     
    // Make sure your data is set before trying to save it  
    if( isset( $_POST['avail'] ) )  
        update_post_meta( $post_id, 'avail', esc_attr( $_POST['avail'] ) );
    if( isset( $_POST['notes'] ) )  
        update_post_meta( $post_id, 'notes', esc_attr( $_POST['notes'] ) );
    if( isset( $_POST['untappd'] ) )  
        update_post_meta( $post_id, 'untappd', esc_attr( $_POST['untappd'] ) );    
}








// ==== CUSTOM BEER FUNCTIONS === //

function embm_get_beer($postid, $attr) {
	$b_attr = get_post_meta($postid, $attr, true); 
	if ($attr == 'abv') {
		return $b_attr . '%';
	}
	else {return $b_attr;}
}
function embm_get_beer_style($postid) {
	$types = wp_get_object_terms($postid, 'style'); 
	foreach($types as $type) { 
		return $type->name; 
	}
}




// ==== CUSTOM STYLE TAXONOMY === //

add_action( 'init', 'embm_create_style_tax', 0 );

function embm_create_style_tax() {
	$labels = array(
		'name'              => __( 'Styles', 'embm' ),
		'singular_name'     => __( 'Style', 'embm' ),
		'search_items'      => __( 'Search Styles', 'embm' ),
		'all_items'         => __( 'All Styles', 'embm' ),
		'edit_item'         => __( 'Edit Style', 'embm' ),
		'update_item'       => __( 'Update Style', 'embm' ),
		'add_new_item'      => __( 'Add New Style', 'embm' ),
		'new_item_name'     => __( 'New Style Name', 'embm' ),
		'popular_items' 	=> __( 'Popular Styles', 'embm' ),
		'choose_from_most_used' => __( 'Choose from the most used styles', 'embm' ),
		'separate_items_with_commas' => __( 'Separate styles with commas', 'embm' ),
		'add_or_remove_items' => __( 'Add or remove styles', 'embm' ),
		'menu_name'         => __( 'Styles', 'embm' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'beers/style', 'with_front' => false ),
	);

	register_taxonomy( 'style', array( 'beer' ), $args );
}



