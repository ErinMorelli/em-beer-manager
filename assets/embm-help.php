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
* EM Beer Manager Untappd Help Document
*
*/
?>

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
<p>Checking the "Disable integration" option under the "EM Beer Manager" settings, will completely disable all Untappd functionality, including per-beer check-in buttons and the Recent Check-Ins widget. You can disable the Untappd check-in button for an individual beer by simply leaving the setting empty. Beers that have an active check-in button will display a square Untappd icon <img width="16"src="<?php EMBM_PLUGIN_URL; ?>img/ut-icon.png" /> next to their entry on the Beers admin page.</p>

<h3>Brewery ID</h3>
<p>Find your Untappd brewery ID number by going to your brewery's official page (i.e. <code>https://untappd.com/BreweryName</code>). Click on the orange RSS feed button. The URL will be formatted like this:</p>
<p><code>https://untappd.com/rss/brewery/<strong>64324</strong></code></p>
<p>The string of numbers at the end of the URL is your brewery ID number, which you can enter below to utilize special Untappd integration features.</p>
			

</body>
</html>

<?php


?>