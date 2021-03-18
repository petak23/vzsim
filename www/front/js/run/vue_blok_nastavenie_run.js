/**
 * Vue komponenta pre nastavenia v simulácii.
 * Posledna zmena(last change): 18.03.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
Vue.component('nastavenie', {
  props: {
    text_v: String
  },
  template: `
    <div class="col-4 bg-info h-3">{{text_v}}</div>
    `
});