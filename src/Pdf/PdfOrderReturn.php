<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpOrderReturn\Pdf;

require_once _PS_MODULE_DIR_ . 'mporderreturn/vendor/autoload.php';

class PdfOrderReturn extends \TCPDF
{
    /**
     * @var \OrderReturn
     */
    protected $orderReturn;

    /**
     * @var \array
     */
    protected $shopAddress;

    /**
     * @var string
     */
    protected $shopName;

    /**
     * @var string
     */
    protected $shopLogo;

    /**
     * @var array
     */
    protected $colors;

    /**
     * @var int
     */
    protected $id_lang;

    /**
     * @var int
     */
    protected $id_shop;

    /**
     * @var array
     */
    protected $formattedAddressDelivery;

    /**
     * Constructor
     */
    public function __construct($orderReturn)
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8');

        $this->orderReturn = $orderReturn;
        $this->shopName = \Configuration::get('PS_SHOP_NAME');
        $this->shopLogo = _PS_IMG_DIR_ . \Configuration::get('PS_LOGO');

        $this->id_lang = (int) \Context::getContext()->language->id;
        $this->idShop = (int) \Context::getContext()->shop->id;

        // Get shop address
        $this->shopAddress = [
            'logo' => $this->shopLogo,
            'name' => $this->shopName,
            'address1' => \Configuration::get('PS_SHOP_ADDR1'),
            'address2' => \Configuration::get('PS_SHOP_ADDR2'),
            'city' => \Configuration::get('PS_SHOP_CITY'),
            'postcode' => \Configuration::get('PS_SHOP_CODE'),
            'phone' => \Configuration::get('PS_SHOP_PHONE'),
            'phone_mobile' => \Configuration::get('PS_SHOP_PHONE_MOBILE'),
            'country' => \Configuration::get('PS_SHOP_COUNTRY'),
            'state' => \Configuration::get('PS_SHOP_STATE'),
        ];

        // Definisci i colori
        $this->colors = [
            'black' => [0, 0, 0],
            'red' => [201, 33, 30],
            'grey' => [128, 128, 128],
            'light_grey' => [240, 240, 240]
        ];

        // Set document information
        $this->SetCreator('Massimiliano Palermo');
        $this->SetAuthor('Massimiliano Palermo');
        $this->SetTitle('MODULO DI RESO - ' . $this->orderReturn->id);
        $this->SetSubject('Modulo di reso per ordine #' . $this->orderReturn->id_order);

        // Set margins
        $this->SetMargins(15, 40, 15);
        $this->SetHeaderMargin(10);
        $this->SetFooterMargin(10);

        // Set auto page breaks
        $this->SetAutoPageBreak(true, 25);

        // Set default font
        $this->SetFont('helvetica', '', 10);

