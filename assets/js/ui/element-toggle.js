import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '[data-toggle-element]' ) ).forEach( ( button ) => {
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
    
    const attribute = button.getAttribute( 'data-toggle-attribute' ) || 'aria-hidden';
    const value     = button.getAttribute( 'data-toggle-value' );
    
    button.addEventListener( 'click', () => {
        if ( elements.length <= 0 ) {
            return;
        }
        
        const v = value || (elements[ 0 ].getAttribute( attribute ) === 'true' ? 'false' : 'true');
        
        elements.forEach( ( element ) => element.setAttribute( attribute, v ) );
        
        button.setAttribute( 'data-value', v );
        button.blur();
    } );
} ) );
