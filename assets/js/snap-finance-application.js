// This is a static example of what a transaction would look like.
// A merchant site would instead pull this data from their customer's shopping cart.
// The value must conform to SnapTransactionSchema specification.
// This code is for utility of this mock-page only.
const appId = document.getElementById('applicationId');
// CHECKOUT BUTTON
snap.init(snap_finance.token);

jQuery(document).ready(
	function () {
		setTimeout(
			function () {
				jQuery('.loader-box').remove();
				jQuery('#snap-checkout-button button').click();
			},
			2000
		);
	}
);

function getCurrentUrlVars() {
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for (var i = 0; i < hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}
// CHECKOUT BUTTON
snap.checkoutButton(
	{
		style: {
			color: snap_finance.color,
			shape: snap_finance.shape,
			height: snap_finance.height
		},

		onClick: function (data, actions) {
			// This method is invoked upon click of the Snap Checkout button.
			// Merchant site developer should include the following code to invoke checkout:

			jQuery('#order_comments').text(snap_finance.transaction);
			return actions.launchCheckout(snap_finance.transaction);
		},

		onApproved: function (data, actions) {
			appId.value = data.applicationId;
			if (data.applicationId) {
				var data = {
					'action': 'snap_finance_complete_payment',
					'orderId': snap_finance.order_id,
					'applicationId': data.applicationId,
					'application': data
				};

				jQuery.post(
					snap_finance.ajaxurl,
					data,
					function (response) {
						setTimeout(
							function () {
								window.location.href = snap_finance.thankyou_url;
							},
							2000
						);

					}
				);
			}

		},
		onCanceled: function (data, actions) {
			console.log(data);
			console.log('onCanceled');
			var data = {
				'action': 'snap_finance_update_status',
				'orderId': snap_finance.order_id,
				'status': 'cancelled',
				'application': data
			};

			jQuery.post(
				snap_finance.ajaxurl,
				data,
				function (response) {
					window.location.href = snap_finance.wc_get_cart_url;
				}
			);


		},
		onDenied: function (data, actions) {
			if (data.applicationId) {

				appId.value = data.applicationId;
				var data = {
					'action': 'snap_finance_order_failed',
					'orderId': snap_finance.order_id,
					'applicationId': data.applicationId,
					'application': data
				};

				jQuery.post(
					snap_finance.ajaxurl,
					data,
					function (response) {
						afterDenialWPAjaxUpdate(data);
					}
				);

				function afterDenialWPAjaxUpdate(data) {
					jQuery(document.body).trigger('wc_fragment_refresh');
					jQuery('.wc_snap_error').remove();
					jQuery('.status-no').html('Your snap application was denied.  For your reference, your application ID is ' + data.applicationId + '.');
					if (data.message) {
						jQuery('#checkout').before('<p class="wc_snap_error" >' + data.message + '</p>');
					}
					var data = {
						'action': 'snap_finance_add_notes',
						'orderId': snap_finance.order_id,
						'message': data.message,
						'full_error': data
					};

					setInterval(
						function () {
							jQuery('body').trigger('wc_fragment_refresh');
							jQuery('body').trigger('wc_fragments_refreshed');
							jQuery('body').trigger('updated_wc_div');
						},
						100
					);

					jQuery.post(
						snap_finance.ajaxurl,
						data,
						function (response) {
							var url_link = window.location.href;
							url_link = url_link.split('payment_method');
							window.location.href = snap_finance.wc_get_cart_url;
						}
					);
				}


			}
			// Snap funding was denied (i.e. approval was less than shopping cart amount)
			// Snap will have notified the customer of this in a separate window.
			// The merchant site developer can include code here to respond with an appropriate user experience.
		},

		onError: function (data, actions) {
			if (data.applicationId) {
				jQuery('.wc_snap_error').remove();
				jQuery('.status-no').html('Your snap application was denied.  For your reference, your application ID is ' + data.applicationId + '.');
				if (data.message) {
					jQuery('#checkout').before('<p class="wc_snap_error" >' + data.message + '</p>');
				}

				var data = {
					'action': 'snap_finance_add_notes',
					'orderId': snap_finance.order_id,
					'message': data.message,
					'full_error': data
				};

				jQuery.post(
					snap_finance.ajaxurl,
					data,
					function (response) {
						var url_link = window.location.href;
						url_link = url_link.split('payment_method');
						window.location.href = snap_finance.wc_get_cart_url;

					}
				);
			}
		}
		// The render method is invoked here to display the Snap Checkout button
	}
).render();


// CHECKOUT MARK
snap.checkoutMark(
	{
		style: {
			color: snap_finance.color,
			height: snap_finance.height
		}
	}
).render();
