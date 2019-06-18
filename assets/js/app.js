/**************************************************
 * CSS
 **************************************************/

import '../css/app.scss';

/**************************************************
 * POLYFILLS
 **************************************************/

import 'core-js/features/object/assign';
import 'core-js/features/object/values';
import 'core-js/features/array/from';
import 'core-js/features/array/for-each';
import 'core-js/features/promise';

/**************************************************
 * COMPONENTS
 **************************************************/

import './ui/input-checkboxes-autocomplete';
import './ui/input-file-preview';
import './ui/see-more';
import './ui/wysiwyg';

import './user/profile';

console.warn( 'Hello fellow developer, ENV is dev.\nDont\'t forget to compile in production mode before deploying.\nHappy coding ! Max.' );
