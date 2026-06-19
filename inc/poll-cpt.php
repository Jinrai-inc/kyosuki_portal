<?php
/**
 * Kyosuki Pop — Couple Poll Custom Post Type
 *
 * 1 ラウンド = 1 投稿（post_type = kp_poll）。
 * 投稿の post meta に選択肢・投票数・開始/終了日時を保存し、
 * 「現在のラウンド」だけをトップページの投票ボックスに表示する。
 *
 *  Post:        kp_poll  (post_status=publish のみ使用)
 *  Post meta:
 *    _kp_poll_options      string   選択肢（1行1組のテキスト）
 *    _kp_poll_votes        array    { name_hash => count }
 *    _kp_poll_started_at   string   ラウンド開始日時 (MySQL)
 *    _kp_poll_archived_at  string   アーカイブ日時 (MySQL, '' = 進行中)
 *
 *  Site option:
 *    kp_active_poll_id     int      現在のラウンドの投稿ID
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Kyosuki_Pop_Poll_CPT {

	const POST_TYPE     = 'kp_poll';
	const META_OPTIONS  = '_kp_poll_options';
	const META_VOTES    = '_kp_poll_votes';
	const META_STARTED  = '_kp_poll_started_at';
	const META_ARCHIVED = '_kp_poll_archived_at';
	const OPT_ACTIVE_ID = 'kp_active_poll_id';
	const OPT_MIGRATED  = 'kp_poll_migrated_to_cpt';
	const NONCE_ACTION  = 'kp_poll_meta';

	public static function init() {
		add_action( 'init',                       array( __CLASS__, 'register_cpt' ) );
		add_action( 'init',                       array( __CLASS__, 'maybe_migrate' ), 30 );
		add_action( 'add_meta_boxes',             array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_post' ), 10, 2 );
		add_action( 'before_delete_post',         array( __CLASS__, 'on_delete' ) );

		// Admin list table columns
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',       array( __CLASS__, 'list_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'list_column_content' ), 10, 2 );

		// CSV export per poll
		add_action( 'admin_post_kp_poll_csv',     array( __CLASS__, 'export_csv' ) );
		add_action( 'admin_post_kp_poll_reset',   array( __CLASS__, 'reset_votes' ) );
		add_action( 'admin_post_kp_poll_log_clear', array( __CLASS__, 'clear_log_action' ) );

		// Disable Gutenberg for this CPT (we use a classic meta box)
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'force_classic_editor' ), 10, 2 );

		// Friendly help text inside the title field
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	/* =========================================================
	 * CPT registration
	 * ========================================================= */

	public static function register_cpt() {
		register_post_type( self::POST_TYPE, array(
			'labels' => array(
				'name'               => __( 'カップル投票', 'kyosuki-pop' ),
				'singular_name'      => __( 'カップル投票', 'kyosuki-pop' ),
				'menu_name'          => __( 'カップル投票', 'kyosuki-pop' ),
				'add_new'            => __( '新しいラウンドを追加', 'kyosuki-pop' ),
				'add_new_item'       => __( '新しいラウンドを追加', 'kyosuki-pop' ),
				'edit_item'          => __( 'ラウンドを編集', 'kyosuki-pop' ),
				'new_item'           => __( '新規ラウンド', 'kyosuki-pop' ),
				'view_item'          => __( 'ラウンドを見る', 'kyosuki-pop' ),
				'search_items'       => __( 'ラウンドを検索', 'kyosuki-pop' ),
				'not_found'          => __( 'ラウンドがありません', 'kyosuki-pop' ),
				'not_found_in_trash' => __( 'ゴミ箱にラウンドはありません', 'kyosuki-pop' ),
				'all_items'          => __( 'ラウンド一覧', 'kyosuki-pop' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'show_in_rest'  => false,
			'menu_icon'     => 'dashicons-chart-pie',
			'menu_position' => 9,
			'supports'      => array( 'title' ),
			'has_archive'   => false,
			'rewrite'       => false,
			'capability_type' => 'post',
		) );
	}

	public static function force_classic_editor( $use_block, $post_type ) {
		return $post_type === self::POST_TYPE ? false : $use_block;
	}

	public static function title_placeholder( $placeholder, $post ) {
		if ( $post && $post->post_type === self::POST_TYPE ) {
			return __( '例: ★ 今週の推しCPは？', 'kyosuki-pop' );
		}
		return $placeholder;
	}

	/* =========================================================
	 * Helpers
	 * ========================================================= */

	public static function vote_key( $name ) {
		return substr( md5( (string) $name ), 0, 16 );
	}

	public static function get_active_id() {
		return (int) get_option( self::OPT_ACTIVE_ID, 0 );
	}

	public static function set_active_id( $post_id ) {
		$post_id = (int) $post_id;
		update_option( self::OPT_ACTIVE_ID, $post_id, false );
		if ( $post_id ) {
			// 開始日時が未設定なら今この瞬間で記録
			if ( ! get_post_meta( $post_id, self::META_STARTED, true ) ) {
				update_post_meta( $post_id, self::META_STARTED, current_time( 'mysql' ) );
			}
			// 復帰時はアーカイブ日時を消す
			delete_post_meta( $post_id, self::META_ARCHIVED );
		}
	}

	public static function clear_active_if_match( $post_id ) {
		if ( self::get_active_id() === (int) $post_id ) {
			delete_option( self::OPT_ACTIVE_ID );
		}
	}

	public static function options_list( $post_id ) {
		$raw   = (string) get_post_meta( $post_id, self::META_OPTIONS, true );
		$lines = preg_split( "/\r\n|\r|\n/", trim( $raw ) );
		$out   = array();
		$seen  = array();
		foreach ( $lines as $line ) {
			$name = trim( $line );
			if ( $name === '' ) continue;
			// 旧形式 "name|78" のサフィックス除去
			if ( strpos( $name, '|' ) !== false ) {
				$name = trim( explode( '|', $name, 2 )[0] );
			}
			if ( $name === '' || isset( $seen[ $name ] ) ) continue;
			$seen[ $name ] = true;
			$out[] = array( 'name' => $name, 'hash' => self::vote_key( $name ) );
		}
		return $out;
	}

	public static function votes( $post_id ) {
		$v = get_post_meta( $post_id, self::META_VOTES, true );
		return is_array( $v ) ? $v : array();
	}

	public static function calculate( $post_id ) {
		$opts   = self::options_list( $post_id );
		$votes  = self::votes( $post_id );
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
			'options'     => $opts,
			'percentages' => $pct,
			'votes'       => $totals,
			'sum'         => $sum,
		);
	}

	public static function increment_vote( $post_id, $hash ) {
		$votes           = self::votes( $post_id );
		$votes[ $hash ]  = (int) ( $votes[ $hash ] ?? 0 ) + 1;
		update_post_meta( $post_id, self::META_VOTES, $votes );
	}

	public static function is_archived( $post_id ) {
		return self::get_active_id() !== (int) $post_id;
	}

	/* =========================================================
	 * Meta box
	 * ========================================================= */

	public static function add_meta_boxes() {
		add_meta_box( 'kp_poll_options_box', __( '① 選択肢（1行に1組）', 'kyosuki-pop' ),
			array( __CLASS__, 'render_options_box' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'kp_poll_status_box',  __( '② ラウンドの状態', 'kyosuki-pop' ),
			array( __CLASS__, 'render_status_box' ), self::POST_TYPE, 'side', 'high' );
		add_meta_box( 'kp_poll_results_box', __( '③ 投票結果', 'kyosuki-pop' ),
			array( __CLASS__, 'render_results_box' ), self::POST_TYPE, 'normal', 'default' );
		add_meta_box( 'kp_poll_log_box',     __( '④ 直近の投票ログ（診断用）', 'kyosuki-pop' ),
			array( __CLASS__, 'render_log_box' ), self::POST_TYPE, 'normal', 'low' );
	}

	public static function render_options_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, 'kp_poll_nonce' );
		$opts = (string) get_post_meta( $post->ID, self::META_OPTIONS, true );
		?>
		<p style="color:#6B5A8A;margin-top:0;">1 行に 1 組ずつ、推しカップル名を入れてください。<br>例: <code>りく♡みお</code></p>
		<textarea name="kp_poll_options" rows="8" style="width:100%;font-family:monospace;" placeholder="りく♡みお&#10;ゆうた♡あい&#10;けんと♡なな"><?php echo esc_textarea( $opts ); ?></textarea>
		<p style="color:#6B5A8A;font-size:12px;margin-bottom:0;">※ 選択肢を直しても、これまでの票はそのまま残ります（名前を変えた選択肢は新規扱いで 0 票スタート）。</p>
		<?php
	}

	public static function render_status_box( $post ) {
		$active_id   = self::get_active_id();
		$is_current  = ( $active_id === (int) $post->ID );
		$started_at  = get_post_meta( $post->ID, self::META_STARTED, true );
		$archived_at = get_post_meta( $post->ID, self::META_ARCHIVED, true );
		?>
		<p style="margin-top:0;">
			<label style="display:block;margin-bottom:8px;">
				<input type="radio" name="kp_poll_status" value="active" <?php checked( $is_current ); ?> />
				<strong>★ 現在のラウンドにする</strong><br>
				<span style="color:#6B5A8A;font-size:12px;margin-left:24px;">トップページの「カップル投票」に表示</span>
			</label>
			<label style="display:block;">
				<input type="radio" name="kp_poll_status" value="archived" <?php checked( ! $is_current ); ?> />
				<strong>アーカイブ（一覧にだけ残す）</strong><br>
				<span style="color:#6B5A8A;font-size:12px;margin-left:24px;">トップには表示されません。集計だけ残ります。</span>
			</label>
		</p>
		<p style="border-top:1px solid #eee;padding-top:10px;margin-bottom:0;font-size:12px;color:#6B5A8A;">
			開始日時: <strong style="color:#1A0B3D;"><?php echo esc_html( $started_at ?: '—' ); ?></strong><br>
			アーカイブ日時: <strong style="color:#1A0B3D;"><?php echo esc_html( $archived_at ?: '—' ); ?></strong>
		</p>
		<p style="font-size:12px;color:#6B5A8A;margin-bottom:0;">
			※ 別のラウンドを「現在」にすると、こちらは自動的にアーカイブされます。
		</p>
		<?php
	}

	public static function render_log_box( $post ) {
		$log = class_exists( 'Kyosuki_Pop_Poll' ) ? Kyosuki_Pop_Poll::get_log() : array();
		echo '<p style="color:#6B5A8A;margin-top:0;">直近 20 件の投票リクエストを記録しています。トップページで投票を試した結果が、どの選択肢に・成功 / 失敗どちらで届いたかを確認できます。<br>「同じ IP」は IP アドレスのハッシュ先頭 8 文字（個人を特定する情報は保存しません）。</p>';
		if ( ! $log ) {
			echo '<p>まだ投票ログはありません。</p>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr><th style="width:150px;">時刻</th><th>選択肢</th><th style="width:120px;">同じ IP かどうか</th><th style="width:140px;">結果</th></tr></thead><tbody>';
		foreach ( $log as $row ) {
			$result_label = '';
			$result_color = '#1A0B3D';
			switch ( $row['result'] ?? '' ) {
				case 'ok':           $result_label = '✓ 成功';           $result_color = '#0a7c2f'; break;
				case 'ok (admin)':   $result_label = '✓ 成功（管理者）'; $result_color = '#0a7c2f'; break;
				case 'kp_already_voted': $result_label = '× 同 IP 連投ブロック'; $result_color = '#a00'; break;
				case 'kp_invalid_option': $result_label = '× 無効な選択肢';     $result_color = '#a00'; break;
				case 'kp_no_poll':       $result_label = '× ラウンド未設定';   $result_color = '#a00'; break;
				case 'kp_archived':      $result_label = '× 終了済ラウンド';   $result_color = '#a00'; break;
				default:                 $result_label = esc_html( $row['result'] );
			}
			printf(
				'<tr><td>%s</td><td>%s</td><td><code>%s</code></td><td style="color:%s;font-weight:900;">%s</td></tr>',
				esc_html( $row['time'] ?? '' ),
				esc_html( $row['option'] ?: '—' ),
				esc_html( $row['ip'] ?? '' ),
				esc_attr( $result_color ),
				$result_label
			);
		}
		echo '</tbody></table>';

		$clear_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=kp_poll_log_clear' ),
			'kp_poll_log_clear'
		);
		echo '<p style="margin-top:12px;"><a class="button button-small" href="' . esc_url( $clear_url ) . '" onclick="return confirm(\'ログを消去しますか？\');">ログを消去</a></p>';
	}

	public static function render_results_box( $post ) {
		$data  = self::calculate( $post->ID );
		$total = (int) $data['sum'];

		if ( ! empty( $_GET['kp_reset'] ) ) {
			echo '<div class="notice notice-success" style="margin:0 0 12px;"><p>このラウンドの票数を 0 にリセットしました。</p></div>';
		}

		if ( ! $data['options'] ) {
			echo '<p>選択肢を追加して保存すると、投票結果がここに表示されます。</p>';
			return;
		}

		$csv_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=kp_poll_csv&poll_id=' . (int) $post->ID ),
			'kp_poll_csv_' . $post->ID
		);
		$reset_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=kp_poll_reset&poll_id=' . (int) $post->ID ),
			'kp_poll_reset_' . $post->ID
		);

		echo '<p>総投票数: <strong>' . (int) $total . '</strong></p>';
		echo '<table class="widefat striped" style="max-width:560px;"><thead><tr><th>選択肢</th><th style="width:120px;">投票数</th><th style="width:80px;">％</th></tr></thead><tbody>';
		foreach ( $data['options'] as $o ) {
			printf(
				'<tr><td>%s</td><td>%d</td><td>%d%%</td></tr>',
				esc_html( $o['name'] ),
				(int) ( $data['votes'][ $o['hash'] ] ?? 0 ),
				(int) ( $data['percentages'][ $o['hash'] ] ?? 0 )
			);
		}
		echo '</tbody></table>';
		echo '<p style="margin-top:16px;">';
		echo '<a class="button" href="' . esc_url( $csv_url ) . '">CSV ダウンロード</a> ';
		if ( $total > 0 ) {
			echo '<a class="button" style="color:#a00;" href="' . esc_url( $reset_url ) . '" onclick="return confirm(\'このラウンドの投票数を 0 に戻しますか？\\n（過去のアーカイブには影響しません）\');">票を 0 にリセット</a>';
		}
		echo '</p>';
	}

	/* =========================================================
	 * Save handler
	 * ========================================================= */

	public static function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['kp_poll_nonce'] ) || ! wp_verify_nonce( $_POST['kp_poll_nonce'], self::NONCE_ACTION ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		// Options
		if ( isset( $_POST['kp_poll_options'] ) ) {
			$opts = sanitize_textarea_field( wp_unslash( $_POST['kp_poll_options'] ) );
			update_post_meta( $post_id, self::META_OPTIONS, $opts );
		}

		// Status: active or archived
		$status = isset( $_POST['kp_poll_status'] ) ? (string) $_POST['kp_poll_status'] : 'archived';
		$was_active = ( self::get_active_id() === (int) $post_id );

		if ( $status === 'active' ) {
			// 別の current があれば自動でアーカイブ
			$prev = self::get_active_id();
			if ( $prev && $prev !== (int) $post_id ) {
				update_post_meta( $prev, self::META_ARCHIVED, current_time( 'mysql' ) );
			}
			self::set_active_id( $post_id );
		} else {
			// archived
			if ( $was_active ) {
				update_post_meta( $post_id, self::META_ARCHIVED, current_time( 'mysql' ) );
				delete_option( self::OPT_ACTIVE_ID );
			} else {
				// 既にアーカイブで、アーカイブ日時が未設定なら今で埋める
				if ( ! get_post_meta( $post_id, self::META_ARCHIVED, true ) ) {
					update_post_meta( $post_id, self::META_ARCHIVED, current_time( 'mysql' ) );
				}
			}
		}

		// 開始日時を確定
		if ( ! get_post_meta( $post_id, self::META_STARTED, true ) ) {
			update_post_meta( $post_id, self::META_STARTED, current_time( 'mysql' ) );
		}
	}

	public static function on_delete( $post_id ) {
		if ( get_post_type( $post_id ) !== self::POST_TYPE ) return;
		self::clear_active_if_match( $post_id );
	}

	/* =========================================================
	 * Admin list table
	 * ========================================================= */

	public static function list_columns( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( $k === 'title' ) {
				$new['kp_status'] = __( '状態', 'kyosuki-pop' );
				$new['kp_total']  = __( '総投票数', 'kyosuki-pop' );
				$new['kp_period'] = __( '期間', 'kyosuki-pop' );
			}
		}
		return $new;
	}

	public static function list_column_content( $col, $post_id ) {
		switch ( $col ) {
			case 'kp_status':
				if ( self::get_active_id() === (int) $post_id ) {
					echo '<span style="background:#FFE066;color:#1A0B3D;padding:2px 8px;border-radius:4px;font-weight:900;">★ 現在のラウンド</span>';
				} else {
					echo '<span style="color:#6B5A8A;">アーカイブ</span>';
				}
				break;
			case 'kp_total':
				$data = self::calculate( $post_id );
				echo (int) $data['sum'];
				break;
			case 'kp_period':
				$s = get_post_meta( $post_id, self::META_STARTED,  true );
				$a = get_post_meta( $post_id, self::META_ARCHIVED, true );
				echo esc_html( ( $s ?: '—' ) . ' 〜 ' . ( $a ?: '進行中' ) );
				break;
		}
	}

	/* =========================================================
	 * CSV export
	 * ========================================================= */

	public static function clear_log_action() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_log_clear' ) ) {
			wp_die( '権限がありません' );
		}
		if ( class_exists( 'Kyosuki_Pop_Poll' ) ) {
			Kyosuki_Pop_Poll::clear_log();
		}
		wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=' . self::POST_TYPE ) );
		exit;
	}

	public static function reset_votes() {
		$post_id = (int) ( $_REQUEST['poll_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( '権限がありません' );
		}
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_reset_' . $post_id ) ) {
			wp_die( '権限がありません' );
		}
		// 票だけを 0 に戻す（選択肢・期間は維持）
		delete_post_meta( $post_id, self::META_VOTES );
		// 同じ IP の COOLDOWN もクリアして、検証用に再投票可能にする
		global $wpdb;
		$like = $wpdb->esc_like( '_transient_' . Kyosuki_Pop_Poll::TRANSIENT . $post_id . '_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
		$like2 = $wpdb->esc_like( '_transient_timeout_' . Kyosuki_Pop_Poll::TRANSIENT . $post_id . '_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like2 ) );

		wp_safe_redirect( add_query_arg( array( 'kp_reset' => 1 ), get_edit_post_link( $post_id, 'redirect' ) ) );
		exit;
	}

	public static function export_csv() {
		$post_id = (int) ( $_REQUEST['poll_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( '権限がありません' );
		}
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'kp_poll_csv_' . $post_id ) ) {
			wp_die( '権限がありません' );
		}
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== self::POST_TYPE ) {
			wp_die( '投稿が見つかりません' );
		}

		$data    = self::calculate( $post_id );
		$started = get_post_meta( $post_id, self::META_STARTED,  true );
		$ended   = get_post_meta( $post_id, self::META_ARCHIVED, true );

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="kp-poll-' . $post_id . '-' . gmdate( 'Ymd-Hi' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputs( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'ラウンドID',  (string) $post_id ) );
		fputcsv( $out, array( 'タイトル',    $post->post_title ) );
		fputcsv( $out, array( '開始日時',    $started ?: '' ) );
		fputcsv( $out, array( '終了日時',    $ended   ?: '（進行中）' ) );
		fputcsv( $out, array( '総投票数',    (int) $data['sum'] ) );
		fputcsv( $out, array() );
		fputcsv( $out, array( '選択肢', '投票数', 'パーセント' ) );
		foreach ( $data['options'] as $o ) {
			fputcsv( $out, array(
				$o['name'],
				(int) ( $data['votes'][ $o['hash'] ] ?? 0 ),
				(int) ( $data['percentages'][ $o['hash'] ] ?? 0 ) . '%',
			) );
		}
		fclose( $out );
		exit;
	}

	/* =========================================================
	 * One-time migration from Customizer + kp_poll_archive option
	 * ========================================================= */

	public static function maybe_migrate() {
		if ( get_option( self::OPT_MIGRATED ) ) return;
		if ( ! current_user_can( 'manage_options' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			// only run from admin context to be safe; mark done lazily otherwise
		}

		// 1) Customizer に選択肢が残っていれば「現在のラウンド」として import
		$cust_opts  = (string) get_theme_mod( 'kp_poll_options', '' );
		$cust_title = (string) get_theme_mod( 'kp_poll_title', '★ 今週の推しCPは？' );
		$old_id     = get_option( 'kp_poll_current_id', '' );
		$old_votes  = $old_id ? get_option( 'kp_poll_votes_' . $old_id, array() ) : array();
		$old_start  = get_option( 'kp_poll_current_started', '' );

		if ( trim( $cust_opts ) !== '' ) {
			$post_id = wp_insert_post( array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $cust_title ?: '★ 今週の推しCPは？',
			) );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, self::META_OPTIONS, $cust_opts );
				if ( is_array( $old_votes ) && $old_votes ) {
					update_post_meta( $post_id, self::META_VOTES, $old_votes );
				}
				update_post_meta( $post_id, self::META_STARTED, $old_start ?: current_time( 'mysql' ) );
				self::set_active_id( $post_id );
			}
		}

		// 2) 旧アーカイブ配列 → 各 record を kp_poll 投稿として import
		$archives = get_option( 'kp_poll_archive', array() );
		if ( is_array( $archives ) && $archives ) {
			foreach ( $archives as $a ) {
				$title = $a['title'] ?? '★ アーカイブ済みラウンド';
				$post_id = wp_insert_post( array(
					'post_type'   => self::POST_TYPE,
					'post_status' => 'publish',
					'post_title'  => $title,
					'post_date'   => $a['started_at'] ?? current_time( 'mysql' ),
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					$opt_text = '';
					$votes_arr = array();
					foreach ( ( $a['options'] ?? array() ) as $row ) {
						$opt_text .= ( $row['name'] ?? '' ) . "\n";
						if ( isset( $row['name'] ) ) {
							$votes_arr[ self::vote_key( $row['name'] ) ] = (int) ( $row['votes'] ?? 0 );
						}
					}
					update_post_meta( $post_id, self::META_OPTIONS,  trim( $opt_text ) );
					update_post_meta( $post_id, self::META_VOTES,    $votes_arr );
					update_post_meta( $post_id, self::META_STARTED,  $a['started_at']  ?? current_time( 'mysql' ) );
					update_post_meta( $post_id, self::META_ARCHIVED, $a['archived_at'] ?? current_time( 'mysql' ) );
				}
			}
		}

		update_option( self::OPT_MIGRATED, 1, false );
	}
}
Kyosuki_Pop_Poll_CPT::init();
