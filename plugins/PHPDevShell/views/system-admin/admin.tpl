<div class="row">
	<div class="column grid_6">
		<h2>{_e('System Version')}</h2>
		<h3>{$phpdevshell_version}</h3>

		<h2>{_e('Operating System Information')}</h2>
		<p>{$php_uname}</p>

        <h2>{_e('Connector Information')}</h2>
        <dl>
            {$CONNECTOR_INFO}
        </dl>
		<dl>
			<dt>{_e('Apache Server Version')}</dt>
				<dd>{$apache_get_version}</dd>
			<dt>{_e('Modules loaded with Apache')}</dt>
				{foreach from=$apache_modules item=apachemods}
				<dd>{$apachemods}</dd>
				{/foreach}
		</dl>
		<h4>{_e('PHP Information')}</h4>
		<dl>
			<dt>{_e('PHP Version')}</dt>
				<dd>{$phpversion}</dd>
			<dt>{_e('Extensions loaded with PHP')}</dt>
				{foreach from=$php_loaded_extensions item=phpext}
				<dd>{$phpext}</dd>
				{/foreach}
		</dl>
	</div>
	<div class="column grid_6 last">
        <h2>{_e('Config files')}</h2>
        <dl id="config_files">
            {$CONFIG_FILES}
        </dl>

		<h2>{_e('Config Data')}</h2>
		<dl id="config_dump">
			{$CONFIG}
		</dl>

	</div>
</div>

<div class="row">
    <div class="column grid_12">
        <h2>{_e('PHP Info')}</h2>
        <div>
            {(-1)|phpinfo}
        </div>
    </div>
</div>

<script>
    $(function() {
        $('.array_dump').not('.array_head').hide();
        $('.array_type').on('click', function(){ $(this).next('span').children('ul').toggle();});
        $('.array_ref').on('click', function() {
            var target = $('#dump_' + $(this).data('id'));
            $('.array_dump LI').not(target).css('outline', 'none');
            target.css('outline', '1px dashed red');
        });


        /// FILE DISPLAY, SHOULD BE A PLUGIN

        $('.value_path').each(function(idx, elem) {
            var html = elem.html;
            elem.replaceWith($('<A>').href('toto').html(html));
            });
    })
</script>
