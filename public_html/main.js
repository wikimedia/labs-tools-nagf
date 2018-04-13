( function () {
	var $rangeUpdate;

	$( '.nagf-select-project' ).on( 'change', function () {
		location.href = './?project=' + this.value;
	} );

	$( '.nagf-select-metric' ).on( 'change', function () {
		location.hash = '#' + this.value;
	} );

	function updateRanges( value, action ) {
		var val = $.cookie( 'nagf-range' ),
			ranges = val ? val.split( '!' ) : [],
			idx = $.inArray( value, ranges );

		if ( action === 'add' && idx === -1 ) {
			ranges.push( value );
		} else if ( action === 'remove' && idx !== -1 ) {
			ranges.splice( idx, 1 );
		}
		$.cookie( 'nagf-range', ranges.join( '!' ) );
	}

	$rangeUpdate = $( '#nagf-select-range-update' ).on( 'click', function ( e ) {
		e.preventDefault();
		location.reload();
	} );

	$( '.nagf-select-range' ).on( 'change', function () {
		updateRanges( this.value, this.checked ? 'add' : 'remove' );
		$rangeUpdate.prop( 'hidden', false );
	} );
}() );
