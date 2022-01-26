import domready from 'mf-js/modules/dom/ready';

domready( () => {
	const input = document.getElementById( 'form_groups_search_bar' );
	if(input!==null){
		input.addEventListener('input', delay(searchGroups, 500) );
	}
});

async function searchGroups(e){
	getGroupHTML('groups-to-activate-elements', e.target.value)
	getGroupHTML('groups-elements', e.target.value)
}

async function getGroupHTML(id, text){
	const groups = document.getElementById( id );
	if(groups!==null){
		const newGroupsObject = await fetch("/groups/search?type="+id+"&q="+text)
										.then(response => response.json());
		groups.innerHTML = newGroupsObject.groups;
	}
}

function delay(fn, ms) {
	let timer = 0
	return function(...args) {
	  clearTimeout(timer)
	  timer = setTimeout(fn.bind(this, ...args), ms || 0)
	}
}