        // Rimuovi intestazione e piè di pagina predefiniti
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        // Add a page
        $this->AddPage();
    }

    /**
     * Crea l'intestazione del documento
     */
    protected function createHeader()
    {
        // Logo
        if (file_exists($this->shopLogo)) {
            $this->Image($this->shopLogo, 15, 10, 50);
        }

        // Titolo del documento
        $this->setFontSize(16);
        $this->setColor('text', 0x30, 0x30, 0x30);
        $this->setX(110);
        $this->setY(10);
        $this->MultiCell(0, 10, 'MODULO RICHIESTA DI RESO', 0, 'L', 0, 1, 110, 10, 1);

        // Shop information
        $this->setFontSize(12);
        $this->setColor('text', 0x30, 0x30, 0x30);
        $this->setX(110);
        $this->Cell(0, 6, $this->shopName, 0, 1, 'L');

        /*
         * // Shop address generic
         * $this->setFontSize(9);
         * $this->setColor('text', 0x30, 0x30, 0x30);
         * $this->setX(110);
         * $this->Cell(0, 4, $this->shopAddress['address1'], 0, 1, 'L');
         * $this->SetX(110);
         * $this->Cell(0, 4, $this->shopAddress['address2'], 0, 1, 'L');
         * $this->SetX(110);
         * $this->Cell(0, 4, $this->shopAddress['postcode'] . ' ' . $this->shopAddress['city'], 0, 1, 'L');
         * $this->SetX(110);
         * $this->Cell(0, 4, $this->shopAddress['country'], 0, 1, 'L');
         */

        // Shop Address imprendo
        $this->setFontSize(9);
        $this->setColor('text', 0x30, 0x30, 0x30);
        $this->SetX(110);
        $this->Cell(0, 4, 'Via Mafalda di Savoia, 28/30', 0, 1, 'L');
        $this->SetX(110);
        $this->Cell(0, 4, '87013 - Fagnano Castello (CS)', 0, 1, 'L');
        $this->SetX(110);
        $this->Cell(0, 4, 'Partita IVA:  IT03412990784', 0, 1, 'L');

        // Line
        $this->setDrawColor(0xD0, 0xD0, 0xD0);
        $this->Line(15, $this->getY() + 2, 195, $this->getY() + 2);

        $this->setY($this->getY() + 2);
    }

    /**
     * Footer
     */
    protected function createFooter()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        // Line
        $this->SetDrawColorArray($this->colors['grey']);
        $this->Line(15, $this->GetY(), 195, $this->GetY());

        // Date
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColorArray($this->colors['black']);
        $this->Cell(100, 10, 'Data: ' . date('d/m/Y'), 0, 0, 'L');

        // Page number
        $this->Cell(80, 10, 'Pagina ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    public function createCustomerAddress()
    {
        $id_lang = (int) \Context::getContext()->language->id;
        $id_order = $this->orderReturn->id_order;
        $order = new \Order($id_order, $id_lang);
        $address_delivery = new \Address($order->id_address_delivery, $id_lang);
        $country_delivery = new \Country($address_delivery->id_country, $id_lang);
        $state_delivery = new \State($address_delivery->id_state, $id_lang);
        $address_invoice = new \Address($order->id_address_invoice, $id_lang);
        $country_invoice = new \Country($address_invoice->id_country, $id_lang);
        $state_invoice = new \State($address_invoice->id_state, $id_lang);

        $formattedAddressDelivery = [
            'name' => $address_delivery->company ?: $address_delivery->firstname . ' ' . $address_delivery->lastname,
            'address1' => $address_delivery->address1,
            'address2' => $address_delivery->address2,
            'postcode' => $address_delivery->postcode,
            'city' => $address_delivery->city,
            'country' => $country_delivery->name,
            'state' => $state_delivery->name
        ];
        $this->formattedAddressDelivery = $formattedAddressDelivery;

        $formattedAddressInvoice = [
            'name' => $address_invoice->company ?: $address_invoice->firstname . ' ' . $address_invoice->lastname,
            'address1' => $address_invoice->address1,
            'address2' => $address_invoice->address2,
            'postcode' => $address_invoice->postcode,
            'city' => $address_invoice->city,
            'country' => $country_invoice->name,
            'state' => $state_invoice->name
        ];

        // Creo due sezioni per indirizzo di consegna e indirizzo di fatturazione

        // Cella bordata per indirizzo di consegna occupa la prima metà della pagina
        $currentY = $this->getY();
        $this->SetX(20);
        $this->SetY($currentY);

        $this->setColor('text', 0x30, 0x30, 0x30);
        $this->setFontSize(12);
        $this->Cell(80, 15, 'INDIRIZZO DI CONSEGNA', 0, 1);
        $this->setFontSize(10);
        $this->Cell(80, 6, "{$formattedAddressDelivery['name']}", 0, 1);
        $this->Cell(80, 6, "{$formattedAddressDelivery['address1']}", 0, 1);
        $this->Cell(80, 6, "{$formattedAddressDelivery['postcode']} {$formattedAddressDelivery['city']} {$formattedAddressDelivery['state']}", 0, 1);
        $this->Cell(80, 6, "{$formattedAddressDelivery['country']}", 0, 1);

        // Cella bordata per indirizzo di fatturazione occupa la seconda metà della pagina
        $this->SetY($currentY);

        $this->setColor('text', 0x30, 0x30, 0x30);
        $this->setFontSize(12);
        $this->SetX(100);
        $this->Cell(80, 15, 'INDIRIZZO DI FATTURAZIONE', 0, 1);
        $this->setFontSize(10);
        $this->SetX(100);
        $this->Cell(80, 6, "{$formattedAddressInvoice['name']}", 0, 1);
        $this->SetX(100);
        $this->Cell(80, 6, "{$formattedAddressInvoice['address1']}", 0, 1);
        $this->SetX(100);
        $this->Cell(0, 6, "{$formattedAddressInvoice['postcode']} {$formattedAddressInvoice['city']} {$formattedAddressInvoice['state']}", 0, 1);
        $this->SetX(100);
        $this->Cell(80, 6, "{$formattedAddressInvoice['country']}", 0, 1);

        $this->Line(15, $this->GetY(), 195, $this->GetY());

        $disclaimer = $this->getDisclaimer();
        $this->setFont('helvetica', '', 10);
        $this->MultiCell(180, 40, $disclaimer, 0, 'J', false, 1, 15, $this->GetY() + 5, true, 0, true, false, 0, 'T', false);
    }

    /**
     * Generate the PDF content
     */
    public function generateContent()
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->setColor('text', 0x30, 0x30, 0x30);
        // Crea l'intestazione
        $this->createHeader();

        // Se è la prima pagina creo indirizzo cliente
        if ($this->getPage() == 1) {
            $this->createCustomerAddress();
            $this->renderReturnInfo();
            $this->AddPage();
        }

        // Pagina Elenco Prodotti
        $this->renderProducts();

        // Pagine Note
        $this->setX(15);
        $this->setY(30);
        $info = $this->getReturnOptions();
        $this->setFont('helvetica', '', 10);
        $this->MultiCell(180, 0, $info, 0, 'J', false, 1, 15, 20, true, 0, true, false, 0, 'T', false);

        $this->renderTicket();
    }

    /**
     * Generate and return the PDF
     */
    public function render()
    {
        $this->generateContent();
        return $this->Output('', 'S');
    }

    protected function getDisclaimer()
    {
        $disclaimer =
            '
            <p>Gentile Cliente,</p>
            <p>Abbiamo registrato la sua richiesta di reso.</p>
            <p>Le ricordiamo che la spedizione dei prodotti resi è a <strong>suo carico</strong> e deve avvenire <strong>entro e non oltre 14 giorni</strong> dalla ricezione del suo ordine.</p>
            <p>Le ricordiamo inoltre che i prodotti di seguito elencati saranno controllati all’arrivo nei nostri magazzini da un operatore per verificare <strong>il rispetto delle condizioni di reso</strong> riportate nell’ultima pagina di questo modulo e consultabili sul nostro sito.</p>
            <p>Una volta che il suo pacco arriverà nei nostri magazzini, riceverà tramite mail l’avanzamento degli stati del suo reso.</p>
            <p>Se il pacco sarà conforme alle condizioni di reso procederemo con l’opzione da lei indicata nella seguente tabella.</p>
        ';

        return $disclaimer;
    }

    protected function getReturnOptions()
    {
        $returnOptions =
            '
        <h3>Condizioni di reso</h3>
        <p><strong>Quando puoi rendere un prodotto?</strong></p>
        <p>Entro 14 giorni dalla ricezione del tuo ordine.</p>
        
        <p><strong>Perché puoi rendere un prodotto?</strong></p>
        <ul>
            <li>non sei soddisfatto dellavestibilità;</li>
            <li>la taglia non va bene;</li>
            <li>il colore non ti piace;</li>
            <li>il tessuto non ti soddisfa;</li>
            <li>ci hai ripensato.</li>
        </ul>
        
        <p><strong>I prodotti possono essere resi solo se:</strong></p>
        <ul>
            <li>non sono stati utilizzati, macchiati, lavati o danneggiati</li>
            <li>il cartellino identificativo è ancora attaccato</li>
            <li>restituiti nella loro confezione originale</li>
            <li>non sono stati personalizzati con stampe o ricami</li>
            <li>la richiesta di reso è stata convalidata dal venditore</li>
        </ul>
        
        <p><strong><i>Il cliente si prenderà cura e carico dei costi di spedizione per effettuare il reso.</i></strong></p>
        
        <h3>Ritaglia questa etichetta lungo le linee tratteggiate e collocala fuori dal pacco che spedirai. Puoi recarti presso le Poste o un corriere di tua fiducia.</h3>
        ';

        return $returnOptions;
    }

    protected function renderReturnInfo()
    {
        $id_lang = (int) \Context::getContext()->language->id;
        $id_order = $this->orderReturn->id_order;
        $order = new \Order($id_order, $id_lang);

        $this->setX(15);
        $this->setY($this->GetY() + 10);
        $this->SetFont('helvetica', 'B', 12);
        // Tabella Informazioni di reso
        $this->SetTextColor(0x30, 0x30, 0x30);
        $this->setFillColor(0xE0, 0xE0, 0xE0);
        $this->Cell(60, 7, 'Numero di Reso', 1, 0, 'C', true);
        $this->Cell(60, 7, 'Data di Richiesta', 1, 0, 'C', true);
        $this->Cell(60, 7, 'Riferimento Ordine', 1, 1, 'C', true);
        $this->setX(15);
        $this->setFillColor(0xF0, 0xF0, 0xF0);
        $this->Cell(60, 7, $this->orderReturn->id, 1, 0, 'C');
        $this->Cell(60, 7, date('d/m/Y', strtotime($this->orderReturn->date_add)), 1, 0, 'C');
        $this->Cell(60, 7, $order->reference, 1, 1, 'C');
        $this->setX(15);
        $this->setY($this->GetY() + 5);
        $this->setFillColor(0xE0, 0xE0, 0xE0);
        $this->Cell(0, 7, 'MOTIVAZIONE DEL RESO', 1, 1, 'C', true);
        $this->setX(15);
        $this->setFillColor(0xF0, 0xF0, 0xF0);
        $this->setFont('helvetica', '', 12);
        $this->MultiCell(180, 50, \Tools::ucfirst($this->orderReturn->question), 1, 'C', 0, 1, 15, $this->GetY(), 1, 0, 0, 1, 0, 'C', 1);
    }

    public function renderProducts()
    {
        $this->setX(15);
        $this->setY(20);
        $this->setFillColor(0xE0, 0xE0, 0xE0);

        // Get order return details
        $details = \OrderReturn::getOrdersReturnDetail($this->orderReturn->id);
        $this->renderTransaction($details);

        $this->AddPage();
    }

    protected function renderTransaction($details)
    {
        foreach ($details as $detail) {
            $order_detail = new \OrderDetail($detail['id_order_detail']);
            if (!\Validate::isLoadedObject($order_detail)) {
                continue;
            }
            $product = new \Product($order_detail->product_id, false, $this->id_lang);
            if (!\Validate::isLoadedObject($product)) {
                continue;
            }

            // Salva la posizione Y corrente
            $currentY = $this->GetY();

            // Inizia una transazione per verificare se tutto il contenuto sta nella pagina
            $this->startTransaction();

            $this->setFontSize(10);
            $this->renderCardProduct($product, $detail);

            $this->setY($this->GetY() + 10);
        }
    }

    protected function renderCardProduct($product, $detail)
    {
        // Intestazione Tabella
        $this->Cell(100, 10, 'Articolo da restituire', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Riferimento', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Quantità', 1, 1, 'C', true);
        // Dettagli Prodotto
        $this->MultiCell(100, 10, $product->name, 1, 'L', 0, 0, 15, $this->GetY(), 1, 0, 0, 1, 0, 'M', 1);
        $this->Cell(30, 10, $product->reference, 1, 0, 'C');
        $this->Cell(30, 10, $detail['product_quantity'], 1, 1, 'C');

        // Verifica se il contenuto è troppo grande per la pagina corrente
        // GetY() restituisce la posizione corrente dopo aver aggiunto il contenuto
        if ($this->GetY() + 40 > $this->getPageHeight() - 20) {  // 20 è un margine di sicurezza
            // Annulla la transazione perché il contenuto non sta nella pagina
            $this->rollbackTransaction(true);

            // Aggiungi una nuova pagina
            $this->AddPage();

            // Ripeti l'inserimento del contenuto nella nuova pagina
            $this->setY(20);
            $this->setFontSize(10);
            $this->renderCardProduct($product, $detail);
        } else {
            // Modulo di reso
            $this->MultiCell(160, 10, $this->getReturnCardOptions(), 1, 'L', 0, 1, 15, $this->GetY(), 1, 0, 1, 1, 0, 'T', 1);
            // Conferma la transazione perché il contenuto sta nella pagina
            $this->commitTransaction();
        }
    }

    protected function getReturnCardOptions()
    {
        $returnOptions =
            '
        <br />
        <h4>Cosa desidera in restituzione?</h4>
        
        <p style="margin-bottom: 5px; background-color: #FFFFFF;">
            <input type="checkbox" name="return_option" value="taglia_sostitutiva" style="width: 20px; height: 20px; margin-right: 10px;">
            <span style="margin-right: 10px;">Taglia Sostitutiva: _________________</span>
        </p>
        <p style="margin-bottom: 5px; background-color: #FFFFFF;">
            <input type="checkbox" name="return_option" value="buono" style="width: 20px; height: 20px; margin-right: 10px;">
            <span style="margin-right: 10px;">Buono</span>: 
        </p>
        <p style="margin-bottom: 5px; background-color: #FFFFFF;">
            <input type="checkbox" name="return_option" value="rimborso" style="width: 20px; height: 20px; margin-right: 10px;">
            <span style="margin-right: 10px;">Rimborso</span>: 
        </p>
        ';

        return $returnOptions;
    }

    public function renderTicket()
    {
        $this->setX(15);
        $this->setY($this->GetY() + 10);
        $this->setFillColor(0xE0, 0xE0, 0xE0);

        // Intestazione del ticket
        $this->Cell(180, 8, 'ETICHETTA DA APPLICARE AL PACCO', 1, 1, 'C', true);
        $this->setY($this->GetY() + 3);

        // Imposta lo stile della linea tratteggiata
        $this->SetLineStyle(array(
            'width' => 0.5,
            'dash' => 4,
            'gap' => 2,
            'color' => array(0, 0, 0)
        ));

        // Disegna il rettangolo tratteggiato per l'etichetta
        $startY = $this->GetY();
        $this->Rect(15, $startY, 180, 100, 'D');

        // Ripristina lo stile della linea
        $this->SetFont('helvetica', 'B', 12);
        $this->SetLineStyle(array('dash' => 0));

        // MITTENTE
        $customer = $this->formattedAddressDelivery;
        $this->setY($startY + 10);
        $this->setX(15);
        $this->Cell(90, 10, 'MITTENTE', 0, 1, 'C');
        $this->setY($startY + 35);
        $this->Cell(90, 10, strtoupper($customer['name']), 0, 1, 'C');
        $this->Cell(90, 10, strtoupper($customer['address1']), 0, 1, 'C');
        if ($customer['address2']) {
            $this->Cell(90, 10, strtoupper($customer['address2']), 0, 1, 'C');
        }
        $this->Cell(90, 10, strtoupper($customer['postcode'] . ' ' . $customer['city']), 0, 1, 'C');
        $this->Cell(90, 10, strtoupper($customer['state']), 0, 1, 'C');
        $this->Cell(90, 10, strtoupper($customer['country']), 0, 1, 'C');

        // Linea Verticale al centro del rettangolo
        $this->Line(105, $startY + 10, 105, $startY + 95);

        // DESTINATARIO
        $this->setY($startY + 10);
        $this->setX(105);
        $this->Cell(90, 10, 'DESTINATARIO', 0, 1, 'C');
        // Logo centrato
        if (file_exists($this->shopLogo)) {
            $availableWidth = 90;  // Larghezza disponibile nella colonna destra
            $displayWidth = 40;  // Larghezza desiderata del logo

            // Calcola la posizione X per centrare l'immagine nella colonna destra
            $x = 105 + (($availableWidth - $displayWidth) / 2);

            // Posiziona l'immagine centrata
            $this->Image($this->shopLogo, $x, $this->getY(), $displayWidth, 0);  // 0 per l'altezza mantiene le proporzioni
        }
        $this->setY($this->GetY() + 15);
        $this->setX(105);
        $this->cell(90, 10, 'IMPRENDO S.R.L.S.', 0, 1, 'C');
        $this->setX(105);
        $this->Cell(90, 10, 'VIA MAFALDA DI SAVOIA 28/30', 0, 1, 'C');
        $this->setX(105);
        $this->Cell(90, 10, '87013 - FAGNANO CASTELLO', 0, 1, 'C');
        $this->setX(105);
        $this->Cell(90, 10, 'COSENZA', 0, 1, 'C');
        $this->setX(105);
        $this->Cell(90, 10, 'ITALIA', 0, 1, 'C');

        /*
         * $shop = $this->shopAddress;
         * $this->setX(105);
         * $this->cell(90, 10, $shop['name'], 0, 1, 'R');
         * $this->setX(105);
         * $this->Cell(90, 10, $shop['address1'], 0, 1, 'R');
         * $this->setX(105);
         * $this->Cell(90, 10, $shop['address2'], 0, 1, 'R');
         * $this->setX(105);
         * $this->Cell(90, 10, $shop['postcode'] . ' ' . $shop['city'], 0, 1, 'R');
         * $this->setX(105);
         * $this->Cell(90, 10, $shop['country'], 0, 1, 'R');
         * $this->setX(105);
         * $this->Cell(90, 10, $shop['state'], 0, 1, 'R');
         */
    }
}
