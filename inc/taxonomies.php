<?php
/**
 * Custom Taxonomies
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function kyosuki_pop_register_taxonomies() {

	// アーク (沖縄編 / 北海道編 等) — post + couple + cast
	register_taxonomy( 'kp_arc', array( 'post', 'couple', 'cast' ), array(
		'labels' => array(
			'name'          => __( 'アーク', 'kyosuki-pop' ),
			'singular_name' => __( 'アーク', 'kyosuki-pop' ),
			'menu_name'     => __( 'アーク', 'kyosuki-pop' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'arc-tax' ),
	) );

	// ステータス (成立/交際/破局/進行中) — couple
	register_taxonomy( 'kp_status', array( 'couple' ), array(
		'labels' => array(
			'name'          => __( 'ステータス', 'kyosuki-pop' ),
			'singular_name' => __( 'ステータス', 'kyosuki-pop' ),
			'menu_name'     => __( 'ステータス', 'kyosuki-pop' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'status' ),
	) );

	// ジャンル (密着/コーデ/PR/考察) — post
	register_taxonomy( 'kp_genre', array( 'post' ), array(
		'labels' => array(
			'name'          => __( 'ジャンル', 'kyosuki-pop' ),
			'singular_name' => __( 'ジャンル', 'kyosuki-pop' ),
			'menu_name'     => __( 'ジャンル', 'kyosuki-pop' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'genre' ),
	) );
}
add_action( 'init', 'kyosuki_pop_register_taxonomies' );

/**
 * 既定タームの自動投入(初回有効化時)
 */
function kyosuki_pop_seed_terms() {
	$option_key = 'kyosuki_pop_seeded';
	if ( get_option( $option_key ) ) {
		return;
	}
	$arcs = array( '沖縄編', '北海道編', '修学旅行', '放課後', '文化祭', '夏休み', '春休み', '卒業編' );
	foreach ( $arcs as $a ) {
		if ( ! term_exists( $a, 'kp_arc' ) ) wp_insert_term( $a, 'kp_arc' );
	}
	$statuses = array( '成立', '交際', '破局', '進行中' );
	foreach ( $statuses as $s ) {
		if ( ! term_exists( $s, 'kp_status' ) ) wp_insert_term( $s, 'kp_status' );
	}
	$genres = array( '密着', 'コーデ', 'PR', '考察', 'ネタバレ', 'ロケ地' );
	foreach ( $genres as $g ) {
		if ( ! term_exists( $g, 'kp_genre' ) ) wp_insert_term( $g, 'kp_genre' );
	}
	update_option( $option_key, 1 );
}
add_action( 'init', 'kyosuki_pop_seed_terms', 20 );
