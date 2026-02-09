// assets/js/store/profile-upload.js
import { isEmpty as empty }  from '@zerodep/is-empty';

/*
  Page:  User Profile Edit Page
  Route: user_profile

  > Preview profile image
  > Hide image when delete button is clicked
  > Display original image when cancel button is clicked
  > Crop uploaded image
      max_width: 500,
      max_height: 500,
*/
if (document.querySelector('#image-upload-profile') !== null)
{

    let profileImage = document.querySelector('#image-upload-profile'),
        input = profileImage.querySelector('input'),
        previewImage =  document.querySelector('#image-preview'),
        cancelImage = document.querySelector('#cancel-image'),
        deleteImage = document.querySelector('#delete-image'),
        style = window.getComputedStyle(previewImage, false),
        existingPath = style.backgroundImage.slice(4, -1).replace(/"/g, ""),
        existingImage;

    if (profileImage.dataset.hasOwnProperty('image'))
      existingImage = profileImage.dataset.image === 'true' ? true : false;

    // Preview if image exists.
    if (!empty(existingPath))
    {
      previewImage.style.display = 'inline-block';
      deleteImage.querySelector('input').value = '';
    }

    if (existingImage)
      deleteImage.style.display = 'inline-block';

    // Hide image when delete button is clicked.
    if (empty(existingPath))
      deleteImage.style.display = 'none';

    input.addEventListener('change', (e) =>
    {
      let preview_url;
      if (input.files)
      {
        // files is a FileList object (similar to NodeList)
        var files = input.files;
        var file;

        // loop through files
        for (var i = 0; i < files.length; i++)
        {
          // get item
          file = files.item(i);
          //or
          // file = files[i];
        }

        // retrieve binary image file
        preview_url = URL.createObjectURL(file);

        // set src of image and show
        previewImage.setAttribute('style',"background-image: url(" + preview_url + ");");
        previewImage.setAttribute('src', preview_url);
        previewImage.setAttribute('data-src', preview_url);

        previewImage.style.display = 'inline-block';
        cancelImage.style.display = 'inline-block';
        deleteImage.style.display = 'none';
      }

    });

    cancelImage.addEventListener('click', (e) =>
    {
      e.preventDefault();

      // Reset to no selection.
      input.value = '';

      if (empty(existingPath))
      {
        // Set image source and display image.
        previewImage.setAttribute('src', existingPath);
        previewImage.setAttribute('data-src', existingPath);

        previewImage.setAttribute('style',"background-image: url(" + existingPath + ");");
        deleteImage.style.display = 'inline-block';
        deleteImage.querySelector('input').value = '';
      }

      deleteImage.style.display = 'none';
      previewImage.style.display = 'none';

      // Hide elements that are not required.
      cancelImage.style.display = 'none';

    });

    deleteImage.addEventListener('click', (e) =>
    {
      e.preventDefault();
      deleteImage.style.display = 'none';
      previewImage.style.display = 'none';
      previewImage.setAttribute('style',"background-image: url('');");

      // Setting hidden delete-image token.
      deleteImage.querySelector('input').value = true;

      // Resetting image input.
      input.value = '';
    });
}
