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
<div>
    {if $errors}
        <h4 class="title">
                <i class="icon fa-exclamation-triangle text-danger"></i>
                <span>ERRORI:</span>
        </h4>
        <div class="alert alert-danger" role="alert">
            <ul>
            {foreach $errors as $error}
                <li>{$error}</li>
            {/foreach}
            </ul>
        </h4>
    {/if}

    {if $warnings}
        <h4 class="title">
            <i class="icon fa-exclamation-circle text-warning"></i>
            <span>AVVISI:</span>
        </h4>
        <div class="alert alert-warning" role="alert">
            <ul>
            {foreach $warnings as $warning}
                <li>{$warning}</li>
            {/foreach}
            </ul>
        </div>
    {/if}

    <h4 class="title">
        <i class="icon fa-check-circle-o text-success"></i>
        <span>MESSAGGI:</span>
    </h4>
    <div class="alert alert-success" role="alert">
        <ul>
        {foreach $confirmations as $confirmation}
            <li>{$confirmation}</li>
        {/foreach}
        </ul>
    </div>
    <div class="alert alert-success" role="alert">
        Totale righe processate: <strong>{$processed}</strong>
    </div>
    <div class="alert alert-success" role="alert">
        Totale prodotti inseriti: <strong>{$inserted}</strong>
    </div>
</div>


