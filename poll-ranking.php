<?php
/**
 * Template Name: ★ カップル投票ランキング（全件）
 *
 * 通常は「カップル投票ランキング」固定ページ（slug=cp-ranking）に
 * 自動割当される Page Template。?poll_id=NN でアーカイブされた
 * 過去ラウンドも表示可能。
 *
 * @package Kyosuki_Pop
 */
get_header();

$req_id    = isset( $_GET['poll_id'] ) ? absint( $_GET['poll_id'] ) : 0;
$poll_post = null;
if ( $req_id ) {
	$p = get_post( $req_id );
	if ( $p && $p->post_type === Kyosuki_Pop_Poll_CPT::POST_TYPE ) {
		$poll_post = $p;
	}
}
if ( ! $poll_post && class_exists( 'Kyosuki_Pop_Poll' ) ) {
	$poll_post = Kyosuki_Pop_Poll::active_poll();
}
?>

<section class="kp-pagehero">
	<div class="kp-pagehero__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<p class="kp-pagehero__kicker">★ FULL RANKING ★</p>
	<h1 class="kp-pagehero__title">カップル投票ランキング</h1>
	<?php if ( $poll_post ) : ?>
		<p class="kp-pagehero__desc"><?php echo esc_html( $poll_post->post_title ); ?></p>
	<?php endif; ?>
</section>

<div class="kp-archive-wrap">
<div class="kp-archive">
	<?php if ( ! $poll_post || ! class_exists( 'Kyosuki_Pop_Poll' ) ) : ?>
		<p class="kp-empty">★ 開催中の投票がありません ★</p>
	<?php else :
		$poll_opts = Kyosuki_Pop_Poll::get_options_list( $poll_post->ID );
		$poll_data = Kyosuki_Pop_Poll::calculate_percentages( $poll_post->ID );
		$kp_items  = array();
		foreach ( $poll_opts as $orig_i => $o ) {
			$kp_items[] = array(
				'name'  => $o['name'],
				'hash'  => $o['hash'],
				'img'   => ! empty( $o['image_url'] ) ? $o['image_url'] : kyosuki_pop_placeholder_url( crc32( $o['hash'] ) ),
				'pct'   => (int) ( $poll_data['percentages'][ $o['hash'] ] ?? 0 ),
				'votes' => (int) ( $poll_data['votes'][ $o['hash'] ] ?? 0 ),
				'_i'    => $orig_i,
			);
		}
		usort( $kp_items, function ( $a, $b ) {
			if ( $a['pct'] === $b['pct'] ) return $a['_i'] - $b['_i'];
			return $b['pct'] - $a['pct'];
		} );

		// アクティブラウンドのみ投票可。アーカイブ済みは閲覧のみ。
		$is_active   = (int) Kyosuki_Pop_Poll::get_active_poll_id() === (int) $poll_post->ID;
		$total_votes = array_sum( array_column( $kp_items, 'votes' ) );
	?>
	<div class="kp-poll-grid kp-podium kp-podium--full<?php echo $is_active ? '' : ' is-archived'; ?>"
		data-kp-poll data-kp-poll-id="<?php echo (int) $poll_post->ID; ?>">

		<p class="kp-podium__summary">
			★ 総投票数: <strong><?php echo number_format_i18n( $total_votes ); ?></strong> 票
			<?php if ( ! $is_active ) : ?>
				<span class="kp-podium__archived-flag">（アーカイブ済みラウンド）</span>
			<?php endif; ?>
		</p>

		<ol class="kp-podium__list" start="1">
			<?php foreach ( $kp_items as $i => $it ) :
				$rank = $i + 1;
				$row_cls = 'kp-poll-card kp-podium__row';
				if ( $rank <= 3 ) $row_cls .= ' kp-podium__row--' . $rank;
			?>
				<li class="kp-podium__li">
					<button type="button"
						class="<?php echo esc_attr( $row_cls ); ?>"
						data-kp-opt-hash="<?php echo esc_attr( $it['hash'] ); ?>"
						data-kp-pct="<?php echo (int) $it['pct']; ?>"
						<?php echo $is_active ? 'aria-label="' . esc_attr( $it['name'] ) . ' に投票"' : 'disabled'; ?>>
						<span class="kp-podium__row-rank"><?php echo (int) $rank; ?></span>
						<span class="kp-poll-card__photo kp-podium__row-photo">
							<img class="kp-poll-card__img" src="<?php echo esc_url( $it['img'] ); ?>" alt="">
						</span>
						<span class="kp-podium__row-body">
							<span class="kp-poll-card__name"><?php echo esc_html( $it['name'] ); ?></span>
							<span class="kp-podium__row-meta">投票数: <?php echo number_format_i18n( $it['votes'] ); ?></span>
						</span>
						<span class="kp-podium__row-pct">
							<span class="kp-poll-card__pct" data-kp-target="<?php echo (int) $it['pct']; ?>">0%</span>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ol>

		<p class="kp-poll-grid__msg" data-kp-poll-msg hidden>♡ 投票ありがとう！</p>
		<p class="kp-poll-grid__err" data-kp-poll-err hidden></p>
	</div>

	<p style="text-align:center;margin-top:28px;">
		<a class="kp-more-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">← トップに戻る</a>
	</p>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
