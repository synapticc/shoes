// assets/js/admin/product/hover-preview.js


/*
  Page:  Admin ProductData Edit pages
  Route: admin_product_data_edit

*/

// Activate for admin_product_data_edit
if (document.querySelector('.hover-preview') !== null)
{
    // var previews;
    // const hoverPreview = window.hoverPreview;
    //
    // var preview = hoverPreview(document.querySelector('.preview'),
    // {
    // 	delay : 100, // sets a delay before the preview is shown
    // 	cursor : true // enables a loading cursor while the preview is loading
    // });

    // apply it to multiple elements
    var previews = [...document.querySelectorAll('.hover-preview')]
                    .map((element, index) =>
      {
      	return hoverPreview(element, {
      		delay : 100,
        	cursor : false
      	});
    });
}
