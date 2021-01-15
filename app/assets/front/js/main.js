import jquery from 'jquery';
import 'bootstrap/dist/js/bootstrap.bundle';
import naja from 'naja';
import netteForms from 'nette-forms';

window.Nette = netteForms;

/* Inicializácia pre ajax knižicu NAJA */
document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));
netteForms.initOnLoad();  

//import './pomocne_front.js';
//import './vue/MainVue.js';