import domready from 'mf-js/modules/dom/ready';
import Observable from "mf-js/modules/core/observable";

domready( () => {
	const dispatcher = Object.assign( {}, Observable );

	Array.from( document.querySelectorAll( '[data-toggle-listen-others]' ) ).forEach( ( element ) => {
		const [ event, value ] = element.getAttribute( 'data-toggle-listen-others' ).split( '|' );
		const group            = element.getAttribute( 'data-toggle-listen-group' ) || 'menu';
		const attribute        = element.getAttribute( 'data-toggle-attribute' ) || 'aria-hidden';

		dispatcher.on( group + '|' + event, ( e ) => {
			if ( ((e.element.getAttribute( 'data-toggle-listen-group' ) || 'menu') === group)
				 && (e.element !== element) ) {
				element.setAttribute( attribute, value );
			}
		} );
	} );

	Array.from( document.querySelectorAll( '[data-toggle-listen-esc]' ) ).forEach( ( element ) => {
		const attribute = element.getAttribute( 'data-toggle-attribute' ) || 'aria-hidden';
		const value     = element.getAttribute( 'data-toggle-listen-esc' ) || 'true';

		window.addEventListener( "keydown", ( e ) => {
			switch ( e.keyCode ) {
				case 27:
					element.setAttribute( attribute, value );
					break;
			}
		} );
	} );

	Array.from( document.querySelectorAll( '[data-toggle-element]' ) ).forEach( ( button ) => {
		let elements;

		switch ( button.getAttribute( 'data-toggle-element' ) ) {
			case 'sibling':
				elements = [ button.nextElementSibling ];
				break;

			case 'parent':
				elements = [ button.parentElement ];
				break;

			default:
				elements = Array.from( document.querySelectorAll( button.getAttribute( 'data-element' ) ) );
		}

		const attribute = button.getAttribute( 'data-toggle-attribute' )
						  || (elements[ 0 ] ? elements[ 0 ].getAttribute( 'data-toggle-attribute' ) : false)
						  || 'aria-hidden';
		const value     = button.getAttribute( 'data-toggle-value' );
		const group     = (elements[ 0 ] ? elements[ 0 ].getAttribute( 'data-toggle-listen-group' ) : false) || 'menu';

		button.addEventListener( 'click', () => {
			if ( elements.length <= 0 ) {
				return;
			}

			const v = value || (elements[ 0 ].getAttribute( attribute ) === 'true' ? 'false' : 'true');

			elements.forEach( ( element ) => {
				element.setAttribute( attribute, v );
				dispatcher.trigger( { type: group + '|' + v, element } );
			} );

			button.setAttribute( 'data-toggle-element-' + attribute, v );
			button.blur();
		} );
	} );
} );
