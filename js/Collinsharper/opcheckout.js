// If we are going to shipping_method test the address with ABFS - if its bad push them to shipping

 Object.extend(checkout, { setStepResponse: function(response){
			//shuipping_method shipping-method shipping

		if (response.goto_section+'' == 'shipping_method' && response.skip != true) {
			this.address_notice = '';
			$('cboAddress').update();
		 _what =  new Ajax.Request(
				checkout.testaddressurl,
				{
					method:'post', onSuccess: function(transport) {
						var _response = transport.responseText.evalJSON();
						if(_response.message != '' && _response.message != null) {

							response.goto_section = 'shipping';
							response.skip = true;
							checkout.address_notice = "<p>Your address is not valid, please correct as below</p>"+_response.message+" " + _response.addresses;
							$('cboAddress').update(checkout.address_notice);
							//update address text boxes
							$('shipping:firstname').value = _response.shipping_firstname;
							$('shipping:lastname').value = _response.shipping_lastname;
							$('shipping:company').value = _response.shipping_company;
							$('shipping:street1').value = _response.shipping_street;
							$('shipping:street2').value = _response.shipping_street2;
							$('shipping:city').value = _response.shipping_city;
							$('shipping:country_id').value = _response.shipping_country;
							  if (window.shippingRegionUpdater) {
												shippingRegionUpdater.update();
											}
							$('shipping:region_id').value = _response.shipping_region;
							$('shipping:postcode').value = _response.shipping_postcode;
							$('shipping:telephone').value = _response.shipping_telephone;
							
							shipping.newAddress(true);
							
							//end update address text boxes						
							checkout.reloadProgressBlock();
							checkout.gotoSection(response.goto_section);
						   return true;
						}
						response.skip = true;
						checkout.setStepResponse(response);

					}.bind(this)
				}
			
			);		

			return true;
	
	} else {
               if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }
        if (response.allow_sections) {
            response.allow_sections.each(function(e){
                $('opc-'+e).addClassName('allow');
            });
        }

        if(response.duplicateBillingInfo)
        {
            shipping.setSameAsBilling(true);
        }

        if (response.goto_section) {
            this.reloadProgressBlock();
            this.gotoSection(response.goto_section);
            return true;
        }
        if (response.redirect) {
            location.href = response.redirect;
            return true;
        }
        return false;
    }
    }
    } );

	// document.observe("dom:loaded", function() {
		// Event.observe($('billing:use_for_shipping_no'), 'click', function () {
		// });
		// Event.observe($('billing:use_for_shipping_yes'), 'click', function () {
		// });
		
	// });
