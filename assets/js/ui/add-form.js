import domready from 'mf-js/modules/dom/ready';
import { changeLinkOrder } from './link-order-change';

function createElementFromHTML(htmlString) {
	var div = document.createElement('div');
	div.innerHTML = htmlString.trim();

	// Change this to div.childNodes to support multiple top-level nodes.
	return div.firstChild;
  }

const addFormToCollection = (e) => {

	let list = e.currentTarget.previousElementSibling;

	// Try to find the counter of the list or use the length of the list
	var counter = list.dataset.widgetCounter || list.childNodes().length;

	// grab the prototype template
	var newWidget = list.getAttribute('data-prototype');
	// replace the "__name__" used in the id and name of the prototype
	// with a number that's unique to your emails
	// end name attribute looks like name="contact[emails][2]"
	newWidget = newWidget.replace(/__name__/g, counter);
	// Increase the counter
	counter++;
	// And store it, the length cannot be used if deleting widgets is allowed
	list.dataset.widgetCounter = counter;

	// create a new list element and add it to the list
	var newElem = document.createElement('div')
	newElem.innerHTML = newWidget;

	newElem.firstChild.className = 'columns__36-36-28';

	newElem.firstChild.childNodes.forEach((child)=> {
		child.className='form-row-aligned '
	})

	newElem.firstChild.appendChild(
		createElementFromHTML("<div  class='form-row-aligned '> <label>Ordre </label><button type='button' class='order_link_change order_link_change_up'></button><button type='button' class='order_link_change order_link_change_down'></button></div >")
	)
	newElem.firstChild.lastChild.lastChild.previousElementSibling.addEventListener("click", changeLinkOrder)
	list.appendChild(newElem.firstChild);
  };

domready( () => {
	document
	.querySelectorAll('.add_item_link')
	.forEach(btn => {
		btn.addEventListener("click", addFormToCollection)
	});
} );
