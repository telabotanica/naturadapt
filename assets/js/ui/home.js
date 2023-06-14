import domready from 'mf-js/modules/dom/ready';

domready( () => {
    // Get the figure element
    const figures = document.querySelectorAll('.wysiwyg-content figure');

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
    });
});
