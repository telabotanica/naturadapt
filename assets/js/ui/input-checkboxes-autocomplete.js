import ready from 'mf-js/modules/dom/ready';
import Awesomplete from 'awesomplete';
import 'awesomplete/awesomplete.css';

ready( () => Array.from( document.querySelectorAll( '.checkboxes-autocomplete' ) ).forEach( ( element ) => {
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

	const wrapper     = document.createElement( 'div' );
	wrapper.className = 'autocomplete-tags';
	element.appendChild( wrapper );

	const tags     = document.createElement( 'div' );
	tags.className = 'tags';
	wrapper.appendChild( tags );

	const input = document.createElement( 'input' );
	wrapper.appendChild( input );

	// Enable autocomplete

	const autocomplete = new Awesomplete( input, { list: list, minChars: 1 } );

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

		const closeButton = document.createElement( 'button' );
		closeButton.type  = 'button';
		closeButton.title = 'remove';
		closeButton.addEventListener( 'click', ( e ) => {
			matchingCheckboxes.forEach( ( checkbox ) => checkbox.checked = false );
			tags.removeChild( tag );
		} );
		tag.appendChild( closeButton );

		tags.appendChild( tag );
	};

	// Add Tags on startup

	checkboxes
		.filter( ( checkbox ) => checkbox.checked )
		.forEach( ( checkbox ) => addTag( checkbox.value, true ) );

	// Add Tags on autocomplete

	input.addEventListener( 'awesomplete-select', ( e ) => {
		e.preventDefault();

		addTag( e.text.value );

		// Clear input
		input.value = '';

		// Close popin
		autocomplete.close();
	} );

	//
} ) );
