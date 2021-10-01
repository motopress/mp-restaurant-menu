<div class="hidden mprm-data">
	<span type="hidden" class="mprm-data-price"><?php echo esc_html( $price ); ?></span>
	<span type="hidden" class="mprm-data-sku"><?php echo esc_html( $sku ); ?></span>

	<span type="hidden" class="mprm-data-calories"><?php echo empty($nutritional['calories']['val']) ? '0' : esc_html( $nutritional['calories']['val'] ); ?></span>
	<span type="hidden" class="mprm-data-cholesterol"><?php echo empty($nutritional['cholesterol']['val']) ? '0' : esc_html( $nutritional['cholesterol']['val'] );?></span>
	<span type="hidden" class="mprm-data-fiber"><?php echo empty($nutritional['fiber']['val']) ? '0' : esc_html( $nutritional['fiber']['val'] );?></span>
	<span type="hidden" class="mprm-data-sodium"><?php echo empty($nutritional['sodium']['val']) ? '0' : esc_html( $nutritional['sodium']['val'] );?></span>
	<span type="hidden" class="mprm-data-carbohydrates"><?php echo empty($nutritional['carbohydrates']['val']) ? '0' : esc_html( $nutritional['carbohydrates']['val'] );?></span>
	<span type="hidden" class="mprm-data-fat"><?php echo empty($nutritional['fat']['val']) ? '0' : esc_html( $nutritional['fat']['val'] );?></span>
	<span type="hidden" class="mprm-data-protein"><?php echo empty($nutritional['protein']['val']) ? '0' : esc_html( $nutritional['protein']['val'] );?></span>

	<span type="hidden" class="mprm-data-weight"><?php echo empty($attributes['weight']['val']) ? '0' : esc_html( $attributes['weight']['val'] );?></span>
	<span type="hidden" class="mprm-data-bulk"><?php echo empty($attributes['bulk']['val']) ? '0' : esc_html( $attributes['bulk']['val'] );?></span>
	<span type="hidden" class="mprm-data-size"><?php echo empty($attributes['size']['val']) ? '0' : esc_html( $attributes['size']['val'] );?></span>
</div>