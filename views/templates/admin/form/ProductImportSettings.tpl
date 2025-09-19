<div class="panel" id="ProductImportSettings">
    <div class="panel-heading">
        <h3 class="modal-title">{l s='Product import settings' mod='mpmassimport'}</h3>
    </div>
    <div class="panel-body">
        <div class="row">
        {foreach $switches as $switch}
            <div class="col-md-4">
                {include file=$switch_path switch=$switch}
            </div>
        {/foreach}
        </div>
    </div>
    <div class="panel-footer">
        <button type="button" class="btn btn-primary pull-right" onclick="javascript:saveImportSettings();">
            <span class="process-icon-save"></span>
            {l s='Save settings' mod='mpmassimport'}
        </button>
    </div>
</div>
<script type="text/javascript">
    function saveImportSettings()
    {
        var switches = $('.switch_import_settings');
        var values = [];
        $(switches).each(function(){
            let radio = $(this).find('input[type="radio"]:checked');
            let name = $(radio).attr('name');
            let value = $(radio).val();
            values.push({ name: name, value: value });
        });
        let data = {
            ajax: 1,
            action: 'updateImportSettings',
            values: values
        };
        $.post( "", data, function(response) {
            alert(response);
        });
    }
    function showImportOptions()
    {
        $('#ProductImportSettings').modal('show');
    }
    $(document).ready(function(){
        
    });
</script>