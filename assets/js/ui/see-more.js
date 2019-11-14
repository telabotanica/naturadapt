import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '[data-see-more]' ) ).forEach( ( button ) => {
	const element = button.nextElementSibling;

	button.addEventListener( 'click', () => {
		button.setAttribute( 'aria-hidden', 'true' );
		element.setAttribute( 'aria-hidden', 'false' );
	} );
} ) );
