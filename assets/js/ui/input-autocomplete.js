import domready from 'mf-js/modules/dom/ready';
import autocomplete from "autocomplete.js";

domready( () =>
			  Array.from( document.querySelectorAll( 'input[data-list]' ) )
				  .forEach( ( input ) => {
					  const suggestions = input.getAttribute( 'data-list' )
						  .split( ',' )
						  .map( ( suggestion ) => suggestion.trim() )
						  .filter( ( suggestion ) => suggestion.length > 0 );

					  console.log( 'input-autocomplete', { suggestions } );

					  if ( suggestions.length > 0 ) {
						  autocomplete( input, { hint: false, openOnFocus: true, minLength: 0 }, [ {
							  source:     ( query, callback ) => callback( suggestions ),
							  displayKey: ( suggestion ) => suggestion,
						  } ] );
					  }
				  } )
);
