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
* EM Beer Manager admin display
*
*/

 
// Enqueue admin styles
function embm_admin_styles() {
    wp_enqueue_style('embm-admin', EMBM_PLUGIN_URL.'assets/css/admin.css');
}
add_action('admin_enqueue_scripts', 'embm_admin_styles');
add_action('login_enqueue_scripts', 'embm_admin_styles');

 
 // ==== CUSTOM BEER ADMIN COLUMNS  === //

function embm_change_columns( $cols ) {
  $cols = array(
    'cb'       => '<input type="checkbox" />',
    'id'	=> __( 'ID', 'embm' ),
    'title'      => __( 'Beer', 'embm' ),
    'taxonomy-style' => __( 'Style', 'embm' ),
    'abv'     => __( 'ABV', 'embm' ),
    'ibu'	=> __( 'IBU', 'embm' ),
    'avail'     => __( 'Availability', 'embm' )
  );
  
  $ut_option = get_option('embm_options');
  $use_untappd = $ut_option['embm_untappd_check']; 
  
  if ($use_untappd != "1") {
	  $cols['untappd'] = __( 'Untappd', 'embm' );
  }
  
  $cols['date'] = __( 'Released', 'embm' );
  
  return $cols;
}
add_filter( 'manage_beer_posts_columns', 'embm_change_columns' );

function embm_custom_columns( $column, $post_id ) {
  switch ( $column ) {
    case "id":
      echo $post_id;
      break;
    case "abv":
      echo embm_get_beer($post_id, 'abv');
      break;
    case "ibu":
      echo embm_get_beer($post_id, 'ibu');
      break;
    case "avail":
      echo embm_get_beer($post_id, 'avail');
      break;
    case "untappd":
      $untap = embm_get_beer($post_id, 'untappd');
      if ($untap != '') {
      	$uticon = EMBM_PLUGIN_URL.'assets/img/ut-icon.png';
      	echo '<a href="'.$untap.'" target="_blank"><img src="'.$uticon.'" border="0" alt="Untappd" /></a>';
      } else {
      	echo '';
      }
      break;
  }
}
add_action( 'manage_beer_posts_custom_column', 'embm_custom_columns', 10, 2 );


// Make these columns sortable

function embm_sortable_columns() {
  return array(
    'title' => 'title',
    'abv'  => 'abv',
    'ibu' => 'ibu',
    'avail' => 'avail',
    'date' => 'date'
  );
}
add_filter( 'manage_edit-beer_sortable_columns', 'embm_sortable_columns' );


/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'embm_edit_beer_load' );

function embm_edit_beer_load() {
	add_filter( 'request', 'embm_sort_beers' );
}

/* Sorts the beers. */
function embm_sort_beers( $vars ) {

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
	global $embm_admin_page;
	
    $page_title = __('EM Beer Manager Settings', 'embm');
    $menu_title = __('EM Beer Manager', 'embm');
    $capability = 'manage_options';
    $menu_slug = 'embm-settings';
    $function = 'embm_settings';
    
    $embm_admin_page = add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
    
    add_action('load-'.$embm_admin_page, 'ebmb_admin_help_tab'); // Load help tab
}

// Add help tab
function ebmb_admin_help_tab() {
    global $embm_admin_page;
    $screen = get_current_screen();
    
    if ( $screen->id != $embm_admin_page )
        return;
        
	$screen->add_help_tab( array(
		'id'      => 'untappd',
		'title'   => __('Untappd Integration', 'embm'),
		'content' => '<p>'.__('Checking the "Disable integration" option below, will completely disable all Untappd functionality, including per-beer check-in buttons and the Recent Check-Ins widget. You can disable the Untappd check-in button for an individual beer by simply leaving the setting empty. Beers that have an active check-in button will display a square Untappd icon next to their entry on the Beers admin page."', 'embm').'</p>'.
			'<h3>Brewery ID</h3>'.
			'<p>' . __("Find your Untappd brewery ID number by going to your brewery's official page (i.e. ", "embm").'<code>https://untappd.com/BreweryName</code>'.__("). Click on the orange RSS feed button. The URL will be formatted like this:", "embm") . '</p>' .
			'<p><code>https://untappd.com/rss/brewery/<strong>64324</strong></code></p>' .
			'<p>' . __("The string of numbers at the end of the URL is your brewery ID number, which you can enter below to utilize special Untappd integration features.", "embm") . '</p>',
	) );
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
<?php
}
add_action('admin_head', 'options_embm_add_js');



