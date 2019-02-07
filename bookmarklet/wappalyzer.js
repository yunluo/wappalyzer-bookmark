"use strict";
function _asyncToGenerator(e){return function(){var t=e.apply(this,arguments);return new Promise(function(e,n){function r(a,o){try{var i=t[a](o),c=i.value}catch(e){return void n(e)}if(!i.done)return Promise.resolve(c).then(function(e){r("next",e)},function(e){r("throw",e)});e(c)}return r("next")})}}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function asArray(e){return e instanceof Array?e:[e]}function asyncForEach(e,t){return Promise.all((e||[]).map(function(e){return new Promise(function(n){return setTimeout(function(){return n(t(e))},1)})}))}function addDetected(e,t,n,r,a){if(e.detected=!0,e.confidence[n+" "+(a?a+" ":"")+t.regex]=void 0===t.confidence?100:parseInt(t.confidence,10),t.version){var o=[],i=t.regex.exec(r),c=t.version;i&&(i.forEach(function(e,t){var n=new RegExp("\\\\"+t+"\\?([^:]+):(.*)$").exec(c);n&&3===n.length&&(c=c.replace(n[0],e?n[1]:n[2])),c=c.trim().replace(new RegExp("\\\\"+t,"g"),e||"")}),c&&-1===o.indexOf(c)&&o.push(c),o.length&&(e.version=o.reduce(function(e,t){return e.length>t.length?e:t})))}}function resolveExcludes(e,t){var n=[],r=Object.assign({},e,t);Object.keys(r).forEach(function(e){var t=r[e];t.props.excludes&&asArray(t.props.excludes).forEach(function(e){n.push(e)})}),Object.keys(e).forEach(function(t){n.indexOf(t)>-1&&delete e[t]})}var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},_slicedToArray=function(){function e(e,t){var n=[],r=!0,a=!1,o=void 0;try{for(var i,c=e[Symbol.iterator]();!(r=(i=c.next()).done)&&(n.push(i.value),!t||n.length!==t);r=!0);}catch(e){a=!0,o=e}finally{try{!r&&c.return&&c.return()}finally{if(a)throw o}}return n}return function(t,n){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,n);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),_createClass=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),validation={hostname:/(www.)?((.+?)\.(([a-z]{2,3}\.)?[a-z]{2,6}))$/,hostnameBlacklist:/((local|dev(elopment)?|stag(e|ing)?|test(ing)?|demo(shop)?|admin|google|cache)\.|\/admin|\.local)/},Application=function(){function e(t,n,r){_classCallCheck(this,e),this.confidence={},this.confidenceTotal=0,this.detected=Boolean(r),this.excludes=[],this.name=t,this.props=n,this.version=""}return _createClass(e,[{key:"getConfidence",value:function(){var e=this,t=0;return Object.keys(this.confidence).forEach(function(n){t+=e.confidence[n]}),this.confidenceTotal=Math.min(t,100),this.confidenceTotal}}]),e}(),Wappalyzer=function(){function e(){_classCallCheck(this,e),this.apps={},this.categories={},this.driver={},this.jsPatterns={},this.detected={},this.hostnameCache={},this.adCache=[],this.config={websiteURL:"https://www.wappalyzer.com/",twitterURL:"https://twitter.com/Wappalyzer",githubURL:"https://github.com/AliasIO/Wappalyzer"}}return _createClass(e,[{key:"log",value:function(e,t,n){this.driver.log&&this.driver.log(e,t||"",n||"debug")}},{key:"analyze",value:function(e,t,n){var r=this,a={},o=[],i=new Date,c=t.scripts,s=t.cookies,u=t.headers,l=t.js,h=t.html;void 0===this.detected[e.canonical]&&(this.detected[e.canonical]={});var p=[],f=null;if(h){"string"!=typeof h&&(h="");var d=t.html.match(new RegExp('<html[^>]*[: ]lang="([a-z]{2}((-|_)[A-Z]{2})?)"',"i"));f=d&&d.length?d[1]:null;var y=/<meta[^>]+>/gi;do{if(!(d=y.exec(h)))break;p.push(d[0])}while(d)}return Object.keys(this.apps).forEach(function(t){a[t]=r.detected[e.canonical]&&r.detected[e.canonical][t]?r.detected[e.canonical][t]:new Application(t,r.apps[t]);var n=a[t];o.push(r.analyzeUrl(n,e)),h&&(o.push(r.analyzeHtml(n,h)),o.push(r.analyzeMeta(n,p))),c&&o.push(r.analyzeScripts(n,c)),s&&o.push(r.analyzeCookies(n,s)),u&&o.push(r.analyzeHeaders(n,u))}),l&&Object.keys(l).forEach(function(e){"function"!=typeof l[e]&&o.push(r.analyzeJs(a[e],l[e]))}),new Promise(function(){var c=_asyncToGenerator(regeneratorRuntime.mark(function c(s){return regeneratorRuntime.wrap(function(c){for(;;)switch(c.prev=c.next){case 0:return c.next=2,Promise.all(o);case 2:return Object.keys(a).forEach(function(e){var t=a[e];t.detected&&t.getConfidence()||delete a[t.name]}),resolveExcludes(a,r.detected[e]),r.resolveImplies(a,e.canonical),r.cacheDetectedApps(a,e.canonical),r.trackDetectedApps(a,e,f),r.log("Processing "+Object.keys(t).join(", ")+" took "+((new Date-i)/1e3).toFixed(2)+"s ("+e.hostname+")","core"),Object.keys(a).length&&r.log("Identified "+Object.keys(a).join(", ")+" ("+e.hostname+")","core"),r.driver.displayApps(r.detected[e.canonical],{language:f},n),c.abrupt("return",s());case 11:case"end":return c.stop()}},c,r)}));return function(e){return c.apply(this,arguments)}}())}},{key:"cacheDetectedAds",value:function(e){this.adCache.push(e)}},{key:"robotsTxtAllows",value:function(e){var t=this;return new Promise(function(){var n=_asyncToGenerator(regeneratorRuntime.mark(function n(r,a){var o,i;return regeneratorRuntime.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:if(o=t.parseUrl(e),"http:"===o.protocol||"https:"===o.protocol){n.next=3;break}return n.abrupt("return",a());case 3:return n.next=5,t.driver.getRobotsTxt(o.host,"https:"===o.protocol);case 5:if(i=n.sent,!i.some(function(e){return 0===o.pathname.indexOf(e)})){n.next=8;break}return n.abrupt("return",a());case 8:return n.abrupt("return",r());case 9:case"end":return n.stop()}},n,t)}));return function(e,t){return n.apply(this,arguments)}}())}},{key:"parseUrl",value:function(e){var t=this.driver.document.createElement("a");return t.href=e,t.canonical=t.protocol+"//"+t.host+t.pathname,t}},{key:"ping",value:function(){Object.keys(this.hostnameCache).length>100&&(this.driver.ping(this.hostnameCache),this.hostnameCache={}),this.adCache.length>50&&(this.driver.ping({},this.adCache),this.adCache=[])}},{key:"parsePatterns",value:function(e){var t=this;if(!e)return[];var n={};return("string"==typeof e||e instanceof Array)&&(e={main:asArray(e)}),Object.keys(e).forEach(function(r){n[r]=[],asArray(e[r]).forEach(function(e){var a={};e.split("\\;").forEach(function(e,n){if(n)e=e.split(":"),e.length>1&&(a[e.shift()]=e.join(":"));else{a.string=e;try{a.regex=new RegExp(e.replace("/","\\/"),"i")}catch(n){a.regex=new RegExp,t.log(n.message+": "+e,"error","core")}}}),n[r].push(a)})}),"main"in n&&(n=n.main),n}},{key:"parseJsPatterns",value:function(){var e=this;Object.keys(this.apps).forEach(function(t){e.apps[t].js&&(e.jsPatterns[t]=e.parsePatterns(e.apps[t].js))})}},{key:"resolveImplies",value:function(e,t){for(var n=this,r=!0,a=(function(a){var o=e[a];o&&o.props.implies&&asArray(o.props.implies).forEach(function(i){var c=n.parsePatterns(i),s=_slicedToArray(c,1);if(i=s[0],!n.apps[i.string])return void n.log("Implied application "+i.string+" does not exist","core","warn");i.string in e||(e[i.string]=n.detected[t]&&n.detected[t][i.string]?n.detected[t][i.string]:new Application(i.string,n.apps[i.string],!0),r=!0),Object.keys(o.confidence).forEach(function(t){e[i.string].confidence[t+" implied by "+a]=o.confidence[t]*(void 0===i.confidence?1:i.confidence/100)})})});r;)r=!1,Object.keys(e).forEach(a)}},{key:"cacheDetectedApps",value:function(e,t){var n=this;Object.keys(e).forEach(function(r){var a=e[r];n.detected[t][r]=a,Object.keys(a.confidence).forEach(function(e){n.detected[t][r].confidence[e]=a.confidence[e]})}),this.driver.ping instanceof Function&&this.ping()}},{key:"trackDetectedApps",value:function(e,t,n){var r=this;if(this.driver.ping instanceof Function){var a=t.protocol+"//"+t.hostname;Object.keys(e).forEach(function(n){var o=e[n];r.detected[t.canonical][n].getConfidence()>=100&&validation.hostname.test(t.hostname)&&!validation.hostnameBlacklist.test(t.hostname)&&(a in r.hostnameCache||(r.hostnameCache[a]={applications:{},meta:{}}),n in r.hostnameCache[a].applications||(r.hostnameCache[a].applications[n]={hits:0}),r.hostnameCache[a].applications[n].hits+=1,e[n].version&&(r.hostnameCache[a].applications[n].version=o.version))}),a in this.hostnameCache&&(this.hostnameCache[a].meta.language=n),this.ping()}}},{key:"analyzeUrl",value:function(e,t){var n=this.parsePatterns(e.props.url);return n.length?asyncForEach(n,function(n){n.regex.test(t.canonical)&&addDetected(e,n,"url",t.canonical)}):Promise.resolve()}},{key:"analyzeHtml",value:function(e,t){var n=this.parsePatterns(e.props.html);return n.length?asyncForEach(n,function(n){n.regex.test(t)&&addDetected(e,n,"html",t)}):Promise.resolve()}},{key:"analyzeScripts",value:function(e,t){var n=this.parsePatterns(e.props.script);return n.length?asyncForEach(n,function(n){t.forEach(function(t){n.regex.test(t)&&addDetected(e,n,"script",t)})}):Promise.resolve()}},{key:"analyzeMeta",value:function(e,t){var n=this.parsePatterns(e.props.meta),r=[];return e.props.meta?(t.forEach(function(t){Object.keys(n).forEach(function(a){if(new RegExp("(?:name|property)=[\"']"+a+"[\"']","i").test(t)){var o=t.match(/content=("|')([^"']+)("|')/i);r.push(asyncForEach(n[a],function(t){o&&4===o.length&&t.regex.test(o[2])&&addDetected(e,t,"meta",o[2],a)}))}})}),Promise.all(r)):Promise.resolve()}},{key:"analyzeHeaders",value:function(e,t){var n=this.parsePatterns(e.props.headers),r=[];return Object.keys(n).forEach(function(a){"function"!=typeof n[a]&&r.push(asyncForEach(n[a],function(n){(a=a.toLowerCase())in t&&t[a].forEach(function(t){n.regex.test(t)&&addDetected(e,n,"headers",t,a)})}))}),r?Promise.all(r):Promise.resolve()}},{key:"analyzeCookies",value:function(e,t){var n=this.parsePatterns(e.props.cookies),r=[];return Object.keys(n).forEach(function(a){if("function"!=typeof n[a]){var o=a.toLowerCase();r.push(asyncForEach(n[a],function(n){var r=t.find(function(e){return e.name.toLowerCase()===o});r&&n.regex.test(r.value)&&addDetected(e,n,"cookies",r.value,a)}))}}),r?Promise.all(r):Promise.resolve()}},{key:"analyzeJs",value:function(e,t){var n=this,r=[];return Object.keys(t).forEach(function(a){"function"!=typeof t[a]&&r.push(asyncForEach(Object.keys(t[a]),function(r){var o=n.jsPatterns[e.name][a][r],i=t[a][r];o&&o.regex.test(i)&&addDetected(e,o,"js",i,a)}))}),r?Promise.all(r):Promise.resolve()}}],[{key:"parseRobotsTxt",value:function(e){var t=[],n=void 0;return e.split("\n").forEach(function(e){var r=/^User-agent:\s*(.+)$/i.exec(e.trim());r?n=r[1].toLowerCase():"*"!==n&&"wappalyzer"!==n||(r=/^Disallow:\s*(.+)$/i.exec(e.trim()))&&t.push(r[1])}),t}}]),e}();"object"===("undefined"==typeof module?"undefined":_typeof(module))&&(module.exports=Wappalyzer);