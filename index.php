<?php
/**
 * Front Page / Home Index — トップページ (見た目重視)
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<?php
// ===== 1. SCOOP HERO =====
$scoop_q = new WP_Query( array( 'posts_per_page' => 1, 'meta_key' => 'kp_pickup', 'meta_value' => '1' ) );
if ( ! $scoop_q->have_posts() ) {
	$scoop_q = new WP_Query( array( 'posts_per_page' => 1 ) );
}
?>

<section class="kp-scoop">
	<div class="kp-scoop__bgshapes" aria-hidden="true">
		<span class="kp-blob kp-blob--pink"></span>
		<span class="kp-blob kp-blob--blue"></span>
		<span class="kp-blob kp-blob--yellow"></span>
		<span class="kp-star">★</span>
		<span class="kp-heart">♡</span>
	</div>

	<div class="kp-scoop__grid">
		<?php if ( $scoop_q->have_posts() ) : while ( $scoop_q->have_posts() ) : $scoop_q->the_post();
			$arc   = kyosuki_pop_get_primary_arc();
			$genre = kyosuki_pop_get_primary_genre();
		?>
		<div class="kp-scoop__media">
			<a href="<?php the_permalink(); ?>" class="kp-scoop__img">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'large' );
				} else {
					printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( 0 ) ) );
				} ?>
			</a>
			<span class="kp-scoop__sticker">★ TODAY'S SCOOP ★</span>
			<span class="kp-scoop__circle">NEW!<br>UP ♡</span>
			<span class="kp-scoop__tape">EXCLUSIVE</span>
		</div>
		<div class="kp-scoop__body">
			<p class="kp-scoop__kicker">
				★ <?php echo $arc ? esc_html( $arc->name ) : '今日好き'; ?>
				<?php if ( $genre ) : ?> × <?php echo esc_html( $genre->name ); ?><?php endif; ?> ★
			</p>
			<h2 class="kp-scoop__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<p class="kp-scoop__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 60 ) ); ?></p>
			<div class="kp-scoop__meta">
				<span>📅 <?php echo esc_html( get_the_date( 'Y.n.j' ) ); ?></span>
				<span>👀 <?php echo number_format_i18n( (int) get_post_meta( get_the_ID(), 'kp_views', true ) ); ?> views</span>
				<span>💬 <?php echo (int) get_comments_number(); ?></span>
			</div>
			<a class="kp-scoop__cta" href="<?php the_permalink(); ?>">READ MORE →</a>
		</div>
		<?php endwhile; wp_reset_postdata(); endif; ?>
	</div>
</section>

<?php
// ===== 2. POPULAR TAGS =====
$popular_tags = get_terms( array(
	'taxonomy'   => 'post_tag',
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 8,
	'hide_empty' => true,
) );
if ( ! is_wp_error( $popular_tags ) && $popular_tags ) :
?>
<section class="kp-section kp-section--chips">
	<h2 class="kp-section__head"><span class="kp-section__deco">★</span> 人気のあるタグ <span class="kp-section__deco">★</span></h2>
	<div class="kp-chips">
		<?php
		$tones = array( '#FF7AC6', '#5AC8FA', '#FFE066', '#9B5DE5', '#C9F28A', '#E83E8C' );
		foreach ( $popular_tags as $i => $t ) {
			$custom = get_term_meta( $t->term_id, 'kp_color', true );
			$tone   = $custom ?: $tones[ $i % count( $tones ) ];
			$emoji  = kyosuki_pop_term_emoji( $t );
			printf(
				'<a class="kp-chip kp-chip--lg" style="background:%s" href="%s"><span class="kp-chip__em">%s</span><span class="kp-chip__txt">%s</span><span class="kp-chip__cnt">%s posts</span></a>',
				esc_attr( $tone ),
				esc_url( get_term_link( $t ) ),
				esc_html( $emoji ?: '#' ),
				esc_html( $t->name ),
				esc_html( number_format_i18n( (int) $t->count ) )
			);
		}
		?>
	</div>
</section>
<?php endif; ?>

<?php
// ===== 3. NEW ARTICLES (Magazine grid) =====
$new_q = new WP_Query( array( 'posts_per_page' => 6 ) );
$more_url = kyosuki_pop_posts_archive_url();
?>
<section class="kp-section">
	<header class="kp-section__bar">
		<h2 class="kp-section__head">★ NEW ARTICLES ★</h2>
	</header>

	<div class="kp-mag-grid kp-mag-grid--3">
		<?php $i = 0; if ( $new_q->have_posts() ) : while ( $new_q->have_posts() ) : $new_q->the_post(); $i++;
			$genre  = kyosuki_pop_get_primary_genre();
			$arc    = kyosuki_pop_get_primary_arc();
			$pid    = get_the_ID();
			$likes  = (int) get_post_meta( $pid, 'kp_likes', true );
		?>
			<article class="kp-card">
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
				<div class="kp-card__body">
					<?php if ( $genre ) : ?>
						<span class="kp-chip" style="background:<?php echo esc_attr( kyosuki_pop_genre_color( $genre ) ); ?>">#<?php echo esc_html( $genre->name ); ?></span>
					<?php endif; ?>
					<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p class="kp-card__meta">
						<span><?php echo esc_html( get_the_date( 'n.j' ) ); ?></span>
						<button type="button" class="kp-likebtn" data-kp-like="<?php echo (int) $pid; ?>">♡ <span data-kp-like-count><?php echo number_format_i18n( $likes ); ?></span></button>
					</p>
				</div>
			</article>
		<?php endwhile; wp_reset_postdata(); endif; ?>
	</div>

	<p class="kp-section__more">
		<a class="kp-more-btn" href="<?php echo esc_url( $more_url ); ?>">詳しく見る →</a>
	</p>
</section>
</section>

<?php
// ===== 4. TOP RANKING + RIGHT PR =====
?>
<section class="kp-section kp-section--rank">
	<div class="kp-rank-wrap">
		<div class="kp-rank-main">
			<h2 class="kp-section__head">★ TOP RANKING ★ <small>今週の人気記事</small></h2>
			<?php
			$rank_q = kyosuki_pop_get_popular_posts( 5, 'post' );
			$rank = 0;
			if ( $rank_q->have_posts() ) : while ( $rank_q->have_posts() ) : $rank_q->the_post(); $rank++;
				$num_class = $rank <= 3 ? 'kp-rank-item__num--' . $rank : 'kp-rank-item__num--n';
				$pid    = get_the_ID();
				$likes  = (int) get_post_meta( $pid, 'kp_likes', true );
				$views  = (int) get_post_meta( $pid, 'kp_views', true );
			?>
				<a class="kp-rank-item" href="<?php the_permalink(); ?>">
					<span class="kp-rank-item__num <?php echo esc_attr( $num_class ); ?>"><?php echo (int) $rank; ?></span>
					<span class="kp-rank-item__thumb">
						<?php if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'thumbnail' );
						} else {
							printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $rank + 1 ) ) );
						} ?>
					</span>
					<span class="kp-rank-item__body">
						<span class="kp-rank-item__title"><?php the_title(); ?></span>
						<span class="kp-rank-item__meta">👀 <?php echo number_format_i18n( $views ); ?> · 💬 <?php echo (int) get_comments_number(); ?> · ♡ <?php echo number_format_i18n( $likes ); ?></span>
					</span>
				</a>
			<?php endwhile; wp_reset_postdata(); else : ?>
				<p>記事がまだありません。</p>
			<?php endif; ?>
		</div>

		<aside class="kp-rank-side">
			<?php
			$pr_title = trim( (string) get_theme_mod( 'kp_pr_title', '' ) );
			$pr_url   = trim( (string) get_theme_mod( 'kp_pr_url', '' ) );
			$pr_show  = get_theme_mod( 'kp_pr_enabled', true ) && $pr_title !== '' && $pr_url !== '';
			if ( $pr_show ) :
				$pr_image = get_theme_mod( 'kp_pr_image' );
				if ( ! $pr_image ) $pr_image = kyosuki_pop_placeholder_url( 9 );
				$pr_label = get_theme_mod( 'kp_pr_label', '' );
				$pr_price = trim( (string) get_theme_mod( 'kp_pr_price', '' ) );
				$pr_shop  = trim( (string) get_theme_mod( 'kp_pr_shop', '' ) );
				$pr_cta   = get_theme_mod( 'kp_pr_cta', 'CHECK →' );
				$price_line = trim( $pr_price . ( $pr_shop !== '' ? ' / ' . $pr_shop : '' ) );
			?>
			<div class="kp-pr">
				<div class="kp-pr__thumb"><a href="<?php echo esc_url( $pr_url ); ?>" rel="sponsored noopener" target="_blank"><img src="<?php echo esc_url( $pr_image ); ?>" alt="<?php echo esc_attr( $pr_title ); ?>" /></a></div>
				<div class="kp-pr__body">
					<?php if ( $pr_label !== '' ) : ?><span class="kp-pr__label"><?php echo esc_html( $pr_label ); ?></span><?php endif; ?>
					<p class="kp-pr__title"><?php echo esc_html( $pr_title ); ?></p>
					<?php if ( $price_line !== '' ) : ?><p class="kp-pr__price"><?php echo esc_html( $price_line ); ?></p><?php endif; ?>
					<a class="kp-pr__cta" href="<?php echo esc_url( $pr_url ); ?>" rel="sponsored noopener" target="_blank"><?php echo esc_html( $pr_cta ); ?></a>
				</div>
			</div>
			<?php endif; ?>

			<?php
			$poll_post = class_exists( 'Kyosuki_Pop_Poll' ) ? Kyosuki_Pop_Poll::active_poll() : null;
			if ( $poll_post && Kyosuki_Pop_Poll::has_options() ) :
				$poll_opts = Kyosuki_Pop_Poll::get_options_list();
				$poll_data = Kyosuki_Pop_Poll::calculate_percentages();

				// %降順で順位確定（同点は登録順を保持）
				$kp_items = array();
				foreach ( $poll_opts as $orig_i => $o ) {
					$kp_items[] = array(
						'name' => $o['name'],
						'hash' => $o['hash'],
						'img'  => ! empty( $o['image_url'] ) ? $o['image_url'] : kyosuki_pop_placeholder_url( crc32( $o['hash'] ) ),
						'pct'  => (int) ( $poll_data['percentages'][ $o['hash'] ] ?? 0 ),
						'_i'   => $orig_i,
					);
				}
				usort( $kp_items, function ( $a, $b ) {
					if ( $a['pct'] === $b['pct'] ) return $a['_i'] - $b['_i'];
					return $b['pct'] - $a['pct'];
				} );
				$kp_top      = array_slice( $kp_items, 0, 3 );
				$kp_has_more = count( $kp_items ) > 3;
				$kp_more_url = function_exists( 'kyosuki_pop_poll_ranking_url' ) ? kyosuki_pop_poll_ranking_url() : home_url( '/' );
			?>
			<div class="kp-poll-grid kp-podium" data-kp-poll data-kp-poll-id="<?php echo (int) $poll_post->ID; ?>">
				<p class="kp-poll-grid__title"><?php echo esc_html( $poll_post->post_title ); ?></p>
				<p class="kp-poll-grid__hint" data-kp-poll-hint>♡ 推しCPをタップ／クリックして投票してね</p>

				<div class="kp-podium__top" data-count="<?php echo count( $kp_top ); ?>">
					<?php foreach ( $kp_top as $i => $it ) :
						$rank = $i + 1;
					?>
						<button type="button"
							class="kp-poll-card kp-podium__item kp-podium__item--<?php echo (int) $rank; ?>"
							data-kp-opt-hash="<?php echo esc_attr( $it['hash'] ); ?>"
							data-kp-pct="<?php echo (int) $it['pct']; ?>"
							aria-label="<?php echo esc_attr( $it['name'] ); ?> に投票">
							<?php if ( $rank === 1 ) : ?>
								<span class="kp-podium__crown" aria-hidden="true">👑</span>
							<?php endif; ?>
							<span class="kp-poll-card__photo">
								<img class="kp-poll-card__img" src="<?php echo esc_url( $it['img'] ); ?>" alt="">
							</span>
							<span class="kp-podium__rank kp-podium__rank--<?php echo (int) $rank; ?>"><?php echo (int) $rank; ?>位</span>
							<span class="kp-poll-card__name"><?php echo esc_html( $it['name'] ); ?></span>
							<span class="kp-poll-card__pct" data-kp-target="<?php echo (int) $it['pct']; ?>">0%</span>
						</button>
					<?php endforeach; ?>
				</div>

				<p class="kp-poll-grid__msg" data-kp-poll-msg hidden>♡ 投票ありがとう！</p>
				<p class="kp-poll-grid__err" data-kp-poll-err hidden></p>

				<?php if ( $kp_has_more ) : ?>
					<p class="kp-podium__more-wrap">
						<a class="kp-podium__more-link" href="<?php echo esc_url( $kp_more_url ); ?>">その他のランキングはこちら →</a>
					</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</aside>
	</div>
</section>

<?php
// ===== 5. CAST CAROUSEL =====
$cast_q = new WP_Query( array( 'post_type' => 'cast', 'posts_per_page' => 8 ) );
?>
<section class="kp-section">
	<header class="kp-section__bar">
		<h2 class="kp-section__head">★ MEMBERS ♡ ★</h2>
		<a class="kp-more" href="<?php echo esc_url( home_url( '/cast/' ) ); ?>">全メンバー →</a>
	</header>

	<div class="kp-carousel" data-carousel>
		<?php $tones = array( '#FF7AC6', '#5AC8FA', '#FFE066', '#9B5DE5', '#C9F28A', '#E83E8C' );
		if ( $cast_q->have_posts() ) : $i = 0; while ( $cast_q->have_posts() ) : $cast_q->the_post(); $tone = $tones[ $i++ % count( $tones ) ]; ?>
			<a class="kp-cast" href="<?php the_permalink(); ?>" style="--tone:<?php echo esc_attr( $tone ); ?>">
				<span class="kp-cast__avatar">
					<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'thumbnail' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) ); ?>
				</span>
				<span class="kp-cast__name"><?php the_title(); ?></span>
			</a>
		<?php endwhile; wp_reset_postdata();
		else : ?>
			<p class="kp-empty">★ まだ出演者投稿がありません ★</p>
		<?php endif; ?>
	</div>
</section>

<?php
// ===== 6. COUPLE GALLERY =====
$couple_q = new WP_Query( array( 'post_type' => 'couple', 'posts_per_page' => 6 ) );
?>
<section class="kp-section kp-section--couple">
	<header class="kp-section__bar">
		<h2 class="kp-section__head">★ NEW COUPLES ★ <small>成立CP速報</small></h2>
		<a class="kp-more" href="<?php echo esc_url( home_url( '/couple/' ) ); ?>">CP図鑑 →</a>
	</header>

	<p class="kp-scrollhint">カップルをスライド</p>
	<div class="kp-couple-grid kp-couple-grid--scroll" data-carousel>
		<?php if ( $couple_q->have_posts() ) : $i = 0; while ( $couple_q->have_posts() ) : $couple_q->the_post(); $i++; ?>
			<a class="kp-couple" href="<?php the_permalink(); ?>">
				<span class="kp-couple__media">
					<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i + 2 ) ) ); ?>
					<span class="kp-couple__heart">♡</span>
				</span>
				<span class="kp-couple__name"><?php the_title(); ?></span>
			</a>
		<?php endwhile; wp_reset_postdata();
		else : ?>
			<p class="kp-empty">★ まだカップル投稿がありません ★</p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
