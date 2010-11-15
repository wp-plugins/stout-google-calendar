<?php
/*
	Plugin Name: Stout Google Calendar
	Plugin URI: http://blog.stoutdesign.com/stout-google-calendar-custom-colors
	Description: Allows you to customize the colors of embedded Google calendars and update its options through the WordPress admin. Customized Google Calendars may be embedded to your WordPress site by adding a widget, shortcode to a post/page or template tag to your theme.
	Version: 1.0.3
	Author: Matt McKenny
	Author URI: http://www.stoutdesign.com
	License: GPL2
*/

/*  
	Copyright 2010  Matt McKenny  (email: sgc@stoutdesign.com)
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Create table for Google calendar data and colors
global $sgc_db_version; 
$sgc_db_version = "1.0"; 

function sgc_install(){
	global $wpdb;
	global $sgc_db_version; 
	
	$sgc_table = $wpdb->prefix . "stoutgc";
	
	// Check to see if table exists
	if($wpdb->get_var("SHOW TABLES LIKE '$sgc_table'") != $sgc_table) {
		
		//Create table v 0.1
			$sql = "CREATE TABLE " . $sgc_table . " (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  name tinytext NOT NULL,
			  googlecalcode text NOT NULL,
				color0 varchar(32) NOT NULL,
				color1 varchar(32) NOT NULL,
				color2 varchar(32) NOT NULL,
				color3 varchar(32) NOT NULL,
				color4 varchar(32) NOT NULL,
				color5 varchar(32) NOT NULL,
				color6 varchar(32) NOT NULL,
				bkgrdTransparent boolean NOT NULL,
				bkgrdImage mediumint(9) NOT NULL,
			  UNIQUE KEY id (id)
			);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("stoutgc_db_version", $sgc_db_version);
	}	
}

// Create the Table on activation
register_activation_hook(__FILE__,'sgc_install');

// add scripts and css to admin menu    
function my_plugin_admin_init() {
	/* Register our script. */
  wp_enqueue_script('colorpickerapp', WP_PLUGIN_URL . '/stout-google-calendar/colorpicker.js');
	wp_enqueue_script('colorpickereye', WP_PLUGIN_URL . '/stout-google-calendar/eye.js');
	wp_enqueue_script('colorpickerutils', WP_PLUGIN_URL . '/stout-google-calendar/utils.js');
	wp_enqueue_script('colorpickerlayout', WP_PLUGIN_URL . '/stout-google-calendar/layout.js');
	wp_enqueue_script('jquery-ui-dialog'); 
	wp_enqueue_script('jquery-plugin-validation', 'http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js');  
	wp_enqueue_script('stout_gc', WP_PLUGIN_URL . '/stout-google-calendar/stout_gc.js');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	wp_enqueue_style('stout_gc', WP_PLUGIN_URL . '/stout-google-calendar/stout_gc.css');
}
add_action('admin_init', 'my_plugin_admin_init');

// Include widget
require_once('stout-gc-widget.php');

/* Build Admin */
add_action('admin_menu','sgc_menu');

function sgc_menu(){
	add_options_page('Stout Google Calendar', 'Stout Google Calendar', 'manage_options', 'stout-gc', 'sgc_plugin_options' );
}

