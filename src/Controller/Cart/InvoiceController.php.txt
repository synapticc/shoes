<?php

// src/Controller/Cart/InvoiceController.php

namespace App\Controller\Cart;

use App\Controller\_Utils\Attributes;
use App\Controller\_Utils\MPDF;
use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\Billing\Billing;
use App\Entity\Billing\Order;
use App\Repository\Billing\OrderRepository as OrderRepo;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Jlorente\CreditCards\CreditCardValidator;
use Knp\Component\Pager\PaginatorInterface;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends AbstractController
{
    use Cart;
    use Attributes;

    /* path: /invoice/{id}/confirm */
    #[ParamDecryptor(['invoice'])]
    public function invoicePdfConfirm(OrderRepo $orderRepo, Order $invoice, ORM $em)
    {
        $user = $invoice->getUsers();
        $uuid = $user->getUuid();
        $invoiceId = $invoice->getId();

        $filesystem = new Filesystem();
        $path = getcwd(); // current directory path
        $thumbUniqueId = bin2hex(random_bytes(4));

        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();

        $safeFilename =
          'invoice-'.

          /* Pad the front of invoice ID with '0' */
          str_pad($invoiceId, 6, '0', STR_PAD_LEFT).'-'.

          /* 1) Replace all blank spaces with '_'
             2) Remove whitespace from the beginning and end
             3) Convert all characters to lower case */
          preg_replace('/\s+/', '_', trim(strtolower($firstName))).'_'.
          preg_replace('/\s+/', '_', trim(strtolower($lastName))).'-'.
          date('d_m_Y', time());

        // New folder to store the invoice
        $pdfPath = "$path/uploads/users/$uuid/invoices/";

        $pdfURL = "uploads/users/$uuid/invoices/$safeFilename.pdf";

        $pdfFile = $pdfPath."$safeFilename.pdf";

        $thumbnailPath = "$path/uploads/users/$uuid/thumbnails/";

        $thumbnailURL = "uploads/users/$uuid/thumbnails/$thumbUniqueId.jpg";

        $thumbnailURLFull = "$path/uploads/users/$uuid/thumbnails/$thumbUniqueId.jpg";

        /* Make a new directory
            1) For PDF
            2) For thumbnail  */
        try {
            // PDF directory
            if (!$filesystem->exists($pdfPath)) {
                $old = umask(0);
                $filesystem->mkdir($pdfPath, 0755);
                $filesystem->chown($pdfPath, 'www-data');
                $filesystem->chgrp($pdfPath, 'www-data');
                umask($old);
            }

            // Thumbnail directory
            if (!$filesystem->exists($thumbnailPath)) {
                $old = umask(0);
                $filesystem->mkdir($thumbnailPath, 0755);
                $filesystem->chown($thumbnailPath, 'www-data');
                $filesystem->chgrp($thumbnailPath, 'www-data');
                umask($old);
            }
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        $billing = !empty($invoice->getBilling())
                          ? $invoice->getBilling()
                          : new Billing();

        $billing->setInvoicePath($pdfURL)
                ->setInvoiceThumbnail($thumbnailURL);
        $invoice->setBilling($billing);

        $em->persist($invoice);
        $em->flush();

        $invoice = $orderRepo->invoice($invoice->getId());

        // Generate pdf
        $pdf = new MPDF('TCPDF');
        $this->invoicePdfGenerator($pdf, $invoice, $safeFilename, $pdfPath);

        try {
            $thumbImage = new \Imagick();
            $thumbImage->readimage($pdfFile.'[0]');
            $thumbImage->thumbnailImage(225, 320, true, true);
            $thumbImage->setImageCompressionQuality(90);
            $thumbImage->stripImage();
            $thumbImage->setImageColorspace(255);
            $thumbImage->setImageFormat('jpeg');
            $thumbImage->writeImages($thumbnailURL, false);
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        return $this->render('store/invoice.html.twig', [
            'invoice' => $billing,
        ]);
    }

    /**
     * Generate invoice PDF file.
     */
    private function invoicePdfGenerator(MPDF $pdf, array $invoice, string $safeFilename, string $pdfPath): void
    {
        // Retrieve various values to be used in PDF.
        $email = $invoice['email'];
        $title = $invoice['title'];
        $firstName = $invoice['firstName'];
        $middleName = $invoice['middleName'];
        $lastName = $invoice['lastName'];
        $invoiceId = $invoice['id'];
        $invoiceId = str_pad($invoiceId, 6, '0', STR_PAD_LEFT);
        $dateOfInvoice = date('d/m/Y', time());
        $invoiceTotal = number_format($invoice['invoiceTotal'], 2);
        $street = $invoice['street'];
        $city = $invoice['city'];
        $country = $invoice['country'];
        $zip = $invoice['zip'];
        $notes = $invoice['deliveryNotes'];
        $orderItems = $invoice['items'];
        $validator = new CreditCardValidator();
        // Get the card type using the CreditCardValidator library. (ex. Mastercard)
        $card = $validator->getType($invoice['cardNumber'])->getNiceType();
        // Mask the card number before using in PDF.
        $cardNumber = $invoice['cardNumber'];
        $maskedCard = $this->maskCard($cardNumber);

        $filesystem = new Filesystem();
        $current_dir_path = getcwd().'/';

        /* Calculate total number of items */
        foreach ($orderItems as $i => $item) {
            $totalItems[] = $item['quantity'];
        }
        $totalItems = array_sum($totalItems);

        $widthItem = 4;
        $widthSKU = 6;
        $widthThumbnail = 22;
        $widthName = 12;
        $widthBrand = 8;
        $widthCategory = 6;
        $widthType = 6;
        $widthColor = 9;
        $widthSize = 4;
        $widthPrice = 7;
        $widthQty = 4;
        $widthSubTotal = 9;
        $author = 'Bella Shoes';
        $subject = 'Customer Invoice (PDF)';
        $factory = 'Bella Shoes Ltd';
        $bottom_1 = 'If you have any complaints about this invoice, please contact:';
        $bottom_2 = 'Kristin Pierce (Head Customer Support) | (+230) 405 2490 | support@remarch-footear.mu';

        $logo_file = $current_dir_path.'build/images/company-logo.png';

        /* For number of items between 1 and 4. */
        if ((count($orderItems) >= 1) && (count($orderItems) < 5)) {
            // Page 1
            $first_page_items = array_slice($orderItems, 0, 5);
            $pdf = $pdf->create('vertical', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetAuthor($author);
            $pdf->SetTitle('Invoice | '.$title.' '.$lastName);
            $pdf->SetSubject($subject);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 7.5, '', true);
            $pdf->SetCreator(PDF_CREATOR);
            // // remove default header/footer
            $pdf->setPrintHeader(false);
            // remove default footer
            $pdf->setPrintFooter(false);

            $pdf->SetHeaderMargin(0);
            // $pdf->SetFooterMargin(0);
            // set margins
            $pdf->SetMargins(10, 10, 10);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            $pdf->setFooterData([162, 159, 159], [205, 205, 205]);

            // set header and footer fonts
            $pdf->setFooterFont(['helvetica', 'M', 6]);

            // set margins
            $pdf->SetFooterMargin(15);

            $pdf->AddPage();

            // Top left bar
            $pdf->Image(
                $logo_file,
                175,
                4,
                30,
                15,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );

            // Date and invoice no (Topmost left-hand)
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);
            $pdf->writeHTML("<b>Date:</b> $dateOfInvoice");
            $pdf->writeHTML("<b>Invoice Reference: </b>#$invoiceId");
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);

            // Company address (Top left-hand)
            $pdf->writeHTML("<b>$factory<b>");
            $pdf->writeHTML('75, Old Ripailles Street,');
            $pdf->writeHTML('Plaines Wilhems,');
            $pdf->writeHTML('Mauritius, 33577.');
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);

            // Customer name & address (Top right-hand)
            $pdf->writeHTML('Bill to:', true, false, false, false, 'R');
            $pdf->writeHTML("<b>$title $lastName $middleName $firstName</b>", true, false, false, false, 'R');
            $pdf->writeHTML("$street,", true, false, false, false, 'R');
            $pdf->writeHTML("$city,", true, false, false, false, 'R');
            $pdf->writeHTML("$country, $zip.", true, false, false, false, 'R');
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);

            // Table header
            $content =
            '<h3 align="center">Invoice</h3>
        <br><br>
        <table border="0.5" cellspacing="0" cellpadding="5">
          <thead>
           <tr align="center" style="verical-align:middle;background-color: rgb(201, 201, 201); color: rgb(0,0,0);">
            <th width="'.$widthItem.'%"><b>Item</b></th>
            <th width="'.$widthSKU.'%"><b>SKU</b></th>
            <th width="'.$widthThumbnail.'%"><b>Thumbnail</b></th>
            <th width="'.$widthName.'%"><b>Name</b></th>
            <th width="'.$widthBrand.'%"><b>Brand</b></th>
            <th width="'.$widthCategory.'%"><b>Category</b></th>
            <th width="'.$widthType.'%"><b>Type</b></th>
            <th width="'.$widthSize.'%"><b>Size</b></th>
            <th width="'.$widthColor.'%"><b>Color</b></th>
            <th width="'.$widthPrice.'%"><b>Price (Rs)</b></th>
            <th width="'.$widthQty.'%"><b>Qty</b></th>
            <th width="'.$widthSubTotal.'%"><b>Sub Total (Rs)</b></th>
           </tr>
          </thead>
          <tbody>';

            $pdf->SetFont('helvetica', '', 5, '', true);
            if (!empty($orderItems)) {
                $y = 72;
                $itemCount = 1;
                foreach ($orderItems as $i => $item) {
                    $image = $current_dir_path.$item['imageMedium'];
                    $sku = $item['sku'];
                    $name = $item['name'];
                    $brand = $item['brand_full'];
                    $category = $item['category_full'];
                    $type = $item['type_full'];
                    $size = $item['size'];
                    $sellingPrice = number_format($item['sellingPrice'], 2);
                    $qty = $item['quantity'];
                    $itemTotal = number_format($item['subtotal'], 2);
                    $color = $item['colors_full'];

                    $x = 30.5;
                    $w = 40;
                    $h = 'auto';
                    $imageType = '';
                    $link = '';
                    $align = 'center';
                    $resize = false;
                    $dpi = 300;
                    $palign = '';
                    $ismask = false;
                    $imgmask = false;
                    $border = 0;
                    $fitbox = false;
                    $hidden = false;
                    $fitonpage = false;

                    $content .=
                    '<tr>
              <td align="center" width="'.$widthItem.'%"><b>'.$itemCount.'</b></td>
              <td width="'.$widthSKU.'%">'.$sku.'</td>
              <td style="background-color: rgb(255, 255, 255);color: white;z-index:-999" width="'.$widthThumbnail.'%" height="95px">'.

                      $pdf->Image($image, $x, $y, '', 30, '', '', '', false, 300, 'center', false, false, 0, true, false, false)

                      .'</td>
              <td align="center" width="'.$widthName.'%">'.$name.'</td>
              <td align="center" width="'.$widthBrand.'%">'.$brand.'</td>
              <td align="center" width="'.$widthCategory.'%">'.$category.'</td>
              <td align="center" width="'.$widthType.'%">'.$type.'</td>
              <td align="center" width="'.$widthSize.'%">'.$size.'</td>
              <td align="center" width="'.$widthColor.'%">'.$color.'</td>
              <td align="center" width="'.$widthPrice.'%">'.$sellingPrice.'</td>
              <td align="center" width="'.$widthQty.'%">'.$qty.'</td>
              <td align="center" width="'.$widthSubTotal.'%">'.$itemTotal.'</td>
            </tr>';
                    $y += 34;
                    ++$itemCount;
                }
            }

            $content .=
            '<tr>
          <td align="right" colspan="11"><b>No. of items</b></td>
          <td align="center">'.$totalItems.' pcs</td>
         </tr>
         <tr>
          <td align="right" colspan="11"><b>Total</b></td>
          <td align="center" style="verical-align:middle;background-color: rgb(201, 201, 201); color: rgb(0,0,0);font-weight:bold">Rs '.$invoiceTotal.'</td>
         </tr>
         </tbody></table>';

            $pdf->writeHTML($content);

            // comments
            $pdf->SetFont('', '', 7);
            // Bottom left
            $pdf->writeHTML('<b>Thank you for your purchase.</b>');
            $pdf->Write(0, "\n\n", '', 0, 'C', true, 0, false, false, 0);
            $pdf->writeHTML('Payment method: <b>'.$card.'</b>');
            $pdf->writeHTML('Card account: <i>'.$maskedCard.'</i>');
            $pdf->writeHTML('Card holder: <i>'.$invoice['cardHolder'].'</i>');
            $pdf->Write(0, "\n\n\n", '', 0, 'C', true, 0, false, false, 0);

            $pdf->SetY(-35);
            // Set font
            $pdf->SetFont('helvetica', '', 6);
            // Page number
            // $pdf->Cell(0, 0, $pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(),
            // 0, false, 'C', 0, '', 0, false, 'T', 'M');

            // Bottom center
            $pdf->writeHTML('If you have any complaints about this invoice, please contact:', true, false, false, false, 'C');
            $pdf->writeHTML('Kristin Pierce (Head Customer Support) | (+230) 405 2490 | support@remarch-footear.mu', true, false, false, false, 'C');
        }
        /* For number of items above 4. */ elseif (count($orderItems) > 4) {
            // Page 1
            $first_page_items = array_slice($orderItems, 0, 5);

            $pdf = $pdf->create('vertical', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetAuthor($author);
            $pdf->SetTitle('Invoice | '.$title.' '.$lastName);
            $pdf->SetSubject($subject);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 7.5, '', true);
            $pdf->SetCreator(PDF_CREATOR);
            // // remove default header/footer
            $pdf->setPrintHeader(false);
            // remove default footer
            $pdf->setPrintFooter(false);

            $pdf->SetHeaderMargin(0);
            $pdf->SetFooterMargin(0);
            // set margins
            $pdf->SetMargins(10, 10, 10);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->AddPage();

            // Top left bar
            $pdf->Image(
                $logo_file,
                175,
                4,
                30,
                15,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );

            // Date and invoice no (Topmost left-hand)
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);
            $pdf->writeHTML("<b>Date:</b> $dateOfInvoice");
            $pdf->writeHTML("<b>Invoice Reference: </b>#$invoiceId");
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);

            // Company address (Top left-hand)
            $pdf->writeHTML("<b>$factory<b>");
            $pdf->writeHTML('75, Old Ripailles Street,');
            $pdf->writeHTML('Plaines Wilhems,');
            $pdf->writeHTML('Mauritius, 33577.');
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);

            // Customer name & address (Top right-hand)
            $pdf->writeHTML('Bill to:', true, false, false, false, 'R');
            $pdf->writeHTML("<b>$title $lastName $middleName $firstName</b>", true, false, false, false, 'R');
            $pdf->writeHTML("$street,", true, false, false, false, 'R');
            $pdf->writeHTML("$city,", true, false, false, false, 'R');
            $pdf->writeHTML("$country, $zip.", true, false, false, false, 'R');
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);

            // Table header
            $content =
            '<h3 align="center">Invoice</h3>
        <br><br>
        <table border="0.5" cellspacing="0" cellpadding="5">
          <thead>
           <tr align="center" style="verical-align:middle;background-color: rgb(201, 201, 201); color: rgb(0,0,0);">
            <th width="'.$widthItem.'%"><b>Item</b></th>
            <th width="'.$widthSKU.'%"><b>SKU</b></th>
            <th width="'.$widthThumbnail.'%"><b>Thumbnail</b></th>
            <th width="'.$widthName.'%"><b>Name</b></th>
            <th width="'.$widthBrand.'%"><b>Brand</b></th>
            <th width="'.$widthCategory.'%"><b>Category</b></th>
            <th width="'.$widthType.'%"><b>Type</b></th>
            <th width="'.$widthSize.'%"><b>Size</b></th>
            <th width="'.$widthColor.'%"><b>Color</b></th>
            <th width="'.$widthPrice.'%"><b>Price (Rs)</b></th>
            <th width="'.$widthQty.'%"><b>Qty</b></th>
            <th width="'.$widthSubTotal.'%"><b>Sub Total (Rs)</b></th>
           </tr>
          </thead>
          <tbody>';

            $pdf->SetFont('helvetica', '', 5, '', true);
            // Table body
            if (!empty($first_page_items)) {
                $y = 73;
                $itemCount = 1;
                foreach ($first_page_items as $i => $item) {
                    $image = $current_dir_path.$item['imageMedium'];
                    $sku = $item['sku'];
                    $name = $item['name'];
                    $brand = $item['brand_full'];
                    $category = $item['category_full'];
                    $type = $item['type_full'];
                    $size = $item['size'];
                    $sellingPrice = number_format($item['sellingPrice'], 2);
                    $qty = $item['quantity'];
                    $itemTotal = number_format($item['subtotal'], 2);
                    $color = $item['colors_full'];

                    $x = 30.5;
                    $w = 40;
                    $h = 'auto';
                    $imageType = '';
                    $link = '';
                    $align = 'center';
                    $resize = false;
                    $dpi = 300;
                    $palign = '';
                    $ismask = false;
                    $imgmask = false;
                    $border = 0;
                    $fitbox = false;
                    $hidden = false;
                    $fitonpage = false;

                    $content .=
                    '<tr>
              <td align="center" width="'.$widthItem.'%"><b>'.$itemCount.'</b></td>
              <td width="'.$widthSKU.'%">'.$sku.'</td>
              <td style="background-color: rgb(255, 255, 255);color: white;z-index:-999" width="'.$widthThumbnail.'%" height="95px">'.

                      $pdf->Image($image, $x, $y, '', 30, '', '', '', false, 300, 'center', false, false, 0, true, false, false)

                      .'</td>
              <td align="center" width="'.$widthName.'%">'.$name.'</td>
              <td align="center" width="'.$widthBrand.'%">'.$brand.'</td>
              <td align="center" width="'.$widthCategory.'%">'.$category.'</td>
              <td align="center" width="'.$widthType.'%">'.$type.'</td>
              <td align="center" width="'.$widthSize.'%">'.$size.'</td>
              <td align="center" width="'.$widthColor.'%">'.$color.'</td>
              <td align="center" width="'.$widthPrice.'%">'.$sellingPrice.'</td>
              <td align="center" width="'.$widthQty.'%">'.$qty.'</td>
              <td align="center" width="'.$widthSubTotal.'%">'.$itemTotal.'</td>
            </tr>';
                    $y += 33.5;
                    ++$itemCount;
                }
                // $itemCount++;
            }

            $content .= '</tbody></table>';

            $pdf->writeHTML($content);

            // $pdf->SetFont('helvetica', 'I', 6);
            // $pdf->writeHTML('Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), true, false, false, false, 'R');

            // $pdf->SetY(-30);
            // // Set font
            // $pdf->SetFont('helvetica', 'I', 6);
            // // Page number
            // $pdf->Cell(0, 0, $pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(),
            //            0, false, 'C', 0, '', 0, false, 'T', 'M');

            $rest_of_items = array_slice($orderItems, 5, count($orderItems));
            $rest_of_pages = (int) ceil(count($rest_of_items) / 6);
            $rest_of_page_items = array_chunk($rest_of_items, 6);
            foreach ($rest_of_page_items as $k => $value) {
                $rest_of_page_items_count[$k] = count($rest_of_page_items[$k]);
            }

            $k = 0;
            if (!empty($rest_of_items)) {
                // Rest of items
                for ($i = 0; $i < $rest_of_pages; ++$i) {
                    // // remove default header/footer
                    $pdf->setPrintHeader(false);
                    // remove default footer
                    $pdf->setPrintFooter(true);

                    // $pdf->SetHeaderMargin(0);
                    // $pdf->SetFooterMargin(0);
                    // set margins
                    // $pdf->SetMargins(10, 10, 10);
                    $pdf->setFooterData([162, 159, 159], [205, 205, 205]);

                    // set header and footer fonts
                    $pdf->setFooterFont(['helvetica', 'M', 6]);

                    // set default monospaced font
                    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                    // set margins
                    $pdf->SetFooterMargin(15);

                    $pdf->AddPage('P', 'A4');

                    // Top left bar
                    $pdf->Image(
                        $logo_file,
                        188,
                        10,
                        15,
                        8,
                        '',
                        '',
                        '',
                        false,
                        300,
                        '',
                        false,
                        false,
                        0,
                        false,
                        false,
                        false
                    );
                    $pdf->Write(0, "\n \n \n \n \n \n \n \n ", '', 0, 'C', true, 0, false, false, 0);
                    // Table header
                    $content =
                    ' <h3 align="center">Invoice</h3>
              <br><br>
              <table border="0.5" cellspacing="0" cellpadding="5">
                <thead>
                 <tr align="center" style="background-color: rgb(201, 201, 201); color: rgb(0,0,0);">
                  <th width="'.$widthItem.'%"><b>Item</b></th>
                  <th width="'.$widthSKU.'%"><b>SKU</b></th>
                  <th width="'.$widthThumbnail.'%"><b>Thumbnail</b></th>
                  <th width="'.$widthName.'%"><b>Name</b></th>
                  <th width="'.$widthBrand.'%"><b>Brand</b></th>
                  <th width="'.$widthCategory.'%"><b>Category</b></th>
                  <th width="'.$widthType.'%"><b>Type</b></th>
                  <th width="'.$widthSize.'%"><b>Size</b></th>
                  <th width="'.$widthColor.'%"><b>Color</b></th>
                  <th width="'.$widthPrice.'%"><b>Price (Rs)</b></th>
                  <th width="'.$widthQty.'%"><b>Qty</b></th>
                  <th width="'.$widthSubTotal.'%"><b>Sub Total (Rs)</b></th>
                 </tr>
                </thead>
                <tbody>';

                    $pdf->SetFont('helvetica', '', 5, '', true);
                    if (!empty($rest_of_page_items[$i])) {
                        $y = 43;
                        foreach ($rest_of_page_items[$i] as $j => $item) {
                            ++$k;
                            $image = $current_dir_path.$item['imageMedium'];
                            $sku = $item['sku'];
                            $name = $item['name'];
                            $brand = $item['brand_full'];
                            $category = $item['category_full'];
                            $type = $item['type_full'];
                            $size = $item['size'];
                            $sellingPrice = number_format($item['sellingPrice'], 2);
                            $qty = $item['quantity'];
                            $itemTotal = number_format($item['subtotal'], 2);
                            $color = $item['colors_full'];

                            $x = 30.7;
                            $w = 40;
                            // $h = 'auto'; $type = ''; $link = ''; $align = 'center';
                            // $resize = false; $dpi = 300; $palign = ''; $ismask = false; $imgmask = false;
                            // $border = 0; $fitbox = false; $hidden = false; $fitonpage = false;

                            // Table body
                            $content .=
                            '<tr>
                  <td align="center" width="'.$widthItem.'%"><b>'.$k.'</b></td>
                  <td width="'.$widthSKU.'%">'.$sku.'</td>
                  <td style="background-color: rgb(255, 255, 255);color: white;z-index:-999" width="'.$widthThumbnail.'%" height="95px">'.

                              $pdf->Image($image, $x, $y, '', 30, '', '', '', false, 300, 'center', false, false, 0, true, false, false)

                              .'</td>
                  <td align="center" width="'.$widthName.'%">'.$name.'</td>
                  <td align="center" width="'.$widthBrand.'%">'.$brand.'</td>
                  <td align="center" width="'.$widthCategory.'%">'.$category.'</td>
                  <td align="center" width="'.$widthType.'%">'.$type.'</td>
                  <td align="center" width="'.$widthSize.'%">'.$size.'</td>
                  <td align="center" width="'.$widthColor.'%">'.$color.'</td>
                  <td align="center" width="'.$widthPrice.'%">'.$sellingPrice.'</td>
                  <td align="center" width="'.$widthQty.'%">'.$qty.'</td>
                  <td align="center" width="'.$widthSubTotal.'%">'.$itemTotal.'</td>
                </tr>';
                            $y += 33.5;
                            ++$itemCount;

                            // /* Add the total at the very bottom of the table  */
                            if (($k - 1) === array_key_last($rest_of_items)) {
                                $content .=
                                '<tr>
                    <td align="right" colspan="11"><b>No. of items</b></td>
                    <td align="center">'.$totalItems.' pcs</td>
                   </tr>
                   <tr>
                    <td align="right" colspan="11"><b>Total</b></td>
                    <td align="center" style="background-color: rgb(201, 201, 201); color: rgb(0,0,0);font-weight:bold">Rs '.$invoiceTotal.'</td>
                   </tr>';
                            }
                        }
                    }

                    $content .= '</tbody></table>';

                    $pdf->writeHTML($content);

                    // // Bottom comments
                    if (($k - 1) == array_key_last($rest_of_items)) {
                        $pdf->SetFont('', '', 7);
                        // Bottom left
                        $pdf->writeHTML('<b>Thank you for your purchase.</b>');
                        $pdf->Write(0, "\n\n", '', 0, 'C', true, 0, false, false, 0);
                        $pdf->writeHTML('Payment method: <b>'.$card.'</b>');
                        $pdf->writeHTML('Card account: <i>'.$maskedCard.'</i>');
                        $pdf->writeHTML('Card holder: <i>'.$invoice['cardHolder'].'</i>');

                        $pdf->Write(0, "\n\n\n", '', 0, 'C', true, 0, false, false, 0);

                        // Bottom center
                        $pdf->writeHTML($bottom_1, true, false, false, false, 'C');
                        $pdf->writeHTML($bottom_2, true, false, false, false, 'C');
                    }

                    // $pdf->SetY(272);
                    // // Set font
                    // $pdf->SetFont('helvetica', 'I', 6);
                    // // Page number
                    // $pdf->Cell(0, 0, $pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(),
                    //            0, false, 'C', 0, '', 0, false, 'T', 'M');
                }
            } else {
                // // remove default header/footer
                $pdf->setPrintHeader(false);
                // remove default footer
                $pdf->setPrintFooter(true);

                // $pdf->SetHeaderMargin(0);
                // $pdf->SetFooterMargin(0);
                // set margins
                // $pdf->SetMargins(10, 10, 10);
                $pdf->setFooterData([162, 159, 159], [205, 205, 205]);

                // set header and footer fonts
                $pdf->setFooterFont(['helvetica', 'M', 6]);

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetFooterMargin(15);

                $pdf->AddPage('P', 'A4');

                // Top left bar
                $pdf->Image(
                    $logo_file,
                    188,
                    10,
                    15,
                    8,
                    '',
                    '',
                    '',
                    false,
                    300,
                    '',
                    false,
                    false,
                    0,
                    false,
                    false,
                    false
                );
                $pdf->Write(0, "\n \n \n \n \n \n \n \n ", '', 0, 'C', true, 0, false, false, 0);
                // Table header
                $widthSubTotal = 12;
                $content =
                ' <h3 align="center">Invoice</h3>
            <br><br>
            <table border="0.5" cellspacing="0" cellpadding="5">
              <thead>
               <tr align="center" style="background-color: rgb(201, 201, 201); color: rgb(0,0,0);">
                <th width="'.$widthItem.'%"><b>Item</b></th>
                <th width="'.$widthSKU.'%"><b>SKU</b></th>
                <th width="'.$widthThumbnail.'%"><b>Thumbnail</b></th>
                <th width="'.$widthName.'%"><b>Name</b></th>
                <th width="'.$widthBrand.'%"><b>Brand</b></th>
                <th width="'.$widthCategory.'%"><b>Category</b></th>
                <th width="'.$widthType.'%"><b>Type</b></th>
                <th width="'.$widthSize.'%"><b>Size</b></th>
                <th width="'.$widthColor.'%"><b>Color</b></th>
                <th width="'.$widthPrice.'%"><b>Price (Rs)</b></th>
                <th width="'.$widthQty.'%"><b>Qty</b></th>
                <th width="'.$widthSubTotal.'%"><b>Sub Total (Rs)</b></th>
               </tr>
              </thead>
              <tbody>';

                $pdf->SetFont('helvetica', '', 5, '', true);
                $image = $current_dir_path.$item['imageMedium'];
                $sku = $item['sku'];
                $name = $item['name'];
                $brand = $item['brand_full'];
                $category = $item['category_full'];
                $type = $item['type_full'];
                $size = $item['size'];
                $sellingPrice = number_format($item['sellingPrice'], 2);
                $qty = $item['quantity'];
                $itemTotal = number_format($item['subtotal'], 2);
                $color = $item['colors_full'];

                // /* Add the total at the very bottom of the table  */
                $content .=
                  '<tr>
              <td align="right" colspan="11"><b>No. of items</b></td>
              <td align="center">'.$totalItems.' pcs</td>
             </tr>
             <tr>
              <td align="right" colspan="11"><b>Total</b></td>
              <td align="center" style="background-color: rgb(201, 201, 201); color: rgb(0,0,0);font-weight:bold">Rs '.$invoiceTotal.'</td>
             </tr>';

                $content .= '</tbody></table>';
                $pdf->writeHTML($content);

                // // Bottom comments
                $pdf->SetFont('', '', 7);
                // Bottom left
                $pdf->writeHTML('<b>Thank you for your purchase.</b>');
                $pdf->Write(0, "\n\n", '', 0, 'C', true, 0, false, false, 0);
                $pdf->writeHTML('Payment method: <b>'.$card.'</b>');
                $pdf->writeHTML('Card account: <i>'.$maskedCard.'</i>');
                $pdf->writeHTML('Card holder: <i>'.$invoice['cardHolder'].'</i>');

                $pdf->Write(0, "\n\n\n", '', 0, 'C', true, 0, false, false, 0);

                // Bottom center
                $pdf->writeHTML($bottom_1, true, false, false, false, 'C');
                $pdf->writeHTML($bottom_2, true, false, false, false, 'C');
            }
        }

        /*
          F: Save a copy
          I: Display in browser
          D: Download a copy
          FD: Both save and download a copy

          > Saves a copy to server(user account) & offers user to download a copy.

          NOTE:
            If the browser setting for PDF viewing is set to display,
            it will display only and the pdf should be downloaded directly.
        */
        $pdf->Output($pdfPath.$safeFilename.'.pdf', 'F');
    }

    /**
     * Mask the last characters of the Card number by replacing them with 'X'.
     *
     * Example:
     * Cart account: 378282246310005
     * Masked Card account: XXXX-XXXX-XXXX-0005
     */
    public function maskCard(
        $cardNumber,
        $maskFrom = 0,
        $maskTo = 4,
        $maskChar = 'X',
        $maskSpacer = '-',
    ) {
        // Clean out extra data that might be in the cc
        $cardNumber = str_replace(['-', ' '], '', $cardNumber);
        // Get the CC Length
        $cc_length = strlen($cardNumber);
        // Initialize the new credit card to contain the last four digits
        $maskedCard = substr($cardNumber, -4);
        // Walk backwards through the credit card number and add a dash after every fourth digit
        for ($i = $cc_length - 5; $i >= 0; --$i) {
            // If on the fourth character add a dash
            if ((($i + 1) - $cc_length) % 4 == 0) {
                $maskedCard = '-'.$maskedCard;
            }

            // Add the current character to the new credit card
            $maskedCard = $cardNumber[$i].$maskedCard;
        }

        // Clean out
        $cardNumber = str_replace(['-', ' '], '', $cardNumber);
        $ccLength = strlen($cardNumber);
        // Mask CC number
        if (empty($maskFrom) && $maskTo == $ccLength) {
            $cardNumber = str_repeat($maskChar, $ccLength);
        } else {
            $cardNumber = substr($cardNumber, 0, $maskFrom).str_repeat($maskChar, $ccLength - $maskFrom - $maskTo).substr($cardNumber, -1 * $maskTo);
        }
        // Format
        if ($ccLength > 4) {
            $maskedCard = substr($cardNumber, -4);
            for ($i = $ccLength - 5; $i >= 0; --$i) {
                // If on the fourth character add the mask char
                if ((($i + 1) - $ccLength) % 4 == 0) {
                    $maskedCard = $maskSpacer.$maskedCard;
                }
                // Add the current character to the new credit card
                $maskedCard = $cardNumber[$i].$maskedCard;
            }
        } else {
            $maskedCard = $cardNumber;
        }

        return $maskedCard;
    }

    /* path: /invoice/{id}/download */
    #[ParamDecryptor(['invoice'])]
    public function invoicePdfDownload(PDF $pdf, Order $invoice, ORM $em, OrderRepo $orderRepo)
    {
        $user = $invoice->getUsers();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();

        $uuid = $user->getUuid();
        $invoiceId = $invoice->getId();
        $safeFilename =
          'invoice-ref-'.
          str_pad($invoiceId, 6, '0', STR_PAD_LEFT).'-'.
          preg_replace('/\s+/', '_', trim($firstName)).
          preg_replace('/\s+/', '_', trim($lastName)).'-'.
          date('d_m_Y', time());

        $filesystem = new Filesystem();
        $current_dir_path = getcwd();
        $thumbUniqueId = bin2hex(random_bytes(4));

        // new folder
        $pdfPath = "$current_dir_path/uploads/users/$uuid/invoices/";

        $pdfURL = "uploads/users/$uuid/invoices/$safeFilename.pdf";

        $pdfFile = $pdfPath.$safeFilename.'.pdf';

        $thumbnailPath = "$current_dir_path/uploads/users/$uuid/thumbnails/";

        $thumbnailURL = "uploads/users/$uuid/thumbnails/$thumbUniqueId.jpg";

        /* Make a new directory
            1) For PDF
            2) For thumbnail
        */
        try {
            if (!$filesystem->exists($pdfPath)) {
                $old = umask(0);
                $filesystem->mkdir($pdfPath, 0755);
                $filesystem->chown($pdfPath, 'www-data');
                $filesystem->chgrp($pdfPath, 'www-data');
                umask($old);
            }

            if (!$filesystem->exists($thumbnailPath)) {
                $old = umask(0);
                $filesystem->mkdir($thumbnailPath, 0755);
                $filesystem->chown($thumbnailPath, 'www-data');
                $filesystem->chgrp($thumbnailPath, 'www-data');
                umask($old);
            }
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        $billing = !empty($invoice->getBilling())
                          ? $invoice->getBilling()
                          : new Billing();

        $billing->setInvoicePath($pdfURL)
                ->setInvoiceThumbnail($thumbnailURL);
        $invoice->setBilling($billing);

        $em->persist($invoice);
        $em->flush();

        // Generate pdf
        $this->invoicePdfGenerator($pdf, $invoice, $safeFilename, $pdfPath);

        try {
            $thumbImage = new \Imagick();
            $thumbImage->readimage($pdfFile.'[0]');
            $thumbImage->thumbnailImage(225, 320, true, true);
            $thumbImage->setImageCompressionQuality(90);
            $thumbImage->stripImage();
            $thumbImage->setImageColorspace(255);
            $thumbImage->setImageFormat('jpeg');
            $thumbImage->writeImages($thumbnailURL, false);
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'.$exception->getPath();
        }

        return $this->redirectToRoute('user_profile_invoices', [], Response::HTTP_SEE_OTHER);
    }

    /* path: /profile/invoices */
    public function invoicePdfView(Request $request, OrderRepo $orderRepo, PaginatorInterface $paginator, RequestStack $requestStack)
    {
        // handling session
        $session = $requestStack->getSession();
        // Retrieve cart items and cart product images
        $this->cart($this->getUser(), null);

        // Initialize Invoices variable
        $invoices = [];

        // Check if user has logged
        if (!empty($this->getUser())) {
            // Check for existing order with status = Order::STATUS_PAID
            $paidOrders = $orderRepo->findBy(
                ['users' => $this->getUser(),
                    'status' => Order::STATUS_PAID],
                ['created' => 'DESC']
            );

            if (!empty($paidOrders)) {
                foreach ($paidOrders as $i => $paidOrder) {
                    if (!empty($paidOrder->getBilling())) {
                        $invoices[$i] = $paidOrder->getBilling();
                    }
                }

                $page = $request->query->getInt('page', 1);
                $invoices = $paginator->paginate($invoices, $page, 6);
            }
        }
        // dd($invoices);

        return $this->render('profile/index.html.twig', [
            'cart' => $this->cart,
            'invoices' => $invoices,
        ]);
    }
}
