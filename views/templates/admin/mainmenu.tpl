{*
* 2017 mpSOFT
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
*  @author    mpSOFT <info@mpsoft.it>
*  @copyright 2017 mpSOFT Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of mpSOFT
*}
<style>
    @keyframes rotate {
        0% {
            transform: rotate(0);
            font-size: 1em;
        }
        100% {
            transform: rotate(-30deg);
            font-size: 2.4em;
        }
    }

    /* The fast, box-shadow! */
    .fast-transition {
        position: relative; /* For positioning the pseudo-element */
        box-shadow: none;
    }

    .fast-transition::before {
    /* Position the pseudo-element. */
        content: ' ';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

    /* Create the box shadow at expanded size. */
        box-shadow: 0 10px 10px rgba(0, 0, 0, 0.5);

    /* Hidden by default. */
        opacity: 0;
        transition: opacity 500ms;
    }

    .fast-transition:hover::before {
        /* Show the pseudo-element on hover. */
        opacity: 1;
    }

    .flex-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 16px;
    }
    a.btn-square {
        background-color: #e1e1e1;
        color: #09424c !important;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        text-decoration: none !important;
        border: none;
        border-radius: 0;
        width: 100%;
        height: 128px;
        font-size: 2em;
        margin-right: 24px;
    }
    a.btn-square:hover {
        background-color: #f1f1f1;
        color: #424656 !important;
        text-shadow: 2px 2px 4px #aaaaaa;
        font-weight: bold;
    }
    a.btn-square i {
        font-size: 2em;
        margin: 6px;
        -webkit-transition: -webkit-transform 0.4s ease-in-out;
                transition: transform 0.4s ease-in-out;
    }
    a.btn-square:hover {
        color: #a94f61 !important;
    }
    a.btn-square:hover i{
        text-shadow: none;
        -webkit-transform: rotate(-30);
                transform: rotate(-30deg);
    }

    a .icon-container {
        width: 96px;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.7rem;
        background-color: #424656;
        color: #fafafa;
        padding-top: 0.7rem;
        margin-left: -0.2rem;
    }

    a:hover .icon-container {
        background-color: #a94f61;
    }

    a.disabled {
        background-color: #94969c !important;
    }

    a.disabled:hover {
        background-color: #7e7e80 !important;
        cursor: not-allowed;
    }

    a.pen {
        padding-right: 12px !important;
    }
</style>
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-list-alt"></i>
        &nbsp;
        <span>{l s='Select Action' mod='mpmassimport'}</span>
    </div>
    <div class="panel-body">
        {foreach $icons as $icon}
        <div class="col-md-4 flex-container">
            <a href="{if isset($icon.disabled) && $icon.disabled}javascript:void(0);alert('DISABLED');{else}{$icon.url}{/if}" class="btn-square fast-transition {if isset($icon.disabled) && $icon.disabled}disabled{/if}">
                <span class="icon-container"><i class="icon {$icon.icon} pen"></i></span>
                &nbsp;&nbsp;&nbsp;
                <span class="pen">{$icon.label}</span>
            </a>
        </div>
        {/foreach}
    </div>
</div>
