<?php
/**
 * Single: cast (出演者プロフィールページ)
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<div class="kp-single-wrap">
<article class="kp-single">

	<?php while ( have_posts() ) : the_post();
		$arc = kyosuki_pop_get_primary_arc();
		$pid = get_the_ID();
	?>

	<nav class="kp-breadcrumbs" aria-label="breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a> ›
		<a href="<?php echo esc_url( get_post_type_archive_link( 'cast' ) ); ?>">CAST</a> ›
		<?php if ( $arc ) : ?>
			<a href="<?php echo esc_url( get_term_link( $arc ) ); ?>">#<?php echo esc_html( $arc->name ); ?></a> ›
		<?php endif; ?>
		<span><?php echo esc_html( wp_trim_words( get_the_title(), 6 ) ); ?></span>
	</nav>

	<header class="kp-single__head">
		<div class="kp-single__chips">
			<span class="kp-chip kp-chip--ink">CAST</span>
			<?php if ( $arc ) : ?>
				<a class="kp-chip" href="<?php echo esc_url( get_term_link( $arc ) ); ?>">#<?php echo esc_html( $arc->name ); ?></a>
			<?php endif; ?>
		</div>
		<h1 class="kp-single__title"><?php the_title(); ?></h1>
		<?php if ( $excerpt = get_the_excerpt() ) : ?>
			<p class="kp-single__lead"><?php echo esc_html( wp_trim_words( $excerpt, 60 ) ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="kp-single__hero">
			<?php the_post_thumbnail( 'large' ); ?>
			<span class="kp-single__sticker">★ CAST ★</span>
		</figure>
	<?php endif; ?>

	<?php
	// ===== 出演者プロフィール表示 =====
	$kp_fields = array(
		'name_kana'   => '名前（よみ）',
		'birthday'    => '誕生日',
		'grade'       => '学年',
		'hometown'    => '出身',
		'height'      => '身長',
		'agency'      => '所属事務所',
		'appearances' => '出演歴',
	);
	$kp_rows = '';
	foreach ( $kp_fields as $key => $label ) {
		$val = get_post_meta( $pid, $key, true );
		if ( $val === '' || $val === null ) { continue; }
		$kp_rows .= '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $val ) . '</td></tr>';
	}

	// SNSリンク
	$kp_sns = array(
		array( '📷 Instagram', get_post_meta( $pid, 'instagram', true ) ),
		array( '🎵 TikTok',    get_post_meta( $pid, 'tiktok', true ) ),
		array( '𝕏 X',         get_post_meta( $pid, 'x_url', true ) ),
	);
	$kp_sns_html = '';
	foreach ( $kp_sns as $s ) {
		if ( empty( $s[1] ) ) { continue; }
		$kp_sns_html .= '<a class="kp-profile__sns" href="' . esc_url( $s[1] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $s[0] ) . '</a>';
	}

	if ( $kp_rows || $kp_sns_html ) : ?>
	<div class="kp-profile">
		<div class="kp-profile__head">★ PROFILE ★</div>
		<?php if ( $kp_rows ) : ?>
			<table class="kp-profile__table"><tbody><?php echo $kp_rows; ?></tbody></table>
		<?php endif; ?>
		<?php if ( $kp_sns_html ) : ?>
			<div class="kp-profile__sns-wrap"><?php echo $kp_sns_html; ?></div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="kp-single__content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<nav class="kp-pagelinks">', 'after' => '</nav>' ) ); ?>
	</div>

	<aside class="kp-share">
		<p class="kp-share__title">★ SHARE ♡ ★</p>
		<div class="kp-share__list">
			<a class="kp-share__btn" style="background:#FF7AC6" href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" rel="noopener">𝕏 シェア</a>
			<a class="kp-share__btn" style="background:#9B5DE5" href="#" target="_blank" rel="noopener">IG ストーリー</a>
			<a class="kp-share__btn" style="background:#C9F28A" href="https://line.me/R/msg/text/?<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener">LINE で送る</a>
		</div>
	</aside>

	<nav class="kp-postnav">
		<?php previous_post_link( '<span class="kp-postnav__prev">‹ %link</span>', '%title' ); ?>
		<?php next_post_link( '<span class="kp-postnav__next">%link ›</span>', '%title' ); ?>
	</nav>

	<section class="kp-related">
		<h3 class="kp-section__head">★ 同じシーズンのメンバー ♡ ★</h3>
		<div class="kp-mag-grid">
		<?php
		$rel = new WP_Query( array(
			'post_type'      => 'cast',
			'posts_per_page' => 4,
			'post__not_in'   => array( $pid ),
			'tax_query'      => $arc ? array( array( 'taxonomy' => 'kp_arc', 'terms' => $arc->term_id ) ) : array(),
		) );
		if ( $rel->have_posts() ) : $i = 0; while ( $rel->have_posts() ) : $rel->the_post(); $i++; ?>
			<article class="kp-card">
				<a href="<?php the_permalink(); ?>" class="kp-card__media">
					<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) ); ?>
				</a>
				<div class="kp-card__body">
					<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				</div>
			</article>
		<?php endwhile; wp_reset_postdata(); endif; ?>
		</div>
	</section>

	<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>

	<?php endwhile; ?>
</article>

<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
