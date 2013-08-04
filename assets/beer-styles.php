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
* EM Beer Manager beer styles list
*
*/

$xmlstr = <<<XML
<catalog>
	<item>
		<name>Ale</name>
		<slug>ale</slug>
	</item>
	<item>
		<name>Altbier</name>
		<slug>altbier</slug>
	</item>
	<item>
		<name>Amber Ale</name>
		<slug>amber-ale</slug>
	</item>
	<item>
		<name>American Black Ale</name>
		<slug>american-black-ale</slug>
	</item>
	<item>
		<name>American Barleywine</name>
		<slug>american-barleywine</slug>
	</item>
	<item>
		<name>English Barleywine</name>
		<slug>english-barleywine</slug>
	</item>
	<item>
		<name>Wheatwine</name>
		<slug>wheatwine</slug>
	</item>
	<item>
		<name>Abbey Dubbel</name>
		<slug>abbey-dubbel</slug>
	</item>
	<item>
		<name>Abbey Quadrupel</name>
		<slug>abbey-quadrupel</slug>
	</item>
	<item>
		<name>Abbey Tripel</name>
		<slug>abbey-tripel</slug>
	</item>
	<item>
		<name>Belgian Ale</name>
		<slug>belgian-ale</slug>
	</item>
	<item>
		<name>Belgian Strong Ale</name>
		<slug>belgian-strong-ale</slug>
	</item>
	<item>
		<name>Bitter</name>
		<slug>bitter</slug>
	</item>
	<item>
		<name>Extra Special Bitter</name>
		<slug>extra-special-bitter</slug>
	</item>
	<item>
		<name>Blonde Ale</name>
		<slug>blonde-ale</slug>
	</item>
	<item>
		<name>Brown Ale</name>
		<slug>brown-ale</slug>
	</item>
	<item>
		<name>Cream Ale</name>
		<slug>cream-ale</slug>
	</item>
	<item>
		<name>English Strong Ale</name>
		<slug>english-strong-ale</slug>
	</item>
	<item>
		<name>Fruit Beer</name>
		<slug>fruit-beer</slug>
	</item>
	<item>
		<name>Imperial IPA</name>
		<slug>imperial-ipa</slug>
	</item>
	<item>
		<name>IPA</name>
		<slug>ipa</slug>
	</item>
	<item>
		<name>Irish Ale</name>
		<slug>irish-ale</slug>
	</item>
	<item>
		<name>Kölsch</name>
		<slug>kolsch</slug>
	</item>
	<item>
		<name>Mild Ale</name>
		<slug>mile-ale</slug>
	</item>
	<item>
		<name>Old Ale</name>
		<slug>old-ale</slug>
	</item>
	<item>
		<name>American Pale Ale</name>
		<slug>american-pale-ale</slug>
	</item>
	<item>
		<name>English Pale Ale</name>
		<slug>english-pale-ale</slug>
	</item>
	<item>
		<name>Baltic Porter</name>
		<slug>baltic-porter</slug>
	</item>
	<item>
		<name>Imperial Porter</name>
		<slug>imperial-porter</slug>
	</item>
	<item>
		<name>Porter</name>
		<slug>porter</slug>
	</item>
	<item>
		<name>Rye Beer</name>
		<slug>rye-beer</slug>
	</item>
	<item>
		<name>Saison</name>
		<slug>saison</slug>
	</item>
	<item>
		<name>Scottish Ale</name>
		<slug>scottish-ale</slug>
	</item>
	<item>
		<name>Smoked Ale</name>
		<slug>smoked-ale</slug>
	</item>
	<item>
		<name>Spiced Beer</name>
		<slug>spiced-beer</slug>
	</item>
	<item>
		<name>Dry Stout</name>
		<slug>dry-stout</slug>
	</item>
	<item>
		<name>Chocolate Stout</name>
		<slug>chocolate-stout</slug>
	</item>
	<item>
		<name>Coffee Stout</name>
		<slug>coffee-stout</slug>
	</item>
	<item>
		<name>Fruit Stout</name>
		<slug>fruit-stout</slug>
	</item>
	<item>
		<name>Foreign / Extra Stout</name>
		<slug>foreign-extra-stout</slug>
	</item>
	<item>
		<name>Imperial Stout</name>
		<slug>imperial-stout</slug>
	</item>
	<item>
		<name>Oyster Stout</name>
		<slug>oyster-stout</slug>
	</item>
	<item>
		<name>Stout</name>
		<slug>stout</slug>
	</item>
	<item>
		<name>Milk Stout</name>
		<slug>milk-stout</slug>
	</item>
	<item>
		<name>Oatmeal Stout</name>
		<slug>oatmeal-stout</slug>
	</item>
	<item>
		<name>Strong Ale</name>
		<slug>strong-ale</slug>
	</item>
	<item>
		<name>American Wheat</name>
		<slug>american-wheat</slug>
	</item>
	<item>
		<name>Berliner Weisse</name>
		<slug>berliner-weisse</slug>
	</item>
	<item>
		<name>Dunkel Weizen</name>
		<slug>dunkel-weizen</slug>
	</item>
	<item>
		<name>Hefeweizen</name>
		<slug>hefeweizen</slug>
	</item>
	<item>
		<name>Krystal Weizen</name>
		<slug>krystal-weizen</slug>
	</item>
	<item>
		<name>Weizenbock</name>
		<slug>weizenbock</slug>
	</item>
	<item>
		<name>Flanders Oude Bruin</name>
		<slug>flanders-oude-bruin</slug>
	</item>
	<item>
		<name>Flanders Red</name>
		<slug>flanders-red</slug>
	</item>
	<item>
		<name>Faro</name>
		<slug>faro</slug>
	</item>
	<item>
		<name>Fruit Lambic</name>
		<slug>fruit-lambic</slug>
	</item>
	<item>
		<name>Gueuze</name>
		<slug>gueuze</slug>
	</item>
	<item>
		<name>Unblended Lambic</name>
		<slug>unblended-lambic</slug>
	</item>
	<item>
		<name>Wild Ale</name>
		<slug>wild-ale</slug>
	</item>
	<item>
		<name>Witbier</name>
		<slug>witbier</slug>
	</item>
	<item>
		<name>Cider</name>
		<slug>cider</slug>
	</item>
	<item>
		<name>Biere de Garde</name>
		<slug>biere-de-garde</slug>
	</item>
	<item>
		<name>Bock</name>
		<slug>bock</slug>
	</item>
	<item>
		<name>Doppelbock</name>
		<slug>doppelbock</slug>
	</item>
	<item>
		<name>Eisbock</name>
		<slug>eisbock</slug>
	</item>
	<item>
		<name>California Common / Steam</name>
		<slug>california-common-steam</slug>
	</item>
	<item>
		<name>Dunkel / Dark Lager</name>
		<slug>dunkel-dark-lager</slug>
	</item>
	<item>
		<name>Schwarzbier</name>
		<slug>schwarzbier</slug>
	</item>
	<item>
		<name>Helles / Dortmunder</name>
		<slug>helles-dortmunder</slug>
	</item>
	<item>
		<name>Kellerbier</name>
		<slug>kellerbier</slug>
	</item>
	<item>
		<name>Lager</name>
		<slug>lager</slug>
	</item>
	<item>
		<name>Marzen / Oktoberfest</name>
		<slug>marzen-oktoberfest</slug>
	</item>
	<item>
		<name>Light / Lite Lager</name>
		<slug>light-lite-lager</slug>
	</item>
	<item>
		<name>Pale Lager</name>
		<slug>pale-lager</slug>
	</item>
	<item>
		<name>Strong Lager</name>
		<slug>strong-lager</slug>
	</item>
	<item>
		<name>Bohemian / Czech Pilsener</name>
		<slug>bohemian-czech-pilsener</slug>
	</item>
	<item>
		<name>Imperial Pilsener</name>
		<slug>imperial-pilsener</slug>
	</item>
	<item>
		<name>Rauchbier</name>
		<slug>rauchbier</slug>
	</item>
	<item>
		<name>Reduced Alcohol</name>
		<slug>reduced-alcohol</slug>
	</item>
	<item>
		<name>Vienna / Amber Lager</name>
		<slug>vienna-amber-lager</slug>
	</item>
	<item>
		<name>Mead</name>
		<slug>mead</slug>
	</item>
</catalog>
XML;

/* XML Beer List generated from BeerPal (http://www.beerpal.com/brain/styles.htm) */

?>