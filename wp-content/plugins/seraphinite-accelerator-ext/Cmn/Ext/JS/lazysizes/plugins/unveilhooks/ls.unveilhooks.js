/*
This plugin extends lazySizes to lazyLoad:
background images, videos/posters and scripts

Background-Image:
For background images, use data-bg attribute:
<div class="lazyload" data-bg="bg-img.jpg"></div>

 Video:
 For video/audio use data-poster and preload="none":
 <video class="lazyload" preload="none" data-poster="poster.jpg" src="src.mp4">
 <!-- sources -->
 </video>

 For video that plays automatically if in view:
 <video
	class="lazyload"
	preload="none"
	muted=""
	data-autoplay=""
	data-poster="poster.jpg"
	src="src.mp4">
</video>

 Scripts:
 For scripts use data-script:
 <div class="lazyload" data-script="module-name.js"></div>


 Script modules using require:
 For modules using require use data-require:
 <div class="lazyload" data-require="module-name"></div>
*/

(function(window, factory) {
	var globalInstall = function(){
		factory(window.lzl_lazySizes);
		window.removeEventListener('lzl-lazyunveilread', globalInstall, true);
	};

	factory = factory.bind(null, window, window.document);

	if(typeof module == 'object' && module.exports){
		factory(require('lazysizes'));
	} else if (typeof define == 'function' && define.amd) {
		define(['lazysizes'], factory);
	} else if(window.lzl_lazySizes) {
		globalInstall();
	} else {
		window.addEventListener('lzl-lazyunveilread', globalInstall, true);
	}
}(window, function(window, document, lazySizes) {
	/*jshint eqnull:true */
	'use strict';
	var bgLoad, regBgUrlEscape;
	var uniqueUrls = {};

	if(document.addEventListener){
		regBgUrlEscape = /\(|\)|\s|'/;

		bgLoad = function (url, cb){
			var img = document.createElement('img');
			//img.fetchPriority = "high";
			img.onload = function(){
				img.onload = null;
				img.onerror = null;
				img = null;
				cb();
			};
			img.onerror = img.onload;

			img.src = url;

			if(img && img.complete && img.onload){
				img.onload();
			}
		};

		addEventListener('lzl-lazybeforeunveil', function(e){
			if(e.detail.instance != lazySizes){return;}

			var tmp, load, bg, poster;
			if(!e.defaultPrevented) {

				var target = e.target;

				if(target.preload == 'none'){
					target.preload = target.getAttribute('data-lzl-preload') || 'auto';
				}

				if (target.getAttribute('data-lzl-autoplay') != null) {
					if (target.getAttribute('data-lzl-expand') && !target.autoplay) {
						try {
							target.play();
						} catch (er) {}
					} else {
						requestAnimationFrame(function () {
							target.setAttribute('data-lzl-expand', '-10');
							lazySizes.aC(target, lazySizes.cfg.lazyClass);
						});
					}
				}

				tmp = target.getAttribute('data-lzl-link');
				if(tmp && target.tagName.toLowerCase() != 'img'){
					addStyleScript(tmp, true);
				}

				// handle data-script
				tmp = target.getAttribute('data-lzl-script');
				if(tmp){
					e.detail.firesLoad = true;
					load = function(){
						e.detail.firesLoad = false;
						lazySizes.fire(target, '_lzl-lazyloaded', {}, true, true);
					};
					addStyleScript(tmp, null, load);
				}

				// handle data-require
				tmp = target.getAttribute('data-lzl-require');
				if(tmp){
					if(lazySizes.cfg.requireJs){
						lazySizes.cfg.requireJs([tmp]);
					} else {
						addStyleScript(tmp);
					}
				}
				
				if( lazySizes.cfg.beforeUnveil )
					lazySizes.cfg.beforeUnveil( target, lazySizes );

				// handle data-bg
				var bgFromStyle;
				bg = target.getAttribute('data-lzl-bg');
				if( bg || target.hasAttribute( "data-lzl-bg" ) )
				{
					bgFromStyle = getComputedStyle( target ).getPropertyValue( "--lzl-bg-img" );
					if( bgFromStyle && ( bgFromStyle[ 0 ] == "\"" || bgFromStyle[ 0 ] == "'" ) )
						bgFromStyle = bgFromStyle.substr( 1, bgFromStyle.length - 2 );
				}
				//if( !bg && target.hasAttribute( "data-lzl-bg" ) )
				//{
				//	bgFromStyle = true;
				//	var bgSt = getComputedStyle( target ).getPropertyValue( "--lzl-bg-img" );
				//	if( bgSt )
				//		bg = bgSt.substr( 1, bgSt.length - 2 );
				//}
				if (bgFromStyle || bg) {
					target.classList.add( "lzl-ing" );
					e.detail.firesLoad = true;
					load = function(){
						//setTimeout(function(){
						if( bg )
						{
							var backgroundImage = target.style.backgroundImage;
							if( typeof( backgroundImage ) !== "string" || !backgroundImage.trim() )
								backgroundImage = "url()";
							target.style.backgroundImage = backgroundImage.replace( /url\([^\(\)]*\)/, 'url(' + (regBgUrlEscape.test(bg) ? JSON.stringify(bg) : bg ) + ')' );
						}
						target.classList.remove( "lzl-ing" );
						e.detail.firesLoad = false;
						lazySizes.fire(target, '_lzl-lazyloaded', {}, true, true);
						//}, 2000);
					};

					bgLoad(bgFromStyle ? bgFromStyle : bg, load);
				}

				// handle data-poster
				poster = target.getAttribute('data-lzl-poster');
				if(poster){
					e.detail.firesLoad = true;
					load = function(){
						target.poster = poster;
						e.detail.firesLoad = false;
						lazySizes.fire(target, '_lzl-lazyloaded', {}, true, true);
					};

					bgLoad(poster, load);

				}
			}
		}, false);

	}

	function addStyleScript(src, style, cb){
		if(uniqueUrls[src]){
			return;
		}
		var elem = document.createElement(style ? 'link' : 'script');
		var insertElem = document.getElementsByTagName('script')[0];

		if(style){
			elem.rel = 'stylesheet';
			elem.href = src;
		} else {
			elem.onload = function(){
				elem.onerror = null;
				elem.onload = null;
				cb();
			};
			elem.onerror = elem.onload;

			elem.src = src;
		}
		uniqueUrls[src] = true;
		uniqueUrls[elem.src || elem.href] = true;
		insertElem.parentNode.insertBefore(elem, insertElem);
	}
}));
