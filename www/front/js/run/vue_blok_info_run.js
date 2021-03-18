/**
 * Vue komponenta pre výpis informácii v simulácii.
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
Vue.component('info', {
  props: 
    ['text_r']
  ,
  data: function () {
    return {
      textr: '',
      textrv: false
    }
  },
  methods: {
    skry_info() {
      this.textrv = false;
      this.$emit('text_r_clr', true);
    },
    zobraz_info(info) {
      this.textrv = info.length > 0 ? true : false;
      this.textr = info;
    }
  },
  computed: {
    infoClass: function () {
      return (typeof this.text_r !== 'undefined' && this.text_r.length > 0) ? "bg-info" : "bg-transparent";
    }
  },
  watch: {
    text_r: function (newText_r, oldText_r) {
      this.textrv = (typeof this.text_r !== 'undefined' && this.text_r.length > 0) ? true : false;
      this.textr = newText_r;
    },
  },
  template: `
    <div class="col-6 mt-1 min-h-my"
          :class="infoClass" @click="skry_info">{{textr}}</div>
    `
});