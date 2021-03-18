/**
 * Vue komponenta pre výpis chybových hlásení v simulácii.
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
Vue.component('errors', {
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
    skry_r() {
      this.textrv = false;
      this.$emit('text_r_clr', true);
    }
  },
  computed: {
    redClass: function () {
      return this.text_r.length > 0 ? "bg-danger" : "bg-transparent";
    }
  },
  watch: {
    text_r: function (newText_r, oldText_r) {
      this.textrv = this.text_r.length > 0 ? true : false;
      this.textr = newText_r;
    },
  },
  template: `
    <div class="col-6 mt-1 errors text-white"
          :class="redClass" @click="skry_r">{{textr}}</div>
    `
});