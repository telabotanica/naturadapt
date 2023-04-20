import domready from 'mf-js/modules/dom/ready';
import autocomplete from 'autocomplete.js';

function get_places(element) {
	return import(/* webpackChunkName: "placesjs" */ 'places.js')
	  .then(({ default: places }) => {
		return places({
		  appId: process.env.ALGOLIA_APP_ID,
		  apiKey: process.env.ALGOLIA_API_KEY,
		  container: element,
		  templates: { value: (suggestion) => suggestion.name }
		}).configure({
		  type: 'city',
		  aroundLatLngViaIP: true,
		});
	  })
	  .catch(error => {
		console.error("Erreur avec Algolia :", error);
	  });
  }

// Fonction pour rechercher les coordonnées d'une ville en particulier
function searchCoords(query, country) {
	return new Promise((resolve, reject) => {
	  const url = new URL('https://nominatim.openstreetmap.org/search');
	  url.searchParams.append('format', 'json');
	  url.searchParams.append('q', `${query}, ${country}`);
	  url.searchParams.append('limit', 1);
  
	  fetch(url)
		.then(response => {
		  if (response.ok) {
			return response.json();
		  } else {
			reject(new Error('Erreur lors de la recherche des coordonnées.'));
		  }
		})
		.then(data => {
		  if (data && data.length > 0) {
			resolve({ lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) });
		  } else {
			reject(new Error('Aucun résultat trouvé pour la recherche de coordonnées.'));
		  }
		})
		.catch(error => {
		  reject(error);
		});
	});
  }

domready( () => {
	Array.from( document.querySelectorAll( '[name="user_profile"]' ) ).forEach( ( form ) => {
		// City autocomplete

		const inputCity = form.querySelector( '[name="user_profile[city]"]' );

		if ( inputCity ) {
			get_places(inputCity)
			.then((autocomplete) => {
			  if (autocomplete) {
				autocomplete.on('change', (e) => {
				  console.log(e.suggestion);
	
				  form.querySelector('[name="user_profile[zipcode]"]').value = e.suggestion.postcode || '';
				  form.querySelector('[name="user_profile[country]"]').value = e.suggestion.countryCode.toUpperCase() || '';
	
				  form.querySelector('[name="user_profile[latitude]"]').value = e.suggestion.latlng.lat || '';
				  form.querySelector('[name="user_profile[longitude]"]').value = e.suggestion.latlng.lng || '';
				});
			  } else {
				console.warn("Algolia n'a pas pu être initialisé. L'autocomplétion pour les villes ne fonctionnera pas.");
			  }
			})
			.catch((error) => {
			  console.error("Erreur avec Algolia :", error);
			});
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

		// TODO: Enlever cette partie de code quand la plupart des longitude et lattitude est renseignée
		// TODO: Il faudra mettre à jour latitude et longitude lorsque la ville est changée 
		// Ajoutez un gestionnaire d'événement 'submit' au formulaire
		form.addEventListener('submit', (event) => {
			// Empêche l'envoi du formulaire jusqu'à ce que les coordonnées soient mises à jour
			event.preventDefault();
		
			const inputCity = form.querySelector('[name="user_profile[city]"]');
			const inputCountry = form.querySelector('[name="user_profile[country]"]'); // Ajoutez cette ligne pour récupérer le pays

			if (inputCity.value) {
				const city = inputCity.value;
				const country = inputCountry.value;
				searchCoords(`${city}`, `${country}`)
				.then((coordinates) => {
					if (coordinates) {
					form.querySelector('[name="user_profile[latitude]"]').value = coordinates.lat || '';
					form.querySelector('[name="user_profile[longitude]"]').value = coordinates.lng || '';
					} else {
					console.warn("Impossible de récupérer les coordonnées pour la ville.");
					}
		
					// Soumet le formulaire une fois les coordonnées mises à jour
					form.submit();
				})
				.catch((error) => {
					console.error("Erreur lors de la récupération des coordonnées :", error);
				});
			} else {
				// Si la valeur de la ville n'est pas définie, soumettez simplement le formulaire
				form.submit();
			}
			});



	} );
} );
