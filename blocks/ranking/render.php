<?php
/**
 * Render: kyosuki/ranking
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$limit     = max( 1, (int) ( $attributes['limit'] ?? 5 ) );
$post_type = $attributes['postType'] ?? 'post';
$show      = ! empty( $attributes['showThumb'] );

$q = kyosuki_pop_get_popular_posts( $limit, $post_type );
$wrapper = get_block_wrapper_attributes( array( 'class' => 'kp-ranking' ) );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h3 class="kp-section__head"><?php echo esc_html( $attributes['title'] ); ?></h3>
	<?php endif; ?>
	<?php if ( $q->have_posts() ) : $rank = 0; ?>
		<?php while ( $q->have_posts() ) : $q->the_post(); $rank++;
			$num_class = $rank <= 3 ? 'kp-rank-item__num--' . $rank : 'kp-rank-item__num--n';
		?>
			<a class="kp-rank-item" href="<?php the_permalink(); ?>">
				<span class="kp-rank-item__num <?php echo esc_attr( $num_class ); ?>"><?php echo (int) $rank; ?></span>
				<?php if ( $show ) : ?>
					<span class="kp-rank-item__thumb">
						<?php if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'thumbnail' );
						} else {
							printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $rank ) ) );
						} ?>
					</span>
				<?php endif; ?>
				<span class="kp-rank-item__title"><?php the_title(); ?></span>
			</a>
		<?php endwhile; wp_reset_postdata(); ?>
	<?php else : ?>
		<p><?php esc_html_e( '記事がまだありません。', 'kyosuki-pop' ); ?></p>
	<?php endif; ?>
</div>
