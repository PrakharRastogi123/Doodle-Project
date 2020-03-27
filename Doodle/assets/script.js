var timer;
$(document).ready(function() {

	$(".result").on("click",function(){//line 46 in SiteResultsProvider.php
		//console.log("I was clicked");
		var url = $(this).attr("href");//we get the url from .result object
		var id = $(this).attr("data-linkId");
		if (!id) {
			alert("data-linkId attribute not found");
		}
		increaseLinkClicks(id,url);
		// we have first updated the clicks and we do the redirection manually 
		return false;//the default action to redirect to clicked url page is now falsed 
	
	});
	var grid = $(".imageResults");

	grid.on("layoutComplete",function(){
		$(".gridItem img").css("visibility", "visible");//images will be visible after loading only
	});

	grid.masonry({//masonry view of images
		itemSelector:".gridItem",
		columnWidth:20,
		gutter:5,
		isInitLayout: false
	});

	$("[data-fancybox]").fancybox({						//for preview of each image
		caption : function( instance, item ) {			// preview details
        var caption = $(this).data('caption') || '';
        var siteUrl = $(this).data('siteurl') || '';
        if ( item.type === 'image' ) {
            caption = (caption.length ? caption + '<br />' : '')
             + '<a href="' + item.src + '">View image</a><br>' 
             + '<a href="' + siteUrl + '">View page</a>';
        }

        return caption;
    },
    afterShow : function( instance, item ) {          // after showing images do this---
    	increaseImageClicks(item.src);
    }   
	});
});
function loadImage(src,className){

	var image = $("<img>");//created image obj using jQuerry
	image.on("load",function(){
		//creating same structure as lines 64-69 in ImageResultsProvider.php using js
		$("." + className + " a").append(image);//.className a(anchor tag inside className)+<img src="">
		clearTimeout(timer);
		timer = setTimeout(function(){
			$(".imageResults").masonry();//recall masonry again for dynamic image loadings after timer. 
		},500);
	});

	image.on("error",function(){//image is broken
		$("."+className).remove();
		$.post("ajax/setBroken.php",{src: src});
	});

	image.attr("src",src); //adding src attribute in <img src="">
}
function increaseLinkClicks(linkId, url){
	$.post("ajax/updateLinkCount.php",{linkId: linkId})
	.done(function(result){//manual redirection to clicked url after ajax call (using ..done)
		if(result != "" ){
			alert(result);
			return;
		}
		window.location.href=url;//redirected to that url page
	});
}
function increaseImageClicks(imageUrl){
	$.post("ajax/updateImageCount.php",{imageUrl: imageUrl})
	.done(function(result){
		if(result != "" ){
			alert(result);
			return;
		}

	});

}