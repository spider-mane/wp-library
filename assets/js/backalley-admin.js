/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./backalley/backalley-core/src/js/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./backalley/backalley-core/src/js/index.js":
/*!**************************************************!*\
  !*** ./backalley/backalley-core/src/js/index.js ***!
  \**************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _models_MetaBox__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./models/MetaBox */ \"./backalley/backalley-core/src/js/models/MetaBox.js\");\n/* harmony import */ var _views_metaBoxView__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./views/metaBoxView */ \"./backalley/backalley-core/src/js/views/metaBoxView.js\");\n/* harmony import */ var _views_base__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./views/base */ \"./backalley/backalley-core/src/js/views/base.js\");\n\n\n // window.elements = elements;\n\nconst state = {};\nwindow.state = state;\n\nif (window.pagenow === _views_base__WEBPACK_IMPORTED_MODULE_2__[\"wpPages\"].editPostLocation) {\n  // Remove Platfrom url input from DOM and thusly POST\n  _views_base__WEBPACK_IMPORTED_MODULE_2__[\"elements\"].platformUrlContainer.addEventListener('click', function (e) {\n    if (e.target.dataset.backalleyLocationPlatform) {\n      e.preventDefault();\n      let confirmationMessage = \"Are you sure you want to remove this platform? If you save after removing it, the url associated with it for this location will be permanantly deleted.\";\n\n      if (window.confirm(confirmationMessage)) {\n        document.getElementById(e.target.dataset.backalleyLocationPlatform).remove();\n      }\n    }\n  }); // Insert new Platform url input\n\n  _views_base__WEBPACK_IMPORTED_MODULE_2__[\"elements\"].newPlatformButton.addEventListener('click', function (e) {\n    e.preventDefault();\n    _views_metaBoxView__WEBPACK_IMPORTED_MODULE_1__[\"insertNewPlatformUrlInput\"]();\n  });\n}\n\n//# sourceURL=webpack:///./backalley/backalley-core/src/js/index.js?");

/***/ }),

/***/ "./backalley/backalley-core/src/js/models/MetaBox.js":
/*!***********************************************************!*\
  !*** ./backalley/backalley-core/src/js/models/MetaBox.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return MetaBox; });\nclass MetaBox {\n  constructor(data) {\n    this.data = data;\n  }\n\n  async addNewDeliveryPlatform() {\n    try {\n      const result = jQuery.post(ajaxurl, this.data);\n      this.result = result;\n    } catch (error) {\n      alert(error);\n    }\n  }\n\n}\n\n//# sourceURL=webpack:///./backalley/backalley-core/src/js/models/MetaBox.js?");

/***/ }),

/***/ "./backalley/backalley-core/src/js/views/base.js":
/*!*******************************************************!*\
  !*** ./backalley/backalley-core/src/js/views/base.js ***!
  \*******************************************************/
/*! exports provided: elements, elementStrings, jQueryElements, wpPages */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"elements\", function() { return elements; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"elementStrings\", function() { return elementStrings; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"jQueryElements\", function() { return jQueryElements; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"wpPages\", function() { return wpPages; });\nconst elements = {\n  // Metabox\n  newPlatformDiv: document.querySelector('#backalley--new_platform--div'),\n  newPlatformInput: document.querySelector('#backalley--new_platform--input'),\n  newPlatformButton: document.querySelector('#backalley--new_platform--submit'),\n  platfromUrlTemplate: document.querySelector('#backalley--platform_url--dummy_markup'),\n  platformUrlContainer: document.querySelector('#backalley--platform_url--container'),\n  platformUrlDeleteBtn: document.querySelectorAll('#backalley--platform_url--delete_btn'),\n  // Sortable Object\n  positionValues: document.querySelectorAll('.position-value'),\n  sortableLi: document.querySelectorAll('.sortable--item-container')\n};\nconst elementStrings = {};\nconst jQueryElements = {};\nconst wpPages = {\n  editPostLocation: 'ba_location'\n};\n\n//# sourceURL=webpack:///./backalley/backalley-core/src/js/views/base.js?");

