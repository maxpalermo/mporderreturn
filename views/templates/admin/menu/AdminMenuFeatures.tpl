{*
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
 *}

<div class="btn-group" id="AdminFeaturesMenu" style="top: 2rem; left: 2rem; display: inline;">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" style="border: none;">
        <i class="process-icon-menu"></i>
        <span>Menu</span>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        {foreach $menu as $key=>$item}
            {if strpos($key, 'divider') === 0}
                <li class="divider"></li>
            {else}
            <li>
                <a href="{$item.href}">
                    {if $item.icon}
                        <i class="icon {$item.icon}"></i>
                    {/if}
                    <span>{$item.label}</span>
                </a>
            </li>
            {/if}
        {/foreach}
    </ul>
</div>

<script type="text/javascript">
    $(function(){
        console.log("MENU");
        let menu = $('#AdminFeaturesMenu').detach();
        //$('#page-header-desc-configuration-Main').after(menu).remove();
        //$('.page-bar.toolbarBox .btn-toolbar').prepend(menu);
        $('.page-head .page-title').after(menu);
    });
</script>