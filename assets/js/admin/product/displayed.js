// assets/js/admin/product/displayed.js

/* Admin Product page table
  Submit 

*/
if (document.querySelectorAll('#product_displayed') !== null)
{
    const productDisplayed = document.querySelectorAll('#product_displayed');

    productDisplayed.forEach((item, i) => {
      item.addEventListener('click', () => {
        let closestForm = item.closest("form");
        closestForm.submit();
      });
    });
}
