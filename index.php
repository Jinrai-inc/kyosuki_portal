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
// ===== 2. GENRE CHIPS =====
$arcs = get_terms( array( 'taxonomy' => 'kp_arc', 'hide_empty' => false, 'number' => 8 ) );
?>
<section class="kp-section kp-section--chips">
	<h2 class="kp-section__head"><span class="kp-section__deco">★</span> 今期のアーク <span class="kp-section__deco">★</span></h2>
	<div class="kp-chips">
		<?php
		$tones = array( '#FF7AC6', '#5AC8FA', '#FFE066', '#9B5DE5', '#C9F28A', '#E83E8C' );
		$emojis = array( '🏝', '❄️', '🎒', '🌸', '☀️', '🎓' );
		if ( ! is_wp_error( $arcs ) && $arcs ) :
			foreach ( $arcs as $i => $t ) {
				$tone = $tones[ $i % count( $tones ) ];
				$em   = $emojis[ $i % count( $emojis ) ];
				printf(
					'<a class="kp-chip kp-chip--lg" style="background:%s" href="%s"><span class="kp-chip__em">%s</span><span class="kp-chip__txt">#%s</span><span class="kp-chip__cnt">%d posts</span></a>',
					esc_attr( $tone ), esc_url( get_term_link( $t ) ), esc_html( $em ), esc_html( $t->name ), (int) $t->count
				);
			}
		else :
			$dummy = array( '沖縄編', '北海道編', '修学旅行', '春休み編', '夏休み編', '卒業編' );
			foreach ( $dummy as $i => $name ) {
				$tone = $tones[ $i % count( $tones ) ];
				$em   = $emojis[ $i % count( $emojis ) ];
				printf(
					'<a class="kp-chip kp-chip--lg" style="background:%s" href="#"><span class="kp-chip__em">%s</span><span class="kp-chip__txt">#%s</span><span class="kp-chip__cnt">%d posts</span></a>',
					esc_attr( $tone ), esc_html( $em ), esc_html( $name ), 0
				);
			}
		endif;
		?>
	</div>
</section>

