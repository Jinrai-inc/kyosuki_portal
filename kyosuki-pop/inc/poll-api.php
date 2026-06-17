<?php
/**
 * Kyosuki Pop Poll API
 *
 * カップル投票の集計を wp_options テーブルに保存し、
 * REST API (/wp-json/kyosuki-pop/v1/poll) 経由でフロントから操作。
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Kyosuki_Pop_Poll {

	const OPT_PREFIX  = 'kp_poll_votes_';   // wp_options に保存するキー
	const TRANSIENT   = 'kp_poll_ip_';      // 同一IP連投ガード用
	const COOLDOWN    = DAY_IN_SECONDS;     // 1日1票

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'admin_menu',    array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_kp_poll_export', array( __CLASS__, 'export_csv' ) );
		add_action( 'admin_post_kp_poll_reset',  array( __CLASS__, 'reset_votes' ) );
	}

	/**
	 * 投票キー (カスタマイザーの設定文字列から一意ハッシュを生成)
	 */
	public static function get_poll_id() {
		$opts = (string) get_theme_mod( 'kp_poll_options', '' );
		return substr( md5( $opts ), 0, 12 );
	}

	/**
	 * 現在の投票数を取得 (連想配列 idx => count)
	 */
	public static function get_votes( $poll_id = null ) {
		$poll_id = $poll_id ?: self::get_poll_id();
		$votes   = get_option( self::OPT_PREFIX . $poll_id, array() );
		return is_array( $votes ) ? $votes : array();
	}

	/**
	 * 選択肢一覧 (idx => array(name, base_pct))
	 */
	public static function get_options_list() {
		$raw   = get_theme_mod( 'kp_poll_options', "りく♡みお|78\nゆうた♡あい|54\nけんと♡なな|32\nそら♡まりん|21" );
		$lines = preg_split( "/\r\n|\r|\n/", trim( $raw ) );
		$out   = array();
		foreach ( $lines as $i => $line ) {
			$parts = explode( '|', $line, 2 );
			$name  = trim( $parts[0] ?? '' );
			$base  = isset( $parts[1] ) ? (int) trim( $parts[1] ) : 0;
			if ( $name !== '' ) $out[ $i ] = array( 'name' => $name, 'base' => $base );
		}
		return $out;
	}

	/**
	 * 結果を ％ 配列にして返す (実投票 + 初期値ベース)
	 */
	public static function calculate_percentages() {
		$opts  = self::get_options_list();
		$votes = self::get_votes();
		// 各選択肢の「実数」= base_pct(基礎値) + actual_votes
		$totals = array();
		$sum    = 0;
		foreach ( $opts as $i => $o ) {
			$v = (int) ( $votes[ $i ] ?? 0 );
			$totals[ $i ] = max( 1, $o['base'] + $v ); // base_pct を初期票数として扱う
			$sum += $totals[ $i ];
		}
		$pct = array();
		foreach ( $totals as $i => $t ) {
			$pct[ $i ] = round( ( $t / $sum ) * 100 );
		}
		return array(
			'percentages' => $pct,
			'votes'       => $votes,
			'totals'      => $totals,
			'sum'         => $sum,
		);
	}

	/**
	 * REST routes
	 */
	public static function register_routes() {
		register_rest_route( 'kyosuki-pop/v1', '/poll', array(
			array(
				'methods'  => 'GET',
				'callback' => array( __CLASS__, 'rest_get' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'  => 'POST',
				'callback' => array( __CLASS__, 'rest_vote' ),
				'permission_callback' => '__return_true',
				'args'     => array(
					'option' => array( 'required' => true, 'type' => 'integer', 'sanitize_callback' => 'absint' ),
				),
			),
		) );
	}

	public static function rest_get( $request ) {
		$data = self::calculate_percentages();
		$opts = self::get_options_list();
		$out  = array(
			'poll_id' => self::get_poll_id(),
			'options' => array(),
		);
		foreach ( $opts as $i => $o ) {
			$out['options'][] = array(
				'index' => $i,
				'name'  => $o['name'],
				'pct'   => (int) ( $data['percentages'][ $i ] ?? 0 ),
				'votes' => (int) ( $data['votes'][ $i ] ?? 0 ),
			);
		}
		return rest_ensure_response( $out );
	}

	public static function rest_vote( $request ) {
		$ip = self::client_ip();
		$poll_id = self::get_poll_id();
		$tkey = self::TRANSIENT . $poll_id . '_' . md5( $ip );
		if ( get_transient( $tkey ) ) {
			return new WP_Error( 'kp_already_voted', '既に投票済みです', array( 'status' => 429 ) );
		}

		$idx = (int) $request->get_param( 'option' );
		$opts = self::get_options_list();
		if ( ! isset( $opts[ $idx ] ) ) {
			return new WP_Error( 'kp_invalid_option', '無効な選択肢', array( 'status' => 400 ) );
		}

		$votes = self::get_votes( $poll_id );
		$votes[ $idx ] = (int) ( $votes[ $idx ] ?? 0 ) + 1;
		update_option( self::OPT_PREFIX . $poll_id, $votes, false );

		set_transient( $tkey, 1, self::COOLDOWN );

		return self::rest_get( $request );
	}

	protected static function client_ip() {
		$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		foreach ( $keys as $k ) {
			if ( ! empty( $_SERVER[ $k ] ) ) {
				$ip = explode( ',', $_SERVER[ $k ] )[0];
				return trim( $ip );
			}
		}
		return '0.0.0.0';
	}

	/* ===== Admin: 集計画面 + CSV ===== */
	public static function admin_menu() {
		add_submenu_page( 'themes.php', __( 'カップル投票 集計', 'kyosuki-pop' ), __( 'カップル投票 集計', 'kyosuki-pop' ), 'manage_options', 'kp-poll', array( __CLASS__, 'admin_page' ) );
	}

	public static function admin_page() {
		$data = self::calculate_percentages();
		$opts = self::get_options_list();
		echo '<div class="wrap"><h1>カップル投票 集計</h1>';
		echo '<p>投票キー: <code>' . esc_html( self::get_poll_id() ) . '</code> ／ 総投票数: <strong>' . (int) array_sum( $data['votes'] ) . '</strong></p>';
		echo '<table class="widefat striped"><thead><tr><th>選択肢</th><th>投票数</th><th>％</th></tr></thead><tbody>';
		foreach ( $opts as $i => $o ) {
			printf(
				'<tr><td>%s</td><td>%d</td><td>%d%%</td></tr>',
				esc_html( $o['name'] ),
				(int) ( $data['votes'][ $i ] ?? 0 ),
				(int) ( $data['percentages'][ $i ] ?? 0 )
			);
		}
		echo '</tbody></table>';

		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=kp_poll_export' ), 'kp_poll_export' );
		$reset_url  = wp_nonce_url( admin_url( 'admin-post.php?action=kp_poll_reset' ),  'kp_poll_reset' );
		echo '<p style="margin-top:20px;"><a href="' . esc_url( $export_url ) . '" class="button button-primary">CSV ダウンロード</a> ';
		echo '<a href="' . esc_url( $reset_url ) . '" class="button button-secondary" onclick="return confirm(\'本当にリセットしますか？\');">投票結果リセット</a></p>';
		echo '</div>';
	}

	public static function export_csv() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_export' ) ) {
			wp_die( '権限がありません' );
		}
		$opts = self::get_options_list();
		$data = self::calculate_percentages();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="kp-poll-' . date( 'Ymd-Hi' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputs( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( '選択肢', '投票数', 'パーセント' ) );
		foreach ( $opts as $i => $o ) {
			fputcsv( $out, array( $o['name'], (int) ( $data['votes'][ $i ] ?? 0 ), (int) ( $data['percentages'][ $i ] ?? 0 ) . '%' ) );
		}
		fclose( $out );
		exit;
	}

	public static function reset_votes() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_reset' ) ) {
			wp_die( '権限がありません' );
		}
		delete_option( self::OPT_PREFIX . self::get_poll_id() );
		wp_safe_redirect( admin_url( 'themes.php?page=kp-poll&reset=1' ) );
		exit;
	}
}
Kyosuki_Pop_Poll::init();
