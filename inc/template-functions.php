<?php
/**
 * Template helper functions
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * ジャンル別カラー (Y2Kポップ)
 */
function kyosuki_pop_genre_color( $slug = '' ) {
	$map = array(
		'密着'   => '#FF7AC6',
		'コーデ' => '#5AC8FA',
		'pr'     => '#FFE066',
		'PR'     => '#FFE066',
		'考察'   => '#9B5DE5',
		'ネタバレ' => '#C9F28A',
		'ロケ地' => '#E83E8C',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '#FF7AC6';
}

/**
 * 投稿の代表ジャンルを返す
 */
function kyosuki_pop_get_primary_genre( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$terms = get_the_terms( $post_id, 'kp_genre' );
	if ( ! $terms || is_wp_error( $terms ) ) return null;
	return $terms[0];
}

/**
 * 投稿の代表アークを返す
 */
function kyosuki_pop_get_primary_arc( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$terms = get_the_terms( $post_id, 'kp_arc' );
	if ( ! $terms || is_wp_error( $terms ) ) return null;
	return $terms[0];
}

/**
 * 人気記事 (PV順 / フォールバック=コメント数)
 */
function kyosuki_pop_get_popular_posts( $limit = 5, $post_type = 'post' ) {
	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $limit,
		'meta_key'       => 'kp_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	);
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		$q = new WP_Query( array( 'post_type' => $post_type, 'posts_per_page' => $limit, 'orderby' => 'comment_count', 'order' => 'DESC' ) );
	}
	return $q;
}

/**
 * PV カウンタ (シングル表示時にカウント)
 */
function kyosuki_pop_count_views() {
	if ( ! is_singular() || is_admin() || is_preview() ) return;
	$id = get_queried_object_id();
	$views = (int) get_post_meta( $id, 'kp_views', true );
	update_post_meta( $id, 'kp_views', $views + 1 );
}
add_action( 'template_redirect', 'kyosuki_pop_count_views' );

/**
 * SCOOPプレースホルダ画像 (グラデ+♡)
 */
function kyosuki_pop_placeholder_url( $seed = 0 ) {
	$colors = array( '%23FF7AC6', '%235AC8FA', '%23FFE066', '%239B5DE5', '%23C9F28A', '%23E83E8C' );
	$c = $colors[ $seed % count( $colors ) ];
	$svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'><rect width='600' height='400' fill='{$c}'/><text x='300' y='240' font-size='160' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900'>♡</text></svg>";
	return 'data:image/svg+xml;utf8,' . $svg;
}

/**
 * カラーチップ HTML
 */
function kyosuki_pop_genre_chip( $term ) {
	if ( ! $term ) return '';
	$color = kyosuki_pop_genre_color( $term->name );
	return sprintf(
		'<a class="kp-chip" style="background:%s" href="%s">#%s</a>',
		esc_attr( $color ),
		esc_url( get_term_link( $term ) ),
		esc_html( $term->name )
	);
}
