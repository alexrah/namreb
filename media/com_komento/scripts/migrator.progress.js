Komento.module("migrator.progress",function(e){var t=this;Komento.require().script("admin.language","komento.common").done(function(){Komento.Controller("Migrator.Progress",{defaults:{"{progressBar}":".progressBar","{progressStatus}":".progressStatus","{progressPercentage}":".progressPercentage","{logList}":".logList","{clearLog}":".clearLog","{totalComments}":".totalComments","{totalPosts}":".totalPosts","{migratedComments}":".migratedComments"}},function(e){return{init:function(){},"{clearLog} click":function(){e.logList().html("")},setTotalPosts:function(t){e.totalPosts().text(t)},setTotalComments:function(t){e.totalComments().text(t)},updateMigratedComments:function(t){var n=e.migratedComments().text(),r=parseInt(n)+parseInt(t);e.migratedComments().text(r);var i=parseInt(e.totalComments().eq(0).text()),s=Math.ceil(r/i*100);e.progressBar().animate({width:s+""+"%"}),e.progressPercentage().text(s)},log:function(t){var n=new Date,r=n.getHours()>9?n.getHours():"0"+n.getHours(),i=n.getMinutes()>9?n.getMinutes():"0"+n.getMinutes(),s=n.getSeconds()>9?n.getSeconds():"0"+n.getSeconds(),o="["+r+":"+i+":"+s+"]",u="<li>"+o+" "+t+"</li>",a=e.logList()[0].scrollHeight;e.logList().append(u).scrollTop(a)}}}),t.resolve()})});