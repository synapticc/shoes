// assets/js/admin/datepicker.js
import Datepicker from 'js-datepicker';
import empty from 'is-blank';


if (document.querySelector('.js-datepicker') !== null)
{
  let widgets = document.querySelectorAll('.js-datepicker');

  widgets.forEach((widget, i) =>
  {
    let selectedDate;

    if (!empty(widget.dataset.date))
      selectedDate = new Date(widget.dataset.date);

    let options =
    {
      startDate: selectedDate,
      formatter: (input, date, instance) => {
        const dateOptions = {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
        };
        let dateString = date.toLocaleDateString('en-US', dateOptions);
        input.value = dateString; // => 'Wednesday, April 16, 2025';

        dateString = date.toLocaleDateString('en-UK');
        let target = document.getElementById(widget.dataset.target);

        target.value = dateString;
        target.setAttribute('value', dateString); // => '16/04/2025';
      }
    };

    Datepicker(widget, options);
  });
}
