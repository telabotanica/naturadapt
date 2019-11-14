import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '.file-preview' ) ).forEach( ( element ) => {
	const preview = element.querySelector( '.file-preview--img' );
	const input   = element.querySelector( '[type="file"]' );

	if ( !preview || !input ) {
		return;
	}

	input.addEventListener( 'change', ( e ) => {
		let img = preview.querySelector( 'img' );
		if ( !img ) {
			img = document.createElement( 'img' );
			preview.appendChild( img );
		}

		const reader = new FileReader();
		reader.addEventListener( 'load', ( e ) => img.src = e.target.result );
		reader.readAsDataURL( input.files[ 0 ] );
	} );
} ) );
