<?php
/**
 * Search results
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-pagehero">
	<p class="kp-pagehero__kicker">★ SEARCH ★</p>
	<h1 class="kp-pagehero__title">「<?php echo esc_html( get_search_query() ); ?>」 の検索結果</h1>
	<form class="kp-pagesearch" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="🔍 推しCPやアークで検索…" />
		<button type="submit">GO ♡</button>
	</form>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php if ( have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; while ( have_posts() ) : the_post(); $i++; ?>
				<article class="kp-card">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) ); ?>
					</a>
					<div class="kp-card__body">
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="kp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<nav class="kp-pagination">
			<?php echo paginate_links( array( 'prev_text' => '‹ PREV', 'next_text' => 'NEXT ›' ) ); ?>
		</nav>
	<?php else : ?>
		<p class="kp-empty">★ 該当する記事が見つかりませんでした ★<br><small>キーワードを変えて、もう一度試してみてね♡</small></p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
