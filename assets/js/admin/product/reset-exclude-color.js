// assets/js/admin/product/reset-exclude-color.js


function resetExcludeColor(select)
{
  let selectSet = document.getElementById(select.dataset.otherColors)
                          .querySelectorAll('select'),
      excludeSelect = document.getElementById(select.dataset.exclude),
      selectedColors = [];

  selectSet.forEach((element, i) =>
  {
    let color = element.options[element.selectedIndex],
        label = color.value;
    selectedColors.push(label);
  });

  excludeSelect.options.forEach((option, i) =>
  {
    if (!selectedColors.includes(option.value))
    {
      if (option.hasAttribute('disabled'))
        option.removeAttribute('disabled');
    }
  });
}

export {resetExcludeColor};
