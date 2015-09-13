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
		'cb'				=> '<input type="checkbox" />',
		'id'				=> __( 'ID', 'embm' ),
		'beer_num'			=> __( 'Beer No.', 'embm' ),
		'title'				=> __( 'Beer', 'embm' ),
		'taxonomy-embm_group'		=> __( 'Group', 'embm' ),
		'taxonomy-embm_style'		=> __( 'Style', 'embm' ),
		'abv'				=> __( 'ABV', 'embm' ),
		'ibu'				=> __( 'IBU', 'embm' ),
		'avail'				=> __( 'Availability', 'embm' )
	);

	$ut_option = get_option('embm_options');

	if ( isset($ut_option['embm_untappd_check']) ) {
		$use_untappd = $ut_option['embm_untappd_check'];
	} else {
		$use_untappd = null;
	}

	if ( $use_untappd != '1' ) {
		$cols['untappd'] = __( 'Untappd', 'embm' );
	}

	$cols['date'] = __( 'Released', 'embm' );

	return $cols;
}

add_filter( 'manage_embm_beer_posts_columns', 'embm_change_columns' );

function embm_custom_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'id':
			echo $post_id;
			break;
		case 'beer_num':
			echo embm_get_beer($post_id, 'beer_num');
			break;
		case 'abv':
			echo embm_get_beer($post_id, 'abv');
			break;
		case 'ibu':
			echo embm_get_beer($post_id, 'ibu');
			break;
		case 'avail':
			echo embm_get_beer($post_id, 'avail');
			break;
		case 'untappd':
			$untap = embm_get_beer($post_id, 'untappd');
			if ( $untap != '' ) {
				$uticon = EMBM_PLUGIN_URL.'assets/img/ut-icon.png';
				echo '<a href="'.$untap.'" target="_blank"><img src="'.$uticon.'" border="0" alt="Untappd" /></a>';
			} else {
				echo '';
			}
			break;
	}
}

add_action( 'manage_embm_beer_posts_custom_column', 'embm_custom_columns', 10, 2 );


// Make these columns sortable

function embm_sortable_columns() {
	return array(
		'title'		=> 'title',
		'abv'		=> 'abv',
		'ibu'		=> 'ibu',
		'avail'		=> 'avail',
		'date'		=> 'date',
		'beer_num'	=> 'beer_num',
	);
}

add_filter( 'manage_edit-embm_beer_sortable_columns', 'embm_sortable_columns' );


/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'embm_edit_beer_load' );

function embm_edit_beer_load() {
	add_filter( 'request', 'embm_sort_beers' );
}

/* Sorts the beers. */
function embm_sort_beers( $vars ) {

	/* Check if we're viewing the 'beer' post type. */
	if ( isset( $vars['post_type'] ) && 'embm_beer' == $vars['post_type'] ) {
		/* Check if 'orderby' is set to 'beer_num'. */
		if ( isset( $vars['orderby'] ) && 'beer_num' == $vars['orderby'] ) {
			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'beer_num',
					'orderby' => 'meta_value_num'
				)
			);
		}
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

  // Global EMBM settings
  add_settings_section('embm_custom_url', 'Global Settings', 'embm_section_text', 'embm');
  add_settings_field('embm_untappd_check', 'Disable Untappd integration:', 'embm_untappd_box', 'embm', 'embm_custom_url');
  add_settings_field('embm_comment_check', 'Enable commenting on beers:', 'embm_comment_box', 'embm', 'embm_custom_url');
  add_settings_field('embm_css_url', 'Enter URL for custom stylesheet:', 'embm_css_box', 'embm', 'embm_custom_url');
  add_settings_field('embm_profile_show', 'Globally hide "profile" info:', 'embm_profile_box', 'embm', 'embm_custom_url');
  add_settings_field('embm_extras_show', 'Globally hide "extras" info:', 'embm_extras_box', 'embm', 'embm_custom_url');

  // Group Tax Settings
  add_settings_section('embm_group_settings', 'Groups Display', 'embm_section_text', 'embm');
  add_settings_field('embm_group_slug', 'Custom Group taxonomy slug:', 'embm_group_box', 'embm', 'embm_group_settings');
  add_settings_field('embm_profile_show_group', 'Hide "profile" info:', 'embm_profile_group_box', 'embm', 'embm_group_settings');
  add_settings_field('embm_extras_show_group', 'Hide "extras" info:', 'embm_extras_group_box', 'embm', 'embm_group_settings');

  // Style Tax Settings
  add_settings_section('embm_style_settings', 'Styles Display', 'embm_section_text', 'embm');
  add_settings_field('embm_profile_show_style', 'Hide "profile" info:', 'embm_profile_style_box', 'embm', 'embm_style_settings');
  add_settings_field('embm_extras_show_style', 'Hide "extras" info:', 'embm_extras_style_box', 'embm', 'embm_style_settings');

  // Single Beer Settings
  add_settings_section('embm_single_settings', 'Single Beer Display', 'embm_section_text', 'embm');
  add_settings_field('embm_profile_show_single', 'Hide "profile" info:', 'embm_profile_single_box', 'embm', 'embm_single_settings');
  add_settings_field('embm_extras_show_single', 'Hide "extras" info:', 'embm_extras_single_box', 'embm', 'embm_single_settings');

}

