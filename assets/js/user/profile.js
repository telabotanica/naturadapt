import ready from 'mf-js/modules/dom/ready';
import {get_places} from './get-places';

ready( () => {
	Array.from( document.querySelectorAll( '[name="user_profile[city]"]' ) ).forEach( ( input ) => {
		get_places( input ).then( ( autocomplete ) => autocomplete.on( 'change', ( e ) => {
			console.log( e.suggestion );

//			document.querySelector( '[name="user_profile[city]"]' ).value    = e.suggestion.name || '';
			document.querySelector( '[name="user_profile[zipcode]"]' ).value = e.suggestion.postcode || '';
			document.querySelector( '[name="user_profile[country]"]' ).value = e.suggestion.country || '';
		} ) );
	} );
} );
