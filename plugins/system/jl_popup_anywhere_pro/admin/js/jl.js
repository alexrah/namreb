jQuery.noConflict();
window.addEvent("domready",function(){
	jQuery('.jl_color').ColorPicker({
		color: '#0000ff',
		onShow: function (colpkr) {
			jQuery(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			jQuery(colpkr).fadeOut(500);
			return false;
		},
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).val("#"+hex);
			//jQuery(el).css('background',jQuery(el).val())
			jQuery(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		jQuery(this).ColorPickerSetColor(this.value);
	});
	jQuery('select:not(.jlmenu)').chosen({
		disable_search_threshold : 10,
		allow_single_deselect : true
	});
});
(function($){
 	$.fn.extend({
 		jlchosen2: function() {
    		return this.each(function() {
				//Thêm mã xử lý ở đây
    		});
    	}
	});
})(jQuery);