add_action( 'admin_init', 'embm_register_settings' );

function embm_options_validate($input) {
	return $input;
}

function embm_section_text() {}

function embm_untappd_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_untappd_check']) ) {
		$use_untappd = $options['embm_untappd_check'];
	} else {
		$use_untappd = null;
	}
	echo '<input name="embm_options[embm_untappd_check]" type="checkbox" id="embm_untappd_check" value="1"'.checked('1', $use_untappd, false).' /> ';
}

function embm_comment_box() {
	$options = get_option('embm_options');
	if (isset($options['embm_comment_check'])) {
		$use_comments = $options['embm_comment_check'];
	} else {
		$use_comments = null;
	}
	echo '<input name="embm_options[embm_comment_check]" type="checkbox" id="embm_comment_check" value="1"'.checked('1', $use_comments, false).' /> ';
}

function embm_css_box() {
	$options = get_option('embm_options');
	echo '<input id="embm_css_url" name="embm_options[embm_css_url]" size="50" type="url" value="'.esc_url($options['embm_css_url']).'" />';
}

function embm_group_box() {
	$options = get_option('embm_options');
	echo '<input id="embm_group_slug" name="embm_options[embm_group_slug]" size="15" type="text" value="'.sanitize_key($options['embm_group_slug']).'" />'."\n";
	echo '<br /><small>'.__('NOTE: You will need to refresh your permalinks ','embm').'<a href="options-permalink.php">'.__('here', 'embm').'</a>'.__(' after updating this setting', 'embm').'</small>';
}

function embm_profile_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_profile_show']) ) {
		$view_profile = $options['embm_profile_show'];
	} else {
		$view_profile = null;
	}
	echo '<input name="embm_options[embm_profile_show]" type="checkbox" id="embm_profile_show" value="1"'.checked('1', $view_profile, false).' /><span class="whats-this"><a href="javascript:untappdHelp()" id="embm-help-link" onclick="createPopup();"><small>'.__("What's this?", "embm").'</small></a></span>';
}

function embm_extras_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_extras_show']) ) {
		$view_extras = $options['embm_extras_show'];
	} else {
		$view_extras = null;
	}
	echo '<input name="embm_options[embm_extras_show]" type="checkbox" id="embm_extras_show" value="1"'.checked('1', $view_extras, false).' /><span class="whats-this"><a href="javascript:untappdHelp()" id="embm-help-link" onclick="createPopup();"><small>'.__("What's this?", "embm").'</small></a></span>';
}

function embm_profile_group_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_profile_show_group']) ) {
		$view_profile = $options['embm_profile_show_group'];
	} else {
		$view_profile = null;
	}
	echo '<input name="embm_options[embm_profile_show_group]" type="checkbox" id="embm_profile_show_group" value="1"'.checked('1', $view_profile, false).' /> ';
}

function embm_extras_group_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_extras_show_group']) ) {
		$view_extras = $options['embm_extras_show_group'];
	} else {
		$view_extras = null;
	}
	echo '<input name="embm_options[embm_extras_show_group]" type="checkbox" id="embm_extras_show_group" value="1"'.checked('1', $view_extras, false).' /> ';
}

function embm_profile_style_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_profile_show_style']) ) {
		$view_profile = $options['embm_profile_show_style'];
	} else {
		$view_profile = null;
	}
	echo '<input name="embm_options[embm_profile_show_style]" type="checkbox" id="embm_profile_show_style" value="1"'.checked('1', $view_profile, false).' /> ';
}

function embm_extras_style_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_extras_show_style']) ) {
		$view_extras = $options['embm_extras_show_style'];
	} else {
		$view_extras = null;
	}
	echo '<input name="embm_options[embm_extras_show_style]" type="checkbox" id="embm_extras_show_style" value="1"'.checked('1', $view_extras, false).' /> ';
}

function embm_profile_single_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_profile_show_single']) ) {
		$view_profile = $options['embm_profile_show_single'];
	} else {
		$view_profile = null;
	}
	echo '<input name="embm_options[embm_profile_show_single]" type="checkbox" id="embm_profile_show_single" value="1"'.checked('1', $view_profile, false).' /> ';
}

function embm_extras_single_box() {
	$options = get_option('embm_options');
	if ( isset($options['embm_extras_show_single']) ) {
		$view_extras = $options['embm_extras_show_single'];
	} else {
		$view_extras = null;
	}
	echo '<input name="embm_options[embm_extras_show_single]" type="checkbox" id="embm_extras_show_single" value="1"'.checked('1', $view_extras, false).' /> ';
}

