/*
Copyright (c) 2013-2016, Erin Morelli.

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
* EM Beer Manager admin script
*
*/

jQuery(document).ready(function($){
	// Check for a hash in the URL
	var hash = location.hash.slice(1);
	if ( hash !== '' ) {
		// Add/remove active classes
		$('#embm-settings-tabs').find('.nav-tab').removeClass('nav-tab-active');
		$('#embm-settings-tabs').find('.nav-tab-'+hash).addClass('nav-tab-active');
	}

	// Setup jquery ui tabs
	$('#embm-settings-tabs').tabs({
		activate: function(event, ui) {
			ui.newTab.find('.nav-tab').toggleClass('nav-tab-active');
			ui.oldTab.find('.nav-tab').toggleClass('nav-tab-active');
		}
	});
});