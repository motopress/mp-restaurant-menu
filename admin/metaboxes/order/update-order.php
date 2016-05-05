<div class="mprmadmin-box">
	<div class="mprm-admin-box-inside">
		<p>
			<span class="label">Status:</span>&nbsp;
			<select name="mprm-order-status" class="medium-text">
				<option value="pending">Pending</option>
				<option value="publish">Complete</option>
				<option value="refunded" selected="selected">Refunded</option>
				<option value="failed">Failed</option>
				<option value="abandoned">Abandoned</option>
				<option value="revoked">Revoked</option>
			</select>
		</p>
	</div>

	<div class="mprm-admin-box-inside">
		<p>
			<span class="label">Date:</span>&nbsp;
			<input type="text" name="mprm-payment-date" value="04/29/2016" class="medium-text edd_datepicker hasDatepicker" id="dp1462456939093">
		</p>
	</div>

	<div class="mprm-admin-box-inside">
		<p>
			<span class="label">Time:</span>&nbsp;
			<input type="text" maxlength="2" name="mprm-payment-time-hour" value="14" class="small-text mprm-payment-time-hour">&nbsp;:&nbsp;
			<input type="text" maxlength="2" name="mprm-payment-time-min" value="06" class="small-text mprm-payment-time-min">
		</p>
	</div>


	<div class="mprm-order-discount mprm-admin-box-inside">
		<p>
			<span class="label">Discount Code:</span>&nbsp;
			<span>None</span>
		</p>
	</div>

	<div class="mprm-order-payment mprm-admin-box-inside">
		<p>
			<span class="label">Total Price:</span>&nbsp;
			$&nbsp;<input name="mprm-payment-total" type="text" class="med-text" value="76.00">
		</p>
	</div>

	<div class="mprm-order-payment-recalc-totals mprm-admin-box-inside" style="display:none;">
		<p>
			<span class="label">Recalculate Totals:</span>&nbsp;
			<a href="" id="mprm-order-recalc-total" class="button button-secondary right">Recalculate</a>
		</p>
	</div>


</div>