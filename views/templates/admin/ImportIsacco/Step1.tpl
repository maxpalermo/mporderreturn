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

<div class="panel panel-step-1">
    <div class="panel-heading">
        <i class="icon icon-hand-right"></i>
        <span>{l s="Step 1: Imprta da file Excel"}</span>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">
                <h3 class="title">{l s='Seleziona il file Excel' mod='mpmassimport'}</h3>
            </div>
            <div class="col-md-3">
                <div class="form-group pull-right">
                    <label>Solo i prodotti nuovi</label>
                    <div class="form-input">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="only_new_products" id="only_new_products_on" value="1" checked>
                            <label for="only_new_products_on"><i
                                    class="icon icon-2x icon-check text-success"></i></label>
                            <input type="radio" name="only_new_products" id="only_new_products_off" value="0">
                            <label for="only_new_products_off"><i
                                    class="icon icon-2x icon-times text-danger"></i></label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="col-sm-9">
                        <input id="fileProductUpload" type="file" name="fileProductUpload" class="hide">
                        <div class="dummyfile input-group">
                            <span class="input-group-addon"><i class="icon-file"></i></span>
                            <input id="fileProductUpload-name" type="text" name="filename" readonly="">
                            <span class="input-group-btn">
                                <button id="fileProductUpload-selectbutton" type="button" name="submitAddAttachments"
                                    class="btn btn-default">
                                    <i class="icon-folder-open"></i>
                                    <span>Aggiungi files</span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#fileProductUpload-selectbutton').click(function(e) {
                            $('#fileProductUpload').trigger('click');
                        });

                        $('#fileProductUpload-name').click(function(e) {
                            $('#fileProductUpload').trigger('click');
                        });

                        $('#fileProductUpload-name').on('dragenter', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                        });

                        $('#fileProductUpload-name').on('dragover', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                        });

                        $('#fileProductUpload-name').on('drop', function(e) {
                            e.preventDefault();
                            var files = e.originalEvent.dataTransfer.files;
                            $('#fileProductUpload')[0].files = files;
                            $(this).val(files[0].name);
                        });

                        $('#fileProductUpload').change(function(e) {
                            if ($(this)[0].files !== undefined) {
                                var files = $(this)[0].files;
                                var name = '';

                                $.each(files, function(index, value) {
                                    name += value.name + ', ';
                                });

                                $('#fileProductUpload-name').val(name.slice(0, -2));
                            } else // Internet Explorer 9 Compatibility
                            {
                                var name = $(this).val().split(/[\/]/);
                                $('#fileProductUpload-name').val(name[name.length - 1]);
                            }
                        });

                        if (typeof fileProductUpload_max_files !== 'undefined') {
                            $('#fileProductUpload').closest('form').on('submit', function(e) {
                                if ($('#fileProductUpload')[0].files.length >
                                    fileProductUpload_max_files) {
                                    e.preventDefault();
                                    alert('You can upload a maximum of  files');
                                }
                            });
                        }
                    });
                </script>
            </div>
            <div class="col-lg-6">
                <div class="progress" style="height: 32px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated progress-import-products"
                        style="width: 0; padding-top: 6px; font-size: 1.3rem; text-align: center;" role="progressbar"
                        aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-default pull-right submitImportProducts" type="button">
                    <i class="process-icon-download-alt"></i>
                    <span>{l s='Importa' mod='mpmassimport'}</span>
                </button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function() {
            $('.submitImportProducts').on('click', function() {
                if (confirm('Iniziare l\'importazione del foglio excel?') == false) {
                    return false;
                }

                /** START EXCEL IMPORT **/
                var formData = new FormData();
                var uploadFiles = document.getElementById('fileProductUpload').files;
                formData.append("fileUpload", uploadFiles[0]);
                formData.append("switch", $('input[name="only_new_products"]:checked').val());
                formData.append("ajax", true);
                formData.append("action", "importExcelProducts");

                $('.submitImportProducts i').addClass('process-icon-loading').removeClass(
                    'process-icon-download-alt');

                $.ajax({
                    type: "POST",
                    url: "{$ajax_controller}",
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    beforeSend: function(e) {
                        progressValue = 0;
                        intervalId = window.setInterval(setProgressBar(
                            '.progress-import-products', progressValue), 500);
                    },
                    success: function(response) {
                        window.clearInterval(intervalId);
                        doneProgressBar('.progress-import-products');
                        $('.submitImportProducts i')
                            .removeClass('process-icon-loading')
                            .addClass('process-icon-download-alt');
                        alert("{l s='Operatione eseguita' mod='mpmassimport'}");
                        $('#modal_results .modal-body').html(response.resultHtml);
                        $('#modal_results').modal({ backdrop: 'static', keyboard: false,
                            show: true });

                        return true;
                    },
                    error: function(response) {
                        $('.submitImportProducts i')
                            .removeClass('process-icon-loading')
                            .addClass('process-icon-download-alt');
                        alert("{l s='Operation interrotta da errori.' mod='mpmassimport'}");
                        return false;
                    }
                });
            });
        });
    </script>
</div>