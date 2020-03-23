import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '.sibling-toggle' ) ).forEach( ( button ) => {
	const element = button.nextElementSibling;

	const attribute = button.getAttribute( 'data-sibling-toggle' ) || 'aria-hidden';

	button.addEventListener( 'click', () => {
		const status = element.getAttribute( attribute ) === 'false' ? 'true' : 'false';
		button.setAttribute( `data-sibling-${attribute}`, status );
		element.setAttribute( attribute, status );
	} );
} ) );
