( function ( wp ) {
	var el = wp.element.createElement;
	var rb = wp.blocks.registerBlockType;
	var ubp = wp.blockEditor.useBlockProps;
	var IC = wp.blockEditor.InspectorControls;
	var MIPC = wp.blockEditor.MediaUploadCheck;
	var MU = wp.blockEditor.MediaUpload;
	var PB = wp.components.PanelBody;
	var TC = wp.components.TextControl;
	var SC = wp.components.SelectControl;
	var Btn = wp.components.Button;
	var Placeholder = wp.components.Placeholder;
	var __ = wp.i18n.__;

	rb( 'kyosuki/pr-box', {
		edit: function ( p ) {
			var a = p.attributes, s = p.setAttributes;
			var inspector = el( IC, {},
				el( PB, { title: __( 'PR設定', 'kyosuki-pop' ), initialOpen: true },
					el( 'p', { style: { fontSize: 12, color: '#6B5A8A', marginTop: 0 } },
						__( '商品名とURLを両方入れると、ページに表示されます。空欄の項目は表示されません。', 'kyosuki-pop' )
					),
					el( MIPC, {},
						el( MU, {
							onSelect: function ( m ) { s( { image: m.url } ); },
							allowedTypes: [ 'image' ],
							render: function ( o ) {
								return el( Btn, { variant: 'secondary', onClick: o.open },
									a.image ? __( '画像を変更', 'kyosuki-pop' ) : __( '① 商品画像を選ぶ', 'kyosuki-pop' )
								);
							}
						} )
					),
					a.image ? el( Btn, {
						variant: 'link', isDestructive: true,
						style: { marginTop: 4 },
						onClick: function () { s( { image: '' } ); }
					}, __( '画像を外す', 'kyosuki-pop' ) ) : null,
					el( TC, {
						label: __( '② ラベル（小さい黄色いバッジ）', 'kyosuki-pop' ),
						help: __( '例: [PR] みお愛用 ♡', 'kyosuki-pop' ),
						placeholder: '[PR] みお愛用 ♡',
						value: a.label, onChange: function ( v ) { s( { label: v } ); }
					} ),
					el( TC, {
						label: __( '③ 商品名（必須）', 'kyosuki-pop' ),
						help: __( '例: マシュマロリップ', 'kyosuki-pop' ),
						placeholder: 'マシュマロリップ',
						value: a.title, onChange: function ( v ) { s( { title: v } ); }
					} ),
					el( TC, {
						label: __( '④ 価格', 'kyosuki-pop' ),
						help: __( '例: ¥1,980', 'kyosuki-pop' ),
						placeholder: '¥1,980',
						value: a.price, onChange: function ( v ) { s( { price: v } ); }
					} ),
					el( TC, {
						label: __( '⑤ ショップ名', 'kyosuki-pop' ),
						help: __( '例: 楽天 / Amazon / Qoo10', 'kyosuki-pop' ),
						placeholder: '楽天',
						value: a.shop, onChange: function ( v ) { s( { shop: v } ); }
					} ),
					el( TC, {
						label: __( '⑥ リンクURL（必須）', 'kyosuki-pop' ),
						help: __( 'アフィリエイトリンクや商品ページの URL を貼ってください。', 'kyosuki-pop' ),
						placeholder: 'https://...',
						value: a.url, onChange: function ( v ) { s( { url: v } ); }
					} ),
					el( TC, {
						label: __( '⑦ ボタンの文字', 'kyosuki-pop' ),
						help: __( '例: CHECK → / 詳しく見る', 'kyosuki-pop' ),
						placeholder: 'CHECK →',
						value: a.ctaLabel, onChange: function ( v ) { s( { ctaLabel: v } ); }
					} ),
					el( SC, {
						label: __( '影のカラー', 'kyosuki-pop' ),
						value: a.tone,
						options: [
							{ label: 'ピンク', value: 'pink' },
							{ label: 'ブルー', value: 'blue' },
							{ label: 'イエロー', value: 'yellow' },
							{ label: 'パープル', value: 'purple' }
						],
						onChange: function ( v ) { s( { tone: v } ); }
					} )
				)
			);

			var hasContent = a.title || a.url || a.image;
			if ( ! hasContent ) {
				return el( 'div', ubp(),
					inspector,
					el( Placeholder, {
						icon: 'tag',
						label: __( 'PR / 商品紹介ボックス', 'kyosuki-pop' ),
						instructions: __( '右の「PR設定」パネルから、商品名・URL・画像などを入力してください。', 'kyosuki-pop' )
					} )
				);
			}

			return el( 'div', ubp(),
				inspector,
				el( 'aside', { className: 'kp-pr', style: { padding: 12, border: '2px solid #1A0B3D', borderRadius: 12 } },
					a.label ? el( 'p', { style: { fontSize: 10, background: '#FFE066', display: 'inline-block', padding: '2px 6px', fontWeight: 900, margin: 0 } }, a.label ) : null,
					a.title ? el( 'p', { style: { fontWeight: 900, fontSize: 13, margin: '6px 0 2px' } }, a.title ) : null,
					( a.price || a.shop ) ? el( 'p', { style: { fontSize: 11, margin: 0 } }, [ a.price, a.shop ].filter( Boolean ).join( ' / ' ) ) : null
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
