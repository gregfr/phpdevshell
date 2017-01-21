<div id="searchForm">
	<span class="ui-icon ui-icon-search left"></span><input id="search_field" type="text" size="40" name="search_field" value="" class="active">
</div>
<table>
	<thead>
		<tr>
			<th>
				{_e('Plugin')}
			</th>
			<th>
				{_e('Description')}
			</th>
			<th>
				{_e('Status')}
			</th>
			<th>
				{_e('Action')}
			</th>
		</tr>
	</thead>
	<tbody>
		{foreach item=p from=$RESULTS}
		{strip}
		{if isset($log[$p.object])}
		<tr class="ok">
		{else}
		<tr>
		{/if}
			<td>
				{$p.plugin_config_message}
			</td>
			<td class="toggleWrap">
				<div class="img_left">{$p.status_icon}</div>
				<h3>
					<a name="{$p.object}">{$p.object}&nbsp;

                        {if (!empty($p.version)) }
					        version {$p.plugin.version}
                        {/if}

					{if $p.activation_db_version != ''}
					-DB-{$p.activation_db_version}
					{/if}
					</a>
				</h3>

                {if (!empty($p.description)) }
				<div>{$p.plugin.description}</div>
                {/if}

				<div class="hover"></div>
				<div class="toggle">
					<div style="padding-top: 3px;">
						{$p.logo} {$p.logo_selected}
					</div>
					<dl>
						{if isset($log[$p.object])}
						<dt>{_e('Install Log')}</dt>
						<dd>{$log[$p.object]}</dd>
						{/if}

                        {if (!empty($p.depends_on)) }
						<dt>{_e('Dependency')}</dt>
						<dd>{$p.depends_on}</dd>
                        {/if}

                        {if (!empty($p.class)) }
						<dt>{_e('Available Classes')}</dt>
						<dd>{$p.class}</dd>
                        {/if}

                        {if (!empty($p.object)) }
						<dt>{_e('Plugin Folder')}</dt>
						<dd>{$p.object}</dd>
                        {/if}

                        {if (!empty($p.plugin_lang_message)) }
						<dt>{_e('Menu Language File')}</dt>
						<dd>{$p.plugin_lang_message}</dd>
                        {/if}

                        {if (!empty($p.info)) }
						<dt>{_e('Plugin Help')}</dt>
						<dd>{$p.plugin.info}</dd>
                        {/if}

                        {if (!empty($p.founder)) }
                        <dt>{_e('Founders')}</dt>
						<dd>{$p.plugin.founder}</dd>
                        {/if}

                        {if (!empty($p.author)) }
                        <dt>{_e('Authors')}</dt>
						<dd>{$p.plugin.author}
                            {if (!empty($p.plugin.email)) } [{$p.plugin.email}]{/if}</dd>
                        {/if}

                        {if (!empty($p.date)) }
                        <dt>{_e('Release Date')}</dt>
						<dd>{$p.plugin.date}</dd>
                        {/if}

                        {if (!empty($p.copyright)) }
                        <dt>{_e('Copyright')}</dt>
						<dd>{$p.plugin.copyright}</dd>
                        {/if}

                        {if (!empty($p.license)) }
                        <dt>{_e('License')}</dt>
						<dd>{$p.plugin.license}</dd>
                        {/if}

                    </dl>
				</div>
			</td>
			<td>
				{$p.status}
				{if isset($log[$p.object])}{$logtext}{/if}
			</td>
			<td>
				{$p.show_part1}
			</td>
		</tr>
		{/strip}
		{/foreach}
	</tbody>
</table>