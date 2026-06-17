# Kyosuki Portal — Claude Code 作業ガイド

このリポジトリは WordPress テーマ **Kyosuki Pop**（Y2K ポップ世界観の高校生恋愛リアリティ番組まとめサイト用ブロックテーマ）を管理します。Claude Code でテーマを編集する際の前提情報をまとめています。

> **リポジトリ＝WordPress テーマ本体**。リポジトリ直下が `wp-content/themes/kyosuki-pop/` にそのまま配置されるテーマフォルダです（クローンしたフォルダを `kyosuki-pop` にリネームしてアップロードしてください。GitHub Actions による zip 配布ワークフロー `.github/workflows/build-theme.yml` を使うと、自動で正しいフォルダ名の zip が得られます）。

## リポジトリ構成

```
kyosuki_portal/                  ← clone 後に "kyosuki-pop" にリネームして wp-content/themes/ に配置
├── style.css                    テーマヘッダ（Theme Name / Version 等）
├── theme.json                   グローバルスタイル / カラー / フォントサイズ / シャドウプリセット
├── functions.php                セットアップ / アセットエンキュー / inc/* の読み込み
├── screenshot.png               テーマ一覧用スクリーンショット
├── 404.php / archive.php / comments.php / footer.php / header.php
├── index.php / page.php / search.php / searchform.php / sidebar.php / single.php
│                                クラシックPHPテンプレート
├── inc/
│   ├── post-types.php           カスタム投稿タイプ cast / arc / couple
│   ├── taxonomies.php           kp_arc / kp_status / kp_genre + 初期ターム seed
│   ├── blocks.php               blocks/ 配下を一括 register_block_type
│   ├── patterns.php             patterns/ 配下の登録
│   ├── customizer.php           レイアウト/色/タイポ/SNS/SEO/GA4/AdSense 設定 UI
│   ├── template-functions.php   共通ヘルパー (kyosuki_pop_get_primary_arc 等)
│   ├── poll-api.php             REST: /wp-json/kyosuki-pop/v1/poll
│   ├── like-api.php             REST: /wp-json/kyosuki-pop/v1/like/{id}
│   └── widgets.php              ウィジェットエリア登録
├── blocks/                      オリジナルブロック（block.json + edit.js + render.php）
│   ├── scoop-hero/              SCOOP ヒーロー（記事ID指定 / ハイライト語 / トーン）
│   ├── ranking/                 PV順ランキング（件数 / 対象 / サムネ表示）
│   ├── pr-box/                  PR商品ボックス（画像 / 価格 / 店舗 / CTA / トーン）
│   ├── quote-bubble/            コメント吹き出し（黄色シャドウ）
│   └── cast-profile/            出演者プロフィール（6カラーチップ可変）
├── patterns/                    ブロックパターン PHP
├── assets/
│   ├── css/theme.css            フロント本体スタイル（~47KB）
│   ├── css/editor.css           エディタ用スタイル
│   ├── js/theme.js              投票・いいね・UI 系
│   └── img/placeholder.svg
├── fonts/README.md              フォント運用メモ
├── languages/kyosuki-pop.pot    翻訳テンプレ
│
├── README.md                    テーマ説明（WordPress.org 風）
├── CLAUDE.md                    本ファイル（Claude Code 用ガイド）
├── .gitignore
├── .distignore                  zip 配布時の除外パターン
├── build-theme.sh               手動で kyosuki-pop.zip を作るスクリプト
└── .github/workflows/build-theme.yml  push / release で自動 zip ビルド
```

> Claude Code/開発者向けのメタファイル（`CLAUDE.md` / `.github/` / `build-theme.sh` / `.distignore` 等）はテーマ動作に影響しません。zip 配布時には `.distignore` の除外ルールで取り除かれます。

## 主要な仕様メモ

