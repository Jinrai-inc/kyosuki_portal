<?php
/**
 * Single post — 記事詳細
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<div class="kp-single-wrap">
<article class="kp-single">

	<?php while ( have_posts() ) : the_post();
		$arc   = kyosuki_pop_get_primary_arc();
		$genre = kyosuki_pop_get_primary_genre();
	?>

	<nav class="kp-breadcrumbs" aria-label="breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a> ›
		<?php if ( $arc ) : ?>
			<a href="<?php echo esc_url( get_term_link( $arc ) ); ?>">#<?php echo esc_html( $arc->name ); ?></a> ›
		<?php endif; ?>
		<span><?php echo esc_html( wp_trim_words( get_the_title(), 6 ) ); ?></span>
	</nav>

	<header class="kp-single__head">
		<div class="kp-single__chips">
			<?php if ( $arc ) : ?>
				<a class="kp-chip kp-chip--ink" href="<?php echo esc_url( get_term_link( $arc ) ); ?>">#<?php echo esc_html( $arc->name ); ?></a>
			<?php endif; ?>
			<?php if ( $genre ) : ?>
				<a class="kp-chip" style="background:<?php echo esc_attr( kyosuki_pop_genre_color( $genre->name ) ); ?>" href="<?php echo esc_url( get_term_link( $genre ) ); ?>">#<?php echo esc_html( $genre->name ); ?></a>
			<?php endif; ?>
			<span class="kp-chip kp-chip--ghost">★ <?php echo esc_html( get_the_date( 'Y.n.j' ) ); ?> ★</span>
		</div>
		<h1 class="kp-single__title"><?php the_title(); ?></h1>
		<p class="kp-single__lead"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 80 ) ); ?></p>

		<div class="kp-single__byline">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 36, '', '', array( 'class' => 'kp-avatar' ) ); ?>
			<div>
				<p class="kp-author">編集部 <?php the_author(); ?></p>
				<p class="kp-meta">📅 <?php echo esc_html( get_the_date( 'Y.n.j H:i' ) ); ?> · 👀 <?php echo number_format_i18n( (int) get_post_meta( get_the_ID(), 'kp_views', true ) ); ?> views · 💬 <?php echo (int) get_comments_number(); ?></p>
			</div>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="kp-single__hero">
			<?php the_post_thumbnail( 'large' ); ?>
			<span class="kp-single__sticker">★ SCOOP ★</span>
		</figure>
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
			<a class="kp-share__btn" style="background:#5AC8FA" href="#" target="_blank" rel="noopener">TikTok 投稿</a>
		</div>
	</aside>

	<nav class="kp-postnav">
		<?php previous_post_link( '<span class="kp-postnav__prev">‹ %link</span>', '%title' ); ?>
		<?php next_post_link( '<span class="kp-postnav__next">%link ›</span>', '%title' ); ?>
	</nav>

	<section class="kp-related">
		<h3 class="kp-section__head">★ RELATED ♡ ★</h3>
		<div class="kp-mag-grid">
		<?php
		$rel = new WP_Query( array(
			'post_type'      => 'post',
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
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
