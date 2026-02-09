// assets/js/admin/nav-active.js
/*
  Page:  Admin Panel
  Route: /admin/*
*/
if (document.querySelector('#left-sidebar > li') !== null)
{
    /* Add active class to the current menu item (highlight it)
      in all sidebar menu.
    */
    let menu = document.querySelectorAll('#left-sidebar > li');

    menu.forEach((item, i) =>
    {
      // Ex. window.location.origin: "http://www.shoewear.net"
      //     window.location.pathname: "/admin/product/details"
      let currentURL = window.location.origin + window.location.pathname,
          anchor =  item.querySelectorAll('a');

      anchor.forEach((link) =>
      {
        let anchorList =  link.closest('li'),
            anchorDiv =  link.closest('div');
        if (link == currentURL)
        {
          anchorList.className += " active";
          anchorDiv.className += " show";
        }
      });
    });
}
// Activate for all User profile pages
if (document.querySelector('#profile-sidebar') !== null)
{
    /* Add active class to the current menu item (highlight it) */
    let menu = document.querySelectorAll('#profile-sidebar > a');

    menu.forEach((item, i) =>
    {
      let currentURL = window.location.origin + window.location.pathname;

      if (item.href == currentURL)
      {
        if (!(item.classList.contains('active')))
          item.classList.add('active');
      }
    });
}
