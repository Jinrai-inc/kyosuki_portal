<?php
/**
 * Custom Post Types
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function kyosuki_pop_register_post_types() {

	// 出演者 cast
	register_post_type( 'cast', array(
		'labels' => array(
			'name'               => __( '出演者', 'kyosuki-pop' ),
			'singular_name'      => __( '出演者', 'kyosuki-pop' ),
			'add_new'            => __( '新規追加', 'kyosuki-pop' ),
			'add_new_item'       => __( '新しい出演者を追加', 'kyosuki-pop' ),
			'edit_item'          => __( '出演者を編集', 'kyosuki-pop' ),
			'all_items'          => __( '出演者一覧', 'kyosuki-pop' ),
			'menu_name'          => __( '出演者', 'kyosuki-pop' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-heart',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'  => true,
		'rewrite'       => array( 'slug' => 'cast' ),
		'template'      => array(
			array( 'kyosuki/cast-profile' ),
			array( 'core/heading', array( 'level' => 2, 'content' => __( 'プロフィール', 'kyosuki-pop' ) ) ),
		),
	) );

	// アーク arc (シーズン)
	register_post_type( 'arc', array(
		'labels' => array(
			'name'          => __( 'アーク', 'kyosuki-pop' ),
			'singular_name' => __( 'アーク', 'kyosuki-pop' ),
			'menu_name'     => __( 'アーク', 'kyosuki-pop' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-palmtree',
		'menu_position' => 7,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
		'rewrite'       => array( 'slug' => 'arc' ),
	) );

	// カップル couple
	register_post_type( 'couple', array(
		'labels' => array(
			'name'          => __( 'カップル', 'kyosuki-pop' ),
			'singular_name' => __( 'カップル', 'kyosuki-pop' ),
			'menu_name'     => __( 'カップル', 'kyosuki-pop' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-buddicons-buddypress-logo',
		'menu_position' => 8,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'  => true,
		'rewrite'       => array( 'slug' => 'couple' ),
	) );
}
add_action( 'init', 'kyosuki_pop_register_post_types' );
