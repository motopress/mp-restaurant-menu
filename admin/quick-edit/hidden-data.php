<div class="hidden mprm-data">
	<span type="hidden" class="mprm-data-price"><?php echo $price ?></span>
	<span type="hidden" class="mprm-data-sku"><?php echo $sku ?></span>

	<span type="hidden" class="mprm-data-calories"><?php echo empty($nutritional['calories']['val']) ? '0' : $nutritional['calories']['val'] ?></span>
	<span type="hidden" class="mprm-data-cholesterol"><?php echo empty($nutritional['cholesterol']['val']) ? '0' : $nutritional['cholesterol']['val'] ?></span>
	<span type="hidden" class="mprm-data-fiber"><?php echo empty($nutritional['fiber']['val']) ? '0' : $nutritional['fiber']['val'] ?></span>
	<span type="hidden" class="mprm-data-sodium"><?php echo empty($nutritional['sodium']['val']) ? '0' : $nutritional['sodium']['val'] ?></span>
	<span type="hidden" class="mprm-data-carbohydrates"><?php echo empty($nutritional['carbohydrates']['val']) ? '0' : $nutritional['carbohydrates']['val'] ?></span>
	<span type="hidden" class="mprm-data-fat"><?php echo empty($nutritional['fat']['val']) ? '0' : $nutritional['fat']['val'] ?></span>
	<span type="hidden" class="mprm-data-protein"><?php echo empty($nutritional['protein']['val']) ? '0' : $nutritional['protein']['val'] ?></span>

	<span type="hidden" class="mprm-data-weight"><?php echo empty($attributes['weight']['val']) ? '0' : $attributes['weight']['val'] ?></span>
	<span type="hidden" class="mprm-data-bulk"><?php echo empty($attributes['bulk']['val']) ? '0' : $attributes['bulk']['val'] ?></span>
	<span type="hidden" class="mprm-data-size"><?php echo empty($attributes['size']['val']) ? '0' : $attributes['size']['val'] ?></span>
</div>