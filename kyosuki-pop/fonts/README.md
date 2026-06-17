# Mamelon フォントの設置について

このテーマは「自家製フォント工房」配布の **マメロン (Mamelon)** フォントを使用します。
ライセンス上、テーマには同梱できないため、以下の手順でフォントファイルを設置してください。

## 1. ダウンロード

配布元: https://jikasei.me/font/mamelon/

サイトから zip をダウンロードし、解凍してください。

## 2. ファイルの配置

このフォルダ (`kyosuki-pop/fonts/`) に以下のファイル名で配置してください。
配布物の `.otf` をそのまま使うか、`.woff2` に変換して両方置くと表示が高速になります。

```
fonts/
  Mamelon-3-Hi-Regular.otf      ← 必須 (本文用)
  Mamelon-5-Hi-Bold.otf         ← 必須 (見出し用)
  Mamelon-3-Hi-Regular.woff2    ← あれば推奨
  Mamelon-5-Hi-Bold.woff2       ← あれば推奨
```

`.woff2` は https://everythingfonts.com/otf-to-woff2 などで変換できます。

## 3. ライセンス

マメロンは「自家製フォント工房」によるフリーフォントです。
配布元の利用規約 (商用利用・再配布条件など) を必ずご確認の上ご利用ください。

設置されていない場合は Zen Maru Gothic / Hiragino Maru Gothic ProN にフォールバックします。
