import domready from "mf-js/modules/dom/ready";

export const urlToLink = ( text ) => text.replace( /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, "<a href='$1'>$1</a>" );

export const processElement = ( element ) => {
	if ( (element.childNodes.length === 1) && (element.childNodes[ 0 ].nodeType === Node.TEXT_NODE) ) {
		element.innerHTML = urlToLink( element.innerHTML );
	}
	else {
		element.childNodes.forEach( ( node ) => {
			switch ( node.nodeType ) {
				case Node.TEXT_NODE:
					const replacedText = urlToLink( node.nodeValue );
					if ( replacedText !== node.nodeValue ) {
						const replacementNode     = document.createElement( 'span' );
						replacementNode.innerHTML = replacedText;
						node.parentNode.insertBefore( replacementNode, node );
						node.parentNode.removeChild( node );
					}
					break;

				case Node.ELEMENT_NODE:
					console.log( node.tagName );

					switch ( node.tagName ) {
						case 'DIV':
						case 'P':
						case 'UL':
						case 'LI':
						case 'SPAN':
							processElement( node );
					}
					break;
			}
		} );
	}
};

domready( () => Array.from( document.querySelectorAll( '.message__full .message--body' ) ).forEach( ( body ) => processElement( body ) ) );