function sgc_plugin_options(){
	global $wpdb;
	$sgc_table = $wpdb->prefix . "stoutgc";
	
	//must check that the user has the required capability 
	if (!current_user_can('manage_options'))
	{
	  wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	// variables for the field and option names 
	$hidden_field_name = 'sgc_submit_hidden';	
	
	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if(isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
			$msg ='<div class="updated"><p><strong>';
			
			// we're updating a record
			if(isset($_POST['update_record']) && $_POST['update_record'] == 'Y'){
				 	global $wpdb;
					$wpdb->update( $sgc_table, array( 'name' => $_POST['name'], 'googlecalcode' => $_POST['googlecalcode'], 'bkgrdImage' => $_POST['bkgrdImage'], 'bkgrdTransparent' => $_POST['bkgrdTransparent'], 'color0' => $_POST['color0'], 'color1' => $_POST['color1'], 'color2' => $_POST['color2'], 'color3' => $_POST['color3'], 'color4' => $_POST['color4'], 'color5' => $_POST['color5'], 'color6' => $_POST['color6'] ), array('id' => $_POST['id']), array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ), array( '%d') );	
					// Put a settings updated message on the screen
					$msg .=  __( 'Settings saved for calendar: '.$_POST['name'].'.', 'sgc-settings-saved' );
			
			//we're creating a new record													
			} elseif(isset($_POST[ 'new_record' ]) && $_POST[ 'new_record' ] == 'Y' ) {
					global $wpdb;
					$wpdb->insert( $sgc_table, array( 'name' => $_POST['name'], 'googlecalcode' => $_POST['googlecalcode'], 'bkgrdImage' => $_POST['bkgrdImage'], 'bkgrdTransparent' => $_POST['bkgrdTransparent'], 'color0' => $_POST['color0'], 'color1' => $_POST['color1'], 'color2' => $_POST['color2'], 'color3' => $_POST['color3'], 'color4' => $_POST['color4'], 'color5' => $_POST['color5'], 'color6' => $_POST['color6'] ), array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ) );
					// Put a settings saved message on the screen
					$msg .=  __( 'Calendar successfully created: '.$_POST['name'].'.', 'sgc-settings-saved' );
		
			//we're deleting a calendar
			} elseif(isset($_POST[ 'delete_record' ]) && $_POST[ 'delete_record' ] == 'Y' ) {
						global $wpdb;
						$wpdb->query( "DELETE FROM $sgc_table WHERE `id` = $_POST[id] LIMIT 1" );
						// Put a settings saved message on the screen
						$msg .=  __( 'Calendar deleted: '.$_POST['name'].'.', 'sgc-settings-saved' );
			}
			$msg .= '</strong></p></div>';
	}

	// Now display the settings editing screen
	echo '<div class="wrap">';
	
	// header
	echo "<h2>" . __( 'Stout Google Calendar', 'sgc-plugin-name' ) . "</h2>";

	echo ($msg != '') ? $msg : '';
	
	// header for new calendar
	echo "<h2 class='sgc-subhead'>" . __( 'Add a New Calendar', 'sgc-new-calendar' ) . "</h2>";
