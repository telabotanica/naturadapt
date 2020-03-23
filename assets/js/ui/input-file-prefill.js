import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( 'input[type="file"][data-prefill]' ) ).forEach( ( fileElement ) => {
	const prefillElements = Array.from( document.querySelectorAll( fileElement.getAttribute( 'data-prefill' ) ) );

	fileElement.addEventListener( 'change', ( e ) => {
		const name = e.target.files[ 0 ].name
			.replace( '_', ' ' )
			.split( '.' )
			.slice( 0, -1 )
			.join( '.' );

		prefillElements.forEach( ( input ) => {
			input.value = name;
		} );
	} );
} ) );
