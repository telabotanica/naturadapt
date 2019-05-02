import ready from 'mf-js/modules/dom/ready';

function get_places( element ) {
	return import( /* webpackChunkName: "placesjs" */ 'places.js' ).then( ( { default: places } ) => {
		return places( {
						   appId:     process.env.ALGOLIA_APP_ID,
						   apiKey:    process.env.ALGOLIA_API_KEY,
						   container: element,
						   templates: { value: ( suggestion ) => suggestion.name }
					   } ).configure( {
										  type:              'city',
										  aroundLatLngViaIP: true,
									  } );
	} );
}

ready( () => {
	Array.from( document.querySelectorAll( '[name="user_profile[city]"]' ) ).forEach( ( input ) => {
		get_places( input ).then( ( autocomplete ) => autocomplete.on( 'change', ( e ) => {
			console.log( e.suggestion );

			document.querySelector( '[name="user_profile[zipcode]"]' ).value = e.suggestion.postcode || '';
			document.querySelector( '[name="user_profile[country]"]' ).value = e.suggestion.countryCode.toUpperCase() || '';
		} ) );
	} );
} );
