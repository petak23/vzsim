/**
 * Vue komponenta pre výpis chybových hlásení v simulácii.
 * Posledna zmena(last change): 07.05.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */
Vue.component('errors', {
  methods: {
    skry_r() {
      this.$store.commit('SET_ERROR_INFO',  {txt: ""})
    }
  },
  computed: {
    redClass: function () {
      return (this.$store.state.error_info.txt.length > 0) ? "bg-danger" : "bg-transparent";
    }
  },
  template: `
    <div class="col-6 mt-1 min-h-my errors text-white" :class="redClass" @click="skry_r">
      {{ $store.state.error_info.txt }}
    </div>
    `
});