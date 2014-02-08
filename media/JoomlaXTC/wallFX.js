/*
  JoomlaXTC responsiveFX

  version 1.0.1

  Copyright (C) 2012  Monev Software LLC.

  All Rights Reserved.

  THIS PROGRAM IS NOT FREE SOFTWARE

  You shall not modify, copy, duplicate, reproduce, sell, license or
  sublicense the Software, or transfer or convey the Software or
  any right in the Software to anyone else without the prior
  written consent of Developer; provided that Licensee may make
  one copy of the Software for backup or archival purposes.

  Monev Software LLC
  www.joomlaxtc.com
*/

if (typeof wallFX != 'function') {
  function wallFX(id, o) {

    var dix = $(id); if (typeof dix == 'undefined') { return; }
    var wallview = dix.getElementById('wallview' + id); if (typeof wallview == 'undefined') { return; }
    var wallslider = wallview.getElementById('wallslider' + id);

		var currentPage = parseInt(o.slidestart);

		var prevbtn = dix.getElementById('prev' + id);
		var nextbtn = dix.getElementById('next' + id);
		var pagebtns = dix.getElements('.pag' + id);

    var wallpages = wallslider.getElements('.wallpage');
    var wallelements = wallslider.getElements('.wallelement');

		function setView() {
	    wallpages.setStyle('width',wallview.getSize().x+'px'); /* set table widths to view size */
			var maxH = 0;
	    wallpages.each(function(f,i) { /* get tallest page */
				var H = f.getSize().y;
	      if (H > maxH) { maxH = H; }
	    })
	    wallview.setStyles({'height':maxH+'px','background':'none'}); /* set view to best height */
		}
		
		if (o.fxmode == 'fade') { // Setup fade
			wallpages.setStyles({'position':'absolute','top':0,'left':0,'opacity':0});
	    wallpages.each(function(f){
	 			f.set('tween', {
			    duration: o.fxspeed,
			    transition: o.fxtype,
			    link: 'cancel'
				});
			});
			wallpages[currentPage].setStyle('opacity',1);
		}
		
		/* Setup display */
		setView();
		
		window.addEvent('resize', function() {  /* reset tables size on redraws */
			setView();
		});

		/* Turn on display */
		dix.setStyle('z-index',o.fxlayer);
    wallslider.setStyle('visibility','visible');
		wallslider.set('tween', {
	    duration: o.fxspeed,
	    transition: o.fxtype,
	    link: 'cancel'
		});
		updateBtns();

    /* Common action button functions */
		function updateBtns() {
			if (wallpages.length == 1) {
				if (prevbtn) { prevbtn.setStyle('opacity',.5); }
				if (nextbtn) { nextbtn.setStyle('opacity',.5); }
			}
			else if (currentPage == 0) {
				if (prevbtn) { prevbtn.setStyle('opacity',.5); }
				if (nextbtn) { nextbtn.setStyle('opacity',1); }
			}
			else if (currentPage == wallpages.length - 1) {
				if (prevbtn) { prevbtn.setStyle('opacity',1); }
				if (nextbtn) { nextbtn.setStyle('opacity',.5); }
			}
			else {
				if (prevbtn) { prevbtn.setStyle('opacity',1); }
				if (nextbtn) { nextbtn.setStyle('opacity',1); }
			}
		}
		
		function slide(i) {
			switch (o.fxmode) {
				case 'slideVer': edge = 'top'; viewsize = wallview.getSize().y; break;
				case 'slideHor': edge = 'left'; viewsize = wallview.getSize().x; break;
			}
			currentPage = i;
			newpos = (currentPage * viewsize * -1);
			wallslider.tween(edge, newpos);
		}

		function fade(i) {
			dix.oldPage = currentPage;
			currentPage = i;
			wallpages[dix.oldPage].fade('out');
			wallpages[currentPage].fade('in');
		}

		function goToPage(i) { // Page switch function
			if (i >= 0 && i < wallpages.length) {
				if (typeof animationloop == 'number') { clearInterval(animationloop); }
				switch (o.fxmode) {
					case 'fade': fade(i); break;
					case 'slideHor': slide(i); break;
					case 'slideVer': slide(i); break;
				}
				updateBtns();
			}
		}

		/* enable button & page events if needed */
 	  if (wallpages.length > 1) {

			var loop = function() {
				if (currentPage == wallpages.length -1) { dix.loopdirection = -1; /* clearInterval('animationloop'); return; */ }
				if (currentPage == 0) { dix.loopdirection = 1; }
				i = currentPage + dix.loopdirection;
				switch (o.fxmode) {
					case 'fade': fade(i); break;
					case 'slideHor': slide(i); break;
					case 'slideVer': slide(i); break;
				}
				updateBtns();
			};
     	if (typeof o.fxpause !== 'undefined' && o.fxpause >= 0) { animationloop = loop.periodical(o.fxpause);  }

			if (prevbtn) { prevbtn.addEvent('click', function(e) { goToPage(currentPage -1); }); }
			if (nextbtn) { nextbtn.addEvent('click', function(e) { goToPage(currentPage +1); }); }
	    pagebtns.each(function(pag,i) { pag.addEvent('click', function(e) { goToPage(i); }); });

		}
  }
}


