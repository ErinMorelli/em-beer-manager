<?php
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
* EM Beer Manager Untappd Help Document
*
*/
?>

<html>
	<head>
		<title>EM Beer Manager | Help</title>
		<style type="text/css">
			p {
				font-size: 16px;
				line-height: 20px;
			}
		</style>
	</head>

	<body>
		<a name="untappd"></a>
		<h2>Untappd Integration</h2>
		<p>Checking the "Disable Untappd integration" option under the "EM Beer Manager" settings, will completely disable all Untappd functionality, including per-beer check-in buttons and the Recent Check-Ins widget. You can disable the Untappd check-in button for an individual beer by simply leaving the setting empty. Beers that have an active check-in button will display a square Untappd icon <img width="16"src="<?php EMBM_PLUGIN_URL; ?>img/ut-icon.png" /> next to their entry on the Beers admin page.</p>

		<h3>Brewery ID</h3>
		<p>Find your Untappd brewery ID number by going to your brewery's official page (i.e. <code>https://untappd.com/BreweryName</code>). Click on the orange RSS feed button. The URL will be formatted like this:</p>
		<p><code>https://untappd.com/rss/brewery/<strong>64324</strong></code></p>
		<p>The string of numbers at the end of the URL is your brewery ID number, which you can enter below to utilize special Untappd integration features.</p>

		<a name="settings"></a>
		<h2>Settings</h2>
		<p><strong>How do I display an image of my beer next to its name and description?</strong></p>
		<p>When creating your new beer entry, set the "featured image" option in the sidebar to the beer image you wish to use, it will display alongside the entry when the beer is displayed on your site. If this option is not available in your post settings, your theme may be blocking post thumbnails.</p>

		<p><strong>I don't want to show that big grey box of information, how do I get rid of it?</strong></p>
		<p>For each of the different displays there is the option to "Hide extras info" and "Hide extras info". Check both of these to hide the grey box.</p>

		<p><strong>What's the difference between "profile" and "extras"?</strong></p>
		<p>The "profile" refers to all the content in the "Beer Profile" information stored for each beer. This includes ABV, IBU, Hops, Malts, Additions, and Yeast. The "extras" setting refers to the "Additional Notes" and "Availability" information stored for each beer.</p>
	</body>
</html>

<?php

?>