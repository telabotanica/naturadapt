import domready from 'mf-js/modules/dom/ready';


function callback() {
    let form = document.getElementById( 'search_engine_form' )
    form.submit();
}

domready( () => {
    let form = document.getElementById( 'search_engine_form' )
    form.addEventListener('change', callback, false);
});