/*
 * Showcase Complements: JXTC HOVER
 */
if (typeof jxtchover != 'function'){
  function jxtchover(id,hi,ho){
    if($(id)){
      var ghover = $(id).getElements('.js_hover');
      ghover.each(function(el) {
        el.setStyles({'background-color':'#' + ho});
        var fx = new Fx.Morph(el, {duration:200, link:'cancel'});
        el.addEvent('mouseenter', function(){
          fx.start({
            'background-color': '#' + hi
          });
        });
        el.addEvent('mouseleave', function(){
          fx.start({
            'background-color': '#' + ho
          });
        });
      });
    }
  }
}


/*
 * Showcase Complements: JXTC POPS
 */
if (typeof jxtcpops != 'function') {
  function jxtcpops(id, o){
    var dix = $(id);
    if(dix){
      var popsh = dix.getElements('.popuphover');

      var vo = 0;
      var vi = 0;
      var ho = 0;
      var hi = 0;
  
      var box = new Element('div',{
        styles:{'opacity':0,'display':'none'}
      });
      box.inject(document.body);
      box.addClass('jxtcpopup');
  
      var inner = new Element('div');
      inner.addClass('jxtcinner');
  
      var x = new Element('div', {'title':'Close'});
      x.addClass('jxtcpopupclose');
      //x.innerHTML = '';
  
      var d = new Element('div', {'title':'Move'});
      d.addClass('jxtcpopupdrag');
      //d.innerHTML = '';
  
      x.inject(box);
      d.inject(box);
      inner.inject(box);
  
      var fx = new Fx.Morph(box,{duration:o.durationin,transition:o.fxtype,link:'cancel'});
  
      x.addEvent('click',function(){
        fx.start({
          'top':window.getScrollTop() + vo,
          'left':window.getScrollLeft() + ho,
          'opacity':o.opacityout
        }).chain(function(){
          box.setStyles({'display':'none'});
        });
      });
  
      popsh.each(function(p,i){
        var pop = p.getElement('.pop');
        pop.setStyles({'display':'none'});
  
      x.addEvent('click',function(){
      (function(){
        pop.setStyles({'display':'none'});
        p.adopt(pop);
      }).delay(o.durationin);
      });
  
        p.addEvent('click',function(){
  
          box.setStyles({
            'position':'absolute',
            'display':'block'
          });
          box.makeDraggable();
      inner.adopt(pop);
  
      pop.setStyles({'display':'block'});
  
          box.setStyles({
            'height':'auto',
            'top': window.getScrollTop() + o.verticalout + 'px',
            'left': window.getScrollLeft() + o.horizontalout + 'px'
          });
  
          if(o.centered=='1'){
            var bw = box.getSize().x;
            var bh = box.getSize().y;
  
            box.setStyles({
              'top': (window.getScrollTop()) + (window.getHeight() - box.getSize().y)/2 + 'px',
              'left': (window.getScrollLeft() + window.getWidth() - bw)/2 + 'px'
            });
  
            vo = vi = (window.getHeight() - box.getSize().y)/2;
            ho = hi = (window.getScrollLeft() + window.getWidth() - bw)/2;
  
          }
  
          fx.start({
            'top':window.getScrollTop() + vi,
            'left':window.getScrollLeft() + hi,
            'opacity':o.opacityin
          });
      inner.setStyles({'width': inner.getSize().x, 'height': inner.getSize().y});
  
        });
  
      });
    }
  }
}


