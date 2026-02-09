// assets/js/admin/theme-radio.js
/*
  Page:  Admin Settings
  Route: /admin/settings
*/
if (document.querySelector('input[admin-theme-radio]') !== null)
{
  let settingsThemeSet =  document.querySelectorAll('input[admin-theme-radio]');

  settingsThemeSet.forEach((settingsTheme, i) =>
  {
      /* The theme select offers 'Dark', 'Light' and unselected.
         Only one radio button can be selected at a time. Hence
         the other option should be unchecked when one of them
         is selected.
      */
      settingsTheme.addEventListener('click', e =>
      {
        settingsThemeSet.forEach((settings, i) =>
        {
          if (settings.hasAttribute('checked'))
          {
            settings.removeAttribute('checked');
            settings.style.backgroundPosition = ' left center ';
            settings.style.backgroundImage  = 'url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%27-4 -4 8 8%27%3e%3ccircle r=%273%27 fill=%27rgba%280, 0, 0, 0.25%29%27/%3e%3c/svg%3e")';
            settings.style.backgroundColor = '#ffffff';
            settings.style.borderColor = '#d1d7e0';
          }
          else if( !settings.hasAttribute('checked'))
          {
            settings.setAttribute('checked','checked');
            settings.style.backgroundPosition = ' right center ';
            settings.style.backgroundImage  = 'url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%27-4 -4 8 8%27%3e%3ccircle r=%273%27 fill=%27%23ffffff%27/%3e%3c/svg%3e")';
            settings.style.backgroundColor = '#262B40';
            settings.style.borderColor = '#262B40';
          }
        });
      });
  });
}
