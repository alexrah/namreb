var $j = jQuery.noConflict();
jQuery(document).ready(function(){
$j("input[value='Azienda']").click(function(){
  if($j(this).is(":checked")){
     $j("#jform_profile_address2-lbl").slideDown();
     $j("#jform_profile_address2").slideDown();
     $j("#jform_profile_address2").addClass('required');
  // } else {
    $j("#jform_profile_address1-lbl").slideUp();
    $j("#jform_profile_address1").slideUp();
     $j("#jform_profile_address1").removeClass('required');
  }
});

$j("input[value='Privato']").click(function(){
  if($j(this).is(":checked")){
     $j("#jform_profile_address1-lbl").slideDown();
     $j("#jform_profile_address1").slideDown();
     $j("#jform_profile_address1").addClass('required');
  // } else {
    $j("#jform_profile_address2-lbl").slideUp();
    $j("#jform_profile_address2").slideUp();
     $j("#jform_profile_address2").removeClass('required');
  }
});

});
