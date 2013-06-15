<?php
/*
 * EM Beer Manager core files
 *
 */


add_theme_support( 'post-thumbnails', array('beer') );


// ==== CUSTOM BEER POST TYPE === //

function custom_post_beer() {
	$labels = array(
		'name'               => _x( 'Beers', 'post type general name' ),
		'singular_name'      => _x( 'Beer', 'post type singular name' ),
		'add_new'            => __( 'Add New' ),
		'add_new_item'       => __( 'Add New Beer' ),
		'edit_item'          => __( 'Edit Beer' ),
		'new_item'           => __( 'New Beer' ),
		'all_items'          => __( 'All Beers' ),
		'view_item'          => __( 'View Beer' ),
		'search_items'       => __( 'Search Beers' ),
		'not_found'          => __( 'No beers found' ),
		'not_found_in_trash' => __( 'No beers found in the Trash' ), 
		'parent_ithwh_colon'  => '',
		'menu_name'          => 'Beers'
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

add_action( 'init', 'custom_post_beer' );




// ==== REGISTER BEER PROFILE META BOX === //

add_action( 'add_meta_boxes', 'beer_specs_add' );  

function beer_specs_add() {  
	add_meta_box( 'beer-specs', 'Beer Profile', 'beer_specs_cb', 'beer', 'side', 'core' );
	add_meta_box( 'beer-info', 'More Information', 'beer_info_cb', 'beer', 'normal', 'core' );
}

function beer_specs_cb() {  
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
		    <p><label for="malts"><strong>Malts: </strong></label><br />
		    	<input type="text" name="malts" id="malts" style="width:100%;" value="<?php echo $b_malts; ?>" /></p>
		    <p><label for="hops"><strong>Hops: </strong></label><br />
		    	<input type="text" name="hops" id="hops" style="width:100%;" value="<?php echo $b_hops; ?>" /></p>
		    <p><label for="adds"><strong>Additions/Spices:</strong></label><br />
		    	<input type="text" name="adds" id="adds" style="width:100%;" value="<?php echo $b_adds; ?>" /></p>
		    <p><label for="yeast"><strong>Yeast:</strong></label><br />
		    	<input type="text" name="yeast" id="yeast" style="width:100%;" value="<?php echo $b_yeast; ?>" /></p>
    	<hr />
		    <p><label for="abv"><strong>ABV:</strong></label>
		       <input type="number" name="abv" id="abv" min="0.0" max="100.0" step="0.1" value="<?php echo $b_abv; ?>" /> %</p>
		    <p><label for="ibu"><strong>IBU:</strong></label>
		       <input type="number" name="ibu" id="style" min="0" max="100" step="1" value="<?php echo $b_ibu; ?>" /></p>
    	</td>
    	</tr>
    </tbody>
    </table>
          
    <?php  
}  

// Save Beer meta box inputs

add_action( 'save_post', 'beer_specs_save' ); 
 
function beer_specs_save( $post_id )  {  
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

function beer_info_cb() {  
    // $post is already set, and contains an object: the WordPress post  
    global $post;  
    $beer_entry = get_post_custom( $post->ID );
	$b_avail = isset( $beer_entry['avail'] ) ? esc_attr( $beer_entry['avail'][0] ) : '';
	$b_untap = isset( $beer_entry['untappd'] ) ? esc_attr( $beer_entry['untappd'][0] ) : '';
	$b_notes = isset( $beer_entry['notes'] ) ? esc_attr( $beer_entry['notes'][0] ) : '';
    // We'll use this nonce field later on when saving.  
    wp_nonce_field( 'my_meta_box_nonce_two', 'meta_box_nonce_two' ); 
    ?> 
    
    <table width="100%" cellpadding="0" cellspacing="0">
    <tbody>
    	<tr>
    	<td width="60%" valign="top">
		   <p><label for="notes"><strong>Additional Notes/Food Pairings:</strong></label><br />
		     <textarea name="notes" id="notes" rows="7" cols="70" style="width:95%;"><?php echo $b_notes; ?></textarea></p>
    	</td><td valign="top">
		    <p><label for="avail"><strong>Availability: </strong></label><br />
		    	<input type="text" name="avail" id="avail" style="width:95%;" value="<?php echo $b_avail; ?>" /></p>
		    <p><label for="untappd"><strong>Untappd Check-In URL:</strong></label><br />
		       <input type="url" name="untappd" id="untappd" style="width:95%;" value="<?php echo $b_untap; ?>" /></p>
    	</td>
    	</tr>
    </tbody>
    </table>
          
    <?php  
}  

// Save Beer meta box inputs

add_action( 'save_post', 'beer_info_save' ); 
 
function beer_info_save( $post_id )  {  
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

function get_beer($postid, $attr) {
	$b_attr = get_post_meta($postid, $attr, true); 
	if ($attr == 'abv') {
		return $b_attr . '%';
	}
	else {return $b_attr;}
}
function get_beer_style($postid) {
	$types = wp_get_object_terms($postid, 'style'); 
	foreach($types as $type) { 
		return $type->name; 
	}
}




// ==== CUSTOM STYLE TAXONOMY === //

add_action( 'init', 'create_style_tax', 0 );

function create_style_tax() {
	$labels = array(
		'name'              => _x( 'Styles', 'taxonomy general name' ),
		'singular_name'     => _x( 'Style', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Styles' ),
		'all_items'         => __( 'All Styles' ),
		'edit_item'         => __( 'Edit Style' ),
		'update_item'       => __( 'Update Style' ),
		'add_new_item'      => __( 'Add New Style' ),
		'new_item_name'     => __( 'New Style Name' ),
		'popular_items' 	=> __( 'Popular Styles' ),
		'choose_from_most_used' => __( 'Choose from the most used styles' ),
		'separate_items_with_commas' => __( 'Separate styles with commas' ),
		'add_or_remove_items' => __( 'Add or remove styles' ),
		'menu_name'         => __( 'Styles' ),
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



