<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<?php if (!$denied) { ?>
				<div class="pull-right">
					<button type="submit" form="form-module" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
					<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
				</div>
			<?php } ?>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($denied) { ?>
			<div class="alert alert-danger">
				<i class="fa fa-exclamation-circle"></i> <?php echo $error_permission; ?>
			</div>
		<?php } else { ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
				</div>
				<div class="panel-body">
					<form action="<?php echo $action; ?>" method="post" id="form-module" class="form-horizontal">
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_server_url"><?php echo $setting_server_url; ?></label>
							<div class="col-sm-10">
								<input
									class="form-control"
									id="module_komtet_kassa_server_url"
									name="module_komtet_kassa_server_url"
									type="text"
									value="<?php echo $settings['server_url']; ?>" />
								<?php if (isset($errors['server_url'])) { ?>
								<div class="text-danger"><?php echo $errors['server_url']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_shop_id"><?php echo $setting_shop_id; ?></label>
							<div class="col-sm-10">
								<input
									class="form-control"
									id="module_komtet_kassa_shop_id"
									name="module_komtet_kassa_shop_id"
									type="text"
									value="<?php echo $settings['shop_id']; ?>" />
								<?php if (isset($errors['shop_id'])) { ?>
								<div class="text-danger"><?php echo $errors['shop_id']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_secret_key"><?php echo $setting_secret_key; ?></label>
							<div class="col-sm-10">
								<input
									class="form-control"
									id="module_komtet_kassa_secret_key"
									name="module_komtet_kassa_secret_key"
									type="text"
									value="<?php echo $settings['secret_key']; ?>" />
								<?php if (isset($errors['secret_key'])) { ?>
								<div class="text-danger"><?php echo $errors['secret_key']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_queue_id"><?php echo $setting_queue_id; ?></label>
							<div class="col-sm-10">
								<input
									class="form-control"
									id="module_komtet_kassa_queue_id"
									name="module_komtet_kassa_queue_id"
									type="text"
									value="<?php echo $settings['queue_id']; ?>" />
								<?php if (isset($errors['queue_id'])) { ?>
								<div class="text-danger"><?php echo $errors['queue_id']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_tax_system"><?php echo $setting_tax_system; ?></label>
							<div class="col-sm-10">
								<select name="module_komtet_kassa_tax_system" id="module_komtet_kassa_tax_system" class="form-control" autocomplete="off">
								<?php foreach ($tax_systems as $i) { ?>
									<option value="<?php echo $i['value']; ?>" <?php echo $i['enabled'] ? ' selected="selected"' : ''; ?>><?php echo $i['label']; ?></option>
								<?php } ?>
								</select>
								<?php if (isset($errors['tax_system'])) { ?>
								<div class="text-danger"><?php echo $errors['tax_system']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_vat_rate_product"><?php echo $setting_vat_rate_product; ?></label>
							<div class="col-sm-10">
								<select name="module_komtet_kassa_vat_rate_product" id="module_komtet_kassa_vat_rate_product" class="form-control" autocomplete="off">
								<?php foreach ($product_vat_rates as $i) { ?>
								<option value=<?php echo $i['value']; ?> <?php echo $i['enabled'] ? ' selected="selected"' : ''; ?>><?php echo $i['label']; ?></option>
								<?php } ?>
								</select>
								<?php if (isset($errors['vat_rate_product'])) { ?>
									<div class="text-danger"><?php echo $errors['vat_rate_product']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_vat_rate_shipping"><?php echo $setting_vat_rate_shipping; ?></label>
							<div class="col-sm-10">
								<select name="module_komtet_kassa_vat_rate_shipping" id="module_komtet_kassa_vat_rate_shipping" class="form-control" autocomplete="off">
								<?php foreach ($shipping_vat_rates as $i) { ?>
									<option value=<?php echo $i['value']; ?> <?php echo $i['enabled'] ? ' selected="selected"' : ''; ?>><?php echo $i['label']; ?></option>
								<?php } ?>
								</select>
								<?php if (isset($errors['vat_rate_shipping'])) { ?>
									<div class="text-danger"><?php echo $errors['vat_rate_shipping']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_payment_codes"><?php echo $setting_payment_codes; ?></label>
							<div class="col-sm-10">
								<select multiple="multiple" size="3" name="module_komtet_kassa_payment_codes[]" id="module_komtet_kassa_payment_codes" class="form-control" autocomplete="off">
								<?php foreach ($payment_codes as $i) { ?>
									<option value="<?php echo $i['value']; ?>" <?php echo $i['enabled'] ? ' selected="selected"' : ''; ?>><?php echo $i['label']; ?></option>
								<?php } ?>
								</select>
								<?php if (isset($errors['payment_codes'])) { ?>
									<div class="text-danger"><?php echo $errors['payment_codes']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_statuses_sell"><?php echo $setting_statuses_sell; ?></label>
							<div class="col-sm-10">
								<select multiple="multiple" size="3" name="module_komtet_kassa_statuses_sell[]" id="module_komtet_kassa_statuses_sell" class="form-control" autocomplete="off">
								<?php foreach ($statuses_sell as $i) { ?>
									<option value="<?php echo $i['value']; ?>" <?php echo $i['enabled'] ? ' selected="selected"' : ''; ?>><?php echo $i['label']; ?></option>
								<?php } ?>
								</select>
								<?php if (isset($errors['statuses_sell'])) { ?>
									<div class="text-danger"><?php echo $errors['statuses_sell']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_statuses_return"><?php echo $setting_statuses_return; ?></label>
							<div class="col-sm-10">
								<select multiple="multiple" size="3" name="module_komtet_kassa_statuses_return[]" id="komtet_kassa_statuses_return" class="form-control" autocomplete="off">
								<?php foreach ($statuses_return as $i) { ?>
									<option value="<?php echo $i['value']; ?>" <?php echo $i['enabled'] ? ' selected="selected"' : ''; ?>><?php echo $i['label']; ?></option>
								<?php } ?>
								</select>
								<?php if (isset($errors['statuses_return'])) { ?>
								<div class="text-danger"><?php $errors['statuses_return']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_should_print"><?php echo $setting_should_print; ?></label>
							<div class="col-sm-10">
								<select name="module_komtet_kassa_should_print" id="module_komtet_kassa_should_print" class="form-control" autocomplete="off">
									<option value="1" <?php echo $settings['should_print'] ? 'selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
									<option value="0" <?php echo $settings['should_print'] ? '' : 'selected="selected"'; ?>><?php echo $text_disabled; ?></option>
								</select>
								<?php if (isset($errors['should_print'])) { ?>
								<div class="text-danger"><?php echo $errors['should_print']; ?></div>
								<?php } ?>
							</div>
						</div>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="module_komtet_kassa_status"><?php echo $setting_status; ?></label>
							<div class="col-sm-10">
								<select name="module_komtet_kassa_status" id="module_komtet_kassa_status" class="form-control" autocomplete="off">
									<option value="1" <?php echo $settings['status'] ? 'selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
									<option value="0" <?php echo $settings['status'] ? '' : 'selected="selected"'; ?>><?php echo $text_disabled; ?></option>
								</select>
								<?php if (isset($errors['status'])) { ?>
								<div class="text-danger"><?php echo $errors['status']; ?></div>
								<?php } ?>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<?php echo $footer; ?>
