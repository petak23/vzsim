/**
 * Hlavná časť Vue v simulácii.
 * Posledna zmena(last change): 24.03.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
new Vue({
  el: '#app',
  data: {
    text_r: "",
    udalost: null,
    urob: null,

    // Premenné pre posielané $event-y
    was_clicked: null,  // canvas -> UI
    kresli: null,       // UI -> canvas
    status: null,       // UI -> statusbar
  },
  methods: {
    text_r_clear() {
      this.text_r = "";
    },
    status_clear() {
      this.status = null;
    }
  }
});