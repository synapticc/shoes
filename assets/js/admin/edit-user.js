// assets/js/admin/edit-user.js

function saveUser(form)
{
  let url = form.dataset.url,
  formData = new FormData(form),
  updatedTarget = document.getElementById(form.dataset.updatedTarget),
  rolesTarget = document.getElementById(form.dataset.rolesTarget),
  statusTarget = document.getElementById(form.dataset.statusTarget),
  closeBtn = form.querySelector('#close-modal'),
  updatedRoles, updatedStatus;

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

  fetch(url, {
      method: "post",
      body: formData,
  }).
  then(response =>
    {
      // Flash success message.
      if(response.status == 200)
        window.FlashMessage.info('User changes saved', flashOptions);

      return response.json();
    }
  ).
  then(data => {
    updatedTarget.innerHTML = data;
    updatedRoles = updatedTarget.querySelector('#updated-roles');
    updatedStatus = updatedTarget.querySelector('#updated-status');

    rolesTarget.innerHTML = '';
    rolesTarget.appendChild(updatedRoles);
    statusTarget.innerHTML = '';
    statusTarget.appendChild(updatedStatus);
    closeBtn.click();
  });
}


let formSet = document.querySelectorAll('#admin_edit_user_form');
formSet.forEach((form, i) =>
{
  form.addEventListener('submit', (e) =>
  {
    e.preventDefault();
    saveUser(form);
  });
});
