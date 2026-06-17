( function ( wp ) {
	var el = wp.element.createElement;
	var rb = wp.blocks.registerBlockType;
	var ubp = wp.blockEditor.useBlockProps;
	var IC = wp.blockEditor.InspectorControls;
	var PB = wp.components.PanelBody;
	var TC = wp.components.TextControl;
	var TA = wp.components.TextareaControl;
	var SC = wp.components.SelectControl;
	var __ = wp.i18n.__;
	rb( 'kyosuki/quote-bubble', {
		edit: function ( p ) {
			var a = p.attributes, s = p.setAttributes;
			return el( 'div', ubp(),
				el( IC, {},
					el( PB, { title: __( '吹き出し設定', 'kyosuki-pop' ) },
						el( TC, { label: __( '話者', 'kyosuki-pop' ), value: a.speaker, onChange: function ( v ) { s( { speaker: v } ); } } ),
						el( TA, { label: __( 'セリフ', 'kyosuki-pop' ), value: a.quote, onChange: function ( v ) { s( { quote: v } ); } } ),
						el( SC, { label: __( '影トーン', 'kyosuki-pop' ), value: a.tone, options: [
							{ label: 'Yellow', value: 'yellow' },
							{ label: 'Pink',   value: 'pink' },
							{ label: 'Blue',   value: 'blue' },
							{ label: 'Lime',   value: 'lime' },
							{ label: 'Purple', value: 'purple' },
						], onChange: function ( v ) { s( { tone: v } ); } } )
					)
				),
				el( 'div', { style: { padding: 14, border: '2px solid #1A0B3D', borderRadius: 12, background: '#fff' } },
					el( 'p', { style: { fontWeight: 900, color: '#E83E8C', fontSize: 11 } }, '♡ ' + a.speaker + 'のコメント' ),
					el( 'p', { style: { fontStyle: 'italic' } }, '「' + a.quote + '」' )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