/***/ }),

/***/ "./backalley/backalley-core/src/js/views/metaBoxView.js":
/*!**************************************************************!*\
  !*** ./backalley/backalley-core/src/js/views/metaBoxView.js ***!
  \**************************************************************/
/*! exports provided: insertNewPlatformUrlInput */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"insertNewPlatformUrlInput\", function() { return insertNewPlatformUrlInput; });\n/* harmony import */ var _base__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./base */ \"./backalley/backalley-core/src/js/views/base.js\");\n\nconst insertNewPlatformUrlInput = () => {\n  let platform = _base__WEBPACK_IMPORTED_MODULE_0__[\"elements\"].newPlatformInput.value;\n\n  if (!platform) {\n    return;\n  } // band-aid af find better solution\n\n\n  let wpSanitizeKeyRegex = /[^a-z0-9_\\-]/;\n  let platformSlug = platform.toLowerCase().replace(wpSanitizeKeyRegex, '');\n  platformSlug = platform; // Container\n\n  let newPlatform = _base__WEBPACK_IMPORTED_MODULE_0__[\"elements\"].platfromUrlTemplate.cloneNode(true); //   let newPlatformId = ;\n\n  newPlatform.id = newPlatform.dataset.idFormat.replace('%platform_name%', platformSlug);\n  newPlatform.removeAttribute('hidden');\n  newPlatform.removeAttribute('data-id-format');\n  newPlatform.querySelectorAll('*').forEach(element => {\n    element.childNodes.forEach(child => {\n      if (child.nodeType === 3) {\n        child.textContent = child.textContent.replace('%platform_title%', platform);\n      }\n    });\n\n    if (element.attributes.length > 0) {\n      Array.from(element.attributes).forEach(attr => {\n        attr.value = attr.value.replace('%platform_name%', platformSlug);\n        attr.value = attr.value.replace('%platform_title%', platform);\n      });\n    }\n\n    if (element.hasAttribute('disabled')) {\n      element.removeAttribute('disabled');\n    }\n  }); //    Label\n  //   let label = newPlatform.querySelector('label');\n  //   let labelFor = label.getAttribute('for').replace('%platform_name%', platformSlug);\n  //   label.textContent = platform;\n  //   label.setAttribute('for', labelFor);\n  //    Text Input\n  //   let input = newPlatform.querySelector('input');\n  //   Array.from(input.attributes).forEach(attr => {\n  //     attr.value = attr.value.replace('%platform_name%', platformSlug);\n  //   });\n  //   input.removeAttribute('disabled');\n  //    Delete Button\n  //   let deleteButton = newPlatform.querySelector('[value=\"Remove\"]');\n  //   Array.from(deleteButton.attributes).forEach(attr => {\n  //     attr.value = attr.value.replace('%platform_name%', platformSlug);\n  //   });\n  //    Hidden name=\"tax_input...\" Input\n  //   let hiddenTax = newPlatform.querySelector('[name=\"tax_input[ba_delivery_platforms][]\"]');\n  //   let hiddenTaxVal = hiddenTax.getAttribute('value').replace('%platform_title%', platform);\n  //   hiddenTax.removeAttribute('disabled');\n  //   hiddenTax.setAttribute('value', hiddenTaxVal);\n  //   console.log(newPlatform);\n\n  _base__WEBPACK_IMPORTED_MODULE_0__[\"elements\"].platformUrlContainer.insertAdjacentElement('beforeend', newPlatform);\n  _base__WEBPACK_IMPORTED_MODULE_0__[\"elements\"].newPlatformInput.value = '';\n};\n\n//# sourceURL=webpack:///./backalley/backalley-core/src/js/views/metaBoxView.js?");

/***/ })

/******/ });