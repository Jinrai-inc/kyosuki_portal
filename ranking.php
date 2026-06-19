<?php
/**
 * Template Name: ★ ランキング
 *
 * Ranking page — PV 順 人気記事ランキング
 *
 * 通常は「ランキング」固定ページ（slug=ranking）に自動割当される
 * Page Template として利用される。inc/template-functions.php の
 * kyosuki_pop_ensure_ranking_page() が初回起動時に対応する固定
 * ページを作成・割当する。
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ RANKING ★</p>
	<h1 class="kp-pagehero__title">人気の記事ランキング</h1>
	<p class="kp-pagehero__desc">読まれている順に並べた人気記事 TOP 30 ♡</p>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php
	$rank_q = kyosuki_pop_get_popular_posts( 30, 'post' );
	if ( $rank_q->have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; while ( $rank_q->have_posts() ) : $rank_q->the_post(); $i++;
				$genre = kyosuki_pop_get_primary_genre();
				$arc   = kyosuki_pop_get_primary_arc();
				$views = (int) get_post_meta( get_the_ID(), 'kp_views', true );
				$likes = (int) get_post_meta( get_the_ID(), 'kp_likes', true );
				$num_class = $i <= 3 ? 'kp-card__rank--' . $i : 'kp-card__rank--n';
			?>
				<article class="kp-card">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<span class="kp-card__rank <?php echo esc_attr( $num_class ); ?>"><?php echo (int) $i; ?></span>
						<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) ); ?>
						<?php if ( $arc ) : ?><span class="kp-card__arc">#<?php echo esc_html( $arc->name ); ?></span><?php endif; ?>
					</a>
					<div class="kp-card__body">
						<?php if ( $genre ) : ?>
							<span class="kp-chip" style="background:<?php echo esc_attr( kyosuki_pop_genre_color( $genre->name ) ); ?>">#<?php echo esc_html( $genre->name ); ?></span>
						<?php endif; ?>
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="kp-card__meta">
							👀 <?php echo number_format_i18n( $views ); ?>
							<?php if ( $likes > 0 ) : ?> · ♡ <?php echo number_format_i18n( $likes ); ?><?php endif; ?>
							· 💬 <?php echo (int) get_comments_number(); ?>
						</p>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	<?php else : ?>
		<p class="kp-empty">★ まだ記事がありません ★</p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
