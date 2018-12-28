( function () {
	var params, ranges, $rangeUpdate;
	if ( !document.querySelector || !window.URLSearchParams ) {
		return;
	}

	params = new URLSearchParams( location.search );

	$( '.nagf-select-project' ).on( 'change', function () {
		params.set( 'project', this.value );
		location.search = params.toString();
	} );

	$( '.nagf-select-metric' ).on( 'change', function () {
		location.hash = '#' + this.value;
	} );

	function updateRanges( value, action ) {
		var val, idx;
		if ( !ranges ) {
			val = params.get( 'range' );
			ranges = val ? val.split( '-' ) : [ 'day' ];
		}

		idx = ranges.indexOf( value );

		if ( action === 'add' && idx === -1 ) {
			ranges.push( value );
		} else if ( action === 'remove' && idx !== -1 ) {
			ranges.splice( idx, 1 );
		}
		if ( ranges.length ) {
			params.set( 'range', ranges.join( '-' ) );
		} else {
			params.delete( 'range' );
		}
	}

	$rangeUpdate = $( '#nagf-select-range-update' ).on( 'click', function ( e ) {
		e.preventDefault();
		location.search = params.toString();
	} );

	$( '.nagf-select-range' ).on( 'change', function () {
		updateRanges( this.value, this.checked ? 'add' : 'remove' );
		$rangeUpdate.prop( 'hidden', false );
	} );
}() );
