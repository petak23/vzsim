import jquery from 'jquery';
import bootstrap from 'bootstrap';
import naja from 'naja';
import netteForms from 'nette-forms';

window.Nette = netteForms;

/* Inicializácia pre ajax knižicu NAJA */
document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));
netteForms.initOnLoad();  

//import './pomocne_front.js';
//import './vue/MainVue.js';