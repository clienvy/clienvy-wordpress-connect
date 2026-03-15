/* Clienvy Connect — Admin JS */

jQuery( function ( $ ) {

	var $revealBtn = $( '#clienvy-reveal-btn' );
	var $resetBtn  = $( '#clienvy-reset-btn' );
	var $copyBtn   = $( '#clienvy-copy-btn' );

	// ── Reveal secret ─────────────────────────────────────────────────────────

	$revealBtn.on( 'click', function () {
		$revealBtn.prop( 'disabled', true ).text( 'Laden…' );

		$.post( clienvyAdmin.ajaxUrl, {
			action : 'clienvy_reveal_secret',
			nonce  : clienvyAdmin.nonce,
		} )
		.done( function ( response ) {
			if ( response.success ) {
				showSecret( response.data.secret );
				$( '#clienvy-state-hidden' ).hide();
			} else {
				$revealBtn.prop( 'disabled', false ).text( 'Klik om de Connection Secret te zien' );
				alert( response.data || 'Er is een fout opgetreden.' );
			}
		} )
		.fail( function () {
			$revealBtn.prop( 'disabled', false ).text( 'Klik om de Connection Secret te zien' );
			alert( 'Verzoek mislukt. Vernieuw de pagina en probeer opnieuw.' );
		} );
	} );

	// ── Reset secret ──────────────────────────────────────────────────────────

	$resetBtn.on( 'click', function () {
		if ( ! window.confirm( clienvyAdmin.confirmReset ) ) {
			return;
		}

		var $btn = $( this );
		$btn.prop( 'disabled', true ).text( 'Bezig…' );

		$.post( clienvyAdmin.ajaxUrl, {
			action : 'clienvy_reset_secret',
			nonce  : clienvyAdmin.nonce,
		} )
		.done( function ( response ) {
			if ( response.success ) {
				window.location.reload();
			} else {
				alert( response.data || 'Reset mislukt.' );
				$btn.prop( 'disabled', false ).html(
					'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> Reset Connection Secret'
				);
			}
		} )
		.fail( function () {
			alert( 'Verzoek mislukt. Vernieuw de pagina en probeer opnieuw.' );
			$btn.prop( 'disabled', false ).html(
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> Reset Connection Secret'
			);
		} );
	} );

	// ── Copy ──────────────────────────────────────────────────────────────────

	$( document ).on( 'click', '#clienvy-copy-btn', function () {
		var secret = $( '#clienvy-secret-value' ).text();
		if ( ! secret ) return;

		var $btn = $( this );

		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( secret )
				.then( function () { flashCopied( $btn ); } )
				.catch( function () { fallbackCopy( secret, $btn ); } );
		} else {
			fallbackCopy( secret, $btn );
		}
	} );

	// ── Helpers ───────────────────────────────────────────────────────────────

	function showSecret( secret ) {
		$( '#clienvy-secret-value' ).text( secret );
		$( '#clienvy-state-revealed' ).show();
	}

	function flashCopied( $btn ) {
		var original = $btn.html();
		$btn.text( 'Gekopieerd!' );
		setTimeout( function () { $btn.html( original ); }, 2000 );
	}

	function fallbackCopy( text, $btn ) {
		var $tmp = $( '<textarea>' )
			.val( text )
			.css( { position: 'absolute', left: '-9999px', top: 0 } )
			.appendTo( 'body' );
		$tmp[ 0 ].select();
		document.execCommand( 'copy' );
		$tmp.remove();
		flashCopied( $btn );
	}

} );