<?php
// ===== 3. NEW ARTICLES (Magazine grid) =====
$new_q = new WP_Query( array( 'posts_per_page' => 8 ) );
?>
<section class="kp-section">
	<header class="kp-section__bar">
		<h2 class="kp-section__head">★ NEW ARTICLES ★</h2>
		<a class="kp-more" href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>もっと見る →</a>
	</header>

	<p class="kp-scrollhint">記事をスライド</p>
	<div class="kp-mag-grid kp-mag-grid--scroll" data-carousel>
		<?php $i = 0; if ( $new_q->have_posts() ) : while ( $new_q->have_posts() ) : $new_q->the_post(); $i++;
			$rotate = '';
			$genre  = kyosuki_pop_get_primary_genre();
			$arc    = kyosuki_pop_get_primary_arc();
			$pid    = get_the_ID();
			$likes  = (int) get_post_meta( $pid, 'kp_likes', true );
		?>
			<article class="kp-card <?php echo esc_attr( $rotate ); ?>">
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
						<span class="kp-chip" style="background:<?php echo esc_attr( kyosuki_pop_genre_color( $genre->name ) ); ?>">#<?php echo esc_html( $genre->name ); ?></span>
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
			<?php if ( get_theme_mod( 'kp_pr_enabled', true ) ) :
				$pr_url   = get_theme_mod( 'kp_pr_url', '#' );
				$pr_image = get_theme_mod( 'kp_pr_image' );
				if ( ! $pr_image ) $pr_image = kyosuki_pop_placeholder_url( 9 );
			?>
			<div class="kp-pr">
				<div class="kp-pr__thumb"><a href="<?php echo esc_url( $pr_url ); ?>" rel="sponsored noopener"><img src="<?php echo esc_url( $pr_image ); ?>" alt="" /></a></div>
				<div class="kp-pr__body">
					<span class="kp-pr__label"><?php echo esc_html( get_theme_mod( 'kp_pr_label', '[PR] みお愛用 ♡' ) ); ?></span>
					<p class="kp-pr__title"><?php echo esc_html( get_theme_mod( 'kp_pr_title', 'マシュマロリップ' ) ); ?></p>
					<p class="kp-pr__price"><?php echo esc_html( get_theme_mod( 'kp_pr_price', '¥1,980 / 楽天' ) ); ?></p>
					<a class="kp-pr__cta" href="<?php echo esc_url( $pr_url ); ?>" rel="sponsored noopener"><?php echo esc_html( get_theme_mod( 'kp_pr_cta', 'CHECK →' ) ); ?></a>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( get_theme_mod( 'kp_poll_enabled', true ) ) :
				$poll_data = class_exists( 'Kyosuki_Pop_Poll' ) ? Kyosuki_Pop_Poll::calculate_percentages() : array( 'percentages' => array() );
				$lines = preg_split( "/\r\n|\r|\n/", trim( get_theme_mod( 'kp_poll_options', "りく♡みお|78\nゆうた♡あい|54\nけんと♡なな|32\nそら♡まりん|21" ) ) );
			?>
			<div class="kp-poll" data-kp-poll data-kp-poll-id="<?php echo class_exists( 'Kyosuki_Pop_Poll' ) ? esc_attr( Kyosuki_Pop_Poll::get_poll_id() ) : ''; ?>">
				<p class="kp-poll__title"><?php echo esc_html( get_theme_mod( 'kp_poll_title', '★ 今週の推しCPは？' ) ); ?></p>
				<p class="kp-poll__hint" data-kp-poll-hint>♡ あなたの推しCPに投票してね（投票後に結果が見れるよ）</p>
				<ul class="kp-poll__list">
					<?php foreach ( $lines as $i => $line ) :
						$parts = explode( '|', $line, 2 );
						$name  = trim( $parts[0] ?? '' );
						if ( $name === '' ) continue;
						$pct = (int) ( $poll_data['percentages'][ $i ] ?? 0 );
					?>
						<li style="--w:<?php echo (int) $pct; ?>%;" data-kp-opt="<?php echo (int) $i; ?>" data-kp-pct="<?php echo (int) $pct; ?>">
							<button type="button" class="kp-poll__opt"><span class="kp-poll__name"><?php echo esc_html( $name ); ?></span><b class="kp-poll__pct"><?php echo (int) $pct; ?>%</b></button>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="kp-poll__msg" data-kp-poll-msg hidden>♡ 投票ありがとう！結果はこちら ↑</p>
				<p class="kp-poll__err" data-kp-poll-err hidden></p>
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
		else :
			$dummy = array( 'りく', 'みお', 'ゆうた', 'あい', 'けんと', 'なな', 'そら', 'まりん' );
			foreach ( $dummy as $i => $name ) {
				$tone = $tones[ $i % count( $tones ) ];
				printf(
					'<a class="kp-cast" href="#" style="--tone:%s"><span class="kp-cast__avatar"><img src="%s" alt="" /></span><span class="kp-cast__name">%s</span></a>',
					esc_attr( $tone ), esc_url( kyosuki_pop_placeholder_url( $i ) ), esc_html( $name )
				);
			}
		endif; ?>
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
		else :
			$cps = array( 'りく ♡ みお', 'ゆうた ♡ あい', 'けんと ♡ なな', 'そら ♡ まりん', 'あおい ♡ かれん', 'はる ♡ ゆめ' );
			foreach ( $cps as $i => $cp ) {
				printf(
					'<a class="kp-couple" href="#"><span class="kp-couple__media"><img src="%s" alt="" /><span class="kp-couple__heart">♡</span></span><span class="kp-couple__name">%s</span></a>',
					esc_url( kyosuki_pop_placeholder_url( $i + 2 ) ), esc_html( $cp )
				);
			}
		endif; ?>
	</div>
</section>

<?php
// ===== 7. INSTAGRAM-LIKE STRIP =====
?>
<section class="kp-section kp-section--ig">
	<h2 class="kp-section__head">★ #今日好き タグでチェック ★</h2>
	<div class="kp-ig-strip">
		<?php for ( $i = 0; $i < 10; $i++ ) : ?>
			<a class="kp-ig" href="#" style="background:<?php echo esc_attr( $tones[ $i % count( $tones ) ] ); ?>">
				<span>♡</span>
			</a>
		<?php endfor; ?>
	</div>
</section>

<?php get_footer(); ?>
