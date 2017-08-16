<p>
	<a href="<?= ee('CP/URL')->make('addons/settings/rets_rabbit_v2/servers_refresh', array('redirect' => 'server')) ?>" style="float:right;" class="submit"><?=lang('refresh') ?></a>
</p>

<div class="clear_left shun"></div>

<?=form_open(ee('CP/URL', 'addons/settings/rets_rabbit_v2/update_servers'));?>
<p><?= lang('view_servers') ?></p>
<?php
$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(array(
	lang('short_code'),
	lang('server_id'),
    lang('server_name'),
	lang('is_default'),
));

foreach ($servers as $key => $val)
{
	$this->table->add_row(array(
			form_input(array(
				"name"	=> "short_code[]",
				"value"	=> $val->short_code,
			)).form_hidden('id[]', $val->id),
			form_label($val->server_id, ''),
            form_label($val->name, ''),
			form_checkbox(array(
				"name"	=> "is_default",
				"value"	=> $val->id,
                "checked"  => ($val->is_default ? TRUE : FALSE)
			)),
		)
	);
}

echo $this->table->generate();

?>
<?php $this->table->clear()?>

<p><?=form_submit('submit', lang('submit'), "class='submit'")?></p>
<?php
form_close();