/*
 * Showcase Complements: JXTC TIPS
 */
if (typeof jxtctips != 'function') {
  function jxtctips(id,options){
    var dix = $(id);
    if(dix){
      var triggers = dix.getElements('.jxtctooltip');
      triggers.each(function(t,i){
  
        var tip = t.getElement('.tip');
        if(tip != null) {
  
					tipx = t.getPosition(dix).x + 'px';
					tipy = t.getPosition(dix).y + 'px';

					dix.adopt(tip);
          tip.setStyles({'opacity':0,'display':'block','bottom':'auto','right':'auto','width':t.getSize().x+'px','overflow':'visible','z-index':9999,
          	'position':'absolute', 'left':tipx, 'top':tipy});
  
          var tfxi = new Fx.Morph(tip, {duration: options.durationin, transtion: options.fxtype, link:'cancel'});
          var tfxo = new Fx.Morph(tip, {duration: options.durationout, transtion: options.fxtype, link:'cancel'});
          var tfxp = new Fx.Morph(tip, {duration: options.pause,link: 'ignore'});
  
          t.addEvent('mouseenter', function(){
            tfxi.start({
              'opacity': options.opacityin,
              'top': t.getPosition(dix).y + options.verticalin + 'px',
              'left': t.getPosition(dix).x + options.horizontalin + 'px'
            });
          });

          t.addEvent('mouseleave', function(){
            tfxp.start({}).chain(function(){
              tfxo.start({
                'opacity': options.opacityout,
                'top' : t.getPosition(dix).y + options.verticalout + 'px',
                'left' : t.getPosition(dix).x + options.horizontalout + 'px'
              });
            });
          });
  
          tip.addEvent('mouseenter', function(){
            tfxp.pause();
            tfxi.start({
              'opacity': options.opacityin,
              'top': t.getPosition(dix).y + options.verticalin + 'px',
              'left': t.getPosition(dix).x + options.horizontalin + 'px'
            });
          });
  
        }
      });
    }
  }
}


/*
 * Showcase Complements: SLIDEBOX
 */
if (typeof slidebox != 'function') {
  function slidebox(id,sbfx,p,a){
    var dix = $(id);
    if(dix){
      var boxslides = dix.getElements('.slidebox');
      var pos = p;
  
      boxslides.each(function(b, i){
        b.setStyles({'overflow': 'hidden','position':'relative'});
        var slide = b.getElement('.slidepanel');
        slide.setStyles({'position': 'relative'});
  
        (function(){
          var s = b.getSize();
          switch(sbfx) {
            case 'RSO':
              pos.xi = s.x; pos.xo = 0; pos.yi = 0; pos.yo = 0;
              break;
            case 'RSI':
              pos.xo = s.x; pos.xi = 0; pos.yi = 0; pos.yo = 0;
              break;
            case 'LSO':
              pos.xi = -s.x; pos.xo = 0; pos.yi = 0; pos.yo = 0;
              break;
            case 'LSI':
              pos.xo = -s.x; pos,xi = 0; pos.yi = 0; pos.yo = 0;
              break;
            case 'BSO':
              pos.yi = s.y; pos.yo = 0; pos.xi = 0; pos.xo = 0;
              break;
            case 'BSI':
              pos.yo = s.y; pos.yi = 0; pos.xi = 0; pos.xo = 0;
              break;
            case 'TSO':
              pos.yo = s.y; pos.yi = 0; pos.xi = 0; pos.xo = 0;
              break;
            case 'TSI':
              pos.yo = -s.y; pos.yi = 0; pos.xi = 0; pos.xo = 0;
              break;
            case 'TRSO':
              pos.xi = s.x; pos.xo = 0; pos.yi = -s.y; pos.yo = 0;
              break;
            case 'TRSI':
              pos.xo = s.x; pos.xi = 0; pos.yo = -s.y; pos.yi = 0;
              break;
            case 'TLSO':
              pos.xi = -s.x; pos.xo = 0; pos.yi = -s.y; pos.yo = 0;
              break;
            case 'TLSI':
              pos.xo = -s.x; pos.xi = 0; pos.yo = -s.y; pos.yi = 0;
              break;
            case 'BRSO':
              pos.xi = s.x; pos.xo = 0; pos.yi = s.y; pos.yo = 0;
              break;
            case 'BRSI':
              pos.xo = s.x; pos.xi = 0; pos.yo = s.y; pos.yi = 0;
              break;
            case 'BLSO':
              pos.xi = -s.x; pos.xo = 0; pos.yi = s.y; pos.yo = 0;
              break;
            case 'BLSI':
              pos.xo = -s.x; pos.xi = 0; pos.yo = s.y; pos.yi = 0;
              break;
          }
  
          slide.setStyles({
            'top': pos.yo,
            'left': pos.xo
          });
        }).delay(100);
  
        var sfx = new Fx.Morph(slide,{duration:a.dura,fps:a.frames,transition:a.fxtype,link:'cancel'});
  
        b.addEvent('mouseenter', function(){
          sfx.start({
            'top': pos.yi,
            'left':pos.xi
          });
        });
        b.addEvent('mouseleave', function(){
          sfx.start({
            'top': pos.yo,
            'left':pos.xo
          });
        });
  
      });
    }
  }
}


