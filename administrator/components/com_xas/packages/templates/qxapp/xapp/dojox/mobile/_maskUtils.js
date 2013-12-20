//>>built
define("dojox/mobile/_maskUtils",["dojo/_base/window","dojo/dom-style","./sniff"],function(m,p,q){var o={};return{createRoundMask:function(l,c,d,b,j,g,e,a,f,i){var b=c+g+b,k=d+e+j;if(m&&m.doc&&null!=m.doc.getCSSCanvasContext){j=("DojoMobileMask"+c+d+g+e+a+f).replace(/\./g,"_");if(!o[j])o[j]=1,b=m.doc.getCSSCanvasContext("2d",j,b,k),b.beginPath(),a==f?2==a&&5==g?(b.fillStyle="rgba(0,0,0,0.5)",b.fillRect(1,0,3,2),b.fillRect(0,1,5,1),b.fillRect(0,e-2,5,1),b.fillRect(1,e-1,3,2),b.fillStyle="rgb(0,0,0)",
b.fillRect(0,2,5,e-4)):2==a&&5==e?(b.fillStyle="rgba(0,0,0,0.5)",b.fillRect(0,1,2,3),b.fillRect(1,0,1,5),b.fillRect(g-2,0,1,5),b.fillRect(g-1,1,2,3),b.fillStyle="rgb(0,0,0)",b.fillRect(2,0,g-4,5)):(b.fillStyle="#000000",b.moveTo(c+a,d),b.arcTo(c,d,c,d+a,a),b.lineTo(c,d+e-a),b.arcTo(c,d+e,c+a,d+e,a),b.lineTo(c+g-a,d+e),b.arcTo(c+g,d+e,c+g,d+a,a),b.lineTo(c+g,d+a),b.arcTo(c+g,d,c+g-a,d,a)):(e=Math.PI,b.scale(1,f/a),b.moveTo(c+a,d),b.arc(c+a,d+a,a,1.5*e,0.5*e,!0),b.lineTo(c+g-a,d+2*a),b.arc(c+g-a,d+
a,a,0.5*e,1.5*e,!0)),b.closePath(),b.fill();l.style.webkitMaskImage="-webkit-canvas("+j+")"}else if(q("svg")){l._svgMask&&l.removeChild(l._svgMask);for(var n=null,h=l.parentNode;h&&(!(n=p.getComputedStyle(h).backgroundColor)||!("transparent"!=n&&!n.match(/rgba\(.*,\s*0\s*\)/)));h=h.parentNode);h=m.doc.createElementNS("http://www.w3.org/2000/svg","svg");h.setAttribute("width",b);h.setAttribute("height",k);h.style.position="absolute";h.style.pointerEvents="none";h.style.opacity="1";h.style.zIndex="2147483647";
k=m.doc.createElementNS("http://www.w3.org/2000/svg","path");i=i||0;a+=i;f+=i;c=" M"+(c+a-i)+","+(d-i)+" a"+a+","+f+" 0 0,0 "+-a+","+f+" v"+-f+" h"+a+" Z M"+(c-i)+","+(d+e-f+i)+" a"+a+","+f+" 0 0,0 "+a+","+f+" h"+-a+" v"+-f+" z M"+(c+g-a+i)+","+(d+e+i)+" a"+a+","+f+" 0 0,0 "+a+","+-f+" v"+f+" h"+-a+" z M"+(c+g+i)+","+(d+f-i)+" a"+a+","+f+" 0 0,0 "+-a+","+-f+" h"+a+" v"+f+" z";0<d&&(c+=" M0,0 h"+b+" v"+d+" h"+-b+" z");0<j&&(c+=" M0,"+(d+e)+" h"+b+" v"+j+" h"+-b+" z");k.setAttribute("d",c);k.setAttribute("fill",
n);k.setAttribute("stroke",n);k.style.opacity="1";h.appendChild(k);l._svgMask=h;l.appendChild(h)}}}});