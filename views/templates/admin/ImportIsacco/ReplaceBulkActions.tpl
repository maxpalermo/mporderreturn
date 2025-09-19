{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Massimiliano Palermo <maxx.palermo@gmail.com>
*  @copyright Since 2016 Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
    var totalBoxes = 0;
    var processedBoxes = 0;
    var stop_ajax_import_products = false;

    function getBoxes()
    {
        var boxes = $('input[name="mp_massimport_isaccoBox[]"]:checked');
        var ids = [];
        $.each(boxes, function(){
            ids.push(this.value);
        });
        return ids;
    }

    function startBulkImport() {
        $('.container.alerts').remove();
        $('.modalProgress-body').html("<h3>Attendere la fine delle operazioni</h3>");
        $('#modalProgress').modal('show');
        $('.modalProgress-spinner').fadeIn();
        $(".progress-import-isacco").attr('aria-valuenow', 0).css('width', 0);
    }

    function progressBulkImport(html) {
        $('.modalProgress-body').html(html);
    }

    function endBulkImport(html) {
        progressBulkImport(html);
        $('.modalProgress-spinner').fadeOut();
        alert("Processo di importazione terminato");
    }

    function setBulkActionImport() {
        boxes = getBoxes();
        if (boxes.length) {
            if (confirm("{l s='Importare i prodotti selezionati?' mod='mpmassimport'}") == false) {
                return false;
            }
            startBulkImport();
            totalBoxes = boxes.length;
            ajaxProcessBulkImportProducts(boxes);
        } else {
            alert("Seleziona prima i prodotti da importare.");
        }
    }

    function ajaxProcessBulkImportProducts(boxes)
    {
        if (boxes.length == 0 || stop_ajax_import_products == true) {
            endBulkImport();
            return true;
        }
        var data = {
            boxes: boxes,
            ajax: true,
            action: 'bulkImport'
        };
        $.post( "{$admin_controller}", data, function(response) {
            if (response.boxes.length == 0) {
                endBulkImport(response.html);
            } else {
                progressBulkImport(response.html);
                ajaxProcessBulkImportProducts(response.boxes);
            }
        });
    }

    function setBulkActionImportAll() {
        if (confirm("{l s='Importare tutti i prodotti?' mod='mpmassimport'}") == false) {
                return false;
            }
        var data = {
            ajax: true,
            action: 'bulkImportAll'
        };
        $.post( "{$admin_controller}", data, function(response) {
        setBulkActionImport(response.boxes);
    });
    }

    $(function() {
        $('.bulk-actions ul.dropdown-menu li:nth-child(4) a').attr('onclick', '').attr('href',
            'javascript:stop_ajax_import_products=false;setBulkActionImport([]);');
        $('.bulk-actions ul.dropdown-menu li:nth-child(5) a').attr('onclick', '').attr('href',
            'javascript:stop_ajax_import_products=false;setBulkActionImportAll();');
    });
</script>