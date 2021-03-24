/**
 * Vue komponenta pre stavový riadok v simulácii.
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
Vue.component('statusbar', {
  props: {
    status: Object,
  },
  data: function () {
    return {
      timer:null,             // Časovač pre zhasnutie status baru
      interval_of_view: 8000, // Dĺžka zobrazenia info v status bare
    }
  },
  methods: {
    skry_g() {
      this.$emit('status_clr', true);
    },
    start_g() {
      if (!this.timer) {
        this.timer = setInterval( () => { // https://codepen.io/edscode/pen/QXXowy
          this.$emit('status_clr', true);
          clearInterval(this.timer);
          this.timer = null;
          this.stop();
        }, this.interval_of_view );
			}
    },
    stop () {
      clearInterval(this.timer);
      this.timer = null;
    }
  },
  computed: {
    activeClass: function () {
      return this.status == null ? 'bg-transparent' : (this.status.type === 'V' ? 'bg-success text-white' : (this.status.type === 'P' ? 'bg-light' : 'bg-transparent'));
    },
    text_status: function () {
      return this.status == null ? "" : this.status.txt;
    }
  },
  watch: {
    status: function(newStatus) {
      if (newStatus !== null) {
        this.start_g();
      }
    }
  },
  template: `
      <div :class="activeClass" @click="skry_g">{{text_status}}</div>
    `
});