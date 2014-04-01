function beautify(){
setTimeout(function(){
        jQuery('tr#listStripes').each(function(index){
          console.log(index);
          jQuery(this).delay(100 * index).show(100);
        });
}, 500);
}
