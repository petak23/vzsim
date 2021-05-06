import jquery from 'jquery';
import 'bootstrap/dist/js/bootstrap.bundle';

import naja from 'naja';
document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

import datagrid from 'ublaboo-datagrid/assets/datagrid.js';

import netteForms from 'nette-forms';
netteForms.initOnLoad(); 
window.Nette = netteForms;

import 'ublaboo-datagrid/assets/datagrid-instant-url-refresh.js';
import 'ublaboo-datagrid/assets/datagrid-spinners.js';

//import './pomocne_admin.js';
jquery(function() {
/* pre zmenu náhľadu pri zmenách okrajového rámčeka */
        jquery("#frm-products-zmenOkrajForm").find("input.input_number").each(function(){
                var el = jquery(this);
                el.change(function(){
                        var val = el.val();
                        var cl = el.attr('name').split("_");
                        jquery(".okraj-"+cl[1]).each(function(){
                                jquery(this).css("border-width", val+"px");
                        });
                });
        });

        jquery("#frm-products-zmenOkrajForm").find("input[type=color]").each(function(){
                var el = jquery(this);
                el.change(function(){
                        var val = el.val();
                        var cl = el.attr('name').split("_");
                        jquery(".okraj-"+cl[1]).each(function(){
                                jquery(this).css("border-color", val);
                        });
                });
        });
});