export function get_places( element ) {
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
