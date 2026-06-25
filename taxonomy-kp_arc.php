<?php
/**
 * Taxonomy: kp_arc (シーズン別 / 記事・カップル・出演者を横断表示)
 *
 * @package Kyosuki_Pop
 */
get_header();
$term = get_queried_object();
?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ SEASON ★</p>
	<h1 class="kp-pagehero__title">#<?php echo esc_html( $term->name ); ?></h1>
	<?php if ( $desc = term_description() ) echo '<div class="kp-pagehero__desc">' . wp_kses_post( $desc ) . '</div>'; ?>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php
	// このシーズンに紐付く全 post_type を横断
	$tax_q = new WP_Query( array(
		'post_type'      => array( 'post', 'couple', 'cast' ),
		'posts_per_page' => 18,
		'paged'          => max( 1, get_query_var( 'paged' ) ),
		'tax_query'      => array(
			array( 'taxonomy' => 'kp_arc', 'terms' => $term->term_id ),
		),
	) );
	if ( $tax_q->have_posts() ) : ?>
		<div class="kp-mag-grid kp-mag-grid--3">
			<?php $i = 0; while ( $tax_q->have_posts() ) : $tax_q->the_post(); $i++;
				$pt    = get_post_type();
				$genre = ( $pt === 'post' ) ? kyosuki_pop_get_primary_genre() : null;
				$pt_label = $pt === 'couple' ? 'CP' : ( $pt === 'cast' ? 'CAST' : 'NEWS' );
			?>
				<article class="kp-card">
					<a href="<?php the_permalink(); ?>" class="kp-card__media">
						<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $i ) ) ); ?>
						<span class="kp-card__arc"><?php echo esc_html( $pt_label ); ?></span>
					</a>
					<div class="kp-card__body">
						<?php if ( $genre ) : ?>
							<span class="kp-chip" style="background:<?php echo esc_attr( kyosuki_pop_genre_color( $genre ) ); ?>">#<?php echo esc_html( $genre->name ); ?></span>
						<?php endif; ?>
						<h3 class="kp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="kp-card__meta"><span><?php echo esc_html( get_the_date( 'Y.n.j' ) ); ?></span></p>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<nav class="kp-pagination">
			<?php echo paginate_links( array(
				'total'     => $tax_q->max_num_pages,
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'prev_text' => '‹ PREV',
				'next_text' => 'NEXT ›',
			) ); ?>
		</nav>

	<?php else : ?>
		<p class="kp-empty">★ #<?php echo esc_html( $term->name ); ?> の投稿はまだありません ★</p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
