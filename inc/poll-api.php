<?php
/**
 * Kyosuki Pop Poll API
 *
 * カップル投票 — 「ラウンド」単位で集計し、終了時にアーカイブする方式。
 *
 *  options:
 *    kp_poll_current_id      string   現在のラウンドID（一度作成したら、アーカイブ時のみ更新）
 *    kp_poll_current_started string   現在のラウンド開始日時 (MySQL datetime)
 *    kp_poll_current_title   string   ラウンド開始時の投票タイトル（後から表示用）
 *    kp_poll_votes_{id}      array    現在ラウンドの投票数 (name_hash => count)
 *    kp_poll_archive         array    過去ラウンドの配列
 *      [
 *        {
 *          id, title, started_at, archived_at,
 *          options: [ { name, votes } ... ],
 *          total
 *        }, ...
 *      ]
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Kyosuki_Pop_Poll {

	const OPT_VOTES_PREFIX = 'kp_poll_votes_';
	const OPT_CURRENT_ID   = 'kp_poll_current_id';
	const OPT_STARTED      = 'kp_poll_current_started';
	const OPT_TITLE_SNAP   = 'kp_poll_current_title';
	const OPT_ARCHIVE      = 'kp_poll_archive';
	const TRANSIENT        = 'kp_poll_ip_';
	const COOLDOWN         = DAY_IN_SECONDS;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'admin_menu',    array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_kp_poll_export',   array( __CLASS__, 'export_csv' ) );
		add_action( 'admin_post_kp_poll_archive',  array( __CLASS__, 'archive_round' ) );
		add_action( 'admin_post_kp_poll_archive_csv',    array( __CLASS__, 'export_archive_csv' ) );
		add_action( 'admin_post_kp_poll_archive_delete', array( __CLASS__, 'delete_archive' ) );
	}

	/* =========================================================
	 * Options list
	 * ========================================================= */

	public static function vote_key( $name ) {
		return substr( md5( (string) $name ), 0, 16 );
	}

	/**
	 * Customizer の「選択肢」（1行1件）をパースして配列で返す。
	 * 旧形式 "name|78" の "|78" 部分は無視して name だけ採用。
	 */
	public static function get_options_list() {
		$raw   = (string) get_theme_mod( 'kp_poll_options', '' );
		$lines = preg_split( "/\r\n|\r|\n/", trim( $raw ) );
		$out   = array();
		$seen  = array();
		foreach ( $lines as $line ) {
			$name = trim( $line );
			if ( $name === '' ) continue;
			if ( strpos( $name, '|' ) !== false ) {
				$name = trim( explode( '|', $name, 2 )[0] );
			}
			if ( $name === '' || isset( $seen[ $name ] ) ) continue;
			$seen[ $name ] = true;
			$out[] = array( 'name' => $name, 'hash' => self::vote_key( $name ) );
		}
		return $out;
	}

	public static function has_options() {
		return ! empty( self::get_options_list() );
	}

	/* =========================================================
	 * Round management
	 * ========================================================= */

	/**
	 * 現在のラウンドID（無ければ初期化して作成）
	 */
	public static function get_current_round_id() {
		$id = get_option( self::OPT_CURRENT_ID, '' );
		if ( ! $id ) {
			$id = self::start_new_round();
		}
		return $id;
	}

	/**
	 * 新しいラウンドを開始（ID生成 + 開始日時記録）
	 */
	protected static function start_new_round() {
		$id = gmdate( 'YmdHis' ) . '-' . wp_generate_password( 6, false, false );
		update_option( self::OPT_CURRENT_ID, $id, false );
		update_option( self::OPT_STARTED, current_time( 'mysql' ), false );
		update_option( self::OPT_TITLE_SNAP, get_theme_mod( 'kp_poll_title', '' ), false );
		update_option( self::OPT_VOTES_PREFIX . $id, array(), false );
		return $id;
	}

	/**
	 * 現在ラウンドの票数を取得 ( name_hash => count )
	 */
	public static function get_votes() {
		$id = self::get_current_round_id();
		$v  = get_option( self::OPT_VOTES_PREFIX . $id, array() );
		return is_array( $v ) ? $v : array();
	}

	/**
	 * 集計結果を返す（現在のラウンドのみ）
	 */
	public static function calculate_percentages() {
		$opts   = self::get_options_list();
		$votes  = self::get_votes();
		$totals = array();
		$sum    = 0;
		foreach ( $opts as $o ) {
			$totals[ $o['hash'] ] = (int) ( $votes[ $o['hash'] ] ?? 0 );
			$sum += $totals[ $o['hash'] ];
		}
		$pct = array();
		foreach ( $opts as $o ) {
			$pct[ $o['hash'] ] = $sum > 0 ? (int) round( ( $totals[ $o['hash'] ] / $sum ) * 100 ) : 0;
		}
		return array(
			'percentages' => $pct,
			'votes'       => $totals,
			'sum'         => $sum,
		);
	}

	/* =========================================================
	 * REST
	 * ========================================================= */

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
					'option_hash' => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				),
			),
		) );
	}

	public static function rest_get( $request ) {
		$data = self::calculate_percentages();
		$opts = self::get_options_list();
		$out  = array(
			'round_id' => self::get_current_round_id(),
			'options'  => array(),
		);
		foreach ( $opts as $o ) {
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
		if ( ! self::has_options() ) {
			return new WP_Error( 'kp_no_options', '投票項目が設定されていません', array( 'status' => 400 ) );
		}

		$round_id = self::get_current_round_id();
		$ip       = self::client_ip();
		$tkey     = self::TRANSIENT . $round_id . '_' . md5( $ip );
		if ( get_transient( $tkey ) ) {
			return new WP_Error( 'kp_already_voted', '既に投票済みです', array( 'status' => 429 ) );
		}

		$hash = (string) $request->get_param( 'option_hash' );
		$opts = self::get_options_list();
		$valid = false;
		foreach ( $opts as $o ) {
			if ( hash_equals( $o['hash'], $hash ) ) { $valid = true; break; }
		}
		if ( ! $valid ) {
			return new WP_Error( 'kp_invalid_option', '無効な選択肢', array( 'status' => 400 ) );
		}

		$votes_key       = self::OPT_VOTES_PREFIX . $round_id;
		$votes           = self::get_votes();
		$votes[ $hash ]  = (int) ( $votes[ $hash ] ?? 0 ) + 1;
		update_option( $votes_key, $votes, false );

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

	/* =========================================================
	 * Archive
	 * ========================================================= */

	public static function get_archives() {
		$a = get_option( self::OPT_ARCHIVE, array() );
		return is_array( $a ) ? $a : array();
	}

	public static function archive_round() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_archive' ) ) {
			wp_die( '権限がありません' );
		}

		$round_id = get_option( self::OPT_CURRENT_ID, '' );
		$opts     = self::get_options_list();
		$votes    = self::get_votes();

		$snapshot_options = array();
		$total            = 0;
		foreach ( $opts as $o ) {
			$c = (int) ( $votes[ $o['hash'] ] ?? 0 );
			$snapshot_options[] = array( 'name' => $o['name'], 'votes' => $c );
			$total += $c;
		}

		$record = array(
			'id'           => $round_id ?: gmdate( 'YmdHis' ),
			'title'        => get_option( self::OPT_TITLE_SNAP, '' ) ?: get_theme_mod( 'kp_poll_title', '' ),
			'started_at'   => get_option( self::OPT_STARTED, '' ),
			'archived_at'  => current_time( 'mysql' ),
			'options'      => $snapshot_options,
			'total'        => $total,
		);

		$archives   = self::get_archives();
		$archives[] = $record;
		update_option( self::OPT_ARCHIVE, $archives, false );

		// 旧ラウンドの投票数オプションを掃除
		if ( $round_id ) {
			delete_option( self::OPT_VOTES_PREFIX . $round_id );
		}

		// 新ラウンド開始
		self::start_new_round();

		wp_safe_redirect( admin_url( 'themes.php?page=kp-poll&archived=1' ) );
		exit;
	}

	public static function delete_archive() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_archive_delete' ) ) {
			wp_die( '権限がありません' );
		}
		$target = sanitize_text_field( $_REQUEST['archive_id'] ?? '' );
		$archives = self::get_archives();
		$archives = array_values( array_filter( $archives, function ( $a ) use ( $target ) {
			return ( $a['id'] ?? '' ) !== $target;
		} ) );
		update_option( self::OPT_ARCHIVE, $archives, false );
		wp_safe_redirect( admin_url( 'themes.php?page=kp-poll&deleted=1' ) );
		exit;
	}

	/* =========================================================
	 * Admin UI
	 * ========================================================= */

	public static function admin_menu() {
		add_submenu_page(
			'themes.php',
			__( 'カップル投票 集計', 'kyosuki-pop' ),
			__( 'カップル投票 集計', 'kyosuki-pop' ),
			'manage_options',
			'kp-poll',
			array( __CLASS__, 'admin_page' )
		);
	}

	public static function admin_page() {
		$data   = self::calculate_percentages();
		$opts   = self::get_options_list();
		$round  = self::get_current_round_id();
		$started = get_option( self::OPT_STARTED, '' );
		$total   = array_sum( $data['votes'] );
		$archives = array_reverse( self::get_archives() );

		$archive_url = wp_nonce_url( admin_url( 'admin-post.php?action=kp_poll_archive' ), 'kp_poll_archive' );
		$export_url  = wp_nonce_url( admin_url( 'admin-post.php?action=kp_poll_export' ),  'kp_poll_export' );

		echo '<div class="wrap"><h1>' . esc_html__( 'カップル投票 集計', 'kyosuki-pop' ) . '</h1>';

		if ( ! empty( $_GET['archived'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>★ 現在のラウンドをアーカイブしました。新しいラウンドが始まっています。</p></div>';
		}
		if ( ! empty( $_GET['deleted'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>アーカイブを削除しました。</p></div>';
		}

		echo '<h2>♡ 現在のラウンド</h2>';
		echo '<p>ラウンドID: <code>' . esc_html( $round ) . '</code><br>';
		echo '開始日時: <strong>' . esc_html( $started ?: '—' ) . '</strong><br>';
		echo '総投票数: <strong>' . (int) $total . '</strong></p>';

		if ( ! $opts ) {
			echo '<div class="notice notice-warning"><p>選択肢が登録されていません。<br><a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=kp_poll' ) ) . '">外観 → カスタマイズ → カップル投票</a> から選択肢を追加してください。</p></div>';
		} else {
			echo '<table class="widefat striped" style="max-width:640px;"><thead><tr><th>選択肢</th><th style="width:120px;">投票数</th><th style="width:80px;">％</th></tr></thead><tbody>';
			foreach ( $opts as $o ) {
				printf(
					'<tr><td>%s</td><td>%d</td><td>%d%%</td></tr>',
					esc_html( $o['name'] ),
					(int) ( $data['votes'][ $o['hash'] ] ?? 0 ),
					(int) ( $data['percentages'][ $o['hash'] ] ?? 0 )
				);
			}
			echo '</tbody></table>';
		}

		echo '<p style="margin-top:20px;">';
		echo '<a href="' . esc_url( $export_url ) . '" class="button button-secondary">CSV ダウンロード</a> ';
		echo '<a href="' . esc_url( $archive_url ) . '" class="button button-primary" onclick="return confirm(\'現在のラウンドを保存して、新しい投票ラウンドを開始しますか？\\n\\n（投票結果はアーカイブに残り、票はゼロから集計し直しになります）\');">★ アーカイブして次のラウンドを開始</a>';
		echo '</p>';

		echo '<hr style="margin:32px 0;">';

		echo '<h2>♡ 過去のアーカイブ（' . count( $archives ) . '件）</h2>';
		if ( ! $archives ) {
			echo '<p>まだアーカイブはありません。</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>期間</th><th>タイトル</th><th>総投票</th><th>トップ得票</th><th style="width:200px;">操作</th></tr></thead><tbody>';
			foreach ( $archives as $a ) {
				$top = null;
				foreach ( ( $a['options'] ?? array() ) as $row ) {
					if ( ! $top || $row['votes'] > $top['votes'] ) $top = $row;
				}
				$csv_url = wp_nonce_url(
					admin_url( 'admin-post.php?action=kp_poll_archive_csv&archive_id=' . rawurlencode( $a['id'] ) ),
					'kp_poll_archive_csv'
				);
				$del_url = wp_nonce_url(
					admin_url( 'admin-post.php?action=kp_poll_archive_delete&archive_id=' . rawurlencode( $a['id'] ) ),
					'kp_poll_archive_delete'
				);
				printf(
					'<tr><td>%s<br><small>〜 %s</small></td><td>%s</td><td>%d</td><td>%s<br><small>%d 票</small></td><td><a class="button button-small" href="%s">CSV</a> <a class="button button-small" href="%s" onclick="return confirm(\'このアーカイブを削除しますか？\');">削除</a></td></tr>',
					esc_html( $a['started_at'] ?? '—' ),
					esc_html( $a['archived_at'] ?? '—' ),
					esc_html( $a['title'] ?: '（タイトルなし）' ),
					(int) ( $a['total'] ?? 0 ),
					$top ? esc_html( $top['name'] ) : '—',
					$top ? (int) $top['votes'] : 0,
					esc_url( $csv_url ),
					esc_url( $del_url )
				);
				echo '<tr><td colspan="5" style="background:#fafafa;"><details><summary style="cursor:pointer;">詳細を見る</summary><table style="margin-top:8px;width:auto;"><thead><tr><th style="padding:4px 12px;">選択肢</th><th style="padding:4px 12px;">票数</th></tr></thead><tbody>';
				foreach ( ( $a['options'] ?? array() ) as $row ) {
					printf( '<tr><td style="padding:4px 12px;">%s</td><td style="padding:4px 12px;">%d</td></tr>', esc_html( $row['name'] ), (int) $row['votes'] );
				}
				echo '</tbody></table></details></td></tr>';
			}
			echo '</tbody></table>';
		}

		echo '</div>';
	}

	/* =========================================================
	 * CSV exports
	 * ========================================================= */

	public static function export_csv() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_export' ) ) {
			wp_die( '権限がありません' );
		}
		$opts = self::get_options_list();
		$data = self::calculate_percentages();
		self::send_csv_headers( 'kp-poll-current-' . gmdate( 'Ymd-Hi' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fputs( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( '選択肢', '投票数', 'パーセント' ) );
		foreach ( $opts as $o ) {
			fputcsv( $out, array(
				$o['name'],
				(int) ( $data['votes'][ $o['hash'] ] ?? 0 ),
				(int) ( $data['percentages'][ $o['hash'] ] ?? 0 ) . '%',
			) );
		}
		fclose( $out );
		exit;
	}

	public static function export_archive_csv() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_archive_csv' ) ) {
			wp_die( '権限がありません' );
		}
		$target = sanitize_text_field( $_REQUEST['archive_id'] ?? '' );
		$found  = null;
		foreach ( self::get_archives() as $a ) {
			if ( ( $a['id'] ?? '' ) === $target ) { $found = $a; break; }
		}
		if ( ! $found ) {
			wp_die( 'アーカイブが見つかりません' );
		}
		$sum = max( 1, (int) $found['total'] );
		self::send_csv_headers( 'kp-poll-archive-' . $target . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fputs( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'ラウンドID', $found['id'] ) );
		fputcsv( $out, array( 'タイトル',    $found['title'] ?? '' ) );
		fputcsv( $out, array( '開始日時',    $found['started_at'] ?? '' ) );
		fputcsv( $out, array( '終了日時',    $found['archived_at'] ?? '' ) );
		fputcsv( $out, array( '総投票数',    (int) $found['total'] ) );
		fputcsv( $out, array() );
		fputcsv( $out, array( '選択肢', '投票数', 'パーセント' ) );
		foreach ( ( $found['options'] ?? array() ) as $row ) {
			$pct = (int) round( ( (int) $row['votes'] / $sum ) * 100 );
			fputcsv( $out, array( $row['name'], (int) $row['votes'], $pct . '%' ) );
		}
		fclose( $out );
		exit;
	}

	protected static function send_csv_headers( $filename ) {
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	}
}
Kyosuki_Pop_Poll::init();
