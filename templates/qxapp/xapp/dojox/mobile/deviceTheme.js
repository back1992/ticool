//>>built
("undefined"===typeof define?function(e,a){a()}:define)(["dojo/_base/config","dojo/_base/lang","dojo/_base/window","require"],function(e,a,g,o){var d=a&&a.getObject("dojox.mobile",!0)||{},a=new function(){if(!g)g=window,g.doc=document,g._no_dojo_dm=d;e=e||g.mblConfig||{};for(var a=g.doc.getElementsByTagName("script"),l=0;l<a.length;l++){var m=a[l],k=m.getAttribute("src")||"";if(k.match(/\/deviceTheme\.js/i)){e.baseUrl=k.replace("deviceTheme.js","../../dojo/");if(a=m.getAttribute("data-dojo-config")||
m.getAttribute("djConfig")){var a=eval("({ "+a+" })"),n;for(n in a)e[n]=a[n]}break}else if(k.match(/\/dojo\.js/i)){e.baseUrl=k.replace("dojo.js","");break}}this.loadCssFile=function(a){var b=g.doc.createElement("link");b.href=a;b.type="text/css";b.rel="stylesheet";a=g.doc.getElementsByTagName("head")[0];a.insertBefore(b,a.firstChild);d.loadedCssFiles.push(b)};this.toUrl=function(a){return o?o.toUrl(a):e.baseUrl+"../"+a};this.setDm=function(a){d=a};this.themeMap=e.themeMap||[["Holodark","holodark",
[]],["Android 3","holodark",[]],["Android 4","holodark",[]],["Android","android",[]],["BlackBerry","blackberry",[]],["BB10","blackberry",[]],["iPhone","iphone",[]],["iPad","iphone",[this.toUrl("dojox/mobile/themes/iphone/ipad.css")]],["MSIE 10","windows",[]],["WindowsPhone","windows",[]],["Custom","custom",[]],[".*","iphone",[]]];d.loadedCssFiles=[];this.loadDeviceTheme=function(a){var b=e.mblThemeFiles||d.themeFiles||["@theme"],f,c;c=this.themeMap;var h=a||e.mblUserAgent||(location.search.match(/theme=(\w+)/)?
RegExp.$1:navigator.userAgent);for(f=0;f<c.length;f++)if(h.match(RegExp(c[f][0]))){var h=c[f][1],i=g.doc.documentElement.className,i=i.replace(RegExp(" *"+d.currentTheme+"_theme"),"")+" "+h+"_theme";g.doc.documentElement.className=i;d.currentTheme=h;f=[].concat(c[f][2]);for(c=0;c<b.length;c++){var j=b[c]instanceof Array||"array"==typeof b[c];!j&&-1!==b[c].indexOf("/")?i=b[c]:(i=j?(b[c][0]||"").replace(/\./g,"/"):"dojox/mobile",j=(j?b[c][1]:b[c]).replace(/\./g,"/"),i=i+"/"+("themes/"+h+"/"+("@theme"===
j?h:j)+".css"));f.unshift(this.toUrl(i))}for(b=0;b<d.loadedCssFiles.length;b++)h=d.loadedCssFiles[b],h.parentNode.removeChild(h);d.loadedCssFiles=[];for(c=0;c<f.length;c++)b=f[c].toString(),!0==e["dojo-bidi"]&&-1==b.indexOf("_rtl")&&-1!="android.css blackberry.css custom.css iphone.css holodark.css base.css Carousel.css ComboBox.css IconContainer.css IconMenu.css ListItem.css RoundRectCategory.css SpinWheel.css Switch.css TabBar.css ToggleButton.css ToolBarButton.css".indexOf(b.substr(b.lastIndexOf("/")+
1))&&this.loadCssFile(b.replace(".css","_rtl.css")),this.loadCssFile(f[c].toString());a&&d.loadCompatCssFiles&&d.loadCompatCssFiles();break}}};a.loadDeviceTheme();return window.deviceTheme=d.deviceTheme=a});