// Register new settings
function embm_register_settings() { // whitelist options
  register_setting( 'embm_options', 'embm_options', 'embm_options_validate' );
  
  // Untappd settings
  add_settings_section('embm_untappd', 'Untappd', 'embm_section_text', 'embm');
  add_settings_field('embm_untappd_check', 'Disable integration:', 'embm_untappd_box', 'embm', 'embm_untappd');
  add_settings_field('embm_untappd_brewery', 'Untappd brewery ID number:', 'embm_untappd_brewery_box', 'embm', 'embm_untappd');
  
  // Custom css settings
  add_settings_section('embm_custom_url', 'Custom Styleseet', 'embm_section_text', 'embm');
  add_settings_field('embm_css_url', 'Enter URL for custom stylesheet:', 'embm_css_url', 'embm', 'embm_custom_url');
  
  /* Age verification settings
  add_settings_section('embm_age_verify', 'Age Verification', 'embm_section_text', 'embm');
  add_settings_field('embm_age_enable', 'Enable age verification check:', 'embm_age_enable_box', 'embm', 'embm_age_verify');
  add_settings_field('embm_age_limit', 'Set age restriction:', 'embm_age_limit_box', 'embm', 'embm_age_verify');
  add_settings_field('embm_age_duration', 'Remember verification for:', 'embm_age_duration_box', 'embm', 'embm_age_verify');
  add_settings_field('embm_age_type', 'Verification type:', 'embm_age_type_box', 'embm', 'embm_age_verify'); 
  */
}

add_action( 'admin_init', 'embm_register_settings' );

function embm_options_validate($input) {
	return $input;
}
function embm_section_text() {
	echo '';
}
function embm_untappd_box() {
	$options = get_option('embm_options');
	echo '<input name="embm_options[embm_untappd_check]" type="checkbox" id="embm_untappd_check" value="1"'.checked('1', $options['embm_untappd_check'], false).' /> ';
} 
function embm_untappd_brewery_box() {
	$options = get_option('embm_options');
	echo "<input id='embm_untappd_brewery' name='embm_options[embm_untappd_brewery]' size='5' type='text' value='{$options['embm_untappd_brewery']}' />";
    echo '&nbsp;<span id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle embm-help"><a href="#contextual-help-wrap" id="contextual-help-link" class="show-settings" aria-controls="contextual-help-wrap" aria-expanded="false"><small>'.__("What's this?", "embm").'</small></a></span>';
} 
function embm_css_url() {
	$options = get_option('embm_options');
	echo "<input id='embm_css_url' name='embm_options[embm_css_url]' size='50' type='url' value='{$options['embm_css_url']}' />";
} 
function embm_age_enable_box() {
	$options = get_option('embm_options');
	echo '<input name="embm_options[embm_age_enable]" type="checkbox" id="embm_age_enable" value="1"'.checked('1', $options['embm_age_enable'], false).' /> ';
}
function embm_age_limit_box() {
	$options = get_option('embm_options');
	echo "<input id='embm_age_limit' name='embm_options[embm_age_limit]' min='10' max='50' step='1' size='2' type='number' style='width:50px;' value='{$options['embm_age_limit']}' /> ";
	_e('Years', 'embm');
}
function embm_age_duration_box() {
	$options = get_option('embm_options');
	echo "<input id='embm_age_duration' name='embm_options[embm_age_duration]' min='10' step='1' size='4' style='width:65px;' type='number' value='{$options['embm_age_duration']}' /> ";
	_e('Minutes', 'embm');
}
function embm_age_type_box() {
	$custom = true;
	$options = get_option('embm_options');
	echo '<input id="embm_age_type" type="radio" name="embm_options[embm_age_type]" value="birthday"';
		if ( $options['embm_age_type'] === 'birthday' ) { 
			echo ' checked="checked"';
			$custom = false;
		}
	echo ' />&nbsp;&nbsp;';
	echo __('Birthday drop down selection', 'embm') . '&nbsp;&nbsp;<code>(' . __('MM/DD/YYYY', 'embm') . ')</code>';
	echo '<br /><input id="embm_age_type" type="radio" name="embm_options[embm_age_type]" value="yesno"';
		if ( $options['embm_age_type'] === 'yesno' ) { 
			echo ' checked="checked"';
			$custom = false;
		}
	echo ' />&nbsp;&nbsp;';
	echo __('"I Certify" checkpoint', 'embm') . '&nbsp;&nbsp;<code>(' . __('Yes/No', 'embm') . ')</code>';
}


function embm_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

?>
<div class="wrap" id="beer-settings">

    <div id="icon-edit" class="icon32 icon32-posts-beer"><br /></div><h2><?php _e("EM Beer Manager", "embm"); ?><span class="add-new-h2"><?php echo 'v'.get_option('embm_version'); ?></span></h2>
    
    <h2><?php _e("Settings", "embm"); ?></h2>
    
    <form method="post" action="options.php" class="emdm-form-settings"> 
    
    <?php settings_fields( 'embm_options' );
    	  do_settings_sections( 'embm' );?>
    	      
    <p style="margin-top:1em;"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
   
    </form>
    
    <br />
    
    <hr />
    
    <h2><?php _e("Usage", "embm"); ?></h2>

    <p><?php _e("Use these shortcodes to display beers in your posts or use the template tags in your theme files", "embm"); ?></p>


    <h3><?php _e("Single Beer Display", "embm"); ?></h3>
    
     <p><?php _e("These will display a single beer entry given it's ID number.", "embm"); ?></p>

     <p><code> [beer id={beer id}] </code></p>
     <p><code><?php echo htmlentities('<?php echo embm_beer_single( [beer id], [show_profile (optional)], [show_extras (optional)] ); ?>'); ?></code></p>

     <p style="margin-top:2em;"><?php _e("Optional attributes (for both shortcode and template code):", "embm"); ?></p>
     <table class="usage" cellpadding="0" cellspacing="0" border="0">
     <tr>
	     <td><code><strong>show_profile=</strong>{true/false}</code></td>
	     <td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
	     <td><em><?php _e('Displays or hides the "Beer Profile" information', 'embm'); ?></em></td>
	 </tr><tr>
		 <td><code><strong>show_extras=</strong>{true/false}</code></td> 
		 <td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
		 <td><em><?php _e('Displays or hides the "More Information" section', 'embm'); ?></em></td>
     </tr>
	</table>
	
	<h3 style="margin-top:2em;"><?php _e('List All Beers', 'embm'); ?></h3>

	  <p><?php _e('These will display a formatted listing of all beers in the database.', 'embm'); ?></p>

     <p><code>[beer-list]</code></p>
     <p><code><?php echo htmlentities('<?php echo embm_beer_list( [exclude (optional)], [show_profile (optional)], [show_extras (optional)], [style (optional)] ); ?>'); ?></code></p>
    
     <p style="margin-top:2em;"><?php _e('Optional attributes (for both shortcode and template code):', 'embm'); ?></p>
     <table class="usage" cellpadding="0" cellspacing="0" border="0">
     <tr>
	     <td><code><strong>exclude=</strong>{"beer ids"}</code></td>
	     <td>(<?php _e('String separated by commas', 'embm'); ?> e.g. <code>"4,23,24"</code>)</td>
	     <td><em><?php _e('Hides listed beers from output', 'embm'); ?></em></td>
     </tr><tr>
	     <td><code><strong>show_profile=</strong>{true/false}</code></td>
	     <td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
	     <td><em><?php _e('Displays or hides the "Beer Profile" information for each listing', 'embm'); ?></em></td>
     </tr><tr>
	     <td><code><strong>show_extras=</strong>{true/false}</code></td>
	     <td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
	     <td><em><?php _e('Displays or hides the "More Information" section for each listing', 'embm'); ?></em></td>
     </tr><tr>
	     <td><code><strong>style=</strong>{"style name"}</code></td>
	     <td>(<?php _e('String'); ?> e.g. <code>"India Pale Ale"</code>)</td>
	     <td><em><?php _e('Displays only beers belonging to a specific beer style', 'embm'); ?></em></td>
     </tr><tr>
	     <td><code><strong>beers_per_page=</strong>{number}</code></td>
	     <td>(<?php _e('Default', 'embm'); ?> = <code>-1</code>, <?php _e('shows all beers on one page', 'embm'); ?></td>
	     <td><em><?php _e('Paginates output and displays the given number of beers per page', 'embm'); ?></em></td>
     </tr>
     </table>
     <br />
     
     <hr />
     
     <p><?php _e('If you like this plugin, please consider donating to help support future development. Thank you!', 'embm'); ?></p>
     
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