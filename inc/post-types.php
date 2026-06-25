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
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-heart',
		'menu_position'      => 6,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'cast',
		'rewrite'            => array( 'slug' => 'cast', 'with_front' => false ),
		'template'           => array(
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
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-palmtree',
		'menu_position'      => 7,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'       => true,
		'rest_base'          => 'arc',
		'rewrite'            => array( 'slug' => 'arc', 'with_front' => false ),
	) );

	// カップル couple
	register_post_type( 'couple', array(
		'labels' => array(
			'name'          => __( 'カップル', 'kyosuki-pop' ),
			'singular_name' => __( 'カップル', 'kyosuki-pop' ),
			'menu_name'     => __( 'カップル', 'kyosuki-pop' ),
		),
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-buddicons-buddypress-logo',
		'menu_position'      => 8,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'couple',
		'rewrite'            => array( 'slug' => 'couple', 'with_front' => false ),
	) );
}
add_action( 'init', 'kyosuki_pop_register_post_types' );

/**
 * Cast / Couple のプロフィール用 post meta を REST に公開
 *
 * 運営は REST API 経由でも作成・更新するため、show_in_rest=true 必須。
 */
function kyosuki_pop_register_profile_meta() {
	$str = array(
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);

	// cast
	foreach ( array( 'name_kana', 'birthday', 'height', 'hometown', 'grade', 'agency', 'instagram', 'tiktok', 'x_url', 'appearances' ) as $key ) {
		register_post_meta( 'cast', $key, array_merge( $str, array( 'description' => $key ) ) );
	}
	// couple
	foreach ( array( 'member_a', 'member_b', 'arc_name', 'established_date', 'instagram_a', 'instagram_b' ) as $key ) {
		register_post_meta( 'couple', $key, array_merge( $str, array( 'description' => $key ) ) );
	}
}
add_action( 'init', 'kyosuki_pop_register_profile_meta', 11 );

/**
 * CPT の rewrite / has_archive 設定を確実に反映するため、テーマ更新後の
 * 一度だけ rewrite_rules をフラッシュする。
 */
function kyosuki_pop_flush_cpt_rules() {
	$flag_key   = 'kp_cpt_rules_version';
	$current    = '2'; // CPT 設定変更時はインクリメント
	if ( get_option( $flag_key ) === $current ) return;
	flush_rewrite_rules( false );
	update_option( $flag_key, $current, false );
}
add_action( 'init', 'kyosuki_pop_flush_cpt_rules', 9999 );
