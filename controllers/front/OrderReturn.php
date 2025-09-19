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

use MpSoft\MpOrderReturn\Pdf\PdfOrderReturn;

class MpOrderReturnOrderReturnModuleFrontController extends ModuleFrontController
{
    private $action;
    
    public function __construct()
    {
        parent::__construct();
        $this->ajax = (int)Tools::getValue('ajax');
        $this->action = "displayAjax" . Tools::ucfirst(Tools::getValue('action'));
        $id_employee = (int)Tools::getValue('id_employee');
        $employee = new Employee($id_employee);
        $this->context->employee = $employee;

        if (method_exists($this, $this->action)) {
            $this->{$this->action}();
            exit;
        }
    }
    
    public function initContent()
    {
        parent::initContent();
    }

    /**
     * Send JSON response for AJAX requests
     * 
     * @param mixed $data The data to send
     * @return void
     */
    public function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send PDF response
     * 
     * @param string $pdfContent The PDF content
     * @param string $filename The filename
     * @return void
     */
    public function pdfResponse($pdfContent, $filename)
    {
        if ($this->ajax) {
            // For AJAX requests, we return the PDF as base64 encoded string
            $this->jsonResponse([
                'success' => true,
                'pdf' => base64_encode($pdfContent),
                'filename' => $filename
            ]);
        } else {
            // For direct access, we output the PDF directly
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $pdfContent;
            exit;
        }
    }

    /**
     * Generate and return the PDF for an order return
     */
    public function displayAjaxPrintPdf()
    {
        $id_order_return = (int)Tools::getValue('id_order_return');
        
        if (!$id_order_return) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'ID reso non valido'
            ]);
            return;
        }
        
        $orderReturn = new OrderReturn($id_order_return);
        
        if (!Validate::isLoadedObject($orderReturn)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Reso non trovato'
            ]);
            return;
        }
        
        // Check if the customer is authorized to view this order return
        if (!isset($this->context->employee)) {
            if ($this->context->customer->id != $orderReturn->id_customer) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Accesso non autorizzato'
                ]);
                return;
            }
        }
        
        try {
            // Generate the PDF
            $pdf = new PdfOrderReturn($orderReturn);
            $pdfContent = $pdf->render();
            
            // Send the PDF response
            $this->pdfResponse(
                $pdfContent, 
                'modulo-reso-' . $orderReturn->id . '.pdf'
            );
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Errore nella generazione del PDF: ' . $e->getMessage()
            ]);
        }
    }
}