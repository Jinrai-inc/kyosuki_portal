<?php
/**
 * Render: kyosuki/cast-profile
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$tone_map = array(
	'pink' => '#FF7AC6', 'pink-deep' => '#E83E8C',
	'blue' => '#5AC8FA', 'yellow' => '#FFE066',
	'purple' => '#9B5DE5', 'lime' => '#C9F28A',
);
$items = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array();
$wrapper = get_block_wrapper_attributes( array( 'class' => 'kp-cast-profile' ) );
?>
<div <?php echo $wrapper; ?> style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
	<?php foreach ( $items as $it ) :
		$tone = $tone_map[ $it['tone'] ?? 'pink' ] ?? '#FF7AC6';
	?>
		<div style="background:#fff;border:2px solid #1A0B3D;border-radius:10px;padding:10px;text-align:center;box-shadow:3px 3px 0 <?php echo esc_attr( $tone ); ?>;">
			<div style="font-size:10px;color:#6B5A8A;font-weight:700;"><?php echo esc_html( $it['key'] ?? '' ); ?></div>
			<div style="font-size:13px;font-weight:900;margin-top:2px;"><?php echo esc_html( $it['value'] ?? '' ); ?></div>
		</div>
	<?php endforeach; ?>
</div>
