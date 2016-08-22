<?php
$defaults = array(
	'id' => '',
	'name' => isset($name) ? $name : 'text',
	'value' => isset($value) ? $value : null,
	'label' => isset($label) ? $label : null,
	'desc' => isset($desc) ? $desc : null,
	'placeholder' => '',
	'class' => 'regular-text',
	'disabled' => false,
	'autocomplete' => '',
	'data' => false
);
$args = wp_parse_args($data, $defaults);
$class = implode(' ', array_map('sanitize_html_class', explode(' ', $args['class'])));
$disabled = '';
if ($args['disabled']) {
	$disabled = ' disabled="disabled"';
}
$data = '';
if (!empty($args['data'])) {
	foreach ($args['data'] as $key => $value) {
		$data .= 'data-' . sanitize_key($key) . '="' . esc_attr($value) . '" ';
	}
}
?>
<span id="mprm-<?php echo sanitize_key($args['name']) ?>-wrap">

	<label class="mprm-label" style="<?php echo empty($args['label']) ? 'display:none' : '' ?>" for="<?php echo sanitize_key($args['id']) ?>"> <?php echo esc_html($args['label']) ?></label>

	<?php
	if (!empty($args['desc'])) { ?>
		<span class="mprm-description"><?php echo esc_html($args['desc']) ?></span>
	<?php } ?>
	<input type="text" name="<?php echo esc_attr($args['name']) ?>" id="<?php echo esc_attr($args['id']) ?>" value="<?php echo esc_attr($args['value']) ?>" placeholder="<?php echo esc_attr($args['placeholder']) ?>" class="<?php echo $class ?>"<?php echo ' ' . $data . ' ' . $disabled ?> autocomplete="<?php echo esc_attr($args['autocomplete']) ?>"/>
</span>
