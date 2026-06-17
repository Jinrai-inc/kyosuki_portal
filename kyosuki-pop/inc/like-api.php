<?php
/**
 * Kyosuki Pop Like API
 *
 * 記事のいいね数を post_meta kp_likes に保存。
 * REST API: GET/POST /wp-json/kyosuki-pop/v1/like/{post_id}
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Kyosuki_Pop_Like {

	const META_KEY = 'kp_likes';
	const TRANSIENT = 'kp_like_ip_';
	const COOLDOWN  = DAY_IN_SECONDS;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route( 'kyosuki-pop/v1', '/like/(?P<id>\d+)', array(
			array(
				'methods'  => 'GET',
				'callback' => array( __CLASS__, 'rest_get' ),
				'permission_callback' => '__return_true',
				'args' => array( 'id' => array( 'sanitize_callback' => 'absint' ) ),
			),
			array(
				'methods'  => 'POST',
				'callback' => array( __CLASS__, 'rest_like' ),
				'permission_callback' => '__return_true',
				'args' => array( 'id' => array( 'sanitize_callback' => 'absint' ) ),
			),
		) );
	}

	public static function rest_get( $req ) {
		$pid = (int) $req['id'];
		if ( ! get_post( $pid ) ) {
			return new WP_Error( 'kp_invalid', '投稿が存在しません', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array(
			'id'    => $pid,
			'likes' => (int) get_post_meta( $pid, self::META_KEY, true ),
		) );
	}

	public static function rest_like( $req ) {
		$pid = (int) $req['id'];
		if ( ! get_post( $pid ) ) {
			return new WP_Error( 'kp_invalid', '投稿が存在しません', array( 'status' => 404 ) );
		}
		$ip = self::client_ip();
		$tkey = self::TRANSIENT . $pid . '_' . md5( $ip );
		if ( get_transient( $tkey ) ) {
			return new WP_Error( 'kp_already_liked', '既にいいね済みです', array( 'status' => 429 ) );
		}
		$cur = (int) get_post_meta( $pid, self::META_KEY, true );
		$cur++;
		update_post_meta( $pid, self::META_KEY, $cur );
		set_transient( $tkey, 1, self::COOLDOWN );
		return rest_ensure_response( array( 'id' => $pid, 'likes' => $cur ) );
	}

	protected static function client_ip() {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $k ) {
			if ( ! empty( $_SERVER[ $k ] ) ) return trim( explode( ',', $_SERVER[ $k ] )[0] );
		}
		return '0.0.0.0';
	}
}
Kyosuki_Pop_Like::init();
