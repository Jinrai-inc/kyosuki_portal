<?php
/**
 * 404
 *
 * @package Kyosuki_Pop
 */
get_header(); ?>

<section class="kp-404">
	<p class="kp-404__num">404</p>
	<h1 class="kp-404__title">★ おさがしのページが見つかりません ★</h1>
	<p class="kp-404__sub">URLをご確認いただくか、検索からお探しください ♡</p>
	<form class="kp-pagesearch" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" placeholder="🔍 推しCPで検索…" />
		<button type="submit">GO ♡</button>
	</form>
	<a class="kp-404__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップへ戻る →</a>
	<div class="kp-404__hearts" aria-hidden="true">
		<?php for ( $i = 0; $i < 12; $i++ ) echo '<span>♡</span>'; ?>
	</div>
</section>

<?php get_footer(); ?>
