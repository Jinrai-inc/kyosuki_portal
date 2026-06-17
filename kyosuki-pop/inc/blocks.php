<?php
/**
 * Register custom blocks
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function kyosuki_pop_register_blocks() {
	$blocks_dir = KYOSUKI_POP_DIR . '/blocks';
	if ( ! is_dir( $blocks_dir ) ) return;
	foreach ( scandir( $blocks_dir ) as $entry ) {
		if ( $entry === '.' || $entry === '..' ) continue;
		$path = $blocks_dir . '/' . $entry;
		if ( is_dir( $path ) && file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}

	// ブロックカテゴリを追加
	add_filter( 'block_categories_all', function( $cats ) {
		array_unshift( $cats, array(
			'slug'  => 'kyosuki',
			'title' => __( '今日好きブロック', 'kyosuki-pop' ),
			'icon'  => 'heart',
		) );
		return $cats;
	} );
}
add_action( 'init', 'kyosuki_pop_register_blocks' );
