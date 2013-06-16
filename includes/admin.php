<?php 
/*
 * EM Beer Manager admin display
 *
 */

 
// Styling for the custom post type

add_action( 'admin_head', 'em_beermanage_style' );

function em_beermanage_style() { ?>

	<style type="text/css" media="screen">
		#menu-posts-beer .wp-menu-image {
            background: url(<?php echo EM_BEERMANAGE_URL.'assets/img/beer-icon.png'; ?>) no-repeat 6px 6px !important;
        }
        #menu-posts-beer:hover .wp-menu-image, #menu-posts-portfolio.wp-has-current-submenu .wp-menu-image {
        	background-position:6px -18px !important;
        }
        #icon-edit.icon32-posts-beer {background: url(<?php echo EM_BEERMANAGE_URL.'assets/img/beer-32x32.png';?>) no-repeat;}
        .widefat .column-id {width: 35px;}
        
    </style>
<?php }

 
 // ==== CUSTOM BEER ADMIN COLUMNS  === //

function change_columns( $cols ) {
  $cols = array(
    'cb'       => '<input type="checkbox" />',
    'id'	=> __( 'ID',      'trans' ),
    'title'      => __( 'Beer',      'trans' ),
    'taxonomy-style' => __( 'Style', 'trans' ),
    'abv'     => __( 'ABV', 'trans' ),
    'ibu'	=> __( 'IBU', 'trans' ),
    'avail'     => __( 'Availability', 'trans' )
  );
  
  $ut_option = get_option('embm_options');
  $use_untappd = $ut_option['embm_untappd_check']; 
  
  if ($use_untappd != "1") {
	  $cols['untappd'] = __( 'Untappd', 'trans' );
  }
  
  $cols['date'] = __( 'Released', 'trans' );
  
  return $cols;
}
add_filter( 'manage_beer_posts_columns', 'change_columns' );

function custom_columns( $column, $post_id ) {
  switch ( $column ) {
    case "id":
      echo $post_id;
      break;
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
    'abv'  => 'abv',
    'ibu' => 'ibu',
    'avail' => 'avail',
    'date' => 'date'
  );
}
add_filter( 'manage_edit-beer_sortable_columns', 'sortable_columns' );


/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'my_edit_beer_load' );

function my_edit_beer_load() {
	add_filter( 'request', 'my_sort_beers' );
}

/* Sorts the beers. */
function my_sort_beers( $vars ) {

	/* Check if we're viewing the 'beer' post type. */
	if ( isset( $vars['post_type'] ) && 'beer' == $vars['post_type'] ) {

		/* Check if 'orderby' is set to 'abv'. */
		if ( isset( $vars['orderby'] ) && 'abv' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'abv',
					'orderby' => 'meta_value_num'
				)
			);
		}
		/* Check if 'orderby' is set to 'ibu'. */
		if ( isset( $vars['orderby'] ) && 'ibu' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'ibu',
					'orderby' => 'meta_value_num'
				)
			);
		}
		/* Check if 'orderby' is set to 'avail'. */
		if ( isset( $vars['orderby'] ) && 'avail' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'avail',
					'orderby' => 'meta_value'
				)
			);
		}
	}

	return $vars;
}


// Add Settings page 
add_action('admin_menu', 'embm_admin_menu');

function embm_admin_menu() {
    $page_title = 'EM Beer Manager Settings';
    $menu_title = 'EM Beer Manager';
    $capability = 'manage_options';
    $menu_slug = 'embm-settings';
    $function = 'embm_settings';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}


// Setup checkboxes
function options_embm_add_js() { ?>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$("input[name='disble_untappd']").focus(function(){
			$("#disble_untappd").attr("checked", "checked");
		});
	});
//]]>
</script>
<style type="text/css">
#beer-settings .usage tr td {
	border-collapse: collapse;
	border-bottom: 1px solid #ccc;

	padding: 5px 10px;
}
#beer-settings .usage tr:first-child td {
	border-top: 1px solid #ccc;
}
#beer-settings .usage tr:nth-child(even) td {
	background: #fafafa;
}
.emdm-form-settings .form-table {
    margin-bottom: 20px;    
}
</style>
<?php
}
add_action('admin_head', 'options_embm_add_js');



