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
 * 公開記事 / コメント / いいねの実カウントを返す
 * 重い集計はトランジェントで 10 分キャッシュ
 */
function kyosuki_pop_site_stats() {
	$cached = get_transient( 'kp_site_stats' );
	if ( is_array( $cached ) ) return $cached;

	$counts          = wp_count_posts( 'post' );
	$comments        = wp_count_comments();
	$published_total = isset( $counts->publish ) ? (int) $counts->publish : 0;

	$week_q = new WP_Query( array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'date_query'             => array( array( 'after' => '1 week ago' ) ),
		'fields'                 => 'ids',
		'posts_per_page'         => -1,
		'no_found_rows'          => false,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );

	global $wpdb;
	$likes_sum = (int) $wpdb->get_var(
		"SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = 'kp_likes'"
	);
	$views_sum = (int) $wpdb->get_var(
		"SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = 'kp_views'"
	);

	$stats = array(
		'new_posts'      => (int) $week_q->found_posts,
		'total_posts'    => $published_total,
		'total_comments' => isset( $comments->approved ) ? (int) $comments->approved : 0,
		'total_likes'    => $likes_sum,
		'total_views'    => $views_sum,
	);
	set_transient( 'kp_site_stats', $stats, 10 * MINUTE_IN_SECONDS );
	return $stats;
}

/**
 * 文字列内の {new_posts} / {total_posts} / {total_comments} / {total_likes} / {total_views}
 * を実カウントへ置換
 */
function kyosuki_pop_replace_stat_tokens( $text ) {
	if ( strpos( $text, '{' ) === false ) return $text;
	$stats = kyosuki_pop_site_stats();
	$map = array();
	foreach ( $stats as $k => $v ) {
		$map[ '{' . $k . '}' ] = number_format_i18n( (int) $v );
	}
	return strtr( $text, $map );
}

/**
 * 統計が変動したらキャッシュを破棄
 */
function kyosuki_pop_flush_stats_cache() {
	delete_transient( 'kp_site_stats' );
}
add_action( 'save_post_post', 'kyosuki_pop_flush_stats_cache' );
add_action( 'deleted_post',   'kyosuki_pop_flush_stats_cache' );
add_action( 'comment_post',   'kyosuki_pop_flush_stats_cache' );
add_action( 'edit_comment',   'kyosuki_pop_flush_stats_cache' );

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
 * ranking.php を Page Template として割り当てた固定ページを保証する。
 *
 * リライトルール方式は、サーバ環境やキャッシュ系プラグインで
 * 404 化する事故が多かったため、WordPress 標準の固定ページとして
 * 実体化する方式に変更。一度作成すれば /ranking/ が実ページに
 * なるので、どんなパーマリンク設定でも常に 200 が返る。
 */
function kyosuki_pop_ensure_ranking_page() {
	$cached = (int) get_option( 'kp_ranking_page_id' );
	if ( $cached && get_post( $cached ) && get_post_status( $cached ) === 'publish' ) {
		// 念のためテンプレ割当を維持
		if ( get_post_meta( $cached, '_wp_page_template', true ) !== 'ranking.php' ) {
			update_post_meta( $cached, '_wp_page_template', 'ranking.php' );
		}
		return $cached;
	}

	// 1) 既存ページ: ranking.php テンプレが割り当てられているもの
	$q = new WP_Query( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'ranking.php',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	if ( ! empty( $q->posts ) ) {
		$id = (int) $q->posts[0];
		if ( get_post_status( $id ) !== 'publish' ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
		update_option( 'kp_ranking_page_id', $id, false );
		return $id;
	}

	// 2) slug 'ranking' が空いていれば、その slug で作成
	$existing = get_page_by_path( 'ranking', OBJECT, 'page' );
	$desired_slug = $existing ? 'ranking-list' : 'ranking';

	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'ランキング',
		'post_name'    => $desired_slug,
		'post_content' => '',
		'meta_input'   => array( '_wp_page_template' => 'ranking.php' ),
	), true );

	if ( $id && ! is_wp_error( $id ) ) {
		update_option( 'kp_ranking_page_id', (int) $id, false );
		return (int) $id;
	}
	return 0;
}
add_action( 'after_switch_theme', 'kyosuki_pop_ensure_ranking_page' );
add_action( 'admin_init',         'kyosuki_pop_ensure_ranking_page' );

/**
 * ランキングページのURL。固定ページ ID が無ければ home に戻すフォールバック付き。
 */
function kyosuki_pop_ranking_url() {
	$id = kyosuki_pop_ensure_ranking_page();
	if ( $id ) {
		$url = get_permalink( $id );
		if ( $url ) return $url;
	}
	return home_url( '/' );
}

/**
 * 旧バージョン (1.6.1〜1.6.3) で登録した /ranking/ リライトルール
 * + kp_ranking_page_id 未設定の環境を一度だけクリーンアップ。
 */
function kyosuki_pop_cleanup_legacy_ranking() {
	if ( get_option( 'kp_ranking_cleanup_v17' ) ) return;
	flush_rewrite_rules( false );
	kyosuki_pop_ensure_ranking_page();
	update_option( 'kp_ranking_cleanup_v17', 1, false );
}
add_action( 'init', 'kyosuki_pop_cleanup_legacy_ranking', 9999 );

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