?>
	<div id="calendar-0" class="sgc-form-wrapper" style="display:block">
		<form name="form1" method="post" action="" id="sgc-form0">
		<div class="sgc-name-code">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y" />
			<input type="hidden" name="new_record" value="Y" />
			<p><?php _e("Calendar Name:", 'sgc-calendar-name' ); ?><br /><input type="text" name="name" value="" class="required" size="50" /></p>
			<p><?php _e("Google Calendar iframe embed code:", 'sgc-google-embed-code' ); ?><br /><textarea name="googlecalcode" cols="44" rows="15" class="required sgccode" id="sgccode0"></textarea></p>
			<div id="sgc_preview_wrapper0">
			<div id="new-preview-msg"></div><a href="#" class="sgc_preview" id="new-preview">Preview Calendar</a>
			<?php $new_src = WP_PLUGIN_URL.'/stout-google-calendar/gcalendar-wrapper.php?src=en.usa%23holiday%40group.v.calendar.google.com&sgc0=FFFFFF&sgc1=c3d9ff&sgc2=000000&sgc3=e8eef7&sgc4=000000&sgc5=ffffcc&sgc6=000000&sgcImage=0&sgcBkgrdTrans=0&wpurl='.WP_PLUGIN_URL; ?>
				<div class="sgc_iframe_wrapper" style="display:none;width:800;height:600;">
					<iframe id="sgc_iframe_0" src="<?php echo $new_src; ?>" allowtransparency="true" style=" border:'0' " width="800" height="600" frameborder="0" scrolling="no"></iframe>
				</div>
			</div>
		</div>	
		<div class="sgc-pickers">
			<table class="sgc-color-picker" >
				<tr><th colspan="2" style="text-align:left">Calendar Colors:</th></tr>
			  <tr>
					<td>
						<?php _e("Main Background:", 'sgc-color0' ); ?><br/>
						<input type="hidden" name="bkgrdTransparent"  value="" /> 
						<input type="checkbox" name="bkgrdTransparent" id="bkgrdTransparent0" class="bkgrdTransparent" value="1" /> <label for="bkgrdTransparent0">Transparent?</label>
					</td> 
					<td><input type="text" class="colorpicker0"  id="color00" name="color0" value="FFFFFF" size="6" style="background-color:#FFFFFF" /></td>
				</tr>
				<tr>
					<td><?php _e("Main Background Text:", 'sgc-color6' ); ?></td> 
					<td><input type="text" class="colorpicker6"  name="color6" value="000000" size="6" style="background-color:#000000" /></td>
				</tr>
				<tr>
					<td><?php _e("Active Tab Bkgrd:", 'sgc-color1' ); ?></td> 
					<td><input type="text" class="colorpicker1"  name="color1" value="c3d9ff" size="6" style="background-color:#c3d9ff" /></td>
				</tr>                                                                                           
				<tr>
					<td><?php _e("Active Tab Text:", 'sgc-color2' ); ?></td>                                                      
					<td><input type="text" class="colorpicker2"  name="color2" value="000000" size="6" style="background-color:#000000" /></td>
				</tr>                                                                                           
				<tr>
					<td><?php _e("Inactive Tab Bkgrd:", 'sgc-color3' ); ?></td>                                                      
					<td><input type="text" class="colorpicker3"  name="color3" value="e8eef7" size="6" style="background-color:#e8eef7" /></td>
				</tr>                                                                                           
				<tr>
					<td><?php _e("Inactive Tab Text:", 'sgc-color4' ); ?></td>                                                  
					<td><input type="text" class="colorpicker4"  name="color4" value="000000" size="6" style="background-color:#000000" /></td>
				</tr> 
				<tr>                                                                                          
					<td><?php _e("Current Day Bkgrd:", 'sgc-color5' ); ?></td>
					<td><input type="text" class="colorpicker5"  name="color5" value="ffffcc" size="6" style="background-color:#ffffcc" /></td>
				</tr>
				<tr><th colspan="2">Calendar Size:</th></tr>
				<tr>                                                                                          
					<td><?php _e("Width:", 'sgc-width' ); ?></td>
					<td><input type="text" class="sgcWidthOrHeight"  id="width0" name="width" value="" size="6" /></td>
				</tr>
				<tr>                                                                                          
					<td><?php _e("Height:", 'sgc-height' ); ?></td>
					<td><input type="text" class="sgcWidthOrHeight"  id="height0" name="height" value="" size="6" /></td>
				</tr>
			</table>
	
			<table class="sgc-button-picker" id="button-image-bkgrd_0">
				<tr><th colspan="2" style="text-align:left">Button Style:</th></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-new0" value="0" title="Google Standard" checked="checked" /></td><td> <label for="bkgrdImage-new0"><img alt="Google Default" height="17" width="32" style="margin-bottom:-3px; background-image: url(https://calendar.google.com/googlecalendar/images/combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> Normal</label></td></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-new1" value="1" title="Solid Gray"/></td><td> <label for="bkgrdImage-new1"><img alt="Solid Gray" height="17" width="32" style="margin-bottom:-3px; background-image: url(<?php echo WP_PLUGIN_URL ?>/stout-google-calendar/images/sgc_gray_combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> Gray</label></td></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-new2" value="2" title="Black, 50% opacity"/></td><td> <label for="bkgrdImage-new2"><img alt="50% Opacity - Black" height="17" width="32" style="margin-bottom:-3px; background-image: url(<?php echo WP_PLUGIN_URL ?>/stout-google-calendar/images/sgc_50black_combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> 50% Black</label></td></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-new3" value="3" title="White, 50% opacity"/></td><td> <label for="bkgrdImage-new3"><img alt="50% Opacity - White" height="17" width="32" style="margin-bottom:-3px; background-image: url(<?php echo WP_PLUGIN_URL ?>/stout-google-calendar/images/sgc_50white_combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> 50% White</label></td></tr>
				<tr class="no-background"><th colspan="2" style="text-align:left">Calendar View:</th></tr>
				<tr class="no-background">
					<td colspan="2">
						<select name="mode" class="calMode">
							<option class="calMode" id="mode-month0" value="MONTH" >Month</option>
							<option class="calMode" id="mode-week0" value="WEEK" >Week</option>
							<option class="calMode" id="mode-agenda0" value="AGENDA" >Agenda</option>
						</select>
					</td>
				</tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showNav" id="showNav0" /></td><td><label for="showNav0">Show Nav?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showDate" id="showDate0" /></td><td><label for="showDate0">Show Date?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showPrint" id="showPrint0" /></td><td><label for="showPrint0">Show Print?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showTabs" id="showTabs0" /></td><td><label for="showTabs0">Show Tabs?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showCalendars" id="showCalendars0" /></td><td><label for="showCalendars0">Show Calendars?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showTz" id="showTz0" /></td><td><label for="showTz0">Show Timezone?</label></td></tr>
			</table>
		</div>
		<p class="submit-new"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Add Calendar') ?>" /></p>
		<br class="clear" />
		</form>
	</div>
<?php
			
	//Subhead for saved Calendars
	echo "<h2 class='sgc-subhead saved-calendars'>" . __( 'Saved Calendars', 'sgc-saved-calendars' ) . "</h2>";

	//Check for existing records
	$calendars = $wpdb->get_results("SELECT * FROM $sgc_table ORDER BY id ASC");

	foreach ($calendars as $calendar) {
?>
	<h3 class="sgc-name"><?php echo $calendar->name; ?> <br /><span style="font-size:smaller;font-weight:normal">Shortcode: <code>[stout_gc id=<?php echo $calendar->id; ?>]</code><br />Template Tag: <code>&lt;?php echo stout_gc(<?php echo $calendar->id; ?>); ?&gt;</code></span></h3> <?php echo stout_gc($calendar->id,FALSE,TRUE); ?>
	<div id="calendar-<?php echo $calendar->id; ?>" class="sgc-form-wrapper">
		<form name="form1" method="post" action="" id="sgc-form<?php echo $calendar->id; ?>">
		<div class="sgc-name-code">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y" />
			<input type="hidden" name="id" value="<?php echo $calendar->id; ?>" />
			<input type="hidden" name="update_record" value="Y" />
			<p><?php _e("Calendar Name:", 'sgc-calendar-name' ); ?><br /><input type="text" name="name" value="<?php echo $calendar->name; ?>" class="required" size="50" /></p>
			<p><?php _e("Google Calendar iframe embed code:", 'sgc-google-embed-code' ); ?><br /><textarea name="googlecalcode" cols="44" rows="15" class="required sgccode" id="sgccode<?php echo $calendar->id; ?>"><?php echo stripslashes($calendar->googlecalcode); ?></textarea></p>
		</div>	
		<div class="sgc-pickers">
			<table class="sgc-color-picker" >
				<tr><th colspan="2" style="text-align:left">Calendar Colors:</th></tr>
			  <tr>
					<td>
						<?php _e("Main Background:", 'sgc-color0' ); ?><br/>
						<input type="checkbox" name="bkgrdTransparent" id="bkgrdTransparent<?php echo $calendar->id; ?>" class="bkgrdTransparent" value="1" <?php echo ($calendar->bkgrdTransparent == 1) ? 'checked="checked"' : '' ?> /> <label for="bkgrdTransparent<?php echo $calendar->id; ?>">Transparent?</label>
					</td> 
					<td><input type="text" class="colorpicker0" name="color0" id="color0<?php echo $calendar->id; ?>" value="<?php echo $calendar->color0; ?>" size="6" style="background-color:#<?php echo $calendar->color0; ?>"/></td>
				</tr>
					<tr>
						<td><?php _e("Main Background Text:", 'sgc-color6' ); ?></td> 
						<td><input type="text" class="colorpicker6" name="color6" id="color6<?php echo $calendar->id; ?>" value="<?php echo $calendar->color6; ?>" size="6" style="background-color:#<?php echo $calendar->color6; ?>"/></td>
					</tr>
				<tr>
					<td><?php _e("Active Tab Bkgrd:", 'sgc-color1' ); ?></td> 
					<td><input type="text" class="colorpicker1" name="color1" id="color1<?php echo $calendar->id; ?>" value="<?php echo $calendar->color1; ?>" size="6" style="background-color:#<?php echo $calendar->color1; ?>"/></td>
				</tr>                                                                                           
				<tr>
					<td><?php _e("Active Tab Text:", 'sgc-color2' ); ?></td>                                                      
					<td><input type="text" class="colorpicker2" name="color2" id="color2<?php echo $calendar->id; ?>" value="<?php echo $calendar->color2; ?>" size="6" style="background-color:#<?php echo $calendar->color2; ?>"/></td>
				</tr>                                                                                           
				<tr>
					<td><?php _e("Inactive Tab Bkgrd:", 'sgc-color3' ); ?></td>                                                      
					<td><input type="text" class="colorpicker3" name="color3" id="color3<?php echo $calendar->id; ?>" value="<?php echo $calendar->color3; ?>" size="6" style="background-color:#<?php echo $calendar->color3; ?>"/></td>
				</tr>                                                                                           
				<tr>
					<td><?php _e("Inactive Tab Text:", 'sgc-color4' ); ?></td>                                                  
					<td><input type="text" class="colorpicker4" name="color4" id="color4<?php echo $calendar->id; ?>" value="<?php echo $calendar->color4; ?>" size="6" style="background-color:#<?php echo $calendar->color4; ?>"/></td>
				</tr> 
				<tr>                                                                                          
					<td><?php _e("Current Day Bkgrd:", 'sgc-color5' ); ?></td>
					<td><input type="text" class="colorpicker5" name="color5" id="color5<?php echo $calendar->id; ?>" value="<?php echo $calendar->color5; ?>" size="6" style="background-color:#<?php echo $calendar->color5; ?>"/></td>
				</tr>
				<tr><th colspan="2">Calendar Size:</th></tr>
				<tr>                                                                                          
					<td><?php _e("Width:", 'sgc-width' ); ?></td>
					<td><input type="text" class="sgcWidthOrHeight" id="width<?php echo $calendar->id; ?>" name="width" value="" size="6"/></td>
				</tr>
				<tr>                                                                                          
					<td><?php _e("Height:", 'sgc-height' ); ?></td>
					<td><input type="text" class="sgcWidthOrHeight" id="height<?php echo $calendar->id; ?>" name="height" value="" size="6"/></td>
				</tr>
			</table>

			<table class="sgc-button-picker" id="button-image-bkgrd_<?php echo $calendar->id; ?>" style="background:#<?php echo $calendar->color0; ?>">
				<tr><th colspan="2" style="text-align:left">Button Style:</th></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-<?php echo $calendar->id; ?>0" value="0" title="Google Standard" <?php echo ($calendar->bkgrdImage == 0) ? 'checked="checked"' : '' ?> /></td><td> <label for="bkgrdImage-<?php echo $calendar->id; ?>0"><img alt="Google Default" height="17" width="32" style="margin-bottom:-3px; background-image: url(https://calendar.google.com/googlecalendar/images/combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> Normal</label></td></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-<?php echo $calendar->id; ?>1" value="1" title="Solid Gray" <?php echo ($calendar->bkgrdImage == 1) ? 'checked="checked"' : '' ?> /></td><td> <label for="bkgrdImage-<?php echo $calendar->id; ?>1"><img alt="Solid Gray" height="17" width="32" style="margin-bottom:-3px; background-image: url(<?php echo WP_PLUGIN_URL ?>/stout-google-calendar/images/sgc_gray_combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> Gray</label></td></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-<?php echo $calendar->id; ?>2" value="2" title="Black, 50% opacity" <?php echo ($calendar->bkgrdImage == 2) ? 'checked="checked"' : '' ?> /></td><td> <label for="bkgrdImage-<?php echo $calendar->id; ?>2"><img alt="50% Opacity - Black" height="17" width="32" style="margin-bottom:-3px; background-image: url(<?php echo WP_PLUGIN_URL ?>/stout-google-calendar/images/sgc_50black_combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> 50% Black</label></td></tr>
				<tr><td><input type="radio" class="bkgrdImage" name="bkgrdImage" id="bkgrdImage-<?php echo $calendar->id; ?>3" value="3" title="White, 50% opacity" <?php echo ($calendar->bkgrdImage == 3) ? 'checked="checked"' : '' ?> /></td><td> <label for="bkgrdImage-<?php echo $calendar->id; ?>3"><img alt="50% Opacity - White" height="17" width="32" style="margin-bottom:-3px; background-image: url(<?php echo WP_PLUGIN_URL ?>/stout-google-calendar/images/sgc_50white_combined_v18.png); background-position: -241px 0" src="http://calendar.google.com/googlecalendar/images/blank.gif" /> 50% White</label></td></tr>
				<tr class="no-background"><th colspan="2" style="text-align:left">Calendar View:</th></tr>
				<tr class="no-background">
					<td colspan="2">
						<select name="mode" class="calMode">
							<option class="calMode" id="mode-month<?php echo $calendar->id; ?>" value="MONTH" >Month</option>
							<option class="calMode" id="mode-week<?php echo $calendar->id; ?>" value="WEEK" >Week</option>
							<option class="calMode" id="mode-agenda<?php echo $calendar->id; ?>" value="AGENDA" >Agenda</option>
						</select>
					</td>
				</tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showNav" id="showNav<?php echo $calendar->id; ?>" /></td><td><label for="showNav<?php echo $calendar->id; ?>">Show Nav?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showDate" id="showDate<?php echo $calendar->id; ?>" /></td><td><label for="showDate<?php echo $calendar->id; ?>">Show Date?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showPrint" id="showPrint<?php echo $calendar->id; ?>" /></td><td><label for="showPrint<?php echo $calendar->id; ?>">Show Print?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showTabs" id="showTabs<?php echo $calendar->id; ?>" /></td><td><label for="showTabs<?php echo $calendar->id; ?>">Show Tabs?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showCalendars" id="showCalendars<?php echo $calendar->id; ?>" /></td><td><label for="showCalendars<?php echo $calendar->id; ?>">Show Calendars?</label></td></tr>
				<tr class="no-background"><td><input type="checkbox" class="sgc-toggle-options" name="showTz" id="showTz<?php echo $calendar->id; ?>" /></td><td><label for="showTz<?php echo $calendar->id; ?>">Show Timezone?</label></td></tr>
			</table>
			</div>
			<p class="submit-update" ><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Update Calendar') ?>" /></p>
			</form>
			
			<form action="" method="post" class="sgcdelete" name="<?php echo $calendar->id; ?>">
				<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y" />
				<input type="hidden" name="id" value="<?php echo $calendar->id; ?>" />
				<input type="hidden" name="delete_record" value="Y" />
				<input type="hidden" name="name" value="<?php echo $calendar->name; ?>" />
				<p class="submit-delete" ><input type="submit" name="Submit" class="button-primary" style="background-image:none;background:red;border-color:red" value="<?php esc_attr_e('Delete Calendar') ?>" /></p>
			</form>
			<div class="delete-confirm" id="delete-confirm<?php echo $calendar->id; ?>" title="Delete Calendar: <?php echo $calendar->name; ?>?">
				<p>Are you sure you want to delete the calendar: <strong><?php echo $calendar->name; ?></strong> ?</p>
			</div>										
		<br style="clear:both" />
	</div>					
<?php
	//end loop for calendars
 	}
 	
	// Close wrap div for all content
	echo '</div>';
	    
}

// Shortcode for embedding calendar
function stout_gc_func($atts) {
	extract(shortcode_atts(array(
		'id' => '1',
		'show_name' => 'FALSE'
	), $atts));

	//return "id = {$id}";
	return stout_gc($id,$show_name);
}

add_shortcode('stout_gc', 'stout_gc_func');


//Display calendar	
function stout_gc($cal, $showName = 'FALSE'){
	global $wpdb;
	$sgc_table = $wpdb->prefix . "stoutgc";

	$errors = '';
	
	//Check to see if valid calendar specified
	if(!in_array($cal,range(0,10000))){
		$errors[] = ('Invalid calendar specified.');
	}else{
		$calendar = $wpdb->get_row("SELECT * FROM $sgc_table WHERE id = $cal");
		$calcode = stripslashes($calendar->googlecalcode);
		$calname = stripslashes($calendar->name); 
	}	

	//Get query string from google embed code
	$calquery = preg_match('/\?(\S+)/',$calcode,$matches);
	if($matches[0] != ''){
		$calquery = substr($matches[0],0,-1);
	}else {
		$errors[] = 'Google calendar embed code appears to be incorrect.';
	}

	// Get the width of iframe from google embed code
	$iframe_width = preg_match('/width="(\d+)"/',$calcode,$matches);
	if($matches[1] != ''){
		$iframe_width = $matches[1];
	}else{
		$errors[] = 'Cannot determine width of the calendar.';
	}

	// Get the width of iframe from google embed code
	$iframe_height = preg_match('/height="(\d+)"/',$calcode,$matches);
	if($matches[1] != ''){
		$iframe_height = $matches[1];
	}else{
		$errors[] = 'Cannot determine height of the calendar.';
	}

	// Get the width of iframe from google embed code
	$iframe_border = preg_match('/border:(\w+ \w+ #\w+)/',$calcode,$matches);
	if($matches[1] != ''){
		$iframe_border = $matches[1];
	}else{
		//no border
		$iframe_border  = '0';
	}
	
		
	if($errors != ''){
		$errors = '<div style="padding:10px;border:1px solid red;color:red">'.$errors[0];
		if( $_SERVER['QUERY_STRING'] == 'page=stout-gc') { $errors .= '<br /><a href="#" class="sgc-form-toggle">Show Calendar Editor</a>'; }
		$errors .= '</div>';
		return $errors;
	}else{
		//build src
		$src = WP_PLUGIN_URL.'/stout-google-calendar/gcalendar-wrapper.php'.$calquery.'&sgc0='.$calendar->color0.'&sgc1='.$calendar->color1.'&sgc2='.$calendar->color2.'&sgc3='.$calendar->color3.'&sgc4='.$calendar->color4.'&sgc5='.$calendar->color5.'&sgc6='.$calendar->color6.'&sgcImage='.$calendar->bkgrdImage.'&sgcBkgrdTrans='.$calendar->bkgrdTransparent.'&wpurl='.WP_PLUGIN_URL;
		
		if( $_SERVER['QUERY_STRING'] == 'page=stout-gc' ) {
			//in preview mode (admin)
			$preview = '
			<div id="sgc_preview_wrapper'.$cal.'">
				<a href="#" class="sgc-form-toggle">Show Calendar Editor</a> | <a href="#" class="sgc_preview">Preview Calendar</a>
				<div class="sgc_iframe_wrapper" style="display:none;width:'.$iframe_width.';height:'.$iframe_height.';">
					<iframe id="sgc_iframe_'.$cal.'" src="'.$src.'" allowtransparency="true" style=" border:'.$iframe_border.' " width="'.$iframe_width.'" height="'.$iframe_height.'" frameborder="0" scrolling="no"></iframe>
				</div>
			</div>';
			return $preview;			
		//return iframe for shortcode
		}else{
			$calendar_output = '';
			if(strtoupper($showName) == 'TRUE') { $calendar_output .= '<span class="sgc-name sgc-'.$cal.'">'.$calname.'</span><br />';}
			$calendar_output .= '<iframe src="'.$src.'" allowtransparency="true" style=" border:'.$iframe_border.' " width="'.$iframe_width.'" height="'.$iframe_height.'" frameborder="0" scrolling="no"></iframe>';
			return $calendar_output;
		}
	}
}

?>
