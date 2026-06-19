<?php
/**
 * Footer
 *
 * @package Kyosuki_Pop
 */
?>
</main><!-- /.kp-main -->

<footer class="kp-footer" role="contentinfo">
	<div class="kp-footer__deco" aria-hidden="true">★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★ ♡ ★</div>
	<div class="kp-footer__inner">
		<div class="kp-footer__brand">
			<p class="kp-footer__logo">♡ <?php bloginfo( 'name' ); ?> ♡</p>
			<p class="kp-footer__desc"><?php echo esc_html( get_theme_mod( 'kp_footer_desc', '高校生恋愛リアリティ番組「今日好き」をひたすら推すY2Kポップなまとめサイト。' ) ); ?></p>
			<div class="kp-footer__sns">
				<?php foreach ( array( 'x' => '𝕏', 'instagram' => 'IG', 'tiktok' => 'TK', 'youtube' => 'YT', 'line' => 'LINE' ) as $k => $label ) :
					$url = get_theme_mod( "kp_sns_{$k}" );
					if ( $url ) printf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $url ), esc_html( $label ) );
				endforeach; ?>
			</div>
		</div>

		<div class="kp-footer__cols">
			<div class="kp-footer__col">
				<h4>★ CONTENT</h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'kp-footer__list' ) );
				} else { ?>
					<ul class="kp-footer__list">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a></li>
						<li><a href="<?php echo esc_url( kyosuki_pop_ranking_url() ); ?>">RANKING</a></li>
						<li><a href="<?php echo esc_url( home_url( '/couple/' ) ); ?>">CP図鑑</a></li>
						<li><a href="<?php echo esc_url( home_url( '/cast/' ) ); ?>">CAST</a></li>
					</ul>
				<?php } ?>
			</div>
			<div class="kp-footer__col">
				<h4>★ ARC</h4>
				<ul class="kp-footer__list">
					<?php $arcs = get_terms( array( 'taxonomy' => 'kp_arc', 'hide_empty' => false, 'number' => 6 ) );
					if ( ! is_wp_error( $arcs ) ) foreach ( $arcs as $t ) {
						printf( '<li><a href="%s">#%s</a></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
					} ?>
				</ul>
			</div>
			<div class="kp-footer__col">
				<h4>♡ INFO</h4>
				<ul class="kp-footer__list">
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">編集部について</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシー</a></li>
				</ul>
			</div>
		</div>
	</div>
	<p class="kp-footer__copy">© <?php echo (int) date( 'Y' ); ?> <?php bloginfo( 'name' ); ?> — Made with ♡ in Y2K</p>
</footer>

<nav class="kp-tabbar" aria-label="<?php esc_attr_e( 'モバイルナビ', 'kyosuki-pop' ); ?>">
	<ul class="kp-tabbar__list">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo is_front_page() ? 'is-current' : ''; ?>"><span class="kp-tabbar__icon">🏠</span><span class="kp-tabbar__label">HOME</span></a></li>
		<li><a href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>"><span class="kp-tabbar__icon">📰</span><span class="kp-tabbar__label">NEWS</span></a></li>
		<li><a href="<?php echo esc_url( kyosuki_pop_ranking_url() ); ?>" class="kp-tabbar__center"><span class="kp-tabbar__icon">⭐</span><span class="kp-tabbar__label">RANK</span></a></li>
		<li><a href="<?php echo esc_url( home_url( '/couple/' ) ); ?>"><span class="kp-tabbar__icon">♡</span><span class="kp-tabbar__label">CP</span></a></li>
		<li><a href="<?php echo esc_url( home_url( '/cast/' ) ); ?>"><span class="kp-tabbar__icon">👤</span><span class="kp-tabbar__label">CAST</span></a></li>
	</ul>
</nav>

<?php wp_footer(); ?>
</body>
</html>
