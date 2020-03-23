import domready from 'mf-js/modules/dom/ready';

class UploadAdapter {
	constructor( loader, uploadURL ) {
		console.log( 'CustomLoader', 'constructor', uploadURL );

		this.loader    = loader;
		this.uploadURL = uploadURL;
	}

	_initRequest() {
		console.log( 'CustomLoader', '_initRequest' );

		const xhr = this.xhr = new XMLHttpRequest();
		xhr.open( 'POST', this.uploadURL, true );
		xhr.responseType = 'json';
	}

	_initListeners( resolve, reject, file ) {
		console.log( 'CustomLoader', '_initListeners' );

		const xhr              = this.xhr;
		const loader           = this.loader;
		const genericErrorText = `Couldn't upload file: ${file.name}.`;

		xhr.addEventListener( 'error', () => reject( genericErrorText ) );
		xhr.addEventListener( 'abort', () => reject() );
		xhr.addEventListener( 'load', () => {
			const response = xhr.response;

			if ( !response || response.error ) {
				return reject( response && response.error ? response.error.message : genericErrorText );
			}

			resolve( {
						 default: response.url
					 } );
		} );

		if ( xhr.upload ) {
			xhr.upload.addEventListener( 'progress', evt => {
				if ( evt.lengthComputable ) {
					loader.uploadTotal = evt.total;
					loader.uploaded    = evt.loaded;
				}
			} );
		}
	}

	_sendRequest( file ) {
		console.log( 'CustomLoader', '_sendRequest' );

		const data = new FormData();

		data.append( 'upload[file]', file );

		this.xhr.send( data );
	}

	upload() {
		console.log( 'CustomLoader', 'upload' );

		return this.loader.file
			.then( file => new Promise( ( resolve, reject ) => {
				this._initRequest();
				this._initListeners( resolve, reject, file );
				this._sendRequest( file );
			} ) );
	}

	abort() {
		console.log( 'CustomLoader', 'abort' );

		if ( this.xhr ) {
			this.xhr.abort();
		}
	}
}

domready( () => {
	const wysiwygs = Array.from( document.querySelectorAll( '.wysiwyg-editor' ) );

	if ( wysiwygs.length > 0 ) {
		import( /* webpackChunkName: "ckeditor" */ '@ckeditor/ckeditor5-build-classic' ).then( ( { default: ClassicEditor } ) => {
			wysiwygs.forEach( ( wysiwyg ) => {
				const textarea    = wysiwyg.querySelector( 'textarea' );
				textarea.required = false;

				let toolbar = [ 'heading', '|',
								'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
								'mediaEmbed', 'insertTable', '|',
								'undo', 'redo' ];

				let extraPlugins = [];

				function UploadAdapterPlugin( editor ) {
					editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
						const uploadURL = wysiwyg.getAttribute( 'data-upload' );

						return new UploadAdapter( loader, uploadURL );
					};
				}

				if ( wysiwyg.getAttribute( 'data-upload' ) ) {
					toolbar      = [ 'heading', '|',
									 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
									 'imageUpload', 'mediaEmbed', 'insertTable', '|',
									 'undo', 'redo' ];
					extraPlugins = [ UploadAdapterPlugin ];
				}

				ClassicEditor
					.create( textarea, {
						toolbar:      toolbar,
						extraPlugins: extraPlugins,
						height:       '500px',
					} )
					.then( editor => {
						console.log( Array.from( editor.ui.componentFactory.names() ) );

						editor.on( 'required', ( evt ) => {
							alert( 'This field is required.' );
							evt.cancel();
						} );
					} )
					.catch( error => {
						console.error( error );
					} );
			} );
		} );
	}
} );
