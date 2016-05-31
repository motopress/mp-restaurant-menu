<?php
$defaults = array(
	'name' => null,
	'current' => null,
	'class' => 'mprm-checkbox',
	'options' => array(
		'disabled' => false,
		'readonly' => false
	)
);

$args = wp_parse_args($data, $defaults);

$class = implode(' ', array_map('sanitize_html_class', explode(' ', $args['class'])));
$options = '';
if (!empty($args['options']['disabled'])) {
	$options .= ' disabled="disabled"';
} elseif (!empty($args['options']['readonly'])) {
	$options .= ' readonly';
}
?>
<input type="checkbox" <?php echo $options ?> name="<?php echo esc_attr($args['name']) ?>" id="<?php echo esc_attr($args['name']) ?>" class="<?php echo $class . ' ' . esc_attr($args['name']) ?>" <?php checked(1, $args['current'], false) ?> />

