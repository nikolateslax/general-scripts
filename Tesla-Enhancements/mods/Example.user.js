// ==UserScript==
// @name         [TES] Example
// @namespace    TeslaEnhancements
// @version      0.0.1
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

console.log(GM.info.script.name + " v" + GM.info.script.version + " loading...");
if (typeof(window) == 'object') {
    await _tesla.registerEnhancement(
        GM.info.script.name, GM.info.script.version, // Automagically retrieves the UserScript metadata from above. Update that and this will update.
        {
            // Called by the framework to prepare the enhancement for use
            init() {
                
            },
            // Called by the framework to start the enhancement doing its job
            ready() {
                
            },
        }
    );
}