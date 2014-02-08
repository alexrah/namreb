dispatch.to("Foundry/2.1 Core Plugins").at(function(e,t){e.uid=function(e,t){return(e?e:"")+(Math.random()+"").replace(".","")+(t?t:"")},e.isDeferred=function(t){return t&&e.isFunction(t.always)},e.distinct=function(t){var n=e.unique;if(t.length<1)return;if(t[0].nodeType)return n.apply(this,arguments);if(typeof t[0]=="object"){var r=Math.random(),i=[];return e.each(t,function(e){t[e][r]||(i.push(t[e]),t[e][r]=!0)}),e.each(i,function(e){delete i[e][r]}),i}return e.grep(t,function(n,r){return e.inArray(n,t)===r})},e.trimSeparators=function(t,n,r){var i=n;return t=t.replace(RegExp("^["+i+"\\s]+|["+i+",\\s]+$","g"),"").replace(RegExp(i+"["+i+"\\s]*"+i,"g"),i).replace(RegExp("[\\s]+"+i,"g"),i).replace(RegExp(i+"[\\s]+","g"),i),r&&(t=e.distinct(t.split(i)).join(i)),t},e.isNumeric=function(e){return!isNaN(parseFloat(e))&&isFinite(e)},e.Number={rotate:function(e,t,n,r){return r===undefined&&(r=0),e+=r,e<t?e+=n+1:e>n&&(e-=n+1),e}},e.fn.stretchToFit=function(){return e.each(this,function(){var t=e(this);t.css("width","100%").css("width",t.width()*2-t.outerWidth(!0)-parseInt(t.css("borderLeftWidth"))-parseInt(t.css("borderRightWidth")))})},e.fn.serializeJSON=function(t){var n={};return e.each(e(this).serializeArray(),function(t,r){n.hasOwnProperty(r.name)?(e.isArray(n[r.name])||(n[r.name]=[n[r.name]]),n[r.name].push(r.value)):n[r.name]=r.value}),t&&(n=(JSON.stringify||e.toJSON).apply(this,n)),n},e.fn.toHTML=function(){return e("<div>").html(this).html()},function(){var t=function(e){this.items=e,this.start=0,this.end=e.length-1,this.node=null,this.stopped=!1};e.extend(t.prototype,{isLooping:function(){return this.stopped?!1:Math.abs(this.start-this.end)>1?(this.node=Math.floor((this.start+this.end)/2),!0):!1},flip:function(e){e?this.end=this.node-1:this.start=this.node+1},stop:function(){this.stop=!0}}),e.Bloop=function(e){return new t(e)}}(),e.remap=function(t,n,r){return e.each(r,function(e,r){t[r]=n[r]}),obj},e.deletes=function(t,n){e.each(n,function(e,n){delete t[n]})},function(){var t=function(e){this.threads=[],this.threadCount=0,this.threadLimit=e.threadLimit||1,this.threadDelay=e.threadDelay||0};e.extend(t.prototype,{add:function(t,n){if(!e.isFunction(t))return;t.type=n||"normal",n=="deferred"&&(t.deferred=e.Deferred().always(e.proxy(this.next,this))),this.threads.push(t),this.run()},addDeferred:function(e){return this.add(e,"deferred")},next:function(){this.threadCount--,this.run()},run:function(){var e=this;setTimeout(function(){if(e.threads.length<1)return;if(e.threadCount<e.threadLimit){e.threadCount++;var t=e.threads.shift();try{t.call(t,t.deferred)}catch(n){console.error(n)}!t.deferred&&e.next()}},e.threadDelay)}}),e.Threads=function(e){return new t(e)}}(),function(){var t=function(){this.lastId=0};t.prototype.queue=function(t){var n=this,r=e.uid();return n.lastId=r,function(){n.lastId===r&&t.apply(this,arguments)}},e.Enqueue=function(){var e=new t;return e.queue}}(),function(){var t="___eventable",n=["on","off","fire"],r=function(e){return e.split(".")[0]},i=function(e){this.fnList={},this.events={},this.mode=e};e.extend(i.prototype,{createEvent:function(t){return this.events[t]=e.Callbacks(this.mode)},on:function(t,n){if(!t||!e.isFunction(n))return this;var i=this.fnList;(i[t]||(i[t]=[])).push(n);var s=r(t);return(this.events[s]||this.createEvent(s)).add(n),this},off:function(t){if(!t)return this;var n=r(t),i=this.events[n];if(!i)return this;var s=function(t){e.each(t,function(e,t){i.remove(t)})};return n!==t?e.each(this.fnList,function(e,t){e.indexOf(n)>-1&&s(t)}):s(this.fnList[t]),this},fire:function(t){var n=this.events[t];if(!n)return;return n.fire.apply(n,e.makeArray(arguments).slice(1)),this},destroy:function(){for(name in this.events)this.events[name].disable()}}),e.eventable=function(r,s){var o=r[t];return o&&s==="destroy"?(o.destroy(),e.deletes(r,n),delete r[t]):(o=r[t]=new i(s),r.on=e.proxy(o.on,o),r.off=e.proxy(o.off,o),r.fire=e.proxy(o.fire,o),r)}}(),e.Chunk=function(t,n){e.isArray(t)&&(t=[]);var n=e.extend({},{size:256,every:1e3},n),r=e.extend(e.Deferred(),{size:n.size,every:n.every,from:0,to:t.length,process:function(e){return r.process.fn=e,r},chunkStart:function(e){return r.chunkStart.fn=e,r},chunkEnd:function(e){return r.chunkEnd.fn=e,r},start:function(){return r.stopped=!1,r.iterate(),r},iterate:function(){if(r.stopped)return;var e=r.process.fn;if(!e)return;r.to=from.size+r.size;var n=t.length;r.to>n&&(r.to=n);var i={from:r.from,to:r.to};r.chunkStart.fn&&r.chunkStart.fn.call(r,i.from,i.to);while(r.from<r.to){if(r.stopped)break;e.call(r,t[r.from]),r.from++}return r.chunkEnd.fn&&r.chunkEnd.fn.call(r,i.from,i.to),r.completed=r.from>=t.length-1,r.completed?r.resolveWith(r):r.nextIteration=setTimeout(r.iterate,r.every),r},pause:function(){return r.stopped=!0,clearTimeout(r.nextIteration),r},restart:function(){return r.state()==="rejected"?r:(r.from=0,r.start(),r)},stop:function(){return r.pause(),r.rejectWith(r,[r.from]),r}});return r},e.fn.disabled=function(e){return e===undefined?this.hasClass("disabled"):this.toggleClass("disabled",!!e)},e.fn.enabled=function(e){return e===undefined?!this.disabled():this.disabled(!e)},function(){var t=e.Ajax=function(){var n=e.Deferred(),r=arguments;return t.queue.addDeferred(function(i){n.xhr=e.ajax.apply(null,r).pipe(n.resolve,n.reject,n.notify),setTimeout(i.resolve,t.requestInterval)}),n};t.queue=e.Threads({threadLimit:1}),t.requestInterval=1200}()});