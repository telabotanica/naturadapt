import domready from 'mf-js/modules/dom/ready';

domready( () => {
	const form = document.getElementById( 'search_engine_form' )
	if(form!==null){
		form.addEventListener('change', () => { form.submit() }, false);
	}
});
