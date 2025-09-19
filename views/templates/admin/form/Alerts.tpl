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
<div class="alerts">
    {if $confirmations}
        <div class="alert alert-success" role="alert">
            {foreach $confirmations as $confirmation}
                {$confirmation} <br>
            {/foreach}
        </div>
    {/if}

    {if $warnings}
        <div class="alert alert-warning" role="alert">
            {foreach $warnings as $warning}
                {$warning} <br>
            {/foreach}
        </div>
    {/if}

    {if $errors}
        <div class="alert alert-danger" role="alert">
            {foreach $errors as $error}
                {$error} <br>
            {/foreach}
        </div>
    {/if}
</div>