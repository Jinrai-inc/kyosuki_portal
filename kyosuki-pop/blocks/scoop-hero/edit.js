/* Kyosuki SCOOP Hero — Editor */
( function ( wp ) {
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var __ = wp.i18n.__;

	registerBlockType( 'kyosuki/scoop-hero', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			return el( 'div', useBlockProps(),
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'SCOOP設定', 'kyosuki-pop' ) },
						el( TextControl, { label: __( '記事ID', 'kyosuki-pop' ), value: a.postId, type: 'number', onChange: function ( v ) { set( { postId: parseInt( v, 10 ) || 0 } ); } } ),
						el( TextControl, { label: __( 'キッカー', 'kyosuki-pop' ), value: a.kicker, onChange: function ( v ) { set( { kicker: v } ); } } ),
						el( TextControl, { label: __( 'ステッカー文言', 'kyosuki-pop' ), value: a.sticker, onChange: function ( v ) { set( { sticker: v } ); } } ),
						el( TextControl, { label: __( '円バッジ文言', 'kyosuki-pop' ), value: a.circle, onChange: function ( v ) { set( { circle: v } ); } } ),
						el( TextControl, { label: __( 'CTAラベル', 'kyosuki-pop' ), value: a.ctaLabel, onChange: function ( v ) { set( { ctaLabel: v } ); } } ),
						el( TextControl, { label: __( 'ハイライトする語', 'kyosuki-pop' ), value: a.highlightWord, onChange: function ( v ) { set( { highlightWord: v } ); } } ),
						el( SelectControl, { label: __( 'トーン', 'kyosuki-pop' ), value: a.tone, options: [
							{ label: 'Pink',   value: 'pink' },
							{ label: 'Blue',   value: 'blue' },
							{ label: 'Yellow', value: 'yellow' },
							{ label: 'Purple', value: 'purple' },
						], onChange: function ( v ) { set( { tone: v } ); } } )
					)
				),
				el( 'div', { className: 'kp-scoop', style: { padding: 14, background: '#fff', border: '3px solid #1A0B3D', borderRadius: 18, boxShadow: '6px 6px 0 #1A0B3D' } },
					el( 'p', { style: { color: '#E83E8C', fontWeight: 900, fontSize: 11, letterSpacing: 2 } }, a.kicker ),
					el( 'p', { style: { fontSize: 18, fontWeight: 900, lineHeight: 1.3 } }, __( '[SCOOP記事プレビュー — フロントで実投稿表示]', 'kyosuki-pop' ) )
				)
			);
		},
		save: function () { return null; },
	} );
} )( window.wp );