- **テーマタイプ**: クラシック PHP テンプレート + theme.json + カスタムブロックのハイブリッド構成（純粋な FSE ではなく、`templates/*.html` や `parts/*.html` は存在しない。同梱の `README.md` の FSE 表記は実装と差分あり）。
- **テキストドメイン**: `kyosuki-pop`
- **バージョン定数**: `KYOSUKI_POP_VERSION` (`functions.php`)、`style.css` の `Version:` と二系統あるので変更時は両方更新する。
- **ブロック名前空間**: `kyosuki/*`（例: `kyosuki/scoop-hero`）。ブロックカテゴリは `kyosuki`（`inc/blocks.php`）。
- **メニューロケーション**: `primary` / `mobile` / `footer`
- **カラーパレット**（theme.json）: bg / ink / sub / pink / pink-deep / blue / purple / yellow / lime / white
- **シャドウプリセット**: pop / pop-lg / pop-pink / pop-yellow（黒縁+箱影が世界観の核）
- **フォント**: LINE Seed JP（本文）、Reggae One（見出し装飾）を Google Fonts から enqueue
- **CSS クラスのプレフィックス**: `kp-*`（例: `kp-scoop`, `kp-blob--pink`）

## カスタム投稿タイプ / タクソノミー

| Post Type | Slug   | 用途             | 主なタクソノミー    |
|-----------|--------|-----------------|--------------------|
| cast      | cast   | 出演者           | kp_arc             |
| arc       | arc    | アーク／シーズン  | -                  |
| couple    | couple | カップル         | kp_arc, kp_status  |
| post      | -      | 記事             | kp_arc, kp_genre   |

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
| トップページの SCOOP セクション差替 | `index.php`（`kp-scoop` ブロック）                          |
| 色変更                              | `theme.json` の `settings.color.palette` + `assets/css/theme.css` |
| 新規カスタムブロック追加            | `blocks/<name>/` に `block.json` + `edit.js` + `render.php`。`inc/blocks.php` が自動検出 |
| 投稿タイプ追加                      | `inc/post-types.php`                                        |
| カスタマイザー項目追加              | `inc/customizer.php`                                        |
| パターン追加                        | `patterns/*.php`（`inc/patterns.php` 経由で登録）            |
| ヘッダー / フッター UI              | `header.php` / `footer.php`                                 |
| グローバル JS（投票・タブ等）        | `assets/js/theme.js`                                        |

## バージョン更新のルール

テーマ仕様を変更した場合は以下の 2 か所を必ず同じバージョンに更新します:

1. `style.css` の `Version:` ヘッダ
2. `functions.php` の `KYOSUKI_POP_VERSION` 定数

## 配布用 zip の作り方

WordPress 管理画面からアップロードできる zip を作るには、フォルダ名が `kyosuki-pop` である必要があります。次の 2 通りの方法があります。

### A. ローカルで作る

```sh
./build-theme.sh
# → dist/kyosuki-pop.zip が生成される
```

### B. GitHub Actions で作る

- `main` / `claude/*` ブランチへ push、もしくは GitHub のリリース作成時に
  `.github/workflows/build-theme.yml` が `kyosuki-pop.zip` をビルドし、
  Actions アーティファクト（リリース時はリリースファイル）として添付します。

### C. 直接クローンして使う

```sh
git clone https://github.com/Jinrai-inc/kyosuki_portal.git kyosuki-pop
cd kyosuki-pop && rm -rf .git
# wp-content/themes/kyosuki-pop/ に配置
```

## デプロイ手順（参考）

1. 上記のいずれかで `kyosuki-pop/` フォルダ or `kyosuki-pop.zip` を用意
2. WP 管理画面 → 外観 → テーマ → 新規追加 → テーマのアップロード で zip を選ぶ
   （または `wp-content/themes/kyosuki-pop/` に直接 FTP/SFTP）
3. 「Kyosuki Pop」を有効化
4. 投稿タイプ・タクソノミー・初期タームは初回 `init` で自動投入
5. 外観 → カスタマイズ → 今日好きテーマ設定 で各種設定

## ライセンス

GPL v2 or later
