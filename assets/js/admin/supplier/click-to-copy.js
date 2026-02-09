// assets/js/admin/supplier/click-to-copy.js
/*
  Page:  Admin Supplier Page
  Route: admin_users_edit
  
  > Copy text to clipboard.


  // Taken from https://codepen.io/JAGATHISH1123/pen/wOORvm
*/
if (document.querySelector('[data-copy-status="active"]') !== null)
{
  let phoneCopyAll = document.querySelectorAll('[data-copy-status="active"]');
  phoneCopyAll.forEach((phoneDiv, i) =>
  {
    phoneDiv.addEventListener('click', e =>
    {
        let phone = phoneDiv.dataset.copy,
        textArea  = document.createElement('textarea');
        textArea.width  = "1px";
        textArea.height = "1px";
        textArea.background =  "transparents" ;
        textArea.value = phone;
        document.body.append(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);

        const flashOptions =
        {
          progress: false,
          interactive: true,
          timeout: 800,
          appear_delay: 10,
          container: '.flash-container',
          theme: 'default',
          classes: {
              container: 'flash-container',
              flash: 'flash-message',
              visible: 'is-visible',
              progress: 'flash-progress',
              progress_hidden: 'is-hidden'
          }};

        window.FlashMessage.info('Copied to clipboard.', flashOptions);
    });
  });
}
