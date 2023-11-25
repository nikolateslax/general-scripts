// ==UserScript==
// @name         Tesla Enhancements Suite
// @namespace    TeslaEnhancements
// @version      1.0.0-alpha
// @description  User-script framework to power the personal enhancements I write for sites and platforms that I use.
// @author       Nicholas Sklar
// @run-at       document-end

// @match        *://*/*
// @icon64       https://github.com/nikolateslax/general-scripts/raw/main/Tesla-Enhancements/Gear.ico
// @grant        none
// @updateURL    https://github.com/nikolateslax/general-scripts/raw/main/Tesla-Enhancements/TeslaEnhancements.user.js
// @downloadURL  https://github.com/nikolateslax/general-scripts/raw/main/Tesla-Enhancements/TeslaEnhancements.user.js
// @require      https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js#sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==
// ==/UserScript==

if (typeof(window) == 'object') {
    if (typeof(window._tesla) == "undefined") {
        window._tesla = {
            name: "TES",
            url: new URL(window.location.href),
            enhancements: {
                initHierarchy() {
                    for (var i in this) {
                        if (typeof this[i] == 'object') {
                            if (!(Object.hasOwn(this[i], "parent") || Object.hasOwn(this[i], "root") || Object.hasOwn(this[i], "system"))) { 
                                this[i].initHierarchy = this.initHierarchy;
                                this[i].initHierarchy();
                                this[i].parent = this;
                                this[i].root = _tesla.enhancements;
                                this[i].system = _tesla;
                            }
                        }
                    }
                    return this;
                },
            },
            filterUnique(item, pos, arr) { return pos === 0 || item !== arr[pos-1]; },
            log(src, msg) { console.log("[$src] $msg".replace("$src", ([this.name].concat(src)).filter(_tesla.filterUnique).join("/")).replace("$msg", msg)); },
            getId(id) { return "_$addr".replace("$addr", id.filter(_tesla.filterUnique).join("_")); },
            init() {
                this.log([this.name], "Initializing " + GM.info.script.name + " v" + GM.info.script.version + "...");
                this.enhancements.log = (src, msg) => { _tesla.log(src, msg); }
                this.enhancements.getId = (id) => { return _tesla.getId.getId(id); }
                this.enhancements.parent = this.enhancements;
                this.enhancements.initHierarchy();
                this.log(["Init"], GM.info.script.name + " v" + GM.info.script.version + " ready.");
                return this;
            },
            async registerEnhancement(name, ver, obj) {
                this.log([this.name], "Enhancement $ext/v$ver wants to register".replace("$ext", name).replace("$ver", ver))
                if (!Object.hasOwn(this.enhancements, name)) {
                    try {
                        if (Object.hasOwn(obj, 'init') && typeof(obj.init == 'function')) { obj.init(); }
                        obj.name = name;
                        obj.version = ver;
                        obj.log = function() { this.parent.log([this.name].concat(src), msg); }
                        obj.getId = function(id) { return this.parent.getId([this.name].concat(id)); }
                        this.enhancements[name] = obj;
                        this.enhancements.initHierarchy();
                        if (Object.hasOwn(obj, 'ready') && typeof(obj.ready == 'function')) { obj.ready(); }
                        this.log([this.name], "Enhancement $ext/v$ver registered successfully".replace("$ext", name).replace("$ver", ver));
                        return Promise.resolve(_tesla);
                    } catch (ex) {
                        this.log([this.name], "Enhancement $ext/v$ver failed to register. Error: $ex".replace("$ext", name).replace("$ver", ver).replace("$ex", ex));
                        return Promise.reject(ex);
                    }
                } else {
                    this.log([this.name], "Enhancement collision: $ext/v$ver collides with $oExt/v$oVer".replace("$ext", name).replace("$ver", ver).replace("$oExt", obj.name).replace("$oVer", obj.version));
                    return Promise.reject("Enhancement collision");
                }
            }
        };
        
        _tesla.init();
    }
}
