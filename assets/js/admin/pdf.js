// assets/js/admin/pdf.js

import PDFViewer from 'pdfobject';
/*
  Page:  Admin Invoices
  Route: /admin/invoices
*/
if (document.querySelector('[pdf-viewer]') !== null)
{
  let viewers = document.querySelectorAll('[pdf-viewer]');

  /*  Embed PDFs in HTML pages for each table rows (invoices) */
  viewers.forEach((viewer, i) =>
    {
      let address = viewer.dataset.address,
          id = viewer.dataset.id,
          options =
          { height: '850px',
            pdfOpenParams:
            {
              view: 'FitH', // fit width
              page: '1',    // start at page 1
              pagemode: 'none',  // no bookmark
            }
           };
      PDFViewer.embed(address, id, options);
    });
}


/*  Embed a single PDF in HTML page (invoice) */
if (document.querySelector('[single-pdf-viewer]') !== null)
{
  let viewer = document.querySelector('[single-pdf-viewer]'),
      address = viewer.dataset.address,
      id = viewer.dataset.id,
      options =
      { height: '1400px',
        pdfOpenParams:
        {
          view: 'FitH', // fit width
          page: '1',    // start at page 1
          pagemode: 'none',  // no bookmark
        }
       };

      PDFViewer.embed(address, id, options);
}
