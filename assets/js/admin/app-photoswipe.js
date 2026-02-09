// assets/js/admin/app-photoswipe.js

import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/photoswipe.css';

/* Activating PhotoSwipeLightbox to view images as gallery */
const lightbox = new PhotoSwipeLightbox({
  gallery: '#admin-gallery',
  children: 'a.gallery-item',
  mouseMovePan: true,
  initialZoomLevel: 'fill',
  secondaryZoomLevel: 3,
  maxZoomLevel: 5,
  pswpModule: () => import('photoswipe')
});

lightbox.on('uiRegister', function() {
  lightbox.pswp.ui.registerElement({
    name: 'custom-caption',
    order: 9,
    isButton: false,
    appendTo: 'root',
    html: 'Caption text',
    onInit: (el, pswp) => {
      lightbox.pswp.on('change', () => {
        const currSlideElement = lightbox.pswp.currSlide.data.element;
        let captionHTML = '';
        if (currSlideElement) {
          const hiddenCaption = currSlideElement.querySelector('.hidden-caption-content');
          if (hiddenCaption) {
            // get caption from element with class hidden-caption-content
            captionHTML = hiddenCaption.innerHTML;
          } else {
            // get caption from alt attribute
            captionHTML = currSlideElement.querySelector('img').getAttribute('alt');
          }
        }
        el.innerHTML = captionHTML || '';
      });
    }
  });
});
lightbox.init();
