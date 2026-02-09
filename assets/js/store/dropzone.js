// assets/js/store/dropzone.js

import empty from 'is-blank';

if (document.querySelector('.dropzone-container') !== null)
{
    let dropAreaSet = document.querySelectorAll('.dropzone-container');

    dropAreaSet.forEach((dropArea, i) =>
    {
      // Highlight drop area when file is dragged over it
      ['dragenter', 'dragover'].forEach(e => {
        dropArea.addEventListener(e, () => {
          dropArea.classList.add('highlight');
        }, false);
      });

      ['dragleave', 'drop'].forEach(e => {
        dropArea.addEventListener(e, () => {

          dropArea.classList.remove('highlight');
        }, false);
      });

    });
}



document.addEventListener(
  'click',
  (e) =>
{
  if (e.target.dataset.deleteReviewImage == '')
  {
    let deleteImage = e.target,
    deleteInput = document.getElementById(deleteImage.dataset.input),
    reviewImage = document.getElementById(deleteImage.dataset.parent);

    deleteInput.setAttribute('value', 1);
    reviewImage.remove();
  }
});
