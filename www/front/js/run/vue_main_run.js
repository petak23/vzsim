/**
 * Hlavná časť Vue v simulácii.
 * Posledna zmena(last change): 25.03.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */
new Vue({
  el: '#app',
  data: {
    // Premenné pre posielané $event-y
    was_clicked: null,  // canvas -> UI
    context_menu: null, // canvas -> UI
    kresli: null,       // UI -> canvas
    status: null,       // UI -> statusbar
    udalost: null,      // UI -> casovac
    urob: null,         // casovac -> UI
    error: null,        // UI -> errors
  },
  methods: {
    error_clear() {
      this.error = null;
    },
    status_clear() {
      this.status = null;
    }
  }
});