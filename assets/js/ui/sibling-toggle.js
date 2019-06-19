import ready from 'mf-js/modules/dom/ready';

ready( () => Array.from( document.querySelectorAll( '.sibling-toggle' ) ).forEach( ( button ) => {
	const element = button.nextElementSibling;

	button.addEventListener( 'click', () => {
		element.setAttribute( 'aria-hidden', element.getAttribute( 'aria-hidden' ) === 'false' ? 'true' : 'false' );
	} );
} ) );
