<?php
/**
 * Single page
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<div class="kp-single-wrap">
<article class="kp-single kp-single--page">
	<?php while ( have_posts() ) : the_post(); ?>
		<header class="kp-single__head">
			<p class="kp-pagehero__kicker">★ PAGE ★</p>
			<h1 class="kp-single__title"><?php the_title(); ?></h1>
		</header>
		<div class="kp-single__content">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<nav class="kp-pagelinks">', 'after' => '</nav>' ) ); ?>
		</div>
	<?php endwhile; ?>
</article>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
