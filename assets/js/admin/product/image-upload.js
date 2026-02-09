// assets/js/admin/product/image-upload.js

import empty from 'is-blank';
/*
  Page:  Admin Product New Page
  Route: admin_product_edit
         admin_product_edit_color
         (/admin/product/new)

*/

// Activate for Admin Product New Page
if (document.querySelector('form[name="product_form"]') !== null)
{
  document.addEventListener('change', e =>
  {
    if (e.target.dataset.imageInput == '')
    {
      let imageInput = e.target,
          productImage = imageInput.closest('div'),
          previewImage = productImage.querySelector('#preview-image'),
          cancelImage = productImage.querySelector('#cancel-image'),
          deleteImage = productImage.querySelector('#delete-image'),
          existingPath = previewImage.attributes.src.value,
          fileChosen = productImage.querySelector('#file-chosen'),
          imagePath = productImage.querySelector('a');

      if (imageInput.files)
      {
        // files is a FileList object (similar to NodeList)
        let files = imageInput.files;
        let file;

        // loop through files
        for (let i = 0; i < files.length; i++)
        {
          // get item
          file = files.item(i);
          //or
          // file = files[i];
        }

        // retrieve binary image file
        // const file = input.files[0];
        let preview_url = URL.createObjectURL(file);

        // set src of image and show
        previewImage.setAttribute('src', preview_url);
        previewImage.setAttribute('data-src', preview_url);
        imagePath.setAttribute('href', preview_url);

        fileChosen.innerHTML = file.name;
        previewImage.style.display = 'inline-block';
        cancelImage.style.display = 'inline-block';
        deleteImage.style.display = 'none';

        // Remove error message if any
        if (!empty(productImage.querySelector('.invalid-feedback')))
          productImage.querySelector('.invalid-feedback').remove();

        let parent = imageInput.closest('[pc-field]'),
            thumbnail1 = parent.querySelector('#thumbnail-1'),
            thumbnail2 = parent.querySelector('#thumbnail-2'),
            thumbnail3 = parent.querySelector('#thumbnail-3'),
            thumbnail4 = parent.querySelector('#thumbnail-4'),
            thumbnail5 = parent.querySelector('#thumbnail-5');

        switch (imageInput.dataset.imageOrder)
        {
          case '1':
              thumbnail1.style.backgroundImage = "url('" + preview_url + "')";;
            break;
          case '2':
              thumbnail2.style.backgroundImage = "url('" + preview_url + "')";;
            break;
          case '3':
              thumbnail3.style.backgroundImage = "url('" + preview_url + "')";;
            break;
          case '4':
              thumbnail4.style.backgroundImage = "url('" + preview_url + "')";;
            break;
          case '5':
              thumbnail5.style.backgroundImage = "url('" + preview_url + "')";;
            break;
          default:
        }
      }
    }
  });

  document.addEventListener('click', (e) =>
  {
    if (e.target.dataset.cancelImage == '')
    {
      let btn = e.target,
          productImage = btn.closest('div'),
          previewImage = productImage.querySelector('#preview-image'),
          cancelImage = productImage.querySelector('#cancel-image'),
          deleteImage = productImage.querySelector('#delete-image'),
          imageInput = productImage.querySelector('input[data-image-input]'),
          fileChosen = productImage.querySelector('#file-chosen'),
          imagePath = productImage.querySelector('a'),
          existingName = previewImage.attributes.alt.value,
          existingPath = previewImage.attributes.original.value;


      e.preventDefault();
      // destroy previous local url
      // URL.revokeObjectURL(_PREVIEW_URL);

      // reset to no selection
      imageInput.value = '';

      if (!empty(existingName))
      {
        // set src of image and show
        previewImage.setAttribute('src', existingPath);
        previewImage.setAttribute('data-src', existingPath);
        imagePath.setAttribute('href', existingPath);

        previewImage.attributes.src.value = existingPath;
        fileChosen.textContent = existingName;
        deleteImage.style.display = 'inline-block';
        deleteImage.querySelector('input').value = '';
      }

      if (existingPath == '')
      {
        deleteImage.style.display = 'none';
        previewImage.style.display = 'none';
        fileChosen.innerHTML = '<span style="font-size:16px;padding-left:20px;">No file chosen</span>';
      }

      // hide elements that are not required
      cancelImage.style.display = 'none';

      let parent = imageInput.closest('[pc-field]'),
          thumbnail1 = parent.querySelector('#thumbnail-1'),
          thumbnail2 = parent.querySelector('#thumbnail-2'),
          thumbnail3 = parent.querySelector('#thumbnail-3'),
          thumbnail4 = parent.querySelector('#thumbnail-4'),
          thumbnail5 = parent.querySelector('#thumbnail-5');

      switch (imageInput.dataset.imageOrder)
      {
        case '1':
            thumbnail1.style.backgroundImage = "url('" + _PREVIEW_URL + "')";;
          break;
        case '2':
            thumbnail2.style.backgroundImage = "url('" + _PREVIEW_URL + "')";;
          break;
        case '3':
            thumbnail3.style.backgroundImage = "url('" + _PREVIEW_URL + "')";;
          break;
        case '4':
            thumbnail4.style.backgroundImage = "url('" + _PREVIEW_URL + "')";;
          break;
        case '5':
            thumbnail5.style.backgroundImage = "url('" + _PREVIEW_URL + "')";;
          break;
        default:
      }
    }

    if (e.target.dataset.deleteImage == '')
    {
      let btn = e.target,
          productImage = btn.closest('div'),
          previewImage = productImage.querySelector('#preview-image'),
          deleteImage = productImage.querySelector('#delete-image'),
          imageInput = productImage.querySelector('input[data-image-input]'),
          existingPath = previewImage.attributes.src.value,
          fileChosen = productImage.querySelector('#file-chosen'),
          imagePath = productImage.querySelector('a'),
          existingName = fileChosen.textContent;

      e.preventDefault();
      deleteImage.style.display = 'none';
      previewImage.style.display = 'none';
      previewImage.attributes.src.value = '';
      fileChosen.innerHTML = '<span style="font-size:16px;padding-left:20px;">No file chosen</span>';

      // setting hidden delete-image token
      deleteImage.querySelector('input').value = true;

      // Add required attribute to the first image input
      if (imageInput.dataset.imageOrder == 1)
        imageInput.setAttribute('data-required','');

      // resetting image input
      imageInput.value = '';

      let parent = imageInput.closest('[pc-field]'),
          thumbnail1 = parent.querySelector('#thumbnail-1'),
          thumbnail2 = parent.querySelector('#thumbnail-2'),
          thumbnail3 = parent.querySelector('#thumbnail-3'),
          thumbnail4 = parent.querySelector('#thumbnail-4'),
          thumbnail5 = parent.querySelector('#thumbnail-5');

      switch (imageInput.dataset.imageOrder)
      {
        case '1':
            thumbnail1.style.backgroundImage = "";
          break;
        case '2':
            thumbnail2.style.backgroundImage = "";
          break;
        case '3':
            thumbnail3.style.backgroundImage = "";
          break;
        case '4':
            thumbnail4.style.backgroundImage = "";
          break;
        case '5':
            thumbnail5.style.backgroundImage = "";
          break;
        default:
      }
    }
  });
}

