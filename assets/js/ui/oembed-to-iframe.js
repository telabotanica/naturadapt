import domready from 'mf-js/modules/dom/ready';

export const parseVideo = ( url ) => {
	// - Supported YouTube URL formats:
	//   - http://www.youtube.com/watch?v=My2FRPA3Gf8
	//   - http://youtu.be/My2FRPA3Gf8
	//   - https://youtube.googleapis.com/v/My2FRPA3Gf8
	// - Supported Vimeo URL formats:
	//   - http://vimeo.com/25451551
	//   - http://player.vimeo.com/video/25451551
	// - Also supports relative URLs:
	//   - //player.vimeo.com/video/25451551

	url.match( /(http:\/\/|https:\/\/|)(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/ );
	let type = null;

	if ( RegExp.$3.indexOf( 'youtu' ) > -1 ) {
		type = 'youtube';
	} else if ( RegExp.$3.indexOf( 'vimeo' ) > -1 ) {
		type = 'vimeo';
	}

	return {
		type: type,
		id: RegExp.$6
	};
};

domready( () => Array.from( document.querySelectorAll( 'oembed' ) ).forEach( ( oembed ) => {
	const VIMEO_URL_ORIGIN = 'https://player.vimeo.com/video/';
	const YOUTUBE_URL_ORIGIN = 'https://www.youtube.com/embed/';
	const attributes = {
		width: 640,
		height: 360,
		frameborder: 0,
		allow: 'autoplay; fullscreen;',
		allowfullscreen: '',
	};

	let iframe = document.createElement( 'iframe' ),
		url = oembed.getAttribute( 'url' ),
		src = url,
		videoData = parseVideo( url );

	switch ( videoData.type ) {
		case 'vimeo':
			src = VIMEO_URL_ORIGIN + videoData.id;
			break;
		case 'youtube':
			src = YOUTUBE_URL_ORIGIN + videoData.id;
			attributes.allow += ' accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture;';
			break;
		default:
			break;
	}

	iframe.setAttribute( 'src', src )

	for ( const attribute in attributes ) {
		iframe.setAttribute( attribute, attributes[attribute] );
	}

	oembed.replaceWith( iframe );
} ) );

