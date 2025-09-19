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

<style>
    .mr-1 {
        margin-right: .25rem;
    }

    .mr-2 {
        margin-right: .50rem;
    }

    .mr-3 {
        margin-right: .75rem;
    }

    .mr-4 {
        margin-right: 1.00rem;
    }
</style>
<div style="margin-left: 2rem;">
    <div class="panel-heading">
        <h3 class="modal-title">{l s='Progresso' mod='mpmassimport'}</h3>
    </div>
    <div class="panel-body">
        <div class="col-md-8">
            <div class="progress" style="height: 32px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated progress-import-isacco"
                    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    <span>{l s='Pagine' mod='mpmassimport'}: </span>&nbsp;<strong><span
                            class="processed-pages">0</span></strong>
                </div>
                <div class="col-md-3">
                    <span>{l s='Prodotti' mod='mpmassimport'}: <strong></span>&nbsp;<span
                        class="processed-products">0</span></strong>
                </div>
                <div class="col-md-3">
                    <span>{l s='Trovati' mod='mpmassimport'}: <strong></span>&nbsp;<span
                        class="processed-founds">0</span></strong>
                </div>
                <div class="col-md-3">
                    <span>{l s='Errori' mod='mpmassimport'}: <strong></span>&nbsp;<span
                        class="processed-errors">0</span></strong>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-danger pull-right mr-2 reset-import"
                onclick="javascript:resetTableIsacco();">
                <span class="process-icon-trash"></span>
                {l s='Azzera' mod='mpmassimport'}
            </button>
            <button type="button" class="btn btn-warning pull-right mr-2 stop-import"
                onclick="javascript:stopImportIsacco();">
                <span class="process-icon-database"></span>
                {l s='Stop Importazione' mod='mpmassimport'}
            </button>
            <button type="button" class="btn btn-primary pull-right mr-2 start-import"
                onclick="javascript:startImportIsacco();">
                <span class="process-icon-database"></span>
                {l s='Inizia Importazione' mod='mpmassimport'}
            </button>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-light table-info">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>{l s='Riferimento' mod='mpmassimport'}</th>
                    <th>{l s='Nome' mod='mpmassimport'}</th>
                    <th>{l s='Prz Acq' mod='mpmassimport'}</th>
                    <th>{l s='Prz Ven' mod='mpmassimport'}</th>
                    <th>{l s='Errori' mod='mpmassimport'}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    var stop_import_isacco = false;

    function stopImportIsacco() {
        stop_import_isacco = true;
    }

    function updateProgress(response) {
        var tbody = $('.table-info tbody');
        var progress = $('.progress-import-isacco');
        var value = Number($(progress).attr('aria-valuenow'));
        value += 20;
        if (value <= 100) {
            $('.progress-import-isacco').css('width', (value + '%'));
            $('.progress-import-isacco').attr('aria-valuenow', value);
        } else {
            $('.progress-import-isacco').css('width', 0);
            $('.progress-import-isacco').attr('aria-valuenow', 0);
        }

        let products = Number($('.processed-products').text()) + response.processed;
        let pages = Number($('.processed-pages').text()) + 1;
        let founds = Number($('.processed-founds').text()) + response.founds;
        let errors = Number($('.processed-errors').text()) + response.errors.length;
        $('.processed-products').text(products);
        $('.processed-pages').text(pages);
        $('.processed-founds').text(founds);
        $('.processed-errors').text(errors);

        let rows = Number($(tbody).find('tr').length);
        $.each(response.errors, function() {
            $(tbody).append(
                $('<tr>')
                .append($('<td>').text(rows + 1))
                .append($('<td>').text(this.reference))
                .append($('<td>').text(this.name))
                .append($('<td>').addClass("text-right").text(this.wholesale_price))
                .append($('<td>').addClass("text-right").text(this.price))
                .append($('<td>').html('<span class="text-danger">' + this.error + '</span>'))
            );
        });

        $.each(response.products, function() {
            let rows = $(tbody).find('tr').length;
            $(tbody).append(
                $('<tr>')
                .append($('<td>').text(rows + 1))
                .append($('<td>').text(this.reference))
                .append($('<td>').text(this.name))
                .append($('<td>').addClass("text-right").text(this.wholesale_price))
                .append($('<td>').addClass("text-right").text(this.price))
                .append($('<td>').html('<i class="icon-check text-success"></i>'))
            );
        });
    }

    function endProgress() {
        var progress = $('.progress-import-isacco');
        $('.progress-import-isacco').css('width', '100%');
        $('.progress-import-isacco').attr('aria-valuenow', 100);
        $('.start-import span').removeClass('process-icon-loading').addClass('process-icon-database');
    }

    function resetTableIsacco()
    {
        var data = {
            reset: true,
        };
        $.post( "{$ajax_controller}", data, function(response) {
                alert("{l s='Reset done' mod='mpmassimport'}");
                window.location.reload();
            });
    }

    function startImportIsacco(page = 0) {
        var tbody = $('.table-info tbody');

        if (page == 0) {
            $(tbody).html("");
            stop_import_isacco = false;
            $('.start-import span').addClass('process-icon-loading').removeClass('process-icon-database');
        }
        if (stop_import_isacco) {
            $('.start-import span').removeClass('process-icon-loading').addClass('process-icon-database');
            alert("{l s='Operation Aborted.' mod='mpmassimport'}");
            return false;
        }

        if ($('#fileUpload').val()) {
            var formData = new FormData();
            var uploadFiles = document.getElementById('fileUpload').files;
            formData.append("fileUpload", uploadFiles[0]);
            formData.append("switch", $('input[name="MPMASSIMPORT_IMPORT_ISACCO_NEW"]:checked').val());

            $('.start-import span').addClass('process-icon-loading').removeClass('process-icon-database');

            $.ajax({
                type: "POST",
                url: "{$ajax_controller}",
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.start-import span').removeClass('process-icon-loading').addClass('process-icon-database');
                    alert("{l s='Operation done' mod='mpmassimport'}");
                    return true;
                },
                error: function(response) {
                    $('.start-import span').removeClass('process-icon-loading').addClass('process-icon-database');
                    alert("{l s='Operation aborted with errors' mod='mpmassimport'}");
                    return false;
                }
            });
        } else if ($('#fileUploadEan13').val()) {
            var formData = new FormData();
            var uploadFiles = document.getElementById('fileUploadEan13').files;
            formData.append("fileUploadEan13", uploadFiles[0]);
            formData.append("switch", $('input[name="MPMASSIMPORT_IMPORT_ISACCO_NEW"]:checked').val());

            $('.start-import span').addClass('process-icon-loading').removeClass('process-icon-database');

            $.ajax({
                type: "POST",
                url: "{$ajax_controller}",
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.start-import span').removeClass('process-icon-loading').addClass('process-icon-database');
                    alert("{l s='Operation done' mod='mpmassimport'}");
                    var alerts = response.alerts;
                    $('.container.alerts').remove();
                    $('#mp_massimport_isacco_form').before(alerts);
                    return true;
                },
                error: function(response) {
                    $('.start-import span').removeClass('process-icon-loading').addClass('process-icon-database');
                    alert("{l s='Operation aborted with errors' mod='mpmassimport'}");
                    return false;
                }
            });
        } else {
            var data = {
                page: page,
                switch: $('input[name="MPMASSIMPORT_IMPORT_ISACCO_NEW"]:checked').val()
            };

            $.post( "{$ajax_controller}", data, function(response) {
                if (response !== false) {
                    updateProgress(response);
                    page++;
                    startImportIsacco(page);
                } else {
                    endProgress();
                    alert("{l s='Import done' mod='mpmassimport'}");
                }
            });
        }
    }
    $(document).ready(function() {
        $('.start-import').closest('div.form-group').find('label').remove();
        $('.start-import').closest('div.form-group').find('.col-lg-9').removeClass('col-lg-9').addClass(
            'col-lg-12');
    });
</script>