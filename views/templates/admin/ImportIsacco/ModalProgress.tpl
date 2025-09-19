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

<div id="modalProgress" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalProgress-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="modal-title" id="modalProgress-title">Importazione in corso...</h3>
                    </div>
                    <div class="col-md-4">
                        <img class="modalProgress-spinner" src="{$root_url}views/img/loading.gif" style="width: 64px;">
                    </div>
                </div>
            </div>
            <div class="modal-body modalProgress-body">
                <h3>Attendere la fine delle operazioni</h3>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default pull-right" type="button" data-dismiss="modal" aria-label="Close" onclick="javascript:stop_ajax_import_products=true;">
                    <i class="process-icon-close"></i>
                    <span>{l s='Chiudi' mod=''}</span>
                </button>
            </div>
        </div>
    </div>
</div>