function embm_settings() {
	if (!current_user_can('manage_options')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}

?>
<div class="wrap" id="beer-settings">

	<div id="icon-edit" class="icon32 icon32-posts-embm_beer"><br /></div><h2><?php _e("EM Beer Manager", "embm"); ?><span class="add-new-h2"><?php echo 'v'.get_option('embm_version'); ?></span></h2>

	<script type="text/javascript">
		function untappdHelp() {
			window.open("<?php echo EMBM_PLUGIN_URL; ?>assets/embm-help.php#settings","Finding Your Untappd Brewery ID","menubar=no,width=460,height=550,toolbar=no");
		}
	</script>

	<form method="post" action="options.php" class="emdm-form-settings">

		<?php settings_fields( 'embm_options' );
			do_settings_sections( 'embm' );?>

		<p style="margin-top:1em;"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>

	</form>

	<br /><hr />

	<h2><?php _e("Usage", "embm"); ?></h2>

	<p><?php _e("Use these shortcodes to display beers in your posts or use the template tags in your theme files", "embm"); ?></p>


	<h3><?php _e("Single Beer Display", "embm"); ?></h3>

	<p><?php _e("These will display a single beer entry given it's ID number.", "embm"); ?></p>

	<p><code> [beer id="beer id"] </code></p>
	<p><code><?php echo htmlentities('<?php echo embm_beer_single( beer id (required), show_profile, show_extras ); ?>'); ?></code></p>

	<p style="margin-top:2em;"><?php _e("Optional attributes (for both shortcode and template code):", "embm"); ?></p>
	<table class="usage" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><code><strong>show_profile=</strong>"true/false"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
			<td><em><?php _e('Displays or hides the "Beer Profile" information', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>show_extras=</strong>"true/false"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
			<td><em><?php _e('Displays or hides the "More Information" section', 'embm'); ?></em></td>
		</tr>
	</table>

	<h3 style="margin-top:2em;"><?php _e('List All Beers', 'embm'); ?></h3>

	<p><?php _e('These will display a formatted listing of all beers in the database.', 'embm'); ?></p>

	<p><code>[beer-list]</code></p>
	<p><code><?php echo htmlentities('<?php echo embm_beer_list( exclude, show_profile, show_extras, style, group, beers_per_page, paginate, orderby, order ); ?>'); ?></code></p>

	<p style="margin-top:2em;"><?php _e('Optional attributes (for both shortcode and template code):', 'embm'); ?></p>

	<table class="usage" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><code><strong>exclude=</strong>"beer ids"</code></td>
			<td>(<?php _e('String separated by commas', 'embm'); ?> e.g. <code>"4,23,24"</code>)</td>
			<td><em><?php _e('Hides listed beers from output', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>show_profile=</strong>"true/false"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
			<td><em><?php _e('Displays or hides the "Beer Profile" information for each listing', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>show_extras=</strong>"true/false"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
			<td><em><?php _e('Displays or hides the "More Information" section for each listing', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>style=</strong>"style name"</code></td>
			<td>(<?php _e('String'); ?> e.g. <code>"India Pale Ale"</code>)</td>
			<td><em><?php _e('Displays only beers belonging to a specific beer style', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>group=</strong>"group name"</code></td>
			<td>(<?php _e('String'); ?> e.g. <code>"Seasonals"</code>)</td>
			<td><em><?php _e('Displays only beers belonging to a specific group', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>beers_per_page=</strong>"number"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>-1</code>, <?php _e('shows all beers on one page', 'embm'); ?>)</td>
			<td><em><?php _e('Paginates output and displays the given number of beers per page', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>paginate=</strong>"true/false"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>true</code>)</td>
			<td><em><?php _e('Disables/enables pagination', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>orderby=</strong>"string"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>date</code>, <?php echo sprintf('See <a href="%s" target="_blank">this list</a> for options', 'http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters'); ?>)</td>
			<td><em><?php _e('Orders output by given paramater', 'embm'); ?></em></td>
		</tr>
		<tr>
			<td><code><strong>order=</strong>"desc/asc"</code></td>
			<td>(<?php _e('Default', 'embm'); ?> = <code>desc</code>)</td>
			<td><em><?php _e('List beer by <code>orderby</code> value in ascending or descending order', 'embm'); ?></em></td>
		</tr>
	</table>

	<br /><hr />

	<p><?php _e('If you like this plugin, please consider donating to help support future development. Thank you!', 'embm'); ?></p>

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
		">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>

	<p><?php _e('Free beer icon from <a href="http://simpleicon.com" title="simple icon">simple icon</a>.', 'embm'); ?></p>

</div>
<?php
}

?>