import domready from "mf-js/modules/dom/ready";

domready( () =>
			  Array.from( document.querySelectorAll( '.documents-folder' ) )
				  .forEach( ( folder ) => {
					  const button  = folder.querySelector( '.documents-folder--name .list-toggle' );
					  const element = folder.querySelector( '.documents-list' );

					  if ( !button || !element ) {
						  return;
					  }

					  const attribute = button.getAttribute( 'data-list-toggle' ) || 'aria-expanded';

					  button.addEventListener( 'click', () => {
						  const status = element.getAttribute( attribute ) === 'false' ? 'true' : 'false';
						  button.setAttribute( `data-list-${attribute}`, status );
						  element.setAttribute( attribute, status );
					  } );
				  } )
);
