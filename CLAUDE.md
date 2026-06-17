# Kyosuki Portal — Claude Code 作業ガイド

このリポジトリは WordPress テーマ **Kyosuki Pop**（Y2K ポップ世界観の高校生恋愛リアリティ番組まとめサイト用ブロックテーマ）を管理します。Claude Code でテーマを編集する際の前提情報をまとめています。

## リポジトリ構成

```
kyosuki_portal/
└── kyosuki-pop/             ← WordPress テーマ本体（このフォルダごと wp-content/themes/ に配置）
    ├── style.css            テーマヘッダ（Theme Name / Version 等）
    ├── theme.json           グローバルスタイル / カラー / フォントサイズ / シャドウプリセット
    ├── functions.php        セットアップ / アセットエンキュー / inc/* の読み込み
    ├── screenshot.png       テーマ一覧用スクリーンショット
    ├── 404.php / archive.php / comments.php / footer.php / header.php
    ├── index.php / page.php / search.php / searchform.php / sidebar.php / single.php
    │                        クラシックPHPテンプレート
    ├── inc/
    │   ├── post-types.php       カスタム投稿タイプ cast / arc / couple
    │   ├── taxonomies.php       kp_arc / kp_status / kp_genre + 初期ターム seed
    │   ├── blocks.php           blocks/ 配下を一括 register_block_type
    │   ├── patterns.php         patterns/ 配下の登録
    │   ├── customizer.php       レイアウト/色/タイポ/SNS/SEO/GA4/AdSense 設定 UI
    │   ├── template-functions.php  共通ヘルパー (kyosuki_pop_get_primary_arc 等)
    │   ├── poll-api.php         REST: /wp-json/kyosuki-pop/v1/poll
    │   ├── like-api.php         REST: /wp-json/kyosuki-pop/v1/like/{id}
    │   └── widgets.php          ウィジェットエリア登録
    ├── blocks/                  オリジナルブロック（block.json + edit.js + render.php）
    │   ├── scoop-hero/          SCOOP ヒーロー（記事ID指定 / ハイライト語 / トーン）
    │   ├── ranking/             PV順ランキング（件数 / 対象 / サムネ表示）
    │   ├── pr-box/              PR商品ボックス（画像 / 価格 / 店舗 / CTA / トーン）
    │   ├── quote-bubble/        コメント吹き出し（黄色シャドウ）
    │   └── cast-profile/        出演者プロフィール（6カラーチップ可変）
    ├── patterns/                ブロックパターン PHP
    ├── assets/
    │   ├── css/theme.css        フロント本体スタイル（~47KB）
    │   ├── css/editor.css       エディタ用スタイル
    │   ├── js/theme.js          投票・いいね・UI 系
    │   └── img/placeholder.svg
    ├── fonts/README.md          フォント運用メモ
    └── languages/kyosuki-pop.pot  翻訳テンプレ
```

## 主要な仕様メモ

- **テーマタイプ**: クラシック PHP テンプレート + theme.json + カスタムブロックのハイブリッド構成（純粋な FSE ではなく、`templates/*.html` や `parts/*.html` は存在しない。README に書かれている FSE 表記は実装と差分あり）。
- **テキストドメイン**: `kyosuki-pop`
- **バージョン定数**: `KYOSUKI_POP_VERSION` (`functions.php`)、style.css の `Version:` と二系統あるので変更時は両方更新する。
- **ブロック名前空間**: `kyosuki/*`（例: `kyosuki/scoop-hero`）。ブロックカテゴリは `kyosuki`（`inc/blocks.php`）。
- **メニューロケーション**: `primary` / `mobile` / `footer`
- **カラーパレット**（theme.json）: bg / ink / sub / pink / pink-deep / blue / purple / yellow / lime / white
- **シャドウプリセット**: pop / pop-lg / pop-pink / pop-yellow（黒縁+箱影が世界観の核）
- **フォント**: LINE Seed JP（本文）、Reggae One（見出し装飾）を Google Fonts から enqueue
- **CSS クラスのプレフィックス**: `kp-*`（例: `kp-scoop`, `kp-blob--pink`）

## カスタム投稿タイプ / タクソノミー

| Post Type | Slug   | 用途       | 主なタクソノミー        |
|-----------|--------|-----------|------------------------|
| cast      | cast   | 出演者     | kp_arc                 |
| arc       | arc    | アーク／シーズン | -                  |
| couple    | couple | カップル   | kp_arc, kp_status      |
| post      | -      | 記事       | kp_arc, kp_genre       |

- `kp_arc`: 沖縄編 / 北海道編 / 修学旅行 / 放課後 / 文化祭 / 夏休み / 春休み / 卒業編
- `kp_status`: 成立 / 交際 / 破局 / 進行中
- `kp_genre`: 密着 / コーデ / PR / 考察 / ネタバレ / ロケ地

初回 `init` で `kyosuki_pop_seeded` オプションをトリガーに自動投入されます（`inc/taxonomies.php`）。

## REST エンドポイント

- `POST /wp-json/kyosuki-pop/v1/poll` — 投票
- `POST /wp-json/kyosuki-pop/v1/like/{id}` — いいね
  - フロントで `KP_POLL` グローバルとして `restUrl` / `likeUrl` / `nonce` が `wp_localize_script` 経由で渡される。

## よくある編集タスク

| やりたいこと                       | 編集ファイル                                                 |
|------------------------------------|------------------------------------------------------------|
| トップページの SCOOP セクション差替 | `kyosuki-pop/index.php`（`kp-scoop` ブロック）              |
| 色変更                              | `kyosuki-pop/theme.json` の `settings.color.palette` + `assets/css/theme.css` |
| 新規カスタムブロック追加            | `kyosuki-pop/blocks/<name>/` に `block.json` + `edit.js` + `render.php`。`inc/blocks.php` が自動検出 |
| 投稿タイプ追加                      | `kyosuki-pop/inc/post-types.php`                            |
| カスタマイザー項目追加              | `kyosuki-pop/inc/customizer.php`                            |
| パターン追加                        | `kyosuki-pop/patterns/*.php`（`inc/patterns.php` 経由で登録）|
| ヘッダー / フッター UI              | `kyosuki-pop/header.php` / `footer.php`                     |
| グローバル JS（投票・タブ等）        | `kyosuki-pop/assets/js/theme.js`                            |

## バージョン更新のルール

テーマ仕様を変更した場合は以下の 2 か所を必ず同じバージョンに更新します:

1. `kyosuki-pop/style.css` の `Version:` ヘッダ
2. `kyosuki-pop/functions.php` の `KYOSUKI_POP_VERSION` 定数

## デプロイ手順（参考）

1. `kyosuki-pop/` フォルダごと `wp-content/themes/` にアップロード
2. 管理画面 → 外観 → テーマで「Kyosuki Pop」を有効化
3. 投稿タイプ・タクソノミー・初期タームは初回 `init` で自動投入
4. 外観 → カスタマイズ → 今日好きテーマ設定 で各種設定

## ライセンス

GPL v2 or later
