<?php
// For gmail compatibility, including CSS styles in head/body are stripped out therefore styles need to be inline. These variables contain rules which are added to the template inline.
$template_footer = "
	border-top:0;
	-webkit-border-radius:3px;
";
$credit = "
	border:0;
	color: #000000;
	font-family: 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif;
	font-size:12px;
	line-height:125%;
	text-align:center;
";
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
<tr>
	<td align="center" valign="top">
		<!-- Footer -->
		<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer" style="<?php echo $template_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<tr>
				<td valign="top">
					<table border="0" cellpadding="10" cellspacing="0" width="100%">
						<tr>
							<td colspan="2" valign="middle" id="credit" style="<?php echo $credit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
								<?php echo wpautop(wp_kses_post(wptexturize(apply_filters('mprm_email_footer_text', '<a href="' . esc_url(home_url()) . '">' . get_bloginfo('name') . '</a>')))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<!-- End Footer -->
	</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>