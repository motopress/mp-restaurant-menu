<?php
$defaults = array(
	'options' => array(),
	'name' => null,
	'class' => '',
	'id' => '',
	'selected' => 0,
	'chosen' => false,
	'placeholder' => null,
	'multiple' => false,
	'show_option_all' => _x('All', 'all dropdown items', 'mp-restaurant-menu'),
	'show_option_none' => _x('None', 'no dropdown items', 'mp-restaurant-menu'),
	'data_attr' => array(),
);
$args = wp_parse_args($data, $defaults);

$data_elements = '';
foreach ($args['data_attr'] as $key => $value) {
	$data_elements .= ' data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
}
if ($args['multiple']) {
	$multiple = ' MULTIPLE';
} else {
	$multiple = '';
}
if ($args['chosen']) {
	$args['class'] .= ' mprm-select-chosen';
}
if ($args['placeholder']) {
	$placeholder = $args['placeholder'];
} else {
	$placeholder = '';
}
$class = implode(' ', array_map('sanitize_html_class', explode(' ', $args['class']))); ?>

<select name="<?php echo esc_attr($args['name']) ?>"
        id="<?php esc_attr(mprm_sanitize_key((str_replace('-', '_', $args['id'])))) ?>"
        class="mprm-select <?php echo $class . $multiple ?>"
        data-placeholder="<?php echo $placeholder ?>" >
	<?php
	if ($args['show_option_all']) {
		if ($args['multiple']) {
			$selected = selected(true, in_array(0, $args['selected']), false);
		} else {
			$selected = selected($args['selected'], 0, false);
		} ?>
		<option value="all" <?php echo $selected ?> > <?php esc_html($args['show_option_all']) ?></option>
		<?php
	}
	if (!empty($args['options'])) {
		if ($args['show_option_none']) {
			if ($args['multiple']) {
				$selected = selected(true, in_array(-1, $args['selected']), false);
			} else {
				$selected = selected($args['selected'], -1, false);
			} ?>
			<option value="-1" <?php echo $selected ?>><?php echo esc_html($args['show_option_none']) ?></option>
		<?php }
		foreach ($args['options'] as $key => $option) {
			if ($args['multiple'] && is_array($args['selected'])) {
				$selected = selected(true, in_array($key, $args['selected'], true), false);
			} else {
				$selected = selected($args['selected'], $key, false);
			} ?>
			<option value="<?php echo esc_attr($key) ?>" <?php echo $selected ?>><?php echo esc_html($option) ?></option>
		<?php }
	} ?>
</select>
