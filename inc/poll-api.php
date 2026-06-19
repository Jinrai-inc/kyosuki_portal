<?php
/**
 * Kyosuki Pop — Poll REST API
 *
 * 投票本体のデータ管理は Kyosuki_Pop_Poll_CPT（inc/poll-cpt.php）が担う。
 * 本ファイルはフロント JS との橋渡しになる REST エンドポイント
 * /wp-json/kyosuki-pop/v1/poll を提供する。
 *
 *  GET  /poll?poll_id=NN   現在ラウンド or 指定ラウンドの結果
 *  POST /poll {poll_id, option_hash}   投票を 1 票加算
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Kyosuki_Pop_Poll {

	const TRANSIENT = 'kp_poll_ip_';
	const COOLDOWN  = HOUR_IN_SECONDS;        // 同一 IP からの連投ガード（1 時間）
	const LOG_OPT   = 'kp_poll_recent_log';   // 直近 20 件の投票ログ
	const LOG_MAX   = 20;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/* ===== back-compat thin wrappers (used by templates) ===== */

	public static function get_active_poll_id() {
		return Kyosuki_Pop_Poll_CPT::get_active_id();
	}

	public static function active_poll() {
		$id = self::get_active_poll_id();
		return $id ? get_post( $id ) : null;
	}

	public static function get_options_list( $post_id = 0 ) {
		$post_id = $post_id ?: self::get_active_poll_id();
		return $post_id ? Kyosuki_Pop_Poll_CPT::options_list( $post_id ) : array();
	}

	public static function calculate_percentages( $post_id = 0 ) {
		$post_id = $post_id ?: self::get_active_poll_id();
		if ( ! $post_id ) {
			return array( 'options' => array(), 'percentages' => array(), 'votes' => array(), 'sum' => 0 );
		}
		return Kyosuki_Pop_Poll_CPT::calculate( $post_id );
	}

	public static function has_options() {
		$id = self::get_active_poll_id();
		return $id && ! empty( Kyosuki_Pop_Poll_CPT::options_list( $id ) );
	}

	/* =========================================================
	 * REST routes
	 * ========================================================= */

	public static function register_routes() {
		register_rest_route( 'kyosuki-pop/v1', '/poll', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_get' ),
				'permission_callback' => '__return_true',
				'args' => array(
					'poll_id' => array( 'required' => false, 'type' => 'integer', 'sanitize_callback' => 'absint' ),
				),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_vote' ),
				'permission_callback' => '__return_true',
				'args' => array(
					'poll_id'     => array( 'required' => false, 'type' => 'integer', 'sanitize_callback' => 'absint' ),
					'option_hash' => array( 'required' => true,  'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
				),
			),
		) );
	}

	protected static function resolve_poll_id( $request ) {
		$id = (int) $request->get_param( 'poll_id' );
		if ( ! $id ) {
			$id = self::get_active_poll_id();
		}
		return $id;
	}

	public static function rest_get( $request ) {
		$id = self::resolve_poll_id( $request );
		if ( ! $id ) {
			return new WP_Error( 'kp_no_poll', '投票が設定されていません', array( 'status' => 404 ) );
		}
		$data = Kyosuki_Pop_Poll_CPT::calculate( $id );
		$out  = array(
			'poll_id' => $id,
			'options' => array(),
		);
		foreach ( $data['options'] as $o ) {
			$out['options'][] = array(
				'hash'  => $o['hash'],
				'name'  => $o['name'],
				'pct'   => (int) ( $data['percentages'][ $o['hash'] ] ?? 0 ),
				'votes' => (int) ( $data['votes'][ $o['hash'] ] ?? 0 ),
			);
		}
		return rest_ensure_response( $out );
	}

	public static function rest_vote( $request ) {
		$id = self::resolve_poll_id( $request );
		if ( ! $id ) {
			self::log( 0, '', '', 'kp_no_poll' );
			return new WP_Error( 'kp_no_poll', '投票が設定されていません', array( 'status' => 404 ) );
		}
		// 投票はアクティブなラウンドにのみ許可
		if ( Kyosuki_Pop_Poll_CPT::get_active_id() !== (int) $id ) {
			self::log( $id, '', '', 'kp_archived' );
			return new WP_Error( 'kp_archived', 'このラウンドは終了しています', array( 'status' => 410 ) );
		}

		$ip       = self::client_ip();
		$ip_hash  = md5( $ip );
		$tkey     = self::TRANSIENT . $id . '_' . $ip_hash;
		// 管理者でログイン中はクールダウンをスキップ（運営者の検証用）
		$is_admin = current_user_can( 'manage_options' );

		if ( ! $is_admin && get_transient( $tkey ) ) {
			self::log( $id, '', $ip_hash, 'kp_already_voted' );
			return new WP_Error( 'kp_already_voted', '同じ回線から既に投票されています。1 時間ほど経ってから再度お試しください。', array( 'status' => 429 ) );
		}

		$hash  = (string) $request->get_param( 'option_hash' );
		$opts  = Kyosuki_Pop_Poll_CPT::options_list( $id );
		$valid_name = '';
		foreach ( $opts as $o ) {
			if ( hash_equals( $o['hash'], $hash ) ) { $valid_name = $o['name']; break; }
		}
		if ( $valid_name === '' ) {
			self::log( $id, $hash, $ip_hash, 'kp_invalid_option' );
			return new WP_Error( 'kp_invalid_option', '無効な選択肢', array( 'status' => 400 ) );
		}

		Kyosuki_Pop_Poll_CPT::increment_vote( $id, $hash );
		if ( ! $is_admin ) {
			set_transient( $tkey, 1, self::COOLDOWN );
		}
		self::log( $id, $valid_name, $ip_hash, $is_admin ? 'ok (admin)' : 'ok' );

		return self::rest_get( $request );
	}

	protected static function client_ip() {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $k ) {
			if ( ! empty( $_SERVER[ $k ] ) ) {
				return trim( explode( ',', $_SERVER[ $k ] )[0] );
			}
		}
		return '0.0.0.0';
	}

	/* =========================================================
	 * Diagnostic log（最新 LOG_MAX 件をリングバッファ的に保持）
	 * ========================================================= */

	protected static function log( $poll_id, $option_name, $ip_hash, $result ) {
		$entry = array(
			'time'    => current_time( 'mysql' ),
			'poll_id' => (int) $poll_id,
			'option'  => (string) $option_name,
			'ip'      => substr( (string) $ip_hash, 0, 8 ),
			'result'  => (string) $result,
		);
		$log = get_option( self::LOG_OPT, array() );
		if ( ! is_array( $log ) ) $log = array();
		array_unshift( $log, $entry );
		if ( count( $log ) > self::LOG_MAX ) {
			$log = array_slice( $log, 0, self::LOG_MAX );
		}
		update_option( self::LOG_OPT, $log, false );
	}

	public static function get_log() {
		$log = get_option( self::LOG_OPT, array() );
		return is_array( $log ) ? $log : array();
	}

	public static function clear_log() {
		delete_option( self::LOG_OPT );
	}
}
Kyosuki_Pop_Poll::init();
