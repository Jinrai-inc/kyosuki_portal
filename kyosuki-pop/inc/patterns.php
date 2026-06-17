<?php
/**
 * Block Patterns registry
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function kyosuki_pop_register_pattern_categories() {
	register_block_pattern_category( 'kyosuki', array( 'label' => __( '今日好きパターン', 'kyosuki-pop' ) ) );
	register_block_pattern_category( 'kyosuki-hero', array( 'label' => __( 'ヒーロー', 'kyosuki-pop' ) ) );
	register_block_pattern_category( 'kyosuki-list', array( 'label' => __( 'リスト/ランキング', 'kyosuki-pop' ) ) );
	register_block_pattern_category( 'kyosuki-pr', array( 'label' => __( 'PR/プロモ', 'kyosuki-pop' ) ) );
}
add_action( 'init', 'kyosuki_pop_register_pattern_categories' );
