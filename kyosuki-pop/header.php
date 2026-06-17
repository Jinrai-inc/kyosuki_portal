<?php
/**
 * Header — クラシックPHPテンプレート版
 *
 * @package Kyosuki_Pop
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta name="theme-color" content="#FF7AC6" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'kp-body' ); ?>>
<?php wp_body_open(); ?>

<a class="kp-skiplink" href="#kp-main"><?php esc_html_e( 'コンテンツへスキップ', 'kyosuki-pop' ); ?></a>

<?php if ( get_theme_mod( 'kp_show_livebar', true ) ) : ?>
	<div class="kp-livebar">
		<span class="badge">◉ LIVE</span>
		<marquee behavior="scroll" direction="left" scrollamount="3"><?php echo wp_kses_post( get_theme_mod( 'kp_livebar_text', '新着 14 件 / フォロワー 42.3K ♡  ★  今日もリアタイで観る？  ★  沖縄編 第8話 木曜21:00 配信  ♡  CP成立速報チェック！' ) ); ?></marquee>
	</div>
<?php endif; ?>

<header class="kp-header" role="banner">
	<div class="kp-header__inner">
		<div class="kp-logo">
			<?php if ( has_custom_logo() ) {
				the_custom_logo();
			} else { ?>
				<span class="kp-logo__mark">♡</span>
			<?php } ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kp-logo__text">
				<span class="kp-logo__main"><?php bloginfo( 'name' ); ?></span>
				<span class="kp-logo__sub">★ TODAY SUKI POP MAG ★</span>
			</a>
		</div>

		<nav class="kp-nav" aria-label="<?php esc_attr_e( 'グローバルメニュー', 'kyosuki-pop' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'kp-nav__list',
					'depth'          => 2,
					'fallback_cb'    => false,
				) );
			} else { ?>
				<ul class="kp-nav__list">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a></li>
					<li><a href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>">NEWS</a></li>
					<li><a href="<?php echo esc_url( home_url( '/couple/' ) ); ?>">CP図鑑</a></li>
					<li><a href="<?php echo esc_url( home_url( '/ranking/' ) ); ?>">RANKING</a></li>
					<li><a href="<?php echo esc_url( home_url( '/cast/' ) ); ?>">CAST</a></li>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">ABOUT</a></li>
				</ul>
			<?php } ?>
		</nav>

		<form class="kp-headersearch" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="🔍 推しCPを検索…" value="<?php echo esc_attr( get_search_query() ); ?>" />
			<button type="submit" aria-label="検索">GO</button>
		</form>

		<button class="kp-hamburger" aria-label="<?php esc_attr_e( 'メニュー', 'kyosuki-pop' ); ?>" aria-expanded="false">
			<span></span><span></span><span></span>
		</button>
	</div>
</header>

<div class="kp-drawer" role="dialog" aria-modal="true" aria-hidden="true">
	<div class="kp-drawer__panel">
		<button class="kp-drawer__close" aria-label="閉じる">×</button>
		<p class="kp-drawer__kicker">★ MENU ★</p>
		<?php
		if ( has_nav_menu( 'mobile' ) ) {
			wp_nav_menu( array( 'theme_location' => 'mobile', 'container' => false, 'menu_class' => 'kp-drawer__list' ) );
		} else { ?>
			<ul class="kp-drawer__list">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">🏠 HOME</a></li>
				<li><a href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>">📰 NEWS</a></li>
				<li><a href="<?php echo esc_url( home_url( '/couple/' ) ); ?>">♡ CP図鑑</a></li>
				<li><a href="<?php echo esc_url( home_url( '/ranking/' ) ); ?>">⭐ RANKING</a></li>
				<li><a href="<?php echo esc_url( home_url( '/cast/' ) ); ?>">👤 CAST</a></li>
				<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">ℹ ABOUT</a></li>
			</ul>
		<?php } ?>
		<div class="kp-drawer__sns">
			<?php foreach ( array( 'x' => 'X', 'instagram' => 'IG', 'tiktok' => 'TK', 'youtube' => 'YT', 'line' => 'LINE' ) as $k => $label ) :
				$url = get_theme_mod( "kp_sns_{$k}" );
				if ( $url ) printf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $url ), esc_html( $label ) );
			endforeach; ?>
		</div>
	</div>
</div>

<main id="kp-main" class="kp-main" role="main">
