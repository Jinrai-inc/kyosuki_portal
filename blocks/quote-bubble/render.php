<?php
/**
 * Render: kyosuki/quote-bubble
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$tone_map = array( 'pink' => '#FF7AC6', 'blue' => '#5AC8FA', 'yellow' => '#FFE066', 'purple' => '#9B5DE5', 'lime' => '#C9F28A' );
$tone = $tone_map[ $attributes['tone'] ] ?? '#FFE066';
$wrapper = get_block_wrapper_attributes( array( 'class' => 'kp-bubble', 'style' => 'box-shadow:4px 4px 0 ' . $tone . ';position:relative;background:#fff;border:2px solid #1A0B3D;border-radius:14px;padding:18px 16px 14px;margin:14px 0;' ) );
?>
<div <?php echo $wrapper; ?>>
	<span style="position:absolute;top:-10px;left:14px;background:#1A0B3D;color:#FFE066;padding:3px 10px;border-radius:8px;font-size:10px;font-weight:900;">♡ <?php echo esc_html( $attributes['speaker'] ); ?>のコメント</span>
	<p style="font-style:italic;font-size:14px;line-height:1.8;margin:0;">「<?php echo esc_html( $attributes['quote'] ); ?>」</p>
</div>
