function asyncGeneratorStep(J,x,O,K,L,H,F){try{var M=J[H](F),G=M.value}catch(P){O(P);return}M.done?x(G):Promise.resolve(G).then(K,L)}function _asyncToGenerator(J){return function(){var x=this,O=arguments;return new Promise(function(K,L){function H(G){asyncGeneratorStep(M,K,L,H,F,"next",G)}function F(G){asyncGeneratorStep(M,K,L,H,F,"throw",G)}var M=J.apply(x,O);H(void 0)})}}
function _typeof(J){_typeof="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(x){return typeof x}:function(x){return x&&"function"===typeof Symbol&&x.constructor===Symbol&&x!==Symbol.prototype?"symbol":typeof x};return _typeof(J)}var _regenerator;
(function(){var J=function(x){function O(a,b,c,t){b=Object.create((b&&b.prototype instanceof L?b:L).prototype);t=new D(t||[]);b._invoke=P(a,c,t);return b}function K(a,b,c){try{return{type:"normal",arg:a.call(b,c)}}catch(t){return{type:"throw",arg:t}}}function L(){}function H(){}function F(){}function M(a){["next","throw","return"].forEach(function(b){a[b]=function(c){return this._invoke(b,c)}})}function G(a,b){function c(d,y,A,C){d=K(a[d],a,y);if("throw"===d.type)C(d.arg);else{var z=d.arg;return(d=
z.value)&&"object"===_typeof(d)&&p.call(d,"__await")?b.resolve(d.__await).then(function(E){c("next",E,A,C)},function(E){c("throw",E,A,C)}):b.resolve(d).then(function(E){z.value=E;A(z)},function(E){return c("throw",E,A,C)})}}var t;this._invoke=function(d,y){function A(){return new b(function(C,z){c(d,y,C,z)})}return t=t?t.then(A,A):A()}}function P(a,b,c){var t=e;return function(d,y){if(t===g)throw Error("Generator is already running");if(t===r){if("throw"===d)throw y;return v()}c.method=d;for(c.arg=
y;;){if(d=c.delegate)if(d=Q(d,c)){if(d===w)continue;return d}if("next"===c.method)c.sent=c._sent=c.arg;else if("throw"===c.method){if(t===e)throw t=r,c.arg;c.dispatchException(c.arg)}else"return"===c.method&&c.abrupt("return",c.arg);t=g;d=K(a,b,c);if("normal"===d.type){t=c.done?r:l;if(d.arg===w)continue;return{value:d.arg,done:c.done}}"throw"===d.type&&(t=r,c.method="throw",c.arg=d.arg)}}}function Q(a,b){var c=a.iterator[b.method];if(c===h){b.delegate=null;if("throw"===b.method){if(a.iterator["return"]&&
(b.method="return",b.arg=h,Q(a,b),"throw"===b.method))return w;b.method="throw";b.arg=new TypeError("The iterator does not provide a 'throw' method")}return w}c=K(c,a.iterator,b.arg);if("throw"===c.type)return b.method="throw",b.arg=c.arg,b.delegate=null,w;c=c.arg;if(!c)return b.method="throw",b.arg=new TypeError("iterator result is not an object"),b.delegate=null,w;if(c.done)b[a.resultName]=c.value,b.next=a.nextLoc,"return"!==b.method&&(b.method="next",b.arg=h);else return c;b.delegate=null;return w}
function S(a){var b={tryLoc:a[0]};1 in a&&(b.catchLoc=a[1]);2 in a&&(b.finallyLoc=a[2],b.afterLoc=a[3]);this.tryEntries.push(b)}function R(a){var b=a.completion||{};b.type="normal";delete b.arg;a.completion=b}function D(a){this.tryEntries=[{tryLoc:"root"}];a.forEach(S,this);this.reset(!0)}function I(a){if(a){var b=a[m];if(b)return b.call(a);if("function"===typeof a.next)return a;if(!isNaN(a.length)){var c=-1;b=function d(){for(;++c<a.length;)if(p.call(a,c))return d.value=a[c],d.done=!1,d;d.value=
h;d.done=!0;return d};return b.next=b}}return{next:v}}function v(){return{value:h,done:!0}}var q=Object.prototype,p=q.hasOwnProperty,h,k="function"===typeof Symbol?Symbol:{},m=k.iterator||"@@iterator",n=k.asyncIterator||"@@asyncIterator",f=k.toStringTag||"@@toStringTag";x.wrap=O;var e="suspendedStart",l="suspendedYield",g="executing",r="completed",w={};k={};k[m]=function(){return this};var B=Object.getPrototypeOf;(B=B&&B(B(I([]))))&&B!==q&&p.call(B,m)&&(k=B);var u=F.prototype=L.prototype=Object.create(k);
H.prototype=u.constructor=F;F.constructor=H;F[f]=H.displayName="GeneratorFunction";x.isGeneratorFunction=function(a){return(a="function"===typeof a&&a.constructor)?a===H||"GeneratorFunction"===(a.displayName||a.name):!1};x.mark=function(a){Object.setPrototypeOf?Object.setPrototypeOf(a,F):(a.__proto__=F,f in a||(a[f]="GeneratorFunction"));a.prototype=Object.create(u);return a};x.awrap=function(a){return{__await:a}};M(G.prototype);G.prototype[n]=function(){return this};x.AsyncIterator=G;x.async=function(a,
b,c,t,d){void 0===d&&(d=Promise);var y=new G(O(a,b,c,t),d);return x.isGeneratorFunction(b)?y:y.next().then(function(A){return A.done?A.value:y.next()})};M(u);u[f]="Generator";u[m]=function(){return this};u.toString=function(){return"[object Generator]"};x.keys=function(a){var b=[],c;for(c in a)b.push(c);b.reverse();return function d(){for(;b.length;){var y=b.pop();if(y in a)return d.value=y,d.done=!1,d}d.done=!0;return d}};x.values=I;D.prototype={constructor:D,reset:function(a){this.next=this.prev=
0;this.sent=this._sent=h;this.done=!1;this.delegate=null;this.method="next";this.arg=h;this.tryEntries.forEach(R);if(!a)for(var b in this)"t"===b.charAt(0)&&p.call(this,b)&&!isNaN(+b.slice(1))&&(this[b]=h)},stop:function(){this.done=!0;var a=this.tryEntries[0].completion;if("throw"===a.type)throw a.arg;return this.rval},dispatchException:function(a){function b(z,E){y.type="throw";y.arg=a;c.next=z;E&&(c.method="next",c.arg=h);return!!E}if(this.done)throw a;for(var c=this,t=this.tryEntries.length-1;0<=
t;--t){var d=this.tryEntries[t],y=d.completion;if("root"===d.tryLoc)return b("end");if(d.tryLoc<=this.prev){var A=p.call(d,"catchLoc"),C=p.call(d,"finallyLoc");if(A&&C){if(this.prev<d.catchLoc)return b(d.catchLoc,!0);if(this.prev<d.finallyLoc)return b(d.finallyLoc)}else if(A){if(this.prev<d.catchLoc)return b(d.catchLoc,!0)}else if(C){if(this.prev<d.finallyLoc)return b(d.finallyLoc)}else throw Error("try statement without catch or finally");}}},abrupt:function(a,b){for(var c=this.tryEntries.length-
1;0<=c;--c){var t=this.tryEntries[c];if(t.tryLoc<=this.prev&&p.call(t,"finallyLoc")&&this.prev<t.finallyLoc){var d=t;break}}d&&("break"===a||"continue"===a)&&d.tryLoc<=b&&b<=d.finallyLoc&&(d=null);c=d?d.completion:{};c.type=a;c.arg=b;return d?(this.method="next",this.next=d.finallyLoc,w):this.complete(c)},complete:function(a,b){if("throw"===a.type)throw a.arg;"break"===a.type||"continue"===a.type?this.next=a.arg:"return"===a.type?(this.rval=this.arg=a.arg,this.method="return",this.next="end"):"normal"===
a.type&&b&&(this.next=b);return w},finish:function(a){for(var b=this.tryEntries.length-1;0<=b;--b){var c=this.tryEntries[b];if(c.finallyLoc===a)return this.complete(c.completion,c.afterLoc),R(c),w}},"catch":function(a){for(var b=this.tryEntries.length-1;0<=b;--b){var c=this.tryEntries[b];if(c.tryLoc===a){a=c.completion;if("throw"===a.type){var t=a.arg;R(c)}return t}}throw Error("illegal catch attempt");},delegateYield:function(a,b,c){this.delegate={iterator:I(a),resultName:b,nextLoc:c};"next"===this.method&&
(this.arg=h);return w}};return x}("object"===("undefined"===typeof module?"undefined":_typeof(module))?module.exports:{});try{_regenerator=J}catch(x){Function("r","_regenerator = r")(J)}})();
(function(){function J(){J=_asyncToGenerator(_regenerator.mark(function p(q){return _regenerator.wrap(function(h){for(;;)switch(h.prev=h.next){case 0:return jQuery(q).attr("disabled",!0),h.prev=1,h.next=4,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"LogClear",{}),type:"POST",data:"",dataType:"json",cache:!1});case 4:h.next=8;break;case 6:h.prev=6,h.t0=h["catch"](1);case 8:jQuery(q).attr("disabled",!1);case 9:case "end":return h.stop()}},p,this,[[1,6]])}));
return J.apply(this,arguments)}function x(){x=_asyncToGenerator(_regenerator.mark(function h(q,p){var k,m,n,f,e,l;return _regenerator.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:k=jQuery(q.parentNode).closest(".blck").first();m=k.find(".seraph_accel_spinner");k.find(".content");n=k.find('input[type="button"]:not(.cancel)');f=k.find('input[type="button"].cancel');if(!p){g.next=23;break}n.prop("disabled",!0);m.show();seraph_accel.Manager._int.notRefreshStat=!0;e=null;g.prev=10;g.next=13;return jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+
"UpdateStatBegin",{}),cache:!1,dataType:"json"});case 13:l=g.sent;seraph_accel.Gen.hr.Fail(l)&&(e=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),l));g.next=20;break;case 17:g.prev=17,g.t0=g["catch"](10),e="HTTP "+g.t0.status;case 20:return e&&(alert(e),n.prop("disabled",!1),m.hide()),delete seraph_accel.Manager._int.notRefreshStat,g.abrupt("return");case 23:return f.prop("disabled",!0),seraph_accel.Manager._int.notRefreshStat=!0,e=null,g.prev=26,g.next=29,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+
"UpdateStatCancel",{}),type:"POST",data:"",cache:!1,dataType:"json"});case 29:l=g.sent;seraph_accel.Gen.hr.Fail(l)&&(e=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),l));g.next=37;break;case 33:g.prev=33,g.t1=g["catch"](26),e="HTTP "+g.t1.status,f.prop("disabled",!1);case 37:e&&alert(e),delete seraph_accel.Manager._int.notRefreshStat;case 39:case "end":return g.stop()}},h,this,[[10,17],[26,33]])}));return x.apply(this,arguments)}function O(v){switch(v){case 0:return D("OpDescr_Invalidate",
"admin.Manage","seraphinite-accelerator");case 3:return D("OpDescr_CheckInvalidate","admin.Manage","seraphinite-accelerator");case 2:return D("OpDescr_Delete","admin.Manage","seraphinite-accelerator");case 10:return D("OpDescr_SrvDel","admin.Manage","seraphinite-accelerator")}return""+v}function K(){K=_asyncToGenerator(_regenerator.mark(function p(q){var h,k,m,n;return _regenerator.wrap(function(f){for(;;)switch(f.prev=f.next){case 0:return h=jQuery(q.parentNode).closest(".blck").first(),k=h.find('input[type="button"].cancel'),
k.prop("disabled",!0),m=null,f.prev=4,f.next=7,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"PostUpdCancel",{}),type:"POST",data:"",cache:!1,dataType:"json"});case 7:n=f.sent;seraph_accel.Gen.hr.Fail(n)&&(m=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),n));f.next=14;break;case 11:f.prev=11,f.t0=f["catch"](4),m="HTTP "+f.t0.status;case 14:m&&alert(m);case 15:case "end":return f.stop()}},p,this,[[4,11]])}));return K.apply(this,arguments)}function L(){L=
_asyncToGenerator(_regenerator.mark(function p(q){var h,k,m,n;return _regenerator.wrap(function(f){for(;;)switch(f.prev=f.next){case 0:return h=jQuery(q.parentNode).closest(".blck").first(),k=h.find('input[type="button"].cancel'),k.prop("disabled",!0),m=null,f.prev=4,f.next=7,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"ScheUpdCancel",{}),type:"POST",data:"",cache:!1,dataType:"json"});case 7:n=f.sent;seraph_accel.Gen.hr.Fail(n)&&(m=I(D("OpSrvErr_%1$08X",
"admin.Manage","seraphinite-accelerator"),n));f.next=14;break;case 11:f.prev=11,f.t0=f["catch"](4),m="HTTP "+f.t0.status;case 14:m&&alert(m);case 15:case "end":return f.stop()}},p,this,[[4,11]])}));return L.apply(this,arguments)}function H(){H=_asyncToGenerator(_regenerator.mark(function h(q,p){var k,m,n,f,e,l,g,r,w,B;return _regenerator.wrap(function(u){for(;;)switch(u.prev=u.next){case 0:return k=jQuery(q.parentNode).closest(".blck").first(),m=k.find(".seraph_accel_spinner"),n=k.find(".descr"),
f=k.find('input[type="button"]:not(.cancel)'),e=k.find('input[type="button"].cancel'),l=k.find(".type").val(),g=seraph_accel.Gen.StrReplaceAll(String(k.find(".uri").val()),["\r","\n","*"],["",";","{ASTRSK}"]),m.show(),n.show(),n.text(O(p)),f.prop("disabled",!0),e.prop("disabled",!1),seraph_accel.Manager._int.notRefreshOp=!0,r={cache:!1,dataType:"json"},512>g.length?r.url=seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"CacheOpBegin",{type:l,op:p,uri:g}):(r.url=seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+
"CacheOpBegin",{type:l,op:p}),r.type="POST",r.contentType="application/x-www-form-urlencoded",r.data="uri="+encodeURIComponent(g)),w=null,u.prev=16,u.next=19,jQuery.ajax(r);case 19:B=u.sent;seraph_accel.Gen.hr.Fail(B)&&(w=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),B));u.next=26;break;case 23:u.prev=23,u.t0=u["catch"](16),w="HTTP "+u.t0.status;case 26:w&&alert(w),delete seraph_accel.Manager._int.notRefreshOp;case 28:case "end":return u.stop()}},h,this,[[16,23]])}));return H.apply(this,
arguments)}function F(){F=_asyncToGenerator(_regenerator.mark(function h(q,p){var k,m,n,f;return _regenerator.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return k=jQuery(q.parentNode).closest(".blck").first(),m=k.find('input[type="button"].cancel'),m.prop("disabled",!0),n=null,e.prev=4,e.next=7,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"CacheOpCancel",{op:p}),type:"POST",data:"",cache:!1,dataType:"json"});case 7:f=e.sent;seraph_accel.Gen.hr.Fail(f)&&
(n=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),f));e.next=14;break;case 11:e.prev=11,e.t0=e["catch"](4),n="HTTP "+e.t0.status;case 14:n&&alert(n);case 15:case "end":return e.stop()}},h,this,[[4,11]])}));return F.apply(this,arguments)}function M(){M=_asyncToGenerator(_regenerator.mark(function k(q,p,h){var m,n,f,e,l;return _regenerator.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return m=jQuery(q.parentNode).closest(".blck").first(),n=m.find(".seraph_accel_spinner"),f=
m.find('input[type="button"]'),n.show(),f.prop("disabled",!0),seraph_accel.Manager._int.notRefreshQueueDeleting=!0,e=null,g.prev=7,g.next=10,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"QueueDelete",{allSites:p?!0:void 0,_wpnonce:h}),type:"GET",cache:!1,dataType:"json"});case 10:l=g.sent;seraph_accel.Gen.hr.Fail(l)&&(e=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),l));g.next=17;break;case 14:g.prev=14,g.t0=g["catch"](7),e="HTTP "+g.t0.status;
case 17:e&&(alert(e),n.hide(),f.prop("disabled",!1)),delete seraph_accel.Manager._int.notRefreshQueueDeleting;case 19:case "end":return g.stop()}},k,this,[[7,14]])}));return M.apply(this,arguments)}function G(){G=_asyncToGenerator(_regenerator.mark(function k(q,p,h){var m,n,f,e,l;return _regenerator.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:if(confirm(D("ItemCancelConfirm","admin.Manage_Queue","seraphinite-accelerator"))){g.next=2;break}return g.abrupt("return");case 2:return m=jQuery(q.parentNode).closest(".blck").first(),
n=m.find(".seraph_accel_spinner"),f=m.find('input[type="button"]'),n.show(),f.prop("disabled",!0),seraph_accel.Manager._int.notRefreshQueueDeleting=!0,e=null,g.prev=9,g.next=12,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"QueueItemCancel",{pc:p,_wpnonce:h}),type:"GET",cache:!1,dataType:"json"});case 12:l=g.sent;seraph_accel.Gen.hr.Fail(l)&&(e=I(D("OpSrvErr_%1$08X","admin.Manage","seraphinite-accelerator"),l));g.next=19;break;case 16:g.prev=16,g.t0=g["catch"](9),
e="HTTP "+g.t0.status;case 19:e&&alert(e),n.hide(),f.prop("disabled",!1),delete seraph_accel.Manager._int.notRefreshQueueDeleting;case 23:case "end":return g.stop()}},k,this,[[9,16]])}));return G.apply(this,arguments)}function P(){P=_asyncToGenerator(_regenerator.mark(function h(q,p){var k,m,n,f;return _regenerator.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:f=function(){f=_asyncToGenerator(_regenerator.mark(function g(){return _regenerator.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return r.next=
2,seraph_accel.Manager._int.OnDataRefresh(q,p);case 2:m();case 3:case "end":return r.stop()}},g,this)}));return f.apply(this,arguments)},n=function(){return f.apply(this,arguments)},m=function(){var l=1E3*parseInt(k.val(),10);100>l&&(l=100);clearTimeout(seraph_accel.Manager._int.refreshTimer);seraph_accel.Manager._int.refreshTimer=setTimeout(n,l)},k=jQuery(q).find("#queue .tmDataRefresh"),k.on("change",m),n();case 6:case "end":return e.stop()}},h,this)}));return P.apply(this,arguments)}function Q(){Q=
_asyncToGenerator(_regenerator.mark(function h(q,p){var k,m,n,f,e,l,g,r,w,B,u,a,b,c,t,d,y,A,C,z,E;return _regenerator.wrap(function(N){for(;;)switch(N.prev=N.next){case 0:return k=jQuery(q).find("#status").first(),m=jQuery(q).find("#stat").first(),n=m.find(".seraph_accel_spinner"),f=m.find('input[type="button"]:not(.cancel)'),e=m.find('input[type="button"].cancel'),l=jQuery(q).find("#operate").first(),g=l.find(".seraph_accel_spinner"),r=l.find(".descr"),w=l.find('input[type="button"]:not(.cancel)'),
B=l.find('input[type="button"].cancel'),u=jQuery(q).find("#queue").first(),a=u.find(".content"),b=u.find(".maxItems"),c=u.find(".seraph_accel_spinner"),t=u.find('input[type="button"]'),d=u.find(".descrNums"),N.prev=16,N.next=19,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"GetData",{nMaxQueueItems:b.val(),allSites:p?!0:void 0}),type:"GET",cache:!1,dataType:"json"});case 19:z=N.sent;N.next=24;break;case 22:N.prev=22,N.t0=N["catch"](16);case 24:if(z){N.next=
26;break}return N.abrupt("return");case 26:if(!p){for(E in z.status.cont)k.find('[data-id-cont="'+E+'"]').html(z.status.cont[E]);seraph_accel.Manager._int.notRefreshPostUpd||(y=k.find(".blck.postupd .seraph_accel_spinner"),A=k.find('.blck.postupd input[type="button"]:not(.cancel)'),C=k.find('.blck.postupd input[type="button"].cancel'),z.status.postUpd?(y.show(),A.prop("disabled",!0),C.prop("disabled",!1)):(y.hide(),A.prop("disabled",!1),C.prop("disabled",!0)));seraph_accel.Manager._int.notRefreshScheUpd||
(y=k.find(".blck.scheupd .seraph_accel_spinner"),A=k.find('.blck.scheupd input[type="button"]:not(.cancel)'),C=k.find('.blck.scheupd input[type="button"].cancel'),z.status.scheUpd?(y.show(),A.prop("disabled",!0),C.prop("disabled",!1)):(y.hide(),A.prop("disabled",!1),C.prop("disabled",!0)));seraph_accel.Manager._int.notRefreshCleanup||(y=k.find(".blck.cleanup .seraph_accel_spinner"),A=k.find('.blck.cleanup input[type="button"]:not(.cancel)'),C=k.find('.blck.cleanup input[type="button"].cancel'),z.status.cleanUp?
(y.show(),A.prop("disabled",!0),C.prop("disabled",!1)):(y.hide(),A.prop("disabled",!1),C.prop("disabled",!0)))}if(!p&&!seraph_accel.Manager._int.notRefreshStat)for(E in z.stat.isUpdating?(n.show(),f.prop("disabled",!0),e.prop("disabled",!1)):(n.hide(),f.prop("disabled",!1),e.prop("disabled",!0)),z.stat.cont)m.find('[data-id-cont="'+E+'"]').html(z.stat.cont[E]);p||seraph_accel.Manager._int.notRefreshOp||(null===z.curOp&&(z.curOp=void 0),void 0!==z.curOp?(g.show(),r.show(),r.text(O(z.curOp)),w.prop("disabled",
!0),B.prop("disabled",!1)):(g.hide(),r.hide(),w.prop("disabled",!1),B.prop("disabled",!0),r.text("")));a.html(z.queue.content);d.text(I(D("QueueNumsDescr_%1$u%2$u","admin.Manage","seraphinite-accelerator"),z.queue.nums.nInitial,z.queue.nums.nInProgress));seraph_accel.Manager._int.notRefreshQueueDeleting||(c.hide(),t.prop("disabled",!1));case 32:case "end":return N.stop()}},h,this,[[16,22]])}));return Q.apply(this,arguments)}function S(){S=_asyncToGenerator(_regenerator.mark(function p(q){var h,k,
m,n,f,e,l,g;return _regenerator.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return h=jQuery(q.parentNode).closest(".blck").first(),k=h.find(".url"),m=h.find(".messages"),n=h.find(".seraph_accel_spinner"),f=h.find('input[type="button"]'),e=0,h.find(".liteChk").prop("checked")&&(e|=1),h.find(".medChk").prop("checked")&&(e|=2),h.find(".tidyChk").prop("checked")&&(e|=524288),m.empty(),n.show(),f.prop("disabled",!0),r.prev=12,r.next=15,jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+
"HtmlCheck",{url:k.val(),norm:e}),type:"POST",data:"",dataType:"json",cache:!1});case 15:l=r.sent;r.next=21;break;case 18:r.prev=18,r.t0=r["catch"](12),l={err:r.t0.statusText};case 21:n.hide();f.prop("disabled",!1);if(!l.err){r.next=26;break}m.append(seraph_accel.Ui.LogItem("error",l.err));return r.abrupt("return");case 26:for(g in l.list)g=l.list[g],m.append(seraph_accel.Ui.LogItem(g.severity,g.text));case 27:case "end":return r.stop()}},p,this,[[12,18]])}));return S.apply(this,arguments)}var R=
seraph_accel.Wp.Loc.GetApi(),D=R._x,I=R.sprintf;seraph_accel.Settings={_int:{StrItem_OnAdd:function(v){function q(m){(m=m.trim())&&seraph_accel.Ui.TokensList.AddItem(k[0],m)}var p=1<arguments.length&&void 0!==arguments[1]?arguments[1]:",",h=jQuery(v.parentNode).closest(".blck").first(),k=h.find(".vals");h=h.find(".val");p?h.val().split(p).forEach(q):q(h.val());h.val("")},StrItem_OnCopyAll:function(v){seraph_accel.Ui.TokensList.CopyAllItems(jQuery(v.parentNode).closest(".blck").first().find(".vals")[0])},
StrItem_OnDelAll:function(v){seraph_accel.Ui.TokensList.DelAllItems(jQuery(v.parentNode).closest(".blck").first().find(".vals")[0])},OnUpdateCssAuto:function(v){seraph_accel.Ui.ComboShowDependedItems(v,jQuery(v.parentNode).closest(".blck").first().get(0))},OnLogClear:function(v){return J.apply(this,arguments)}}};seraph_accel.Manager={_int:{OnStatOp:function(v,q){return x.apply(this,arguments)},OnCacheOp:function(v,q){return H.apply(this,arguments)},OnCacheOpCancel:function(v,q){return F.apply(this,
arguments)},OnPostUpdCancel:function(v){return K.apply(this,arguments)},OnScheUpdCancel:function(v){return L.apply(this,arguments)},OnQueueDel:function(v,q,p){return M.apply(this,arguments)},OnQueueItemCancel:function(v,q,p){return G.apply(this,arguments)},OnDataRefreshInit:function(v,q){return P.apply(this,arguments)},OnDataRefresh:function(v,q){return Q.apply(this,arguments)},OnHtmlCheck:function(v){return S.apply(this,arguments)}}};(function(){function v(k){return q.apply(this,arguments)}function q(){q=
_asyncToGenerator(_regenerator.mark(function n(m){var f,e;return _regenerator.wrap(function(l){for(;;)switch(l.prev=l.next){case 0:return f={},l.prev=1,seraph_accel.SelfDiag._int.curAjax=jQuery.ajax({url:seraph_accel.Net.UpdateQueryArgs(seraph_accel.Plugin.GetAdminApiUrl()+"SelfDiag_"+m,{}),cache:!1,dataType:"json"}),l.next=5,seraph_accel.SelfDiag._int.curAjax;case 5:f=l.sent;delete seraph_accel.SelfDiag._int.curAjax;l.next=14;break;case 9:l.prev=9,l.t0=l["catch"](1),delete seraph_accel.SelfDiag._int.curAjax,
0===l.t0.status?f.hr=seraph_accel.Gen.hr.S_ABORTED:(f.hr=seraph_accel.Gen.hr.E_FAIL,f.descr="HTTP "+l.t0.status),500==l.t0.status&&(e=document.implementation.createHTMLDocument(""),e.documentElement.innerHTML=l.t0.responseText,e=e.body.textContent,e=seraph_accel.Gen.StrReplaceAll(e,"\r",""),e=seraph_accel.Gen.StrReplaceAll(e,["\t","\v"]," "),e=seraph_accel.Gen.StrReplaceAll(e,"\n",". "),(e=seraph_accel.Gen.StrReplaceAllWhileChanging(e,["  ",". . "],[" ",". "]).trim())&&"."!=e&&(f.descr+="<br>"+e));
case 14:return l.abrupt("return",f);case 15:case "end":return l.stop()}},n,this,[[1,9]])}));return q.apply(this,arguments)}function p(){p=_asyncToGenerator(_regenerator.mark(function n(m){var f,e,l,g,r,w,B,u,a,b,c,t=arguments;return _regenerator.wrap(function(d){for(;;)switch(d.prev=d.next){case 0:f=1<t.length&&void 0!==t[1]?t[1]:!1;e=jQuery(m.parentNode).closest(".blck").first();l=e.find(".seraph_accel_spinner");g=e.find('input[type="button"]:not(.cancel)');r=e.find('input[type="button"].cancel');
w=e.find(".log");B=[{id:"3rdPartySettCompat",name:h("TestName_3rdPartySettCompat","admin.SelfDiag","seraphinite-accelerator")},{id:"AsyncRequest",name:h("TestName_AsyncRequest","admin.SelfDiag","seraphinite-accelerator")},{id:"SetMaxExecTime",name:h("TestName_SetMaxExecTime","admin.SelfDiag","seraphinite-accelerator")},{id:"PageOptimize",name:h("TestName_PageOptimize","admin.SelfDiag","seraphinite-accelerator")},{id:"VendorSrv",name:h("TestName_VendorSrv","admin.SelfDiag","seraphinite-accelerator")},
{id:"ExtCache",name:h("TestName_ExtCache","admin.SelfDiag","seraphinite-accelerator")}];w.empty();for(u=0;u<B.length;u++)a=B[u],w.append(seraph_accel.Ui.LogItem("none",seraph_accel.Ui.Tag("strong",a.name?a.name:a.id),!1));if(!f){d.next=11;break}return d.abrupt("return");case 11:l.show(),g.prop("disabled",!0),r.prop("disabled",!1),seraph_accel.SelfDiag._int.inProgress=!0,u=0;case 16:if(!(u<B.length)){d.next=31;break}a=B[u];jQuery(w.children().get(u)).replaceWith(seraph_accel.Ui.LogItem("info",seraph_accel.Ui.Tag("strong",
a.name?a.name:a.id)+h("TestRunning","admin.SelfDiag","seraphinite-accelerator"),!1));d.next=21;return v(a.id);case 21:b=d.sent;c=h("TestSucc","admin.SelfDiag","seraphinite-accelerator");b.hr!=seraph_accel.Gen.hr.S_OK&&(c=seraph_accel.Gen.hr.Succ(b.hr)?b.hr==seraph_accel.Gen.hr.S_ABORTED?h("TestAbort","admin.SelfDiag","seraphinite-accelerator"):b.hr==seraph_accel.Gen.hr.S_FALSE?h("TestNotice","admin.SelfDiag","seraphinite-accelerator"):h("TestWarn","admin.SelfDiag","seraphinite-accelerator"):h("TestError",
"admin.SelfDiag","seraphinite-accelerator"));b.descr&&(c+="<br>"+b.descr);jQuery(w.children().get(u)).replaceWith(seraph_accel.Ui.LogItem(b.hr==seraph_accel.Gen.hr.S_OK?"success":seraph_accel.Gen.hr.Succ(b.hr)?b.hr==seraph_accel.Gen.hr.S_FALSE?"normal":"warning":"error",seraph_accel.Ui.Tag("strong",a.name?a.name:a.id)+c,!1));if(seraph_accel.SelfDiag._int.inProgress){d.next=28;break}return d.abrupt("break",31);case 28:u++;d.next=16;break;case 31:l.hide(),g.prop("disabled",!1),r.prop("disabled",!0);
case 34:case "end":return d.stop()}},n,this)}));return p.apply(this,arguments)}var h=seraph_accel.Wp.Loc.GetApi()._x;seraph_accel.SelfDiag={_int:{OnStart:function(k){return p.apply(this,arguments)},OnCancel:function(k){k=jQuery(k.parentNode).closest(".blck").first();k.find(".seraph_accel_spinner");k.find('input[type="button"]:not(.cancel)');k.find('input[type="button"].cancel').prop("disabled",!0);seraph_accel.SelfDiag._int.inProgress=!1;seraph_accel.SelfDiag._int.curAjax&&seraph_accel.SelfDiag._int.curAjax.abort()},
inProgress:!1}}})()})();