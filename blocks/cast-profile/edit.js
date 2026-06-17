( function ( wp ) {
	var el = wp.element.createElement;
	var rb = wp.blocks.registerBlockType;
	var ubp = wp.blockEditor.useBlockProps;
	var IC = wp.blockEditor.InspectorControls;
	var PB = wp.components.PanelBody;
	var TC = wp.components.TextControl;
	var SC = wp.components.SelectControl;
	var Btn = wp.components.Button;
	var __ = wp.i18n.__;
	var TONES = [
		{ label: 'Pink',      value: 'pink' },
		{ label: 'Pink Deep', value: 'pink-deep' },
		{ label: 'Blue',      value: 'blue' },
		{ label: 'Yellow',    value: 'yellow' },
		{ label: 'Purple',    value: 'purple' },
		{ label: 'Lime',      value: 'lime' },
	];
	rb( 'kyosuki/cast-profile', {
		edit: function ( p ) {
			var a = p.attributes, s = p.setAttributes;
			var items = a.items || [];
			function update( i, k, v ) {
				var next = items.slice();
				next[ i ] = Object.assign( {}, next[ i ], { [k]: v } );
				s( { items: next } );
			}
			function add() {
				s( { items: items.concat( [ { key: '項目', value: '', tone: 'pink' } ] ) } );
			}
			function remove( i ) {
				s( { items: items.filter( function ( _, j ) { return j !== i; } ) } );
			}
			return el( 'div', ubp(),
				el( IC, {},
					el( PB, { title: __( 'プロフィール項目', 'kyosuki-pop' ) },
						items.map( function ( it, i ) {
							return el( 'div', { key: i, style: { borderBottom: '1px solid #ddd', paddingBottom: 8, marginBottom: 8 } },
								el( TC, { label: __( '項目名', 'kyosuki-pop' ), value: it.key, onChange: function ( v ) { update( i, 'key', v ); } } ),
								el( TC, { label: __( '値', 'kyosuki-pop' ), value: it.value, onChange: function ( v ) { update( i, 'value', v ); } } ),
								el( SC, { label: __( '色', 'kyosuki-pop' ), value: it.tone, options: TONES, onChange: function ( v ) { update( i, 'tone', v ); } } ),
								el( Btn, { variant: 'tertiary', isDestructive: true, onClick: function () { remove( i ); } }, __( '削除', 'kyosuki-pop' ) )
							);
						} ),
						el( Btn, { variant: 'primary', onClick: add }, __( '+ 項目を追加', 'kyosuki-pop' ) )
					)
				),
				el( 'div', { style: { display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 6 } },
					items.map( function ( it, i ) {
						return el( 'div', { key: i, style: { background: '#fff', border: '2px solid #1A0B3D', borderRadius: 10, padding: 8, textAlign: 'center' } },
							el( 'div', { style: { fontSize: 10, color: '#6B5A8A' } }, it.key ),
							el( 'div', { style: { fontWeight: 900 } }, it.value )
						);
					} )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
