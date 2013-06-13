<?php 
/*
 * EM Beer Manager admin display
 *
 */
 
 
// Styling for the custom post type icon

add_action( 'admin_head', 'em_beermanage_icons' );

function em_beermanage_icons() { ?>

	<style type="text/css" media="screen">
		#menu-posts-beer .wp-menu-image {
            background: url(<?php echo EM_BEERMANAGE_URL.'assets/img/beer-icon.png'; ?>) no-repeat 6px 6px !important;
        }
        #menu-posts-beer:hover .wp-menu-image, #menu-posts-portfolio.wp-has-current-submenu .wp-menu-image {
        	background-position:6px -18px !important;
        }
        #icon-edit.icon32-posts-beer {background: url(<?php echo EM_BEERMANAGE_URL.'assets/img/beer-32x32.png';?>) no-repeat;}
    </style>
<?php }

 
 // ==== CUSTOM BEER ADMIN COLUMNS  === //

function change_columns( $cols ) {
  $cols = array(
    'cb'       => '<input type="checkbox" />',
    'title'      => __( 'Beer',      'trans' ),
    'taxonomy-style' => __( 'Style', 'trans' ),
    'abv'     => __( 'ABV', 'trans' ),
    'ibu'	=> __( 'IBU', 'trans' ),
    'avail'     => __( 'Availability', 'trans' ),
    'untappd'     => __( 'Untappd', 'trans' ),
    'date'     => __( 'Released', 'trans' )
  );
  return $cols;
}
add_filter( 'manage_beer_posts_columns', 'change_columns' );

function custom_columns( $column, $post_id ) {
  switch ( $column ) {
    case "abv":
      echo get_beer($post_id, 'abv');
      break;
    case "ibu":
      echo get_beer($post_id, 'ibu');
      break;
    case "avail":
      echo get_beer($post_id, 'avail');
      break;
    case "untappd":
      $untap = get_beer($post_id, 'untappd');
      if ($untap != '') {
      	$uticon = EM_BEERMANAGE_URL.'assets/img/ut-icon.png';
      	echo '<a href="'.$untap.'" target="_blank"><img src="'.$uticon.'" border="0" alt="Untappd" /></a>';
      } else {
      	echo '';
      }
      break;
  }
}
add_action( 'manage_beer_posts_custom_column', 'custom_columns', 10, 2 );


// Make these columns sortable

function sortable_columns() {
  return array(
    'title' => 'title',
    'taxonomy-style'  => 'taxonomy-style',
    'abv'  => 'abv',
    'ibu' => 'ibu',
    'avail' => 'avail',
    'date' => 'date'
  );
}
add_filter( 'manage_edit-beer_sortable_columns', 'sortable_columns' );


?>