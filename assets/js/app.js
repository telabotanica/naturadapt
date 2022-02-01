/**************************************************
 * CSS
 **************************************************/
//
import '../css/app.scss';
//
/**************************************************
 * POLYFILLS
 **************************************************/
//
import 'core-js/features/object/assign';
import 'core-js/features/object/values';
import 'core-js/features/array/from';
import 'core-js/features/array/for-each';
import 'core-js/features/promise';
//
/**************************************************
 * COMPONENTS
 **************************************************/
//
import './ui/input-autocomplete';
import './ui/input-checkboxes-autocomplete';
import './ui/input-file-preview';
import './ui/input-file-name';
import './ui/input-file-prefill';
import './ui/see-more';
import './ui/element-toggle';
import './ui/wysiwyg';
import './ui/documents-folding';
import './ui/url-to-link';
import './ui/confirm';
import './ui/oembed-to-iframe';
//
import './user/profile';
import './user/dashboard';

console.warn( 'Hello fellow developer, ENV is dev.\nDont\'t forget to compile in production mode before deploying.\nHappy coding ! Max.' );
