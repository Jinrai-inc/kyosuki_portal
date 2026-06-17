<?php
/**
 * Render: kyosuki/pr-box
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$tone_map = array( 'pink' => '#FF7AC6', 'blue' => '#5AC8FA', 'yellow' => '#FFE066', 'purple' => '#9B5DE5' );
$tone = $tone_map[ $attributes['tone'] ] ?? '#FF7AC6';
$image = ! empty( $attributes['image'] ) ? $attributes['image'] : kyosuki_pop_placeholder_url( 5 );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'kp-pr', 'style' => 'box-shadow:4px 4px 0 ' . $tone . ';' ) );
?>
<aside <?php echo $wrapper; ?>>
	<div class="kp-pr__thumb"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $attributes['title'] ); ?>" /></div>
	<div>
		<span class="kp-pr__label"><?php echo esc_html( $attributes['label'] ); ?></span>
		<p class="kp-pr__title"><?php echo esc_html( $attributes['title'] ); ?></p>
		<p class="kp-pr__price"><?php echo esc_html( $attributes['price'] ); ?> / <?php echo esc_html( $attributes['shop'] ); ?></p>
		<a class="kp-pr__cta" href="<?php echo esc_url( $attributes['url'] ); ?>" rel="sponsored noopener" target="_blank"><?php echo esc_html( $attributes['ctaLabel'] ); ?></a>
	</div>
</aside>
