import domready from 'mf-js/modules/dom/ready';

domready( () => {
    // Get the figure element
    const figures = document.querySelectorAll('.wysiwyg-content figure');
    let threeColumnDiv = document.createElement('div');
    threeColumnDiv.classList.add('front-page--columns');
    threeColumnDiv.classList.add('columns__33-33-33');
    let columnCount = 0;

    figures.forEach( figure => {

        // Check if the figure element has the image-style-align-left class
        if (figure.classList.contains('image-style-align-left')) {

            // Get the previous sibling of the figure element
            let sibling = figure.previousElementSibling;

            // Loop through all previous siblings and add the class
            while (sibling) {
            sibling.classList.add('image-margin-left');
            sibling = sibling.previousElementSibling;
            }

            // Get the next sibling of the figure element
            sibling = figure.nextElementSibling;

            // Loop through all next siblings and add the class
            while (sibling) {
            sibling.classList.add('image-margin-left');
            sibling = sibling.nextElementSibling;
            }


        }
        if(figure.classList.contains('threeImagesInSameLine')) {
            columnCount++;
            const parent = figure.parentNode;
            const siblings = Array.from(parent.children);
            const index = siblings.indexOf(figure);
            const nextSibling = siblings[index + 1];
            const nextNextSibling = siblings[index + 2];
            const div = document.createElement('div');
            threeColumnDiv.appendChild(div);
            div.appendChild(figure);
            div.appendChild(nextSibling);
            console.log(parent);
            console.log(nextNextSibling);
            if (columnCount === 3) {
                columnCount = 0;
                parent.insertBefore(threeColumnDiv, nextNextSibling);
                threeColumnDiv = document.createElement('div');
                threeColumnDiv.classList.add('front-page--columns');
                threeColumnDiv.classList.add('columns__33-33-33');
            }
        }

    });
});
