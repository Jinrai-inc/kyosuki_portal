/* Kyosuki Pop — front-end JS */
( function () {
	'use strict';

	// ===== Hamburger drawer =====
	var btn   = document.querySelector( '.kp-hamburger' );
	var draw  = document.querySelector( '.kp-drawer' );
	var close = document.querySelector( '.kp-drawer__close' );
	if ( btn && draw ) {
		btn.addEventListener( 'click', function () {
			draw.classList.add( 'is-open' );
			draw.setAttribute( 'aria-hidden', 'false' );
			btn.setAttribute( 'aria-expanded', 'true' );
		} );
	}
	if ( close && draw ) {
		close.addEventListener( 'click', function () {
			draw.classList.remove( 'is-open' );
			draw.setAttribute( 'aria-hidden', 'true' );
			if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
		} );
	}
	if ( draw ) {
		draw.addEventListener( 'click', function ( e ) {
			if ( e.target === draw ) close && close.click();
		} );
	}

	// ===== Carousel: drag-to-scroll =====
	document.querySelectorAll( '[data-carousel]' ).forEach( function ( el ) {
		var down = false, startX = 0, scroll = 0;
		el.addEventListener( 'pointerdown', function ( e ) {
			down = true; startX = e.pageX; scroll = el.scrollLeft;
			el.style.cursor = 'grabbing';
		} );
		el.addEventListener( 'pointerup',     function () { down = false; el.style.cursor = ''; } );
		el.addEventListener( 'pointerleave',  function () { down = false; el.style.cursor = ''; } );
		el.addEventListener( 'pointermove', function ( e ) {
			if ( ! down ) return;
			el.scrollLeft = scroll - ( e.pageX - startX );
		} );
	} );

	// ===== Poll voting (REST API集計) =====
	function kpAnimateCount( el, target, duration ) {
		if ( ! el ) return;
		target = Math.max( 0, parseInt( target, 10 ) || 0 );
		duration = duration || 900;
		var start = parseInt( ( el.textContent || '' ).replace( /[^\d]/g, '' ), 10 ) || 0;
		var startTime = null;
		function step ( now ) {
			if ( startTime === null ) startTime = now;
			var elapsed = now - startTime;
			var progress = Math.min( 1, elapsed / duration );
			var eased = 1 - Math.pow( 1 - progress, 3 );
			var current = Math.round( start + ( target - start ) * eased );
			el.textContent = current + '%';
			if ( progress < 1 ) requestAnimationFrame( step );
		}
		requestAnimationFrame( step );
	}

	document.querySelectorAll( '[data-kp-poll]' ).forEach( function ( poll ) {
		var pollId = poll.dataset.kpPollId || 'default';
		var key    = 'kp_poll_' + pollId;
		var items  = poll.querySelectorAll( '[data-kp-opt-hash]' );
		var msg    = poll.querySelector( '[data-kp-poll-msg]' );
		var hint   = poll.querySelector( '[data-kp-poll-hint]' );
		var errEl  = poll.querySelector( '[data-kp-poll-err]' );
		var voted  = false;
		try { voted = !!localStorage.getItem( key ); } catch ( e ) {}

		poll.classList.add( 'is-prevote' );

		function lockResults() {
			poll.classList.remove( 'is-prevote' );
			poll.classList.add( 'is-voted' );
			if ( hint ) hint.hidden = true;
			if ( msg )  msg.hidden  = false;
			items.forEach( function ( li ) { li.classList.add( 'is-locked' ); } );
		}

		function applyData( options, animate ) {
			if ( ! options || ! options.length ) return;
			options.forEach( function ( o ) {
				var item = poll.querySelector( '[data-kp-opt-hash="' + o.hash + '"]' );
				if ( ! item ) return;
				item.dataset.kpPct = o.pct;
				item.style.setProperty( '--w', o.pct + '%' );
				var pctEl = item.querySelector( '.kp-poll-card__pct, .kp-poll__pct' );
				if ( pctEl ) {
					pctEl.dataset.kpTarget = o.pct;
					if ( animate ) {
						kpAnimateCount( pctEl, o.pct, 900 );
					} else {
						pctEl.textContent = o.pct + '%';
					}
				}
			} );
		}

		function showError( txt ) {
			if ( ! errEl ) return;
			errEl.textContent = '⚠ ' + txt;
			errEl.hidden = false;
			// 自動非表示はしない（運営者が原因を見落とさないため）
		}

		function vote( hash, li ) {
			if ( voted || ! hash ) return;
			if ( ! window.KP_POLL || ! KP_POLL.restUrl ) {
				// REST 設定なし → ローカル投票のみ
				voted = true;
				try { localStorage.setItem( key, hash ); } catch ( e ) {}
				lockResults();
				return;
			}
			fetch( KP_POLL.restUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': KP_POLL.nonce || '' },
				body: JSON.stringify( { poll_id: parseInt( pollId, 10 ) || 0, option_hash: hash } ),
			} )
			.then( function ( r ) { return r.json().then( function ( j ) { return { ok: r.ok, body: j }; } ); } )
			.then( function ( res ) {
				if ( ! res.ok ) {
					showError( ( res.body && res.body.message ) || '投票できませんでした' );
					if ( res.body && res.body.code === 'kp_already_voted' ) {
						voted = true;
						try { localStorage.setItem( key, hash ); } catch ( e ) {}
						lockResults();
					}
					return;
				}
				applyData( res.body.options, true );
				voted = true;
				try { localStorage.setItem( key, hash ); } catch ( e ) {}
				if ( li ) li.classList.add( 'is-active' );
				lockResults();
			} )
			.catch( function () { showError( '通信エラー' ); } );
		}

		items.forEach( function ( item ) {
			// クリック対象: item 自身がボタンなら item、そうでなければ中の .kp-poll__opt
			var clickable = ( item.tagName === 'BUTTON' || item.tagName === 'A' )
				? item
				: ( item.querySelector( '.kp-poll__opt' ) || item );
			clickable.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( voted ) return;
				var clicked = ( e.currentTarget && e.currentTarget.closest )
					? e.currentTarget.closest( '[data-kp-opt-hash]' )
					: item;
				if ( ! clicked ) clicked = item;
				var hash = clicked.dataset.kpOptHash || '';
				if ( ! hash ) return;
				items.forEach( function ( x ) { x.classList.remove( 'is-active' ); } );
				clicked.classList.add( 'is-active' );
				vote( hash, clicked );
			} );
		} );

		if ( voted ) {
			var savedHash = null;
			try { savedHash = localStorage.getItem( key ); } catch ( e ) {}
			items.forEach( function ( li ) {
				if ( li.dataset.kpOptHash && li.dataset.kpOptHash === savedHash ) li.classList.add( 'is-active' );
			} );
			// 最新結果を取得して反映（カウントアップ付き）
			if ( window.KP_POLL && KP_POLL.restUrl ) {
				var sep = KP_POLL.restUrl.indexOf( '?' ) === -1 ? '?' : '&';
				fetch( KP_POLL.restUrl + sep + 'poll_id=' + encodeURIComponent( pollId ) )
					.then( function ( r ) { return r.json(); } )
					.then( function ( j ) {
						lockResults();
						if ( j && j.options ) applyData( j.options, true );
					} ).catch( function () { lockResults(); } );
			} else {
				lockResults();
			}
		}
	} );

	// ===== Podium "show others" toggle =====
	document.querySelectorAll( '[data-kp-podium-toggle]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var poll = btn.closest( '[data-kp-poll]' );
			var rest = poll && poll.querySelector( '.kp-podium__rest' );
			if ( ! rest ) return;
			var hidden = rest.hasAttribute( 'hidden' );
			if ( hidden ) {
				rest.removeAttribute( 'hidden' );
				btn.setAttribute( 'aria-expanded', 'true' );
				btn.textContent = '閉じる ▲';
			} else {
				rest.setAttribute( 'hidden', '' );
				btn.setAttribute( 'aria-expanded', 'false' );
				btn.textContent = 'その他はこちら ▼';
			}
		} );
	} );

	// ===== Like buttons =====
	document.querySelectorAll( '[data-kp-like]' ).forEach( function ( btn ) {
		var pid = parseInt( btn.dataset.kpLike, 10 );
		if ( ! pid ) return;
		var key = 'kp_liked_' + pid;
		var countEl = btn.querySelector( '[data-kp-like-count]' );
		try { if ( localStorage.getItem( key ) ) btn.classList.add( 'is-liked' ); } catch ( e ) {}

		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			if ( btn.classList.contains( 'is-liked' ) ) return;
			if ( ! window.KP_POLL || ! KP_POLL.likeUrl ) return;
			btn.disabled = true;
			fetch( KP_POLL.likeUrl + pid, {
				method: 'POST',
				headers: { 'X-WP-Nonce': KP_POLL.nonce || '' }
			} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( j ) {
				if ( j && typeof j.likes === 'number' ) {
					if ( countEl ) countEl.textContent = j.likes.toLocaleString();
					btn.classList.add( 'is-liked' );
					try { localStorage.setItem( key, '1' ); } catch ( e ) {}
				}
				btn.disabled = false;
			} )
			.catch( function () { btn.disabled = false; } );
		} );
	} );

	// ===== Lazy images (native lazy fallback) =====
	document.querySelectorAll( '.kp-card__media img, .kp-rank-item__thumb img' ).forEach( function ( img ) {
		if ( ! img.hasAttribute( 'loading' ) ) img.setAttribute( 'loading', 'lazy' );
		if ( ! img.hasAttribute( 'decoding' ) ) img.setAttribute( 'decoding', 'async' );
	} );

} )();

