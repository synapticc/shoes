// assets/js/admin/product/exclude-color.js


function excludeColor(select)
{
  let main = select.closest('div[data-other-colors]'),
      selectSet = main.querySelectorAll('select[data-other-color-select]'),
      excludeSelect = document.getElementById(main.dataset.exclude),
      selectedColors = [];

  selectSet.forEach((element, i) =>
  {
    let color = element.options[element.selectedIndex],
        label = color.value;
    selectedColors.push(label);
  });

  excludeSelect.options.forEach((option, i) =>
  {
    if (selectedColors.includes(option.value))
    {
      if (!option.hasAttribute('disabled'))
        option.setAttribute('disabled','');
    }
    else
    {
      if (option.hasAttribute('disabled'))
        option.removeAttribute('disabled');
    }
  });
}

export {excludeColor};
