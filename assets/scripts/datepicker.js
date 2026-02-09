

import '@lamplightdev/aeon';

if (document.getElementById('aeon-datepicker') !== null)
{
  let aeonTags = document.querySelectorAll('#aeon-datepicker');
  aeonTags.forEach((aeonTag, i) =>
  {
    let target = document.getElementById(aeonTag.dataset.target);
    aeonTag.addEventListener('change', e =>
    {
      target.setAttribute('value',`${aeonTag.value['date']} ${aeonTag.value['time']}`);
      target.value = `${aeonTag.value['date']} ${aeonTag.value['time']}`;
    });
  });
}
