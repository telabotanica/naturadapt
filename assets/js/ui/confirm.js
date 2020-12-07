import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '[data-confirm-message]' ) ).forEach( ( button ) => {
	let confirmMessage = button.dataset.confirmMessage;

	button.addEventListener( 'click', ( event ) => {
		if( !confirm( confirmMessage ) ) {
			event.preventDefault();
		}
	} );
} ) );
