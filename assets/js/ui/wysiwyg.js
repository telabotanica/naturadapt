import ready from 'mf-js/modules/dom/ready';

ready( () => {
	const textareas = Array.from( document.querySelectorAll( '.wysiwyg textarea' ) );

	if ( textareas.length > 0 ) {
		import( /* webpackChunkName: "ckeditor" */ '@ckeditor/ckeditor5-build-classic' ).then( ( { default: ClassicEditor } ) => {
			textareas.forEach( ( textarea ) => {
				ClassicEditor
					.create( textarea )
					.then( editor => {
						console.log( editor );
					} )
					.catch( error => {
						console.error( error );
					} );
			} );
		} );
	}
} );
