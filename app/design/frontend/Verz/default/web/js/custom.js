
require(["jquery","matchHeight"],function(e){
	
	jQuery(document).ready(function() {
		setTimeout(function() {
			jQuery(".page.messages div .messages").remove();
			jQuery("body.checkout-cart-index .reward-message").remove();
		}, 8000);
		jQuery("nav span.arrow").click(function(){
			jQuery(this).toggleClass("active");
			jQuery(this).next("ul").toggleClass("active");
		});
		jQuery(".toggle-btn .nav-toggle").click(function(){
			jQuery('html').toggleClass('nav-before-open nav-open');
		});
		jQuery(".minicart-wrapper .action.showcart").click(function(){
			jQuery('html').removeClass('nav-before-open nav-open');
		});
		jQuery(".show-more-items").click(function(){
			jQuery(this).hide();
			jQuery(this).parent().find("ol li.hideshow").show();
			jQuery(this).siblings(".less-more-items").show();
		});
		jQuery(".less-more-items").click(function(){
			jQuery(this).hide();
			jQuery(this).parent().find("ol li.hideshow").hide();
			jQuery(this).siblings(".show-more-items").show();
		});
		function MatchHeight() {
			 
			 
			 
			// jQuery(".product-item .product-item-info").matchHeight();
			
			jQuery(".price_wishlist").matchHeight();
			jQuery(".block-order-details-view .box strong.box-title").matchHeight();
			jQuery(".products-grid.wishlist .product-item-name>.product-item-link").matchHeight();
			jQuery(".wishlist .price_wishlist").matchHeight();
			jQuery(".products-grid.wishlist .product-item-name").matchHeight();
			// jQuery(".product-item-details .size20 a").matchHeight();
			
			jQuery(".product-item-details h2.size20").matchHeight();
			
			jQuery(".product-item-details .size20 a span").matchHeight();
			jQuery(".meet-arts .size22").matchHeight();
			jQuery(".grid-set .dsk-text").matchHeight();
			jQuery(".meetarts-section .center").matchHeight();
			jQuery(".about-two-section .dsk-text").matchHeight();
			jQuery(".about-two-section .mb70").matchHeight();
			jQuery(".mission-info .size26").matchHeight();
			jQuery(".mission-info .dsk-text").matchHeight();
			jQuery(".our-impact-wrp").matchHeight();
			// jQuery(".inpact-info h2").matchHeight();
			
			jQuery(".our-impact-wrp .dsk-text").matchHeight();
			jQuery(".stories-info .dsk-text").matchHeight();
			jQuery(".userprl-info h3").matchHeight();
			jQuery(".stories-info").matchHeight();
			jQuery(".title-buy .size30").matchHeight();
			jQuery(".can-buy-setion .center").matchHeight();
			jQuery(".featured-collection .product-item-info").matchHeight();
			jQuery(".wk_mp_design .product-item-name").matchHeight();
			
			
			
			
			jQuery(".event-list-info-holder").matchHeight();
			jQuery(".event-list-holder label.event-date").matchHeight();
			
			// jQuery(".first-event-banner .col-sm-8").matchHeight();
			// jQuery(".first-event-banner .col-sm-4").matchHeight();
			
			
			jQuery(".event-drop .size24").matchHeight();
			
			jQuery(".event-drop").matchHeight();
			
			jQuery(".mission-img.mb25").matchHeight();
			
			jQuery(".wk-mp-sellerlist-wrap").matchHeight();
			
			jQuery(".footer.content .block .block-title").matchHeight();
			
			
		
		
			jQuery(".event-drop span.blg-cat").matchHeight();
			
			jQuery(".wk_mp_design .product-item-name").matchHeight();
			jQuery(".wk-sellerlist-divide2 a").matchHeight();
			jQuery(".wk-sellerlist-divide2 a strong").matchHeight();
			
			
			jQuery(".cms-home .artis-name .size25 strong").matchHeight();
			jQuery(".event-list-wrapper .event-list-holder label.event-address").matchHeight();
			jQuery(".event-list-wrapper .event-list-holder").matchHeight();
			jQuery("#amasty-shopby-product-list .col-md-4").matchHeight();
			
			
			
		
		}
		MatchHeight();
		
	});


	jQuery(window).scroll(function() {
		if(jQuery('#back2Top').length)
		{
		if (window.pageYOffset > 200) {
			document.getElementById('back2Top').style.display = "flex";
		  } else {
			document.getElementById('back2Top').style.display = "none";
		  }
		}
		  
		if (jQuery(this).scrollTop() > 100){  
		 	jQuery('.page-header').addClass("sticky");
		   }
		else{
		 	jQuery('.page-header').removeClass("sticky");
		   }
		
		
	});
	
	
		
	function equalHeight(){
		jQuery('.e-height-cnt').each(function(){
			var highestBox = 0;
				jQuery('.e-height-element', this).each(function(){
					if(jQuery(this).outerHeight() > highestBox) {
						highestBox = jQuery(this).outerHeight(); 
					}
				});
				jQuery('.e-height-element',this).css("min-height", highestBox);
			}); 
		}

		equalHeight();

		jQuery(document).ready(function(){
			equalHeight();
		});

		jQuery(window).scroll(function() {
			equalHeight();
		});

		jQuery(window).resize(function(){
			equalHeight();
		});
		jQuery(window).on("load",function(){

		equalHeight();

	});
	
	
	
});