( function ( wp ) {
	var el = wp.element.createElement;
	var rb = wp.blocks.registerBlockType;
	var ubp = wp.blockEditor.useBlockProps;
	var IC = wp.blockEditor.InspectorControls;
	var PB = wp.components.PanelBody;
	var TC = wp.components.TextControl;
	var SC = wp.components.SelectControl;
	var TG = wp.components.ToggleControl;
	var __ = wp.i18n.__;
	rb( 'kyosuki/ranking', {
		edit: function ( p ) {
			var a = p.attributes, s = p.setAttributes;
			return el( 'div', ubp(),
				el( IC, {},
					el( PB, { title: __( 'ランキング設定', 'kyosuki-pop' ) },
						el( TC, { label: __( '件数', 'kyosuki-pop' ), type: 'number', value: a.limit, onChange: function ( v ) { s( { limit: parseInt( v, 10 ) || 5 } ); } } ),
						el( SC, { label: __( '対象', 'kyosuki-pop' ), value: a.postType, options: [
							{ label: '記事',     value: 'post' },
							{ label: '出演者',   value: 'cast' },
							{ label: 'カップル', value: 'couple' },
						], onChange: function ( v ) { s( { postType: v } ); } } ),
						el( TG, { label: __( 'サムネを表示', 'kyosuki-pop' ), checked: a.showThumb, onChange: function ( v ) { s( { showThumb: v } ); } } ),
						el( TC, { label: __( '見出し', 'kyosuki-pop' ), value: a.title, onChange: function ( v ) { s( { title: v } ); } } )
					)
				),
				el( 'div', { style: { background: '#fff', border: '2px solid #1A0B3D', borderRadius: 12, padding: 12 } },
					el( 'p', { style: { fontWeight: 900, color: '#E83E8C' } }, '★ RANKING (Top ' + a.limit + ') — ' + a.postType )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
