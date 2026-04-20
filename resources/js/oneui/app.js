// // --- jQuery global primero ---
// import jQuery from 'jquery';
// window.$ = window.jQuery = jQuery;

// // --- Shim para require() usado por plugins legacy CommonJS ---
// if (typeof window.require === 'undefined') {
//   window.require = (name) => {
//     if (name === 'jquery') return window.jQuery;
//     // podés agregar más aliases si hiciera falta:
//     // if (name === 'datatables.net') return window.jQuery.fn.DataTable ? window.jQuery : {};
//     console.warn('require() llamado para:', name, '→ devolviendo objeto vacío');
//     return {};
//   };
// }

// Usar la instancia global que dejó el CDN
const $ = window.jQuery;
if (!$ || !$.fn) {
  console.warn('jQuery global no cargó antes del bundle');
}

// Luego el resto de tus imports:
import 'bootstrap';

// import DataTable from 'datatables.net-bs5';
// DataTable(window, window.$);
// import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

// --- Dummy require para plugins legacy ---
window.require = window.require || function () {
  return window;
};

// --- Select2 (desde node_modules) ---
// import 'select2/dist/js/select2.full.js'
// import 'select2/dist/css/select2.css'

// (opcional: si tenés SCSS/CSS propios, mantenelos)
import '../bootstrap'               // si tuvieras este archivo
// import './modules/loquesea'     // el resto de tus módulos ESM

// Luego los plugins

import '../plugins/dropzone/dropzone.css';
// import '../plugins/dropzone/dropzone.js';
import '../plugins/cropperjs/cropper.min.css';
import '../plugins/cropperjs/cropper.min.js';
import '../plugins/select2/css/select2.min.css';
import '../plugins/select2/js/select2.full.min.js';
import '../plugins/datatables-bs5/css/dataTables.bootstrap5.css';
import '../plugins/datatables/dataTables.js';
import '../plugins/datatables-bs5/js/dataTables.bootstrap5.js';
import '../plugins/datatables-bs5/js/dataTables.bootstrap5.js';
import '../plugins/bootstrap-notify/bootstrap-notify.js';

// Import required modules
import Template from "./modules/template.js";

// App extends Template
export default class App extends Template {
  /*
   * Auto called when creating a new instance
   *
   */
  constructor(options) {
    super(options);
  }

  /*
   *  Here you can override or extend any function you want from Template class
   *  if you would like to change/extend/remove the default functionality.
   *
   *  This way it will be easier for you to update the module files if a new update
   *  is released since all your changes will be in here overriding the original ones.
   *
   *  Let's have a look at the _uiInit() function, the one that runs the first time
   *  we create an instance of Template class or App class which extends it. This function
   *  inits all vital functionality but you can change it to fit your own needs.
   *
   */

  /*
   * EXAMPLE #1 - Removing default functionality by making it empty
   *
   */

  //  _uiInit() {}

  /*
   * EXAMPLE #2 - Extending default functionality with additional code
   *
   */

  //  _uiInit() {
  //      // Call original function
  //      super._uiInit();
  //
  //      // Your extra JS code afterwards
  //  }

  /*
   * EXAMPLE #3 - Replacing default functionality by writing your own code
   *
   */

  //  _uiInit() {
  //      // Your own JS code without ever calling the original function's code
  //  }
}

// Create a new instance of App
window.One = new App({ darkMode: "system" }); // Default darkMode preference: "on" or "off" or "system"
