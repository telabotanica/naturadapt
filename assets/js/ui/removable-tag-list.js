import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '.removable-tag-list ' ) ).forEach( ( element ) => {
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

	// Create elements

    const wrapper     = document.createElement( 'label' );
    element.appendChild( wrapper );

	const tags     = document.createElement( 'span' );
	tags.className = 'removable-tags';
	wrapper.appendChild( tags );

	const input = document.getElementById( 'form_query' );
	wrapper.setAttribute( 'for', input.id );

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
		tag.className = 'removable-tag';

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
		} );
		tag.appendChild( closeButton );

		tags.appendChild( tag );
	};

	// Add Tags on startup

	checkboxes
		.filter( ( checkbox ) => (checkbox.checked) )
		.forEach( ( checkbox ) => addTag( checkbox.value, true ) );

} ) );
