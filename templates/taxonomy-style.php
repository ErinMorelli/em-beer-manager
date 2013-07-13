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
* EM Beer Manager style taxonomy page template
*
*/

get_header(); ?>

	<div id="content" class="embm-content-style site-content" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php echo embm_display_beer($post->ID, 'false', 'false'); ?>

		<?php endwhile; // end of the loop. ?>

	</div><!-- #content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>