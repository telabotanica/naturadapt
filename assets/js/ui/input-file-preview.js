import ready from 'mf-js/modules/dom/ready';

ready( () => Array.from( document.querySelectorAll( '.file-preview' ) ).forEach( ( element ) => {
	const img   = element.querySelector( '.file-preview--img img' );
	const input = element.querySelector( '[type="file"]' );

	if ( !img || !input ) {
		return;
	}

	input.addEventListener( 'change', ( e ) => {
		const reader = new FileReader();
		reader.addEventListener( 'load', ( e ) => img.src = e.target.result );
		reader.readAsDataURL( input.files[ 0 ] );
	} );
} ) );
