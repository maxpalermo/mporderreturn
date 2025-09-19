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

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-hand-right"></i>
        <span>{l s="Step 3: Importa EAN13 da file Excel"}</span>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <h3 class="title">{l s='Seleziona il file con gli EAN13' mod='mpmassimport'}</h3>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="id_group_size">{l s='Gruppo Taglia' mod='mpmassimport'}</label>
                    <select id="id_group_size" class="form-control chosen" name="id_group_size">
                        <option>{l s='Seleziona il gruppo taglia' mod='mpmassimport'}</option>
                        {foreach $attributeGroups as $attributeGroup}
                            <option value="{$attributeGroup.id_attribute_group}">{$attributeGroup.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_no_size">{l s='Taglia unica' mod='mpmassimport'}</label>
                    <select id="id_no_size" class="form-control chosen" name="id_group_size">
                        <option>{l s='Seleziona la taglia unica' mod='mpmassimport'}</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="fileProductUploadEan13">{l s='Seleziona il file Excel' mod='mpmassimport'}</label>
                    <input id="fileProductUploadEan13" type="file" name="fileProductUploadEan13" class="hide">
                    <div class="dummyfile input-group">
                        <span class="input-group-addon"><i class="icon-file"></i></span>
                        <input id="fileProductUploadEan13-name" type="text" name="filename" readonly="">
                        <span class="input-group-btn">
                            <button id="fileProductUploadEan13-selectbutton" type="button" name="submitAddAttachments"
                                class="btn btn-default">
                                <i class="icon-folder-open"></i>
                                <span>Aggiungi files</span>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="progress" style="height: 32px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated progress-import-ean13"
                            style="width: 0; padding-top: 6px; font-size: 1.3rem; text-align: center;" role="progressbar"
                            aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#fileProductUploadEan13-selectbutton').click(function(e) {
                            $('#fileProductUploadEan13').trigger('click');
                        });

                        $('#fileProductUploadEan13-name').click(function(e) {
                            $('#fileProductUploadEan13').trigger('click');
                        });

                        $('#fileProductUploadEan13-name').on('dragenter', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                        });

                        $('#fileProductUploadEan13-name').on('dragover', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                        });

                        $('#fileProductUploadEan13-name').on('drop', function(e) {
                            e.preventDefault();
                            var files = e.originalEvent.dataTransfer.files;
                            $('#fileProductUploadEan13')[0].files = files;
                            $(this).val(files[0].name);
                        });

                        $('#fileProductUploadEan13').change(function(e) {
                            if ($(this)[0].files !== undefined) {
                                var files = $(this)[0].files;
                                var name = '';

                                $.each(files, function(index, value) {
                                    name += value.name + ', ';
                                });

                                $('#fileProductUploadEan13-name').val(name.slice(0, -2));
                            } else // Internet Explorer 9 Compatibility
                            {
                                var name = $(this).val().split(/[\/]/);
                                $('#fileProductUploadEan13-name').val(name[name.length - 1]);
                            }
                        });

                        if (typeof fileProductUpload_max_files !== 'undefined') {
                            $('#fileProductUploadEan13').closest('form').on('submit', function(e) {
                                if ($('#fileProductUploadEan13')[0].files.length >
                                    fileProductUpload_max_files) {
                                    e.preventDefault();
                                    alert('You can upload a maximum of  files');
                                }
                            });
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-default pull-right submitImportEan13" type="button">
                    <i class="process-icon-download-alt"></i>
                    <span>{l s='Importa' mod='mpmassimport'}</span>
                </button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function() {
            $('#id_group_size').on('change', function(){
                var data = {
                    ajax: true,
                    action: 'getSizeAttributes',
                    id_group: this.value
                };
                $.post( "{$ajax_controller}", data, function(response) {
                    $('#id_no_size').html('').trigger('chosen:updated');
                    $.each(response.options, function(){
                        $('#id_no_size').append(
                            $('<option>',
                            {
                                value: this.id_attribute,
                                text: this.name
                            })
                        ).trigger('chosen:updated');
                    });
                });
            });
            $('.submitImportEan13').on('click', function() {
                if (confirm('Iniziare l\'importazione del foglio excel?') == false) {
                    return false;
                }

                /** START EXCEL IMPORT **/
                var formData = new FormData();
                var uploadFiles = document.getElementById('fileProductUploadEan13').files;
                formData.append("fileUpload", uploadFiles[0]);
                formData.append("ajax", true);
                formData.append("action", "importExcelEan13");
                formData.append("products", '');
                formData.append("id_group_size", $('#id_group_size').val());
                formData.append("id_no_size", $('#id_no_size option:selected').text());

                $('.submitImportEan13 i').addClass('process-icon-loading').removeClass('process-icon-download-alt');

                $.ajax({
                    type: "POST",
                    url: "{$ajax_controller}",
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    beforeSend: function(e) {
                        progressValue = 0;
                        intervalId = window.setInterval(setProgressBar('.progress-import-ean13', progressValue), 500);
                    },
                    success: function(response) {
                        window.clearInterval(intervalId);
                        doneProgressBar('.progress-import-ean13');
                        $('.submitImportProducts i')
                            .removeClass('process-icon-loading')
                            .addClass('process-icon-download-alt');
                        alert("{l s='Operatione eseguita' mod='mpmassimport'}");
                        $('#modal_results .modal-body').html(response.resultHtml);
                        $('#modal_results').modal({ backdrop: 'static', keyboard: false, show: true });

                        $('.submitImportEan13 i')
                            .removeClass('process-icon-loading')
                            .addClass('process-icon-download-alt');

                        return true;
                    },
                    error: function(response) {
                        $('.submitImportEan13 i')
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