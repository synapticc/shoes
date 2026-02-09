// assets/js/store/review-helpful.js


if (document.querySelector('[data-review-helpful]') !== null)
{

  function rateHelpful(tag)
  {
    let url = tag.dataset.url,
    target = document.getElementById(tag.dataset.target),
    unhelpful = false;

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

    fetch(url).
    then(response =>
      {
        if(response.status == 200)
          window.FlashMessage.success('Rating saved', flashOptions);

        return response.json();
      }).
    then((received) => {
      target.innerHTML = received.helpfulCount;
      unhelpful = received.unhelpful;
    });

    let unhelpfulBtn = document.getElementById(tag.dataset.unhelpful);

    if (unhelpfulBtn.classList.contains('fill-warning'))
    {
      unhelpfulBtn.classList.replace('fill-warning', 'fill-danger-hover');
      return true;
    }

  }

  function helpful(e)
  {
    if (e.target === undefined)
      return false;

    let tag = e.target, path;

    if ((tag.tagName === 'BUTTON') ||
        (tag.tagName === 'svg'))
      path = tag.querySelector('path[data-style]');

    if ((tag.tagName === 'path') &&
        (tag.dataset.style == ''))
      path = tag.closest('svg').querySelector('path[data-style]');


    if (path.classList.contains('fill-success'))
    {
      path.classList.replace('fill-success', 'fill-info');
      rateHelpful(tag);
      return true;
    }

    if (path.classList.contains('fill-success-hover'))
    {
      path.classList.replace('fill-success-hover', 'fill-info');
      rateHelpful(tag);
      return true;
    }

    if (path.classList.contains('fill-info'))
    {
       path.classList.replace('fill-info', 'fill-success-hover');
       rateHelpful(tag);
       return true;
    }
  }

  function notHelpful(e)
  {
    if (e.target === undefined)
      return false;

    let tag = e.target, svg;

    if (tag.tagName === 'BUTTON')
      svg = tag.querySelector('svg[data-style]');

    if (tag.tagName === 'path')
      svg = tag.closest('svg[data-style]');

    if ((tag.tagName === 'svg') ||
        (tag.dataset.style == ''))
      svg = tag;

    if (svg.classList.contains('fill-danger-hover'))
    {
      svg.classList.replace('fill-danger-hover', 'fill-warning');
      return true;
    }

    if (svg.classList.contains('fill-danger'))
    {
      svg.classList.replace('fill-danger', 'fill-warning');
      return true;
    }

    if (svg.classList.contains('fill-warning'))
    {
      svg.classList.replace('fill-warning', 'fill-danger-hover');
      return true;
    }
  }

  document.addEventListener(
    'click',
    (e) =>
  {
    if (e.target.dataset.reviewHelpful == '')
      helpful(e);

    if (e.target.dataset.reviewNotHelpful == '')
      notHelpful(e);

  });

}
