import domready from 'mf-js/modules/dom/ready';


export const changeLinkOrder = (e) => {

	const idForm = e.currentTarget.parentNode.parentNode.id;
	const idFormWithoutIndex = idForm.slice(0, -1);
	const index = idForm.slice(-1);
	var indexToChange
	if (e.currentTarget.classList.contains("order_link_change_up")){
		indexToChange = parseInt(index) -1;
	} else if (e.currentTarget.classList.contains("order_link_change_down")){
		indexToChange = parseInt(index) +1;
	}

	changeText(idFormWithoutIndex, index, indexToChange, 'nom');
	changeText(idFormWithoutIndex, index, indexToChange, 'lien');

  };

function changeText(idBase, index1, index2, formType) {

	let form1 = document.getElementById( idBase + index1 + '_' + formType )
	let form2 = document.getElementById( idBase + index2 + '_' + formType )

	const valueTemp = form1.value;
	form1.value = form2.value;
	form2.value= valueTemp;
}

domready( () => {
	document
	.querySelectorAll('.order_link_change')
	.forEach(btn => {
		btn.addEventListener("click", changeLinkOrder)
	});
} );
