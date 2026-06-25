<?php
/**
 * Archive: arc (シーズン / アーク一覧)
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ SEASONS ★</p>
	<h1 class="kp-pagehero__title">シーズン一覧</h1>
	<p class="kp-pagehero__desc">「今日好き」の歴代シーズンを一覧で ♡</p>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php if ( have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; while ( have_posts() ) : the_post(); $i++; ?>
				<article class="kp-card">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<?php if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'medium' );
						} else {
							printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) );
						} ?>
					</a>
					<div class="kp-card__body">
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="kp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
						<p class="kp-card__meta"><span><?php echo esc_html( get_the_date( 'Y.n.j' ) ); ?></span></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<nav class="kp-pagination">
			<?php echo paginate_links( array( 'prev_text' => '‹ PREV', 'next_text' => 'NEXT ›' ) ); ?>
		</nav>

	<?php else : ?>
		<p class="kp-empty">★ まだアーク投稿がありません ★</p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
