<?=form_open(ee('CP/URL')->make('addons/settings/rets_rabbit_v2/save_config'));?>

<?php
$this->table->set_template($cp_pad_table_template);

foreach ($data as $key => $val)
{
    $this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

?>
<?php $this->table->clear()?>

<p><?=form_submit('submit', lang('submit'), "class='submit'")?></p>
<?php
form_close();
