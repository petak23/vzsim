/**
 * Hlavná časť Vue v simulácii.
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
new Vue({
  el: '#app',
  data: {
    text_g: "",
    text_r: "",
    udalost: null,
    urob: null,
  },
  methods: {
    text_r_clear() {
      this.text_r = "";
    },
    text_g_clear() {
      this.text_g = "";
    }
  }
});