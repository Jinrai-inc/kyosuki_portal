<?php
/**
 * Render: kyosuki/pr-box
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$title = isset( $attributes['title'] ) ? trim( (string) $attributes['title'] ) : '';
$url   = isset( $attributes['url'] )   ? trim( (string) $attributes['url'] )   : '';
if ( $title === '' || $url === '' ) {
	return;
}

$tone_map  = array( 'pink' => '#FF7AC6', 'blue' => '#5AC8FA', 'yellow' => '#FFE066', 'purple' => '#9B5DE5' );
$tone      = $tone_map[ $attributes['tone'] ?? 'pink' ] ?? '#FF7AC6';
$image     = ! empty( $attributes['image'] ) ? $attributes['image'] : kyosuki_pop_placeholder_url( 5 );
$label     = isset( $attributes['label'] )    ? trim( (string) $attributes['label'] )    : '';
$price     = isset( $attributes['price'] )    ? trim( (string) $attributes['price'] )    : '';
$shop      = isset( $attributes['shop'] )     ? trim( (string) $attributes['shop'] )     : '';
$cta       = isset( $attributes['ctaLabel'] ) ? $attributes['ctaLabel'] : 'CHECK →';
$price_line = trim( $price . ( $shop !== '' ? ' / ' . $shop : '' ) );

$wrapper = get_block_wrapper_attributes( array(
	'class' => 'kp-pr',
	'style' => 'box-shadow:4px 4px 0 ' . $tone . ';',
) );
?>
<aside <?php echo $wrapper; ?>>
	<div class="kp-pr__thumb">
		<a href="<?php echo esc_url( $url ); ?>" rel="sponsored noopener" target="_blank">
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
		</a>
	</div>
	<div class="kp-pr__body">
		<?php if ( $label !== '' ) : ?>
			<span class="kp-pr__label"><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>
		<p class="kp-pr__title"><?php echo esc_html( $title ); ?></p>
		<?php if ( $price_line !== '' ) : ?>
			<p class="kp-pr__price"><?php echo esc_html( $price_line ); ?></p>
		<?php endif; ?>
		<a class="kp-pr__cta" href="<?php echo esc_url( $url ); ?>" rel="sponsored noopener" target="_blank"><?php echo esc_html( $cta ); ?></a>
	</div>
</aside>
