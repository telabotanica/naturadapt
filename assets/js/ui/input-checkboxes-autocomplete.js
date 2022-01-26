import domready from 'mf-js/modules/dom/ready';
import autocomplete from 'autocomplete.js';

const normalizeKey = str => str.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, "" ).toLocaleLowerCase();

domready( () => Array.from( document.querySelectorAll( '.checkboxes-autocomplete' ) ).forEach( ( element ) => {
	const checkboxes = Array.from( element.querySelectorAll( 'input[type="checkbox"]' ) );

	// Create of values and labels and hide original elements
	const list = checkboxes.map( ( checkbox ) => {
		const label = element.querySelector( `label[for="${checkbox.id}"]` );

		checkbox.setAttribute( 'tabIndex', '-1' );
		label.setAttribute( 'aria-hidden', 'true' );

		return {
			label: label.innerHTML,
			value: checkbox.value,
		};
	} );

	element.setAttribute( 'data-autocomplete', 'true' );

	// Create elements

	const wrapper     = document.createElement( 'label' );
	wrapper.className = 'autocomplete-tags';
	element.appendChild( wrapper );

	const tags     = document.createElement( 'div' );
	tags.className = 'tags';
	wrapper.appendChild( tags );

	const input = document.createElement( 'input' );
	input.id    = 'input-' + Math.floor( 1000000 * Math.random() );
	wrapper.setAttribute( 'for', input.id );
	wrapper.appendChild( input );

	// Enable autocomplete

	const autocompleteComponent = autocomplete( input, { hint: false, clearOnSelected: true }, [
		{
			source:     ( query, callback ) => {
				const keys = query
					.split( ' ' )
					.map( ( key ) => normalizeKey( key ) )
					.filter( ( key ) => key.length > 0 );

				const results = list
					.map( ( item ) => {
						const r = Object.assign( { suggestion: item.label, match: 0 }, item );

						keys.forEach( ( key ) => {
							if ( normalizeKey( r.label ).includes( key ) ) {
								r.suggestion = r.suggestion.replace( key, `<em>${key}</em>` );
								r.match++;
							}
						} );

						return r;
					} )
					.filter( ( item ) => item.match > 0 );

				callback( results );
			},
			displayKey: ( item ) => item.label,
			templates:  {
				suggestion: ( item ) => item.suggestion,
			}
		} ] );

	// Add tag from a given value

	const addTag = ( value, force = false ) => {
		console.log( 'addTag', value );

		// Check matching checboxes
		const matchingCheckboxes = Array.from( element.querySelectorAll( `[value="${value}"]` ) );

		if ( !force && (matchingCheckboxes.filter( ( checkbox ) => checkbox.checked ).length > 0) ) {
			console.log( 'skipped' );
			return;
		}

		matchingCheckboxes.forEach( ( checkbox ) => checkbox.checked = true );

		const tag     = document.createElement( 'div' );
		tag.className = 'tag';

		const name     = document.createElement( 'span' );
		name.innerHTML = list.filter( ( item ) => item.value == value ).pop().label;
		tag.appendChild( name );

		const closeButton     = document.createElement( 'button' );
		closeButton.type      = 'button';
		closeButton.title     = 'remove';
		closeButton.className = 'remove';
		closeButton.addEventListener( 'click', ( e ) => {
			matchingCheckboxes.forEach( ( checkbox ) => checkbox.checked = false );
			tags.removeChild( tag );
			const searchEngineForm = document.getElementById( 'search_engine_form' )
			if(searchEngineForm !== null){
				searchEngineForm.submit();
			}
		} );
		tag.appendChild( closeButton );

		tags.appendChild( tag );

	};

	// Add Tags on startup

	checkboxes
		.filter( ( checkbox ) => checkbox.checked )
		.forEach( ( checkbox ) => addTag( checkbox.value, true ) );

	// Add Tags on autocomplete
	autocompleteComponent.on(
		'autocomplete:selected',
		( e, suggestion, dataset, context ) => {
			addTag( suggestion.value );
			const searchEngineForm = document.getElementById( 'search_engine_form' );
			if(searchEngineForm !== null){
				searchEngineForm.submit();
			}
		});
} ) );
