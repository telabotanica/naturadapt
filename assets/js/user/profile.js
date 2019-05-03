import ready from 'mf-js/modules/dom/ready';
import autocomplete from 'autocomplete.js';

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
	Array.from( document.querySelectorAll( '[name="user_profile"]' ) ).forEach( ( form ) => {
		// City autocomplete

		const inputCity = form.querySelector( '[name="user_profile[city]"]' );

		if ( inputCity ) {
			get_places( inputCity ).then( ( autocomplete ) => autocomplete.on( 'change', ( e ) => {
				console.log( e.suggestion );

				form.querySelector( '[name="user_profile[zipcode]"]' ).value = e.suggestion.postcode || '';
				form.querySelector( '[name="user_profile[country]"]' ).value = e.suggestion.countryCode.toUpperCase() || '';

				form.querySelector( '[name="user_profile[latitude]"]' ).value  = e.suggestion.latlng.lat || '';
				form.querySelector( '[name="user_profile[longitude]"]' ).value = e.suggestion.latlng.lng || '';
			} ) );
		}

		// Natural Site autocomplete

		const inputSite = form.querySelector( '[name="user_profile[siteName]"]' );

		if ( inputSite ) {
			autocomplete( inputSite, { hint: false }, [
				{
					source:     ( query, callback ) => {
						const ajax = new XMLHttpRequest();
						ajax.open( 'GET', inputSite.getAttribute( 'data-query' ).replace( 'query', query ), true );
						ajax.onload = () => callback( JSON.parse( ajax.responseText ).results );
						ajax.send();
					},
					displayKey: ( item ) => item.name,
				} ] );
		}
	} );
} );