// Register new settings
function register_mysettings() { // whitelist options
  register_setting( 'embm_options', 'embm_options', 'embm_options_validate' );
  
  add_settings_section('embm_untappd', 'Untappd', 'embm_section_text', 'embm');
  add_settings_field('embm_untappd_check', 'Check-in button:', 'embm_untappd_box', 'embm', 'embm_untappd');
  
  add_settings_section('embm_custom_url', 'Custom Styleseet', 'embm_section_text', 'embm');
  add_settings_field('embm_css_url', 'Enter URL for custom stylesheet:', 'embm_css_url', 'embm', 'embm_custom_url');
}

add_action( 'admin_init', 'register_mysettings' );

function embm_options_validate($input) {
	return $input;
}

function embm_untappd_box() {
	$options = get_option('embm_options');
	echo '<input name="embm_options[embm_untappd_check]" type="checkbox" id="embm_untappd_check" value="1"'.checked('1', $options['embm_untappd_check'], false).' /> Disable option';
} 
function embm_css_url() {
	$options = get_option('embm_options');
	echo "<input id='embm_css_url' name='embm_options[embm_css_url]' style='width: 50%;' type='url' value='{$options['embm_css_url']}' />";
} 


function embm_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

?>
<div class="wrap" id="beer-settings">

    <div id="icon-edit" class="icon32 icon32-posts-beer"><br /></div><h2>EM Beer Manager</h2>
    
    <h2>Settings</h2>
    
    <form method="post" action="options.php" class="emdm-form-settings"> 
    
    <?php settings_fields( 'embm_options' );
    	  do_settings_sections( 'embm' );?>
    	      
    <p style="margin-top:1em;"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
   
    </form>
    
    <br />
    
    <hr />
    
    <h2>Usage</h2>

    <p>Use these shortcodes to display beers in your posts or use the template tags in your theme files</p>


    <h3>Single Beer Display</h3>
    
     <p>These will display a single beer entry given it's ID number.</p>

     <p><code> [beer id={beer id}] </code></p>
     <p><code><?php echo htmlentities('<?php echo em_beer_single( [beer id], [show_profile (optional)], [show_extras (optional)] ); ?>'); ?></code></p>

     <p style="margin-top:2em;">Optional attributes (for both shortcode and template code):</p>
     <table class="usage" cellpadding="0" cellspacing="0" border="0">
     <tr>
	     <td><code><strong>show_profile=</strong>{true/false}</code></td>
	     <td>(Default = <code>true</code>)</td>
	     <td><em>Will display or hide the "Beer Profile" box</em></td>
	 </tr><tr>
		 <td><code><strong>show_extras=</strong>{true/false}</code></td> 
		 <td>(Default = <code>true</code>)</td>
		 <td><em>Will display or hide the "More Information" section</em></td>
     </tr>
	</table>
	
	<h3 style="margin-top:2em;">List All Beers</h3>

	  <p>These will display a formatted listing all beers in the database.</p>

     <p><code>[beer-list]</code></p>
     <p><code><?php echo htmlentities('<?php echo em_beer_list( [exclude (optional)], [show_profile (optional)], [show_extras (optional)], [style (optional)] ); ?>'); ?></code></p>
    
     <p style="margin-top:2em;">Optional attributes (for both shortcode and template code):</p>
     <table class="usage" cellpadding="0" cellspacing="0" border="0">
     <tr>
	     <td><code><strong>exclude=</strong>{"beer ids"}</code></td>
	     <td>(String separated by commas e.g. <code>"4,23,24"</code>)</td>
	     <td><em>Will hide listed beers from listing</em></td>
     </tr><tr>
	     <td><code><strong>show_profile=</strong>{true/false}</code></td>
	     <td>(Default = <code>true</code>)</td>
	     <td><em>Will display or hide the "Beer Profile" box for each listing</em></td>
     </tr><tr>
	     <td><code><strong>show_extras=</strong>{true/false}</code></td>
	     <td>(Default = <code>true</code>)</td>
	     <td><em>Will display or hide the "More Information" section for each listing</em></td>
     </tr><tr>
	     <td><code><strong>style=</strong>{"style name"}</code></td>
	     <td>(String e.g. <code>"India Pale Ale"</code>)</td>
	     <td><em>Will display only beers belonging to a specific beer style</em></td>
     </tr><tr>
	     <td><code><strong>beers_per_page=</strong>{number}</code></td>
	     <td>(Default = <code>-1</code>, shows all beers on one page)</td>
	     <td><em>Will display the given number of beers per page</em></td>
     </tr>
     </table>
     <br />
     
     <hr />
     
     <p>If you like this plugin, please consider donating to help support future development. Thank you!</p>
     
     <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>


</div>
<?php
}


?>