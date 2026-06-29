<?php
/**
 * Kyosuki Pop — Theme Functions
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'KYOSUKI_POP_VERSION', '1.13.0' );
define( 'KYOSUKI_POP_DIR', get_template_directory() );
define( 'KYOSUKI_POP_URI', get_template_directory_uri() );

/**
 * Theme setup
 */
function kyosuki_pop_setup() {
	load_theme_textdomain( 'kyosuki-pop', KYOSUKI_POP_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 80,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_editor_style( array( 'assets/css/editor.css' ) );

	register_nav_menus( array(
		'primary' => __( 'グローバルメニュー', 'kyosuki-pop' ),
		'mobile'  => __( 'モバイルメニュー', 'kyosuki-pop' ),
		'footer'  => __( 'フッターメニュー', 'kyosuki-pop' ),
	) );
}
add_action( 'after_setup_theme', 'kyosuki_pop_setup' );

/**
 * Enqueue front-end assets
 */
function kyosuki_pop_enqueue_assets() {
	// LINE Seed JP — 本文・UI 全般のメインフォント
	wp_enqueue_style(
		'kyosuki-pop-fonts',
		'https://fonts.googleapis.com/css2?family=LINE+Seed+JP:wght@400;700;900&family=Reggae+One&display=swap',
		array(),
		KYOSUKI_POP_VERSION
	);

	wp_enqueue_style(
		'kyosuki-pop-style',
		KYOSUKI_POP_URI . '/assets/css/theme.css',
		array(),
		KYOSUKI_POP_VERSION
	);

	wp_enqueue_script(
		'kyosuki-pop-script',
		KYOSUKI_POP_URI . '/assets/js/theme.js',
		array(),
		KYOSUKI_POP_VERSION,
		true
	);

	wp_localize_script( 'kyosuki-pop-script', 'KP_POLL', array(
		'restUrl' => esc_url_raw( rest_url( 'kyosuki-pop/v1/poll' ) ),
		'likeUrl' => esc_url_raw( rest_url( 'kyosuki-pop/v1/like/' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'kyosuki_pop_enqueue_assets' );

/**
 * Editor assets
 */
function kyosuki_pop_enqueue_editor_assets() {
	wp_enqueue_style(
		'kyosuki-pop-fonts',
		'https://fonts.googleapis.com/css2?family=LINE+Seed+JP:wght@400;700;900&family=Reggae+One&display=swap',
		array(),
		KYOSUKI_POP_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'kyosuki_pop_enqueue_editor_assets' );

/**
 * Includes
 */
/**
 * パフォーマンス最適化: script defer / asset preload / emoji除外
 */
function kyosuki_pop_perf_init() {
	// 絵文字スクリプト除去
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	// 不要な oEmbed/RSD/WLW
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'kyosuki_pop_perf_init' );

function kyosuki_pop_defer_scripts( $tag, $handle ) {
	if ( $handle === 'kyosuki-pop-script' ) {
		return str_replace( '<script ', '<script defer ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'kyosuki_pop_defer_scripts', 10, 2 );

function kyosuki_pop_preload_assets() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
}
add_action( 'wp_head', 'kyosuki_pop_preload_assets', 1 );

require KYOSUKI_POP_DIR . '/inc/post-types.php';
require KYOSUKI_POP_DIR . '/inc/taxonomies.php';
require KYOSUKI_POP_DIR . '/inc/blocks.php';
require KYOSUKI_POP_DIR . '/inc/patterns.php';
require KYOSUKI_POP_DIR . '/inc/template-functions.php';
require KYOSUKI_POP_DIR . '/inc/widgets.php';
require KYOSUKI_POP_DIR . '/inc/customizer.php';
require KYOSUKI_POP_DIR . '/inc/poll-cpt.php';
require KYOSUKI_POP_DIR . '/inc/poll-api.php';
require KYOSUKI_POP_DIR . '/inc/like-api.php';