if (document.querySelector('#image-upload-profile') !== null)
{
  let userImage = document.getElementById('image-upload-profile'),
      input = userImage.querySelector('input'),
      previewImage = userImage.querySelector('#image-preview'),
      cancelImage = userImage.querySelector('button[id="cancel-image"]'),
      deleteImage = userImage.querySelector('button[id="delete-image"]'),
      existingPath = previewImage.dataset.imgPath,
      imagePath = userImage.querySelector('a');

  // preview if image exists
  if (existingPath)
  {
    previewImage.style.display = 'inline-block';
    deleteImage.querySelector('input').value = '';
  }

  if (existingPath == '')
    deleteImage.style.display = 'none';


  input.addEventListener('change', function()
  {
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
      // const file = input.files[0];
      let preview_url = URL.createObjectURL(file);

      // set src of image and show
      previewImage.style.backgroundImage = "url(" + preview_url + ")";
      imagePath.setAttribute('href', preview_url);


      // previewImage.setAttribute('src', _PREVIEW_URL);
      // previewImage.setAttribute('data-src', _PREVIEW_URL);
      // imagePath.setAttribute('href', _PREVIEW_URL);

      previewImage.style.display = 'inline-block';
      cancelImage.style.display = 'inline-block';
      deleteImage.style.display = 'none';
    }
  });

  cancelImage.addEventListener('click', (e) =>
  {
      e.preventDefault();
      // reset to no selection
      input.value = '';

      if (existingPath)
      {
        // set src of image and show
        previewImage.style.backgroundImage = "url(" + existingPath + ")";
        imagePath.setAttribute('href', existingPath);

        deleteImage.style.display = 'inline-block';
        deleteImage.querySelector('input').value = '';
      }

      if (existingPath == '')
      {
        deleteImage.style.display = 'none';
        previewImage.style.display = 'none';
      }

      // hide elements that are not required
      // document.querySelector("#image-name").style.display = 'none';
      cancelImage.style.display = 'none';
    });

  deleteImage.addEventListener('click', (e) =>
  {
    e.preventDefault();
    deleteImage.style.display = 'none';
    previewImage.style.display = 'none';
    previewImage.style.backgroundImage = "";

    // setting hidden delete-image token
    deleteImage.querySelector('input').value = true;

    // resetting image input
    input.value = '';
  });
}
