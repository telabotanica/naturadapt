import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '.form-row__file' ) ).forEach( ( fileUpload ) => {
	const fileElement = fileUpload.querySelector( 'input[type="file"]' );

	if ( !fileElement ) {
		console.log( 'no file input' );
		return;
	}

	const fileLabel = fileUpload.querySelector( `input[type="file"] ~ label[for="${fileElement.id}"]` );

	if ( !fileLabel ) {
		console.log( 'no file label' );
		return;
	}

	const fileNameContainer     = document.createElement( 'div' );
	fileNameContainer.className = 'filename-container';
	fileLabel.parentNode.insertBefore( fileNameContainer, fileLabel );

	fileNameContainer.appendChild( fileLabel );

	const fileName     = document.createElement( 'span' );
	fileName.className = 'filename';
	fileNameContainer.appendChild( fileName );

	fileElement.addEventListener( 'change', ( e ) => fileName.innerHTML = e.target.files[ 0 ].name );
} ) );
