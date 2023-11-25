// ==UserScript==
// @name         Tesla Enhancements Suite
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  User-script enhancements to various sites and platforms that I use.
// @author       NikolaTeslaX
// @match        *
// @icon         
// @grant        none
// ==/UserScript==

(function() {
    'use strict';
    window._tes = {
        log: function(src, msg) {
            src = src.filter(function(item, pos, arr){
                return pos === 0 || item !== arr[pos-1];
            });
            console.log("[TeslaEnhancements: " + src.join("/") + "] " + msg);
        },
        subLog: function(src, msg) {
            this.parent.log([this.name].concat(src), msg);
        },
        deps: {
            name: "DepMngr",
            depList: [],
            add: function(src, dep, ele) {
                _tesla.log([src.name], "Dependency request for " + dep + " from " + src.name);
                if (!this.depList.contains(dep)) {
                    _tesla.log([src.name], "Adding dependency for " + dep);
                    this.depList[this.depList.length] = ele;
                    jQuery(document.head).append(ele);
                } else {
                    _tesla.log([src.name], "Dependency " + dep + " already added");
                }
            }
        },
        root: null,
        init: function() {
            this.log(["Init"], "Initializing " & GM.info.script.name & " v" + GM.info.script.version);
            let turkey = this;
            for (let i in this) {
                if (typeof this[i] == 'object') {
                    this[i].init = this.init;
                    this[i].init();
                    this[i].parent = this;
                    if (this["root"] !== null) {
                        this[i].root = turkey;
                    }
                    if (Object.hasOwn(this[i], 'name')) {
                        this[i].log = function(src, msg) { this.parent.log([this.name].concat(src), msg); }
                    }
                }
            }
            return this;
        }
    }
    _tes.init();
})();
