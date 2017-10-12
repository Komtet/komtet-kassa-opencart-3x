<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
			<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td>ID</td>
						<td><?php echo $lang_order_id; ?></td>
						<td><?php echo $lang_status; ?></td>
						<td><?php echo $lang_error; ?></td>
					</tr>
				</thead>
				<tbody>
					<?php if ($items) { ?>
						<?php foreach ($items as $i) { ?>
							<tr>
								<td><?php echo $i['id']; ?></td>
								<td><a href="<?php echo $i['order_url']; ?>"><?php echo $i['order_id']; ?></a></td>
								<td>
									<i class="fa fa-<?php echo $i['success'] ? 'check-circle' : 'exclamation-circle'; ?>" style="color: <?php echo $i['success'] ? 'green' : 'red'; ?>"></i>
									<span><?php echo $i['success'] ? $lang_success : $lang_failed ?></span>
								</td>
								<td><?php echo $i['error']; ?></td>
							</tr>
						<?php } ?>
					<?php } else { ?>
					<tr>
						<td colspan="4"><?php echo $lang_no_data; ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php echo $pagination; ?>
	</div>
</div>
<?php echo $footer; ?>
