import domready from 'mf-js/modules/dom/ready';

domready( () => Array.from( document.querySelectorAll( '.user-dashboard' ) ).forEach( ( dashboard ) => {
	const tabs         = Array.from( dashboard.querySelectorAll( '.user-dashboard--tabs li' ) );
	const tabsContents = tabs.map( ( tab ) => {
		const button = tab.querySelector( 'button[data-for]' );
		return dashboard.querySelector( '.' + button.getAttribute( 'data-for' ) );
	} );

	tabs.forEach( ( tab ) => {
		const button     = tab.querySelector( 'button[data-for]' );
		const tabContent = dashboard.querySelector( '.' + button.getAttribute( 'data-for' ) );

		tabContent.setAttribute( 'aria-selected', (tab.getAttribute( 'aria-selected' ) === 'true') ? 'true' : 'false' );

		button.addEventListener( 'click', () => {
			tabs.forEach( ( otherTab ) => otherTab.setAttribute( 'aria-selected', otherTab === tab ? 'true' : 'false' ) );
			tabsContents.forEach( ( otherTabContent ) => otherTabContent.setAttribute( 'aria-selected', otherTabContent === tabContent ? 'true' : 'false' ) );
		} )
	} );
} ) );
