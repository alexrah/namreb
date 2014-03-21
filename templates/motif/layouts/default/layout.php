<?php
defined( '_JEXEC' ) or die;
JHtml::_('behavior.framework', true);
$document =JFactory::getDocument();
$app = JFactory::getApplication();
$menu = $app->getMenu();
if ($menu->getActive() == $menu->getDefault()) {
        $pageview = 'frontpage';
} else{ 
        $pageview = 'innerpage';
}
$user =JFactory::getUser();
$params = $templateParameters->group->$layout; // We got $layout from the index.php
// Use the Grid parameters to compute the main columns width
$grid = $params->xtcgrid;
$style = $params->xtcstyle;
$typo = $params->xtctypo;

//Group parameters from grid.xml
$gridParams = $templateParameters->group->$grid;
$styleParams = $templateParameters->group->$style;
$typoParams = $templateParameters->group->$typo;
$tmplWidth = 100;
// Start of HEAD
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="stylesheet" href="<?php echo $xtc->templateUrl; ?>css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $xtc->templateUrl; ?>css/bootstrap-responsive.min.css" type="text/css" />
<?php
// Include the CSS files using the groups as defined in the layout parameters
echo xtcCSS($params->xtctypo,$params->xtcgrid,$params->xtcstyle);

$document->addStyleSheet( $xtc->templateUrl.'css/css3effects.css', 'text/css');

// Get Xtc Menu library
$document->addScript($xtc->templateUrl.'js/xtcMenu.js'); 
$document->addScriptDeclaration("window.addEvent('load', function(){ xtcMenu(null, 'menu', 200, 50, 'h', new Fx.Transition(Fx.Transitions.Cubic.easeInOut), 90, false, false); });");
// Get the Template General Scripts file
// $document->addScript($xtc->templateUrl.'js/scripts.js');
//JHtml::script(JURI::base(). "plugins/user/profile/profiles/profile.js", $mootools= true)
//
?>
	<script src="http://code.jquery.com/jquery-latest.js" type="text/javascript"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
<script src="<?php echo $xtc->templateUrl; ?>js/bootstrap.js" type="text/javascript"></script>
<jdoc:include type="head" />
 

</head>
<?php
// End of HEAD
// Start of BODY
?>
<body id="bttop" class="<?php echo $pageview;?>">
  
<div id="headerwrap" class="xtc-bodygutter">
    <div id="headerpad" class="xtc-wrapperpad">
<div id="header" class="clearfix xtc-wrapper row-fluid header">

<div id="logo"><a class="hideTxt" href="index.php"><?php echo $app->getCfg('sitename');?></a></div>
<div id="menuwrap">
        <?php if ($this->countModules('menuright2')) : ?>
       <div id="menuright2">                           
        <jdoc:include type="modules" name="menuright2" style="none"/>
       </div>
<?php endif; ?> 
    <?php if ($this->countModules('menuright1')) : ?>
       <div id="menuright1">                           
        <jdoc:include type="modules" name="menuright1" style="none"/>
       </div>
<?php endif; ?> 
<div id="menu" class="clearfix"><jdoc:include type="modules" name="menubarleft" style="raw" />	</div>

</div>
</div>
</div>
    </div>
   </div>
   


<?php
			// Draw the regions in the specified order
			$regioncfg = $gridParams->regioncfg;
			foreach (explode(",",$regioncfg) as $region) {
				if ($region == '') continue;
				require 'layout_includes/region'.$region.'.php';
			}
?>
<?php if ($this->countModules('footer')) : ?>
		<div id="footerwrap" class="xtc-bodygutter">
                <div id="footerwrappad" class="xtc-wrapperpad">
		<div id="footerpad" class="row-fluid xtc-wrapper"> <jdoc:include type="modules" name="footer" style="xtc"/></div>
	    </div>
                </div>
    
<?php endif; ?> 
<jdoc:include type="modules" name="debug" />
</body>
</html>
<?php
// End of BODY
?>
