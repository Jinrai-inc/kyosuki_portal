<?php
/**
 * Sidebar
 *
 * @package Kyosuki_Pop
 */
?>
<aside class="kp-sidebar" role="complementary">

	<div class="kp-widget">
		<h3 class="kp-widget__title">★ RANKING ★</h3>
		<?php
		$rk = kyosuki_pop_get_popular_posts( 5, 'post' );
		$r = 0;
		if ( $rk->have_posts() ) : while ( $rk->have_posts() ) : $rk->the_post(); $r++;
			$num_class = $r <= 3 ? 'kp-rank-item__num--' . $r : 'kp-rank-item__num--n';
		?>
			<a class="kp-rank-item kp-rank-item--sm" href="<?php the_permalink(); ?>">
				<span class="kp-rank-item__num <?php echo esc_attr( $num_class ); ?>"><?php echo (int) $r; ?></span>
				<span class="kp-rank-item__thumb">
					<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'thumbnail' ); else printf( '<img src="%s" alt="" />', esc_url( kyosuki_pop_placeholder_url( $r ) ) ); ?>
				</span>
				<span class="kp-rank-item__title"><?php the_title(); ?></span>
			</a>
		<?php endwhile; wp_reset_postdata(); endif; ?>
	</div>

	<div class="kp-widget">
		<h3 class="kp-widget__title">★ ARCS ★</h3>
		<div class="kp-tagcloud">
			<?php
			$arcs = get_terms( array( 'taxonomy' => 'kp_arc', 'hide_empty' => false ) );
			$tones = array( '#FF7AC6', '#5AC8FA', '#FFE066', '#9B5DE5', '#C9F28A', '#E83E8C' );
			if ( ! is_wp_error( $arcs ) && $arcs ) {
				foreach ( $arcs as $i => $t ) {
					$custom = get_term_meta( $t->term_id, 'kp_color', true );
					$tone   = $custom ?: $tones[ $i % count( $tones ) ];
					$emoji  = kyosuki_pop_term_emoji( $t );
					printf( '<a class="kp-chip" style="background:%s" href="%s">%s#%s</a>',
						esc_attr( $tone ),
						esc_url( get_term_link( $t ) ),
						$emoji ? esc_html( $emoji ) . ' ' : '',
						esc_html( $t->name )
					);
				}
			}
			?>
		</div>
	</div>

	<div class="kp-widget">
		<h3 class="kp-widget__title">★ GENRE ★</h3>
		<div class="kp-tagcloud">
			<?php
			$gs = get_terms( array( 'taxonomy' => 'kp_genre', 'hide_empty' => false ) );
			if ( ! is_wp_error( $gs ) && $gs ) {
				foreach ( $gs as $g ) {
					$emoji = kyosuki_pop_term_emoji( $g );
					printf( '<a class="kp-chip" style="background:%s" href="%s">%s#%s</a>',
						esc_attr( kyosuki_pop_genre_color( $g ) ),
						esc_url( get_term_link( $g ) ),
						$emoji ? esc_html( $emoji ) . ' ' : '',
						esc_html( $g->name )
					);
				}
			}
			?>
		</div>
	</div>

	<div class="kp-widget kp-widget--ad">
		<p class="kp-widget__ad-label">[ AD ]</p>
		<div class="kp-widget__ad-box">300×250</div>
	</div>

	<?php if ( is_active_sidebar( 'kp-sidebar' ) ) dynamic_sidebar( 'kp-sidebar' ); ?>
</aside>
