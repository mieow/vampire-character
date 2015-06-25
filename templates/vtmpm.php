<?php
/*
Template Name: Private Message
*/

get_header(); ?>

		<div id="primary">

			<div id="content" role="main">
	
			<?php
			if (have_posts()) :
				while (have_posts()) : 
					the_post(); ?>
					
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
					<?php vtm_pm_render_pmmsg(); ?>
					
					</article><!-- post -->

					<?php
				endwhile;
			endif;
			?>
	
			</div> <!-- content -->
		</div> <!-- primary-->
	
<?php 
get_footer();

?>