<?php
/**
 * Archive (category / tag / taxonomy)
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ ARCHIVE ★</p>
	<h1 class="kp-pagehero__title"><?php
		if ( is_post_type_archive() ) post_type_archive_title();
		elseif ( is_category() || is_tag() || is_tax() ) single_term_title();
		elseif ( is_author() ) the_author();
		elseif ( is_date() ) echo esc_html( get_the_date( 'Y年n月' ) );
		else esc_html_e( '記事一覧', 'kyosuki-pop' );
	?></h1>
	<?php if ( $desc = term_description() ) echo '<div class="kp-pagehero__desc">' . wp_kses_post( $desc ) . '</div>'; ?>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php if ( have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; while ( have_posts() ) : the_post(); $i++;
				$genre = kyosuki_pop_get_primary_genre();
			?>
				<article class="kp-card">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) ); ?>
					</a>
					<div class="kp-card__body">
						<?php if ( $genre ) : ?>
							<span class="kp-chip" style="background:<?php echo esc_attr( kyosuki_pop_genre_color( $genre ) ); ?>">#<?php echo esc_html( $genre->name ); ?></span>
						<?php endif; ?>
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="kp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<nav class="kp-pagination">
			<?php echo paginate_links( array( 'prev_text' => '‹ PREV', 'next_text' => 'NEXT ›' ) ); ?>
		</nav>

	<?php else : ?>
		<p class="kp-empty">★ 該当する記事が見つかりませんでした ★</p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
