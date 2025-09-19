<div class="form-group">
    <label>{$switch.label}</label>
    <div class="form-input">    		
        <span class="{$class_name} switch prestashop-switch fixed-width-lg">
            <input type="radio" name="{$switch.name}" id="{$switch.name}_on" value="1" {if $switch.value == 1} checked {/if}>
            <label for="{$switch.name}_on"><i class="icon icon-2x icon-check text-success"></i></label>
            <input type="radio" name="{$switch.name}" id="{$switch.name}_off" value="0" {if $switch.value == 0} checked {/if}>
            <label for="{$switch.name}_off"><i class="icon icon-2x icon-times text-danger"></i></label>
            <a class="slide-button btn"></a>
        </span>									
    </div>
</div>
