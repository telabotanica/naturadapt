import domready from 'mf-js/modules/dom/ready';

domready( () => {
	const form = document.getElementById( 'search_engine_form' )
	form.addEventListener('change', () => { form.submit() }, false);
});
