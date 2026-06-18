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
	const COOLDOWN  = DAY_IN_SECONDS;

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
			return new WP_Error( 'kp_no_poll', '投票が設定されていません', array( 'status' => 404 ) );
		}
		// 投票はアクティブなラウンドにのみ許可
		if ( Kyosuki_Pop_Poll_CPT::get_active_id() !== (int) $id ) {
			return new WP_Error( 'kp_archived', 'このラウンドは終了しています', array( 'status' => 410 ) );
		}

		$ip   = self::client_ip();
		$tkey = self::TRANSIENT . $id . '_' . md5( $ip );
		if ( get_transient( $tkey ) ) {
			return new WP_Error( 'kp_already_voted', '既に投票済みです', array( 'status' => 429 ) );
		}

		$hash  = (string) $request->get_param( 'option_hash' );
		$opts  = Kyosuki_Pop_Poll_CPT::options_list( $id );
		$valid = false;
		foreach ( $opts as $o ) {
			if ( hash_equals( $o['hash'], $hash ) ) { $valid = true; break; }
		}
		if ( ! $valid ) {
			return new WP_Error( 'kp_invalid_option', '無効な選択肢', array( 'status' => 400 ) );
		}

		Kyosuki_Pop_Poll_CPT::increment_vote( $id, $hash );
		set_transient( $tkey, 1, self::COOLDOWN );

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
}
Kyosuki_Pop_Poll::init();
