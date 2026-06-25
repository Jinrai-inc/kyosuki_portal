<?php
/**
 * Archive: cast (出演者一覧 / キャスト)
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ CAST ★</p>
	<h1 class="kp-pagehero__title">出演メンバー</h1>
	<p class="kp-pagehero__desc">「今日好き」に登場したメンバーをひとり残らず ♡</p>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php if ( have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; $tones = array( '#FF7AC6', '#5AC8FA', '#FFE066', '#9B5DE5', '#C9F28A', '#E83E8C' );
			while ( have_posts() ) : the_post(); $i++;
				$arc      = kyosuki_pop_get_primary_arc();
				$tone     = $tones[ $i % count( $tones ) ];
				$instagram = get_post_meta( get_the_ID(), 'instagram', true );
				$tiktok    = get_post_meta( get_the_ID(), 'tiktok', true );
				$agency    = get_post_meta( get_the_ID(), 'agency', true );
			?>
				<article class="kp-card kp-card--cast">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<?php if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'medium' );
						} else {
							printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) );
						} ?>
						<?php if ( $arc ) : ?>
							<span class="kp-card__arc">#<?php echo esc_html( $arc->name ); ?></span>
						<?php endif; ?>
					</a>
					<div class="kp-card__body" style="--tone:<?php echo esc_attr( $tone ); ?>">
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $agency ) : ?>
							<p class="kp-card__meta"><span>所属: <?php echo esc_html( $agency ); ?></span></p>
						<?php endif; ?>
						<?php if ( $instagram || $tiktok ) : ?>
							<p class="kp-card__sns">
								<?php if ( $instagram ) : ?>
									<a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener">IG</a>
								<?php endif; ?>
								<?php if ( $tiktok ) : ?>
									<a href="<?php echo esc_url( $tiktok ); ?>" target="_blank" rel="noopener">TK</a>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<nav class="kp-pagination">
			<?php echo paginate_links( array( 'prev_text' => '‹ PREV', 'next_text' => 'NEXT ›' ) ); ?>
		</nav>

	<?php else : ?>
		<p class="kp-empty">★ まだ出演者投稿がありません ★<br><small>「出演者 → 新規追加」からメンバーを登録してください。</small></p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
