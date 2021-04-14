/**
 * Vue komponenta pre výpis chybových hlásení v simulácii.
 * Posledna zmena(last change): 25.03.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
Vue.component('errors', {
  props: {
    error: Object,
  },
  methods: {
    skry_r() {
      this.$emit('error_clr', true);
    }
  },
  computed: {
    redClass: function () {
      return (this.error !== null && this.error.txt.length > 0) ? "bg-danger" : "bg-transparent";
    }
  },
  template: `
    <div class="col-6 mt-1 min-h-my errors text-white" :class="redClass" @click="skry_r">{{error !== null ? error.txt : ''}}</div>
    `
});