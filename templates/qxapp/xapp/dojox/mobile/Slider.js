//>>built
define("dojox/mobile/Slider","dojo/_base/array,dojo/_base/connect,dojo/_base/declare,dojo/_base/lang,dojo/_base/window,dojo/sniff,dojo/dom-class,dojo/dom-construct,dojo/dom-geometry,dojo/dom-style,dojo/keys,dojo/touch,dijit/_WidgetBase,dijit/form/_FormValueMixin".split(","),function(p,t,u,j,r,x,d,c,s,k,g,l,v,w){return u("dojox.mobile.Slider",[v,w],{value:0,min:0,max:100,step:1,baseClass:"mblSlider",flip:!1,orientation:"auto",halo:"8pt",buildRendering:function(){if(!this.templateString){this.focusNode=
this.domNode=c.create("div",{});this.valueNode=c.create("input",this.srcNodeRef&&this.srcNodeRef.name?{type:"hidden",name:this.srcNodeRef.name}:{type:"hidden"},this.domNode,"last");var b=c.create("div",{style:{position:"relative",height:"100%",width:"100%"}},this.domNode,"last");this.progressBar=c.create("div",{style:{position:"absolute"},"class":"mblSliderProgressBar"},b,"last");this.touchBox=c.create("div",{style:{position:"absolute"},"class":"mblSliderTouchBox"},b,"last");this.handle=c.create("div",
{style:{position:"absolute"},"class":"mblSliderHandle"},b,"last")}this.inherited(arguments);if("undefined"!=typeof this.domNode.style.msTouchAction)this.domNode.style.msTouchAction="none"},_setValueAttr:function(b,c){b=Math.max(Math.min(b,this.max),this.min);this.valueNode.value=b;this.inherited(arguments);if(this._started){this.focusNode.setAttribute("aria-valuenow",b);var h=100*(b-this.min)/(this.max-this.min);!0===c?(d.add(this.handle,"mblSliderTransition"),d.add(this.progressBar,"mblSliderTransition")):
(d.remove(this.handle,"mblSliderTransition"),d.remove(this.progressBar,"mblSliderTransition"));k.set(this.handle,this._attrs.handleLeft,(this._reversed?100-h:h)+"%");k.set(this.progressBar,this._attrs.width,h+"%")}},postCreate:function(){function b(a){function b(a){c=g?a[this._attrs.pageX]:a.touches?a.touches[0][this._attrs.pageX]:a[this._attrs.clientX];h=c-o;h=Math.min(Math.max(h,0),m);a=this.step?(this.max-this.min)/this.step:m;if(1>=a||Infinity==a)a=m;i=(this.max-this.min)*Math.round(h*a/m)/a;
i=this._reversed?this.max-i:this.min+i}a.preventDefault();var g="mousedown"==a.type,f=s.position(n,!1),d=k.get(r.body(),"zoom")||1;isNaN(d)&&(d=1);var e=k.get(n,"zoom")||1;isNaN(e)&&(e=1);var o=f[this._attrs.x]*e*d+s.docScroll()[this._attrs.x],m=f[this._attrs.w]*e*d;j.hitch(this,b)(a);a.target==this.touchBox&&this.set("value",i,!0);p.forEach(q,t.disconnect);var a=r.doc.documentElement,q=[this.connect(a,l.move,function(a){a.preventDefault();j.hitch(this,b)(a);this.set("value",i,!1)}),this.connect(a,
l.release,function(a){a.preventDefault();p.forEach(q,j.hitch(this,"disconnect"));q=[];this.set("value",this.value,!0)})]}this.inherited(arguments);var c,h,i,n=this.domNode;if("auto"==this.orientation)this.orientation=n.offsetHeight<=n.offsetWidth?"H":"V";d.add(this.domNode,p.map(this.baseClass.split(" "),j.hitch(this,function(a){return a+this.orientation})));var f="V"!=this.orientation,o=f?this.isLeftToRight():!1,e=!!this.flip;this._reversed=!(f&&(o&&!e||!o&&e)||!f&&e);this._attrs=f?{x:"x",w:"w",
l:"l",r:"r",pageX:"pageX",clientX:"clientX",handleLeft:"left",left:this._reversed?"right":"left",width:"width"}:{x:"y",w:"h",l:"t",r:"b",pageX:"pageY",clientX:"clientY",handleLeft:"top",left:this._reversed?"bottom":"top",width:"height"};this.progressBar.style[this._attrs.left]="0px";this.connect(this.touchBox,l.press,b);this.connect(this.handle,l.press,b);this.connect(this.domNode,"onkeypress",function(a){if(!this.disabled&&!this.readOnly&&!a.altKey&&!a.ctrlKey&&!a.metaKey){var b=this.step,c=1;switch(a.keyCode){case g.HOME:b=
this.min;break;case g.END:b=this.max;break;case g.RIGHT_ARROW:c=-1;case g.LEFT_ARROW:b=this.value+c*(e&&f?b:-b);break;case g.DOWN_ARROW:c=-1;case g.UP_ARROW:b=this.value+c*(!e||f?b:-b);break;default:return}a.preventDefault();this._setValueAttr(b,!1)}});this.connect(this.domNode,"onkeyup",function(a){!this.disabled&&!this.readOnly&&!a.altKey&&!a.ctrlKey&&!a.metaKey&&this._setValueAttr(this.value,!0)});this.startup();this.set("value",this.value)}})});