// ==UserScript==
// @name         [TES] Example
// @namespace    TeslaEnhancements
// @version      0.0.1-dev
// @description  Tesla Enhancement Suite enhancement example. This enhancement provides an example on writing enhancements.
// @author       Nicholas Sklar
// @run-at       document-idle

// @match        https://example.org/
// @icon64       https://github.com/nikolateslax/general-scripts/raw/main/Tesla-Enhancements/Gear.ico
// @grant        none
// @updateURL    https://github.com/nikolateslax/general-scripts/raw/main/Tesla-Enhancements/mods/Example.user.js
// @downloadURL  https://github.com/nikolateslax/general-scripts/raw/main/Tesla-Enhancements/mods/Example.user.js
// @require      https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js#sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==
// ==/UserScript==

if (GM.info.script.version.endsWith("-dev")) { console.log(GM.info.script.name + " v" + GM.info.script.version + " loading..."); }
if (typeof(window) == 'object') {
    await _tesla.registerEnhancement(
        GM.info.script.name, GM.info.script.version, // Automagically retrieves the UserScript metadata from above. Update that and this will update.
        {
            // You'll be provided with `system`, `parent`, and `name` properties.
            // System references my root variable, window._tesla.
            // Parent is an automatic reference to the parent of the object.
            // Name is determined by the UserScript metadata, or optionally, whatever you replace `GM.info.script.name` with.
            // `init()` is called by the framework to prepare the enhancement for use
            init() {
                
            },
            // `ready()` is called by the framework when it's ready for the enhancement to start doing its job
            ready() {
                
            },
        }
    );
}