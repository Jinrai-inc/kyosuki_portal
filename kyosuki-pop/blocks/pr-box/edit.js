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
	var __ = wp.i18n.__;
	rb( 'kyosuki/pr-box', {
		edit: function ( p ) {
			var a = p.attributes, s = p.setAttributes;
			return el( 'div', ubp(),
				el( IC, {},
					el( PB, { title: __( 'PR設定', 'kyosuki-pop' ) },
						el( MIPC, {},
							el( MU, {
								onSelect: function ( m ) { s( { image: m.url } ); },
								allowedTypes: [ 'image' ],
								render: function ( o ) { return el( Btn, { variant: 'secondary', onClick: o.open }, __( '画像を選択', 'kyosuki-pop' ) ); }
							} )
						),
						el( TC, { label: __( 'ラベル', 'kyosuki-pop' ), value: a.label, onChange: function ( v ) { s( { label: v } ); } } ),
						el( TC, { label: __( '商品名', 'kyosuki-pop' ), value: a.title, onChange: function ( v ) { s( { title: v } ); } } ),
						el( TC, { label: __( '価格', 'kyosuki-pop' ), value: a.price, onChange: function ( v ) { s( { price: v } ); } } ),
						el( TC, { label: __( '店舗', 'kyosuki-pop' ), value: a.shop, onChange: function ( v ) { s( { shop: v } ); } } ),
						el( TC, { label: __( 'URL', 'kyosuki-pop' ), value: a.url, onChange: function ( v ) { s( { url: v } ); } } ),
						el( TC, { label: __( 'CTAラベル', 'kyosuki-pop' ), value: a.ctaLabel, onChange: function ( v ) { s( { ctaLabel: v } ); } } ),
						el( SC, { label: __( 'トーン', 'kyosuki-pop' ), value: a.tone, options: [
							{ label: 'Pink', value: 'pink' },
							{ label: 'Blue', value: 'blue' },
							{ label: 'Yellow', value: 'yellow' },
							{ label: 'Purple', value: 'purple' },
						], onChange: function ( v ) { s( { tone: v } ); } } )
					)
				),
				el( 'aside', { className: 'kp-pr', style: { padding: 12, border: '2px solid #1A0B3D', borderRadius: 12 } },
					el( 'p', { style: { fontSize: 10, background: '#FFE066', display: 'inline-block', padding: '2px 6px', fontWeight: 900 } }, a.label ),
					el( 'p', { style: { fontWeight: 900, fontSize: 13 } }, a.title ),
					el( 'p', { style: { fontSize: 11 } }, a.price + ' / ' + a.shop )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
