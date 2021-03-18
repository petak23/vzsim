/**
 * Vue komponenta pre stavový riadok v simulácii.
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
Vue.component('statusbar', {
  props: 
    ['text_g']
  ,
  data: function () {
    return {
      textg: '',
      textgv: false,
      timer:null
    }
  },
  methods: {
    skry_g() {
      this.textgv = false;
      this.$emit('text_g_clr', true);
    },
    start_g() {
      if (!this.timer) {
        this.timer = setInterval( () => { // https://codepen.io/edscode/pen/QXXowy
          this.textg = "";
          this.textgv = false;
          this.$emit('text_g_clr', true);
          clearInterval(this.timer);
          this.timer = null;
          this.stop();
        }, 8000 );
			}
    },
    stop () {
      clearInterval(this.timer);
      this.timer = null;
    }
  },
  computed: {
    activeClass: function () {
      var sp = this.text_g.split("<||>");
      return sp[1] === 'V' ? 'bg-success text-white' : (sp[1] === 'P' ? 'bg-light' : 'bg-transparent');
    }
  },
  watch: {
    text_g: function(newText_g, oldText_g) {
      this.textgv = this.text_g.length > 0 ? true : false;
      var sp = newText_g.split("<||>");
      this.textg = sp[0];
      this.start_g();
    }
  },
  template: `
      <div :class="activeClass" @click="skry_g">{{textg}}</div>
    `
});