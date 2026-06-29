/* Kyosuki Pop — poll edit screen: visual choices editor */
( function ( $ ) {
	'use strict';

	var $container = $( '#kp-poll-choices' );
	if ( ! $container.length ) return;

	var MAX  = 10;
	var $add = $( '#kp-poll-add-choice' );
	var $cnt = $( '#kp-poll-count-hint' );

	function updateCount () {
		var n = $container.children( '.kp-choice-row' ).length;
		$cnt.text( n + ' / ' + MAX + ' 組' );
		$add.prop( 'disabled', n >= MAX );
	}

	function renumber () {
		$container.children( '.kp-choice-row' ).each( function ( i ) {
			$( this ).attr( 'data-index', i );
			$( this ).find( 'input[name]' ).each( function () {
				this.name = this.name.replace( /\[\d+\]/, '[' + i + ']' );
			} );
		} );
		updateCount();
	}

	function makeRow ( idx ) {
		return $(
			'<div class="kp-choice-row" data-index="' + idx + '">' +
				'<div class="kp-choice-thumb"><span class="kp-choice-thumb__ph">♡</span></div>' +
				'<input type="hidden" name="kp_poll_choices[' + idx + '][image_id]" value="0">' +
				'<input type="text"   name="kp_poll_choices[' + idx + '][name]" placeholder="例: りく♡みお">' +
				'<button type="button" class="button kp-choice-pick">画像を選ぶ</button>' +
				'<button type="button" class="button kp-choice-remove" aria-label="削除">×</button>' +
			'</div>'
		);
	}

	$add.on( 'click', function () {
		var n = $container.children( '.kp-choice-row' ).length;
		if ( n >= MAX ) return;
		$container.append( makeRow( n ) );
		renumber();
	} );

	$container.on( 'click', '.kp-choice-remove', function () {
		if ( ! confirm( 'この選択肢を削除しますか？（保存するまで実際の票には影響しません）' ) ) return;
		$( this ).closest( '.kp-choice-row' ).remove();
		renumber();
	} );

	$container.on( 'click', '.kp-choice-pick', function ( e ) {
		e.preventDefault();
		var $row = $( this ).closest( '.kp-choice-row' );
		var frame = wp.media( {
			title:    '画像を選ぶ',
			button:   { text: 'この画像を使う' },
			multiple: false,
			library:  { type: 'image' },
		} );
		frame.on( 'select', function () {
			var att = frame.state().get( 'selection' ).first().toJSON();
			$row.find( 'input[type=hidden]' ).val( att.id );
			var thumbUrl = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
			$row.find( '.kp-choice-thumb' ).html( '<img src="' + thumbUrl + '" alt="">' );
		} );
		frame.open();
	} );

	updateCount();

} )( jQuery );
