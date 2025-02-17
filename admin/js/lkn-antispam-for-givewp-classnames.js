/**
 * Minified by jsDelivr using Terser v5.19.2.
 * Original file: /npm/classnames@2.5.1/index.js
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
/*!
    Copyright (c) 2018 Jed Watson.
    Licensed under the MIT License (MIT), see
    http://jedwatson.github.io/classnames
*/
!(function () { 'use strict'; const t = {}.hasOwnProperty; function e() { for (var t = '', e = 0; e < arguments.length; e++) { const o = arguments[e]; o && (t = r(t, n(o))) } return t } function n(n) { if (typeof n === 'string' || typeof n === 'number') return n; if (typeof n !== 'object') return ''; if (Array.isArray(n)) return e.apply(null, n); if (n.toString !== Object.prototype.toString && !n.toString.toString().includes('[native code]')) return n.toString(); let o = ''; for (const i in n) t.call(n, i) && n[i] && (o = r(o, i)); return o } function r(t, e) { return e ? t ? t + ' ' + e : t + e : t } typeof module !== 'undefined' && module.exports ? (e.default = e, module.exports = e) : typeof define === 'function' && typeof define.amd === 'object' && define.amd ? define('classnames', [], function () { return e }) : window.classNames = e }())
// # sourceMappingURL=/sm/50a6ceeee5bc0c3f552f4ddc31add6575d0d07394c7127f1a9ff57cd5f389a58.map
