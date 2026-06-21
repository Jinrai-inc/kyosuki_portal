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

/**
 * ターム編集画面に「色」「絵文字」フィールドを追加。
 * 対象タクソノミー: kp_arc / kp_genre / kp_status
 */
function kyosuki_pop_term_customizable_taxonomies() {
	return array( 'kp_arc', 'kp_genre', 'kp_status' );
}

function kyosuki_pop_term_add_color_fields() {
	?>
	<div class="form-field">
		<label for="kp_color">★ 表示色</label>
		<input type="color" name="kp_color" id="kp_color" value="#FF7AC6" />
		<p>このタグを表示するときの背景色。サイト全体（チップ・サイドバーなど）に反映されます。</p>
	</div>
	<div class="form-field">
		<label for="kp_emoji">★ 絵文字 / アイコン</label>
		<input type="text" name="kp_emoji" id="kp_emoji" value="" maxlength="4" placeholder="🏝 など" />
		<p>このタグの横に小さく表示される絵文字。空欄でもOK。</p>
	</div>
	<?php
}

function kyosuki_pop_term_edit_color_fields( $term ) {
	$color = get_term_meta( $term->term_id, 'kp_color', true );
	$emoji = get_term_meta( $term->term_id, 'kp_emoji', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="kp_color">★ 表示色</label></th>
		<td>
			<input type="color" name="kp_color" id="kp_color" value="<?php echo esc_attr( $color ?: '#FF7AC6' ); ?>" />
			<?php if ( $color ) : ?>
				<button type="button" class="button button-small" onclick="document.getElementById('kp_color').value='';this.parentNode.removeChild(this);">色をリセット</button>
			<?php endif; ?>
			<p class="description">サイト全体（チップ・サイドバー・記事カード）でこのタグに使われる背景色。</p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="kp_emoji">★ 絵文字 / アイコン</label></th>
		<td>
			<input type="text" name="kp_emoji" id="kp_emoji" value="<?php echo esc_attr( $emoji ); ?>" maxlength="4" placeholder="🏝 など" style="width:100px;" />
			<p class="description">このタグの横に小さく表示される絵文字（最大 4 文字）。空欄でもOK。</p>
		</td>
	</tr>
	<?php
}

function kyosuki_pop_save_term_meta( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) return;
	if ( isset( $_POST['kp_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['kp_color'] ) );
		if ( $color ) {
			update_term_meta( $term_id, 'kp_color', $color );
		} else {
			delete_term_meta( $term_id, 'kp_color' );
		}
	}
	if ( isset( $_POST['kp_emoji'] ) ) {
		$emoji = sanitize_text_field( wp_unslash( $_POST['kp_emoji'] ) );
		$emoji = function_exists( 'mb_substr' ) ? mb_substr( $emoji, 0, 4 ) : substr( $emoji, 0, 12 );
		if ( $emoji !== '' ) {
			update_term_meta( $term_id, 'kp_emoji', $emoji );
		} else {
			delete_term_meta( $term_id, 'kp_emoji' );
		}
	}
}

function kyosuki_pop_register_term_meta_hooks() {
	foreach ( kyosuki_pop_term_customizable_taxonomies() as $tax ) {
		add_action( $tax . '_add_form_fields',  'kyosuki_pop_term_add_color_fields' );
		add_action( $tax . '_edit_form_fields', 'kyosuki_pop_term_edit_color_fields' );
		add_action( 'created_' . $tax,          'kyosuki_pop_save_term_meta' );
		add_action( 'edited_'  . $tax,          'kyosuki_pop_save_term_meta' );

		// term meta を REST 経由でも読めるように登録
		register_term_meta( $tax, 'kp_color', array(
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
		) );
		register_term_meta( $tax, 'kp_emoji', array(
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
		) );
	}
}
add_action( 'init', 'kyosuki_pop_register_term_meta_hooks', 25 );

/**
 * ターム一覧テーブルに「色」プレビュー列を追加（kp_arc / kp_genre / kp_status）。
 */
function kyosuki_pop_term_admin_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( $k === 'name' ) {
			$new['kp_color_preview'] = __( '色 / 絵文字', 'kyosuki-pop' );
		}
	}
	return $new;
}

function kyosuki_pop_term_admin_column_content( $content, $column, $term_id ) {
	if ( $column !== 'kp_color_preview' ) return $content;
	$color = get_term_meta( $term_id, 'kp_color', true ) ?: '#EFE7FF';
	$emoji = get_term_meta( $term_id, 'kp_emoji', true );
	return sprintf(
		'<span style="display:inline-block;background:%s;border:2px solid #1A0B3D;border-radius:999px;padding:2px 10px;font-weight:900;font-size:11px;">%s%s</span>',
		esc_attr( $color ),
		$emoji ? esc_html( $emoji ) . ' ' : '',
		'sample'
	);
}

function kyosuki_pop_register_term_admin_columns() {
	foreach ( kyosuki_pop_term_customizable_taxonomies() as $tax ) {
		add_filter( 'manage_edit-' . $tax . '_columns',  'kyosuki_pop_term_admin_columns' );
		add_filter( 'manage_' . $tax . '_custom_column', 'kyosuki_pop_term_admin_column_content', 10, 3 );
	}
}
add_action( 'admin_init', 'kyosuki_pop_register_term_admin_columns' );
