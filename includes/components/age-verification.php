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
* EM Beer Manager age verification functions
*
*/

$getOptions = get_option('embm_options');

if($getOptions['embm_age_enable'] === '1') {
	// run component functions
}

function embm_show_verify() {
	$options = get_option('embm_options');
	$minAge = $options['embm_age_limit'];
	
	$output = '';
	$output .= '<div id="embm-age-check" class="embm-age-check-'.$options['embm_age_type'].'">'."\n";
	
	if($options['embm_age_type'] === 'birthday') {
		// Age dropdown form
		$output .= '<input type="date" name="embm-bday" id="embm-bday" required>'; // convert to dropdowns :(
	}
		
	if($options['embm_age_type'] === 'yesno') {
		// Yes/No check
		$output .= '<input type="radio" name="embm-cert" id="embm-cert" value="true" /> ';
		$output .= sprintf(__('I am age %s or older', 'embm'), $minAge)."\n";
		$output .= '<input type="radio" name="embm-cert" id="embm-cert" value="false" /> '.__('I am not', 'embm')."\n";
	}
	
	$output .= '</div>';

	return $output;
}

function embm_verify_age() {
	// Determine if age input is valid
}
function embm_verify_cert() {
	// Determine if yes certification has been checked	
}

function embm_hide_content() {
	// Output message if verification not met
}



?>