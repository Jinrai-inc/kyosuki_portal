<?php
/**
 * Layout & Design (Emanon-style) — カスタマイザーで色/フォント/レイアウト/SNS/SEO/AdSense等を制御
 *
 * @package Kyosuki_Pop
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Kyosuki_Pop_Customizer {

	public static function init() {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
		add_action( 'wp_head',             array( __CLASS__, 'inject_dynamic_css' ), 99 );
		add_action( 'wp_head',             array( __CLASS__, 'inject_seo_meta' ), 5 );
		add_action( 'wp_footer',           array( __CLASS__, 'inject_analytics' ) );
		add_action( 'wp_head',             array( __CLASS__, 'inject_adsense' ) );
	}

	public static function register( $wp ) {
		// --- Panel: KP Settings ---
		$wp->add_panel( 'kp_panel', array(
			'title'    => __( '今日好きテーマ設定', 'kyosuki-pop' ),
			'priority' => 30,
		) );

		// === Layout ===
		$wp->add_section( 'kp_layout', array( 'title' => __( 'レイアウト', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_layout_archive', array( 'default' => '3col', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_layout_archive', array(
			'section' => 'kp_layout', 'type' => 'select',
			'label'   => __( 'アーカイブ列数', 'kyosuki-pop' ),
			'choices' => array( '2col' => '2列', '3col' => '3列', '4col' => '4列', 'list' => 'リスト' ),
		) );
		$wp->add_setting( 'kp_layout_sidebar', array( 'default' => 'right', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_layout_sidebar', array(
			'section' => 'kp_layout', 'type' => 'select',
			'label'   => __( 'サイドバー位置', 'kyosuki-pop' ),
			'choices' => array( 'right' => '右', 'left' => '左', 'none' => 'なし(1カラム)' ),
		) );
		$wp->add_setting( 'kp_content_width', array( 'default' => 1200, 'sanitize_callback' => 'absint' ) );
		$wp->add_control( 'kp_content_width', array(
			'section' => 'kp_layout', 'type' => 'number',
			'label'   => __( 'コンテンツ最大幅(px)', 'kyosuki-pop' ),
		) );

		// === Colors ===
		$wp->add_section( 'kp_colors', array( 'title' => __( 'カラー', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$colors = array(
			'kp_color_pink'     => array( '#FF7AC6', 'メインピンク' ),
			'kp_color_blue'     => array( '#5AC8FA', 'アクセントブルー' ),
			'kp_color_yellow'   => array( '#FFE066', 'アクセントイエロー' ),
			'kp_color_purple'   => array( '#9B5DE5', 'アクセントパープル' ),
			'kp_color_ink'      => array( '#1A0B3D', 'インク(黒)' ),
			'kp_color_bg'       => array( '#EFE7FF', '背景' ),
		);
		foreach ( $colors as $id => $meta ) {
			$wp->add_setting( $id, array( 'default' => $meta[0], 'sanitize_callback' => 'sanitize_hex_color' ) );
			$wp->add_control( new WP_Customize_Color_Control( $wp, $id, array(
				'section' => 'kp_colors', 'label' => $meta[1],
			) ) );
		}

		// === Typography ===
		$wp->add_section( 'kp_typo', array( 'title' => __( 'タイポグラフィ', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_font_family', array( 'default' => 'zen-maru', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_font_family', array(
			'section' => 'kp_typo', 'type' => 'select',
			'label'   => __( '本文フォント', 'kyosuki-pop' ),
			'choices' => array(
				'line-seed' => 'LINE Seed JP',
				'reggae'    => 'Reggae One (見出し向き)',
				'noto'      => 'Noto Sans JP',
				'system'    => 'システムフォント',
			),
		) );
		$wp->add_setting( 'kp_font_size_base', array( 'default' => 14, 'sanitize_callback' => 'absint' ) );
		$wp->add_control( 'kp_font_size_base', array(
			'section' => 'kp_typo', 'type' => 'number',
			'label'   => __( '本文フォントサイズ(px)', 'kyosuki-pop' ),
		) );

		// === Header / Logo ===
		$wp->add_section( 'kp_header', array( 'title' => __( 'ヘッダー', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_show_livebar', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp->add_control( 'kp_show_livebar', array(
			'section' => 'kp_header', 'type' => 'checkbox',
			'label'   => __( 'LIVEバーを表示', 'kyosuki-pop' ),
		) );
		$wp->add_setting( 'kp_livebar_text', array( 'default' => '新着 14 件 / フォロワー 42.3K ♡', 'sanitize_callback' => 'wp_kses_post' ) );
		$wp->add_control( 'kp_livebar_text', array( 'section' => 'kp_header', 'type' => 'text', 'label' => __( 'LIVEバーのテキスト', 'kyosuki-pop' ) ) );

		// === SNS ===
		$wp->add_section( 'kp_sns', array( 'title' => __( 'SNS', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		foreach ( array( 'x', 'instagram', 'tiktok', 'youtube', 'line' ) as $sns ) {
			$wp->add_setting( "kp_sns_{$sns}", array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
			$wp->add_control( "kp_sns_{$sns}", array(
				'section' => 'kp_sns', 'type' => 'url',
				'label'   => strtoupper( $sns ) . ' URL',
			) );
		}

		// === SEO ===
		$wp->add_section( 'kp_seo', array( 'title' => __( 'SEO / OGP', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_meta_description', array( 'default' => '', 'sanitize_callback' => 'wp_kses_post' ) );
		$wp->add_control( 'kp_meta_description', array( 'section' => 'kp_seo', 'type' => 'textarea', 'label' => __( 'メタディスクリプション', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_og_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( new WP_Customize_Image_Control( $wp, 'kp_og_image', array( 'section' => 'kp_seo', 'label' => __( 'OG画像', 'kyosuki-pop' ) ) ) );
		$wp->add_setting( 'kp_twitter_card', array( 'default' => 'summary_large_image', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_twitter_card', array( 'section' => 'kp_seo', 'type' => 'select', 'label' => __( 'Twitter Card', 'kyosuki-pop' ),
			'choices' => array( 'summary' => 'summary', 'summary_large_image' => 'summary_large_image' ) ) );

		// === Analytics ===
		$wp->add_section( 'kp_analytics', array( 'title' => __( '解析タグ', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_ga4', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_ga4', array( 'section' => 'kp_analytics', 'type' => 'text', 'label' => 'GA4 Measurement ID' ) );
		$wp->add_setting( 'kp_gtm', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_gtm', array( 'section' => 'kp_analytics', 'type' => 'text', 'label' => 'GTM Container ID' ) );

		// === AdSense ===
		$wp->add_section( 'kp_ads', array( 'title' => __( '広告', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_adsense_id', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_adsense_id', array( 'section' => 'kp_ads', 'type' => 'text', 'label' => 'AdSense Publisher ID (ca-pub-XXXX)' ) );
		$wp->add_setting( 'kp_ads_auto', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp->add_control( 'kp_ads_auto', array( 'section' => 'kp_ads', 'type' => 'checkbox', 'label' => __( '自動広告を有効化', 'kyosuki-pop' ) ) );

		// === PR Box (サイドバナー) ===
		$wp->add_section( 'kp_pr', array( 'title' => __( 'PR/アフィリエイトボックス', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_pr_enabled', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp->add_control( 'kp_pr_enabled', array( 'section' => 'kp_pr', 'type' => 'checkbox', 'label' => __( 'PRボックスを表示する', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_pr_label', array( 'default' => '[PR] みお愛用 ♡', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_pr_label', array( 'section' => 'kp_pr', 'type' => 'text', 'label' => __( 'ラベル', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_pr_title', array( 'default' => 'マシュマロリップ', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_pr_title', array( 'section' => 'kp_pr', 'type' => 'text', 'label' => __( '商品名', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_pr_price', array( 'default' => '¥1,980 / 楽天', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_pr_price', array( 'section' => 'kp_pr', 'type' => 'text', 'label' => __( '価格 / ショップ', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_pr_url', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( 'kp_pr_url', array( 'section' => 'kp_pr', 'type' => 'url', 'label' => __( 'リンクURL', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_pr_cta', array( 'default' => 'CHECK →', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_pr_cta', array( 'section' => 'kp_pr', 'type' => 'text', 'label' => __( 'ボタン文言', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_pr_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( new WP_Customize_Image_Control( $wp, 'kp_pr_image', array( 'section' => 'kp_pr', 'label' => __( '商品画像', 'kyosuki-pop' ) ) ) );

		// === Poll (カップル投票) ===
		$wp->add_section( 'kp_poll', array( 'title' => __( 'カップル投票', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_poll_enabled', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp->add_control( 'kp_poll_enabled', array( 'section' => 'kp_poll', 'type' => 'checkbox', 'label' => __( '投票ボックスを表示する', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_poll_title', array( 'default' => '★ 今週の推しCPは？', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_poll_title', array( 'section' => 'kp_poll', 'type' => 'text', 'label' => __( '投票タイトル', 'kyosuki-pop' ) ) );
		$wp->add_setting( 'kp_poll_options', array(
			'default' => "りく♡みお|78\nゆうた♡あい|54\nけんと♡なな|32\nそら♡まりん|21",
			'sanitize_callback' => 'sanitize_textarea_field',
		) );
		$wp->add_control( 'kp_poll_options', array(
			'section' => 'kp_poll', 'type' => 'textarea',
			'label'   => __( '選択肢 (1行1件「名前|初期%」)', 'kyosuki-pop' ),
			'description' => __( '例: りく♡みお|78', 'kyosuki-pop' ),
		) );
		$wp->add_setting( 'kp_poll_cta', array( 'default' => '投票する →', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'kp_poll_cta', array( 'section' => 'kp_poll', 'type' => 'text', 'label' => __( '投票ボタン文言', 'kyosuki-pop' ) ) );

		// === i18n ===
		$wp->add_section( 'kp_i18n', array( 'title' => __( '多言語', 'kyosuki-pop' ), 'panel' => 'kp_panel' ) );
		$wp->add_setting( 'kp_show_lang_switcher', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp->add_control( 'kp_show_lang_switcher', array(
			'section' => 'kp_i18n', 'type' => 'checkbox',
			'label'   => __( 'Polylang/WPML/TranslatePress連携で言語切替を表示', 'kyosuki-pop' ),
		) );
	}

	public static function inject_dynamic_css() {
		$pink   = get_theme_mod( 'kp_color_pink',   '#FF7AC6' );
		$blue   = get_theme_mod( 'kp_color_blue',   '#5AC8FA' );
		$yellow = get_theme_mod( 'kp_color_yellow', '#FFE066' );
		$purple = get_theme_mod( 'kp_color_purple', '#9B5DE5' );
		$ink    = get_theme_mod( 'kp_color_ink',    '#1A0B3D' );
		$bg     = get_theme_mod( 'kp_color_bg',     '#EFE7FF' );
		$base   = (int) get_theme_mod( 'kp_font_size_base', 14 );
		$cw     = (int) get_theme_mod( 'kp_content_width', 1200 );

		echo '<style id="kp-dynamic">:root{--kp-pink:' . esc_attr( $pink ) . ';--kp-blue:' . esc_attr( $blue ) . ';--kp-yellow:' . esc_attr( $yellow ) . ';--kp-purple:' . esc_attr( $purple ) . ';--kp-ink:' . esc_attr( $ink ) . ';--kp-bg:' . esc_attr( $bg ) . ';}body{font-size:' . $base . 'px;}.wp-site-blocks{max-width:' . $cw . 'px;margin:0 auto;}</style>';
	}

	public static function inject_seo_meta() {
		$desc = get_theme_mod( 'kp_meta_description' );
		$og   = get_theme_mod( 'kp_og_image' );
		$card = get_theme_mod( 'kp_twitter_card', 'summary_large_image' );

		if ( $desc && ! is_singular() ) {
			echo "<meta name=\"description\" content=\"" . esc_attr( $desc ) . "\" />\n";
		}
		if ( $og ) {
			echo "<meta property=\"og:image\" content=\"" . esc_url( $og ) . "\" />\n";
			echo "<meta name=\"twitter:card\" content=\"" . esc_attr( $card ) . "\" />\n";
		}
	}

	public static function inject_analytics() {
		$ga4 = get_theme_mod( 'kp_ga4' );
		$gtm = get_theme_mod( 'kp_gtm' );
		if ( $ga4 ) {
			echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . esc_attr( $ga4 ) . "\"></script>\n";
			echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . esc_js( $ga4 ) . "');</script>\n";
		}
		if ( $gtm ) {
			echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc_js( $gtm ) . "');</script>\n";
		}
	}

	public static function inject_adsense() {
		$pub = get_theme_mod( 'kp_adsense_id' );
		if ( ! $pub ) return;
		echo "<script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=" . esc_attr( $pub ) . "\" crossorigin=\"anonymous\"></script>\n";
	}
}
Kyosuki_Pop_Customizer::init();