/*
 * WallFX
 */
if (typeof wallfx != 'function') {
  function wallfx(id, w, h, mode){
    if($(id)){
      var mode = (typeof mode != 'undefined') ? mode : 0;
      var con = $(id);
      if (con) {
        var conSize = con.getSize();
        var inside = con.getElement('.showcase' + id);
        if (inside) {
          inside.setStyles({
            'position': 'relative',
            'display':'block',
            'visibility':'visible',
            'top':0,
            'left':0,
            'width': w + 'px',
            'height': h + 'px'
          });
          var inSize = inside.getSize();
          var divShow = inside.getElement('.sframe' + id);
          if(divShow){
            divShow.setStyles({
              'position':'absolute',
              'display':'block'
            });
            switch(mode)  {
            case 0:
            inside.setStyles({
              'overflow': 'hidden'
            });
            var table = divShow.getElement('.table' + id);
            var tSize = table.getSize();
            table.setStyles({
              'position':'relative',
              'width':tSize.x,
              'height':tSize.y,
              'top':tSize.y - h,
              'left':tSize.x - w
            });
            divShow.setStyles({
              'left': -(tSize.x - w),
              'top': -(tSize.y - h),
              'width': (tSize.x - w) + tSize.x,
              'height': (tSize.y - h) + tSize.y
            });
            table.makeDraggable({container:divShow});
            break;
            case 1:
            var table = divShow.getElement('.table' + id);
            var tSize = table.getSize();
            table.setStyles({
              'position':'relative',
              'top':0,
              'left':0
            });
            divShow.setStyles({
              'overflow': 'hidden',
              'position':'relative',
              'width': w,
              'height': h
            });
            var margenx = inSize.size.x * .05;
            var margeny = inSize.size.y * .05;
            var dimDiffsx = tSize.x - inSize.size.x;
            var dimDiffsy = tSize.y - inSize.size.y;
            var dimPropsx = dimDiffsx / (inSize.size.x - (margenx * 2));
            var dimPropsy = dimDiffsy / (inSize.size.y - (margeny * 2));
  
            inside.addEvent('mousemove', function(event){
              var event = new Event(event);
              var mposx = event.page.x - inside.getPosition().x;
              var mposy = event.page.y - inside.getPosition().y;
              var newx = parseInt(dimPropsx * (mposx - margenx));
              if (newx < 0) {newx = 0};
              if (newx > tSize.x - inSize.size.x) {newx = tSize.x - inSize.size.x};
              var newy = parseInt(dimPropsy * (mposy - margeny));
              if (newy < 0) {newy = 0};
              if (newy > tSize.y - inSize.size.y) {newy = tSize.y - inSize.size.y};
              table.style.left  = -newx + 'px';
              table.style.top  = -newy + 'px';
            });
            break;
            }
          }
        }
      }
    }
  }
}