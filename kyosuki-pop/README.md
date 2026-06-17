# Kyosuki Pop — WordPress Block Theme

Y2Kポップ世界観の高校生恋愛リアリティ番組まとめサイト用ブロックテーマです。

## 特徴

- **FSE (Full Site Editing)** : `theme.json` + `templates/` + `parts/` でフルサイト編集に対応
- **モバイルファースト** : SP最適化レイアウト + 下タブナビ + ハンバーガーメニュー
- **多言語対応** : `languages/kyosuki-pop.pot` 同梱、Polylang/WPML/TranslatePress 連携可
- **オリジナルブロック** :
  - `kyosuki/scoop-hero` — SCOOPヒーロー (記事ID指定/ハイライト語/トーン)
  - `kyosuki/ranking` — PV順ランキング (件数/対象/サムネ表示)
  - `kyosuki/pr-box` — PR商品ボックス (画像/価格/店舗/CTA/トーン)
  - `kyosuki/quote-bubble` — コメント吹き出し (黄色シャドウ)
  - `kyosuki/cast-profile` — 出演者プロフィール (6カラーチップ可変)
- **カスタム投稿タイプ** : `cast`(出演者) / `arc`(アーク) / `couple`(カップル)
- **カスタムタクソノミー** : `kp_arc`(アーク) / `kp_status`(ステータス) / `kp_genre`(ジャンル)
- **Emanon相当の管理機能 (カスタマイザー)** :
  - レイアウト (列数/サイドバー位置/コンテンツ幅)
  - カラーパレット (6色エディタ)
  - タイポグラフィ (フォントファミリー/サイズ)
  - SNSリンク (X / Instagram / TikTok / YouTube / LINE)
  - SEO (メタDesc / OG画像 / Twitter Card)
  - GA4 / GTM / AdSense 連携
  - 多言語スイッチャー表示制御

## インストール

1. 本フォルダ全体を `wp-content/themes/kyosuki-pop/` にアップロード
2. WP管理画面 → 外観 → テーマで「Kyosuki Pop」を有効化
3. 投稿タイプ・タクソノミー・サンプルタームが自動投入されます
4. 外観 → カスタマイズ → 今日好きテーマ設定 で各種設定

## ファイル構成

```
kyosuki-pop/
├── style.css
├── theme.json
├── functions.php
├── README.md
├── screenshot.png
├── languages/
│   └── kyosuki-pop.pot
├── inc/
│   ├── post-types.php
│   ├── taxonomies.php
│   ├── blocks.php
│   ├── patterns.php
│   ├── customizer.php
│   ├── template-functions.php
│   └── widgets.php
├── templates/
│   ├── index.html
│   ├── single.html
│   ├── archive.html
│   ├── search.html
│   ├── 404.html
│   ├── page.html
│   ├── page-about.html
│   ├── page-cast.html
│   ├── page-couple.html
│   └── page-ranking.html
├── parts/
│   ├── header.html
│   ├── footer.html
│   ├── sidebar.html
│   └── mobile-tabbar.html
├── patterns/
│   ├── scoop-hero.php
│   ├── genre-chips.php
│   ├── top-ranking.php
│   ├── carousel.php
│   ├── cast-grid.php
│   ├── share-buttons.php
│   └── pr-box.php
├── blocks/
│   ├── scoop-hero/   (block.json + edit.js + render.php)
│   ├── ranking/
│   ├── pr-box/
│   ├── quote-bubble/
│   └── cast-profile/
└── assets/
    ├── css/
    │   ├── theme.css
    │   └── editor.css
    ├── js/
    │   └── theme.js
    └── img/
        └── placeholder.svg
```

## 多言語化

- 翻訳テンプレート: `languages/kyosuki-pop.pot`
- 日本語以外も対応可。`languages/kyosuki-pop-en_US.po` 等を生成してください
- 多言語プラグイン (Polylang/WPML/TranslatePress) と併用可

## ライセンス

GPL v2 or later
