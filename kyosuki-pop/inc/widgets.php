<?php
/**
 * Sidebar widget areas (Classic Widgets fallback)
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function kyosuki_pop_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'サイドバー', 'kyosuki-pop' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="kp-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="kp-widget__title">★ ',
		'after_title'   => ' ★</h3>',
	) );
}
add_action( 'widgets_init', 'kyosuki_pop_widgets_init' );
