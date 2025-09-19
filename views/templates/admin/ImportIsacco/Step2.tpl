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
        <span>{l s="Step 2: Importa il Database ISACCO"}</span>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">
                <h3 class="title">{l s='Database Isacco' mod='mpmassimport'}</h3>
            </div>
            <div class="col-md-3">
                <div class="form-group pull-right">
                    <label>Solo i prodotti importati</label>
                    <div class="form-input">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="only_imported_products" id="only_imported_products_on" value="1" checked>
                            <label for="only_imported_products_on"><i
                                    class="icon icon-2x icon-check text-success"></i></label>
                            <input type="radio" name="only_imported_products" id="only_imported_products_off" value="0">
                            <label for="only_imported_products_off"><i
                                    class="icon icon-2x icon-times text-danger"></i></label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="col-md-6 text-center">
                    <button class="btn btn-info submitReadDatabase" type="button">
                        <i class="process-icon-download"></i>
                        <span>{l s='Leggi Database' mod='mpmassimport'}</span>
                    </button>
                </div>
                <div class="col-md-6 text-center">
                    <button class="btn btn-danger submitStopDatabase" type="button">
                        <i class="process-icon-close"></i>
                        <span>{l s='Ferma processo' mod='mpmassimport'}</span>
                    </button>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="progress" style="height: 32px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated progress-database-isacco"
                        style="width: 0; padding-top: 6px; font-size: 1.3rem; text-align: center;" role="progressbar"
                        aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="panel-body database-process-info">
                    <div class="col-md-6">
                        <i class="fa fa-list-ol"></i> <strong>Processati: </strong> <span class="info-database-processed">0</span>
                    </div>
                    <div class="col-md-6">
                        <i class="fa fa-check-circle text-success"></i> <strong>Aggiornati: </strong> <span class="info-database-updated">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var stop_read_database = false;

        function startProgress()
        {
            stop_read_database = false;

            var progress = $('.progress-database-isacco');
            $(progress).css('width', '0');
            $(progress).attr('aria-valuenow', 0);
            $(progress).attr('progress-value', 0);
            $('.info-database-processed').text("0");
            $('.info-database-updated').text("0");
            $('.submitReadDatabase i').removeClass('process-icon-download').addClass('process-icon-loading');
        }

        function endProgress()
        {
            window.clearInterval(id_read_database);

            var progress = $('.progress-database-isacco');
            $(progress).css('width', '100%');
            $(progress).attr('aria-valuenow', 100);
            $(progress).attr('progress-value', 100);
            $('.submitReadDatabase i').removeClass('process-icon-loading').addClass('process-icon-download');
        }

        function duringProgress(page, processed, updated)
        {
            var progress = $('.progress-database-isacco');
            var value = Number($(progress).attr('progress-value'));
            value += 20;
            if (value > 100) {
                value = 20;
            }
            $(progress).css('width', value +'%');
            $(progress).attr('aria-valuenow', value);
            $(progress).attr('progress-value', value);
            $(progress).html("PASSO " + page);

            $('.info-database-processed').text(processed + Number($('.info-database-processed').text()));
            $('.info-database-updated').text(updated + Number($('.info-database-updated').text()));
        }

        function ajaxProcessReadDatabase(page)
        {
            if (stop_read_database) {
                endProgress();
                return true;
            }

            var data = {
                page: page,
                switch: $('input[name="only_imported_products"]:checked').val(),
                ajax: true,
                action: 'importDatabase'
            };

            $.post( "{$ajax_controller}", data, function(response) {
                if (response !== false) {
                    page++;
                    duringProgress(page, response.processed, response.updated);
                    ajaxProcessReadDatabase(page);
                } else {
                    endProgress();
                    alert("{l s='Importazione eseguita' mod='mpmassimport'}");
                }
            });
        }

        $(function() {
            $('.submitStopDatabase').on('click', function(){
                if (confirm('Terminare la lettura del database?') == false) {
                    return false;
                }
                stop_read_database = true;
            });
            $('.submitReadDatabase').on('click', function() {
                if (confirm('Importare i dati da Database Isacco?') == false) {
                    return false;
                }

                page = 0;

                startProgress();
                ajaxProcessReadDatabase(page);
            });
        });
    </script>
</div>