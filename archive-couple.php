<?php
/**
 * Archive: couple (カップル一覧 / CP図鑑)
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ CP COLLECTION ★</p>
	<h1 class="kp-pagehero__title">CP図鑑</h1>
	<p class="kp-pagehero__desc">「今日好き」で生まれたカップルをひたすら推しまとめ ♡</p>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php if ( have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; while ( have_posts() ) : the_post(); $i++;
				$arc      = kyosuki_pop_get_primary_arc();
				$statuses = get_the_terms( get_the_ID(), 'kp_status' );
				$status   = ( $statuses && ! is_wp_error( $statuses ) ) ? $statuses[0] : null;
			?>
				<article class="kp-card kp-card--couple">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<?php if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'medium' );
						} else {
							printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i + 2 ) ) );
						} ?>
						<?php if ( $arc ) : ?>
							<span class="kp-card__arc">#<?php echo esc_html( $arc->name ); ?></span>
						<?php endif; ?>
						<span class="kp-couple__heart">♡</span>
					</a>
					<div class="kp-card__body">
						<?php if ( $status ) : ?>
							<span class="kp-chip kp-chip--status kp-chip--status-<?php echo esc_attr( $status->slug ); ?>">
								<?php echo esc_html( $status->name ); ?>
							</span>
						<?php endif; ?>
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="kp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
						<p class="kp-card__meta"><span><?php echo esc_html( get_the_date( 'Y.n.j' ) ); ?></span></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<nav class="kp-pagination">
			<?php echo paginate_links( array( 'prev_text' => '‹ PREV', 'next_text' => 'NEXT ›' ) ); ?>
		</nav>

	<?php else : ?>
		<p class="kp-empty">★ まだ CP 投稿がありません ★<br><small>「カップル → 新規追加」からカップル投稿を作成してください。</small></p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
