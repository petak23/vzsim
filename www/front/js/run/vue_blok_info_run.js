/**
 * Vue komponenta pre výpis informácii v simulácii.
 * Posledna zmena(last change): 07.05.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
Vue.component('info', {
  methods: {
    skry_info() {
      this.$store.commit('SET_TEXT_INFO', "")
    },
  },
  computed: {
    infoClass: function () {
      return (this.$store.state.text_info.length > 0) ? "bg-info" : "bg-transparent";
    }
  },
  template: `
    <div class="col-6 mt-1 min-h-my"
          :class="infoClass" @click="skry_info">{{ $store.state.text_info }}</div>
    `
});