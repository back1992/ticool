//>>built
define("dojox/mobile/_DataMixin",["dojo/_base/kernel","dojo/_base/array","dojo/_base/declare","dojo/_base/lang","dojo/_base/Deferred"],function(f,g,h,e,i){f.deprecated("dojox/mobile/_DataMixin","Use dojox/mobile/_StoreMixin instead","2.0");return h("dojox.mobile._DataMixin",null,{store:null,query:null,queryOptions:null,setStore:function(a,b,d){if(a===this.store)return null;this.store=a;this._setQuery(b,d);if(a&&a.getFeatures()["dojo.data.api.Notification"])g.forEach(this._conn||[],this.disconnect,
this),this._conn=[this.connect(a,"onSet","onSet"),this.connect(a,"onNew","onNew"),this.connect(a,"onDelete","onDelete"),this.connect(a,"close","onStoreClose")];return this.refresh()},setQuery:function(a,b){this._setQuery(a,b);return this.refresh()},_setQuery:function(a,b){this.query=a;this.queryOptions=b||this.queryOptions},refresh:function(){if(!this.store)return null;var a=new i,b=e.hitch(this,function(b,c){this.onComplete(b,c);a.resolve()}),d=e.hitch(this,function(b,c){this.onError(b,c);a.resolve()}),
c=this.query;this.store.fetch({query:c,queryOptions:this.queryOptions,onComplete:b,onError:d,start:c&&c.start,count:c&&c.count});return a}})});