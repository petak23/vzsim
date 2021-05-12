/**
 * Vue komponenta pre stavový riadok v simulácii.
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
Vue.component('statusbar', {
  data: function () {
    return {
      timer:null,             // Časovač pre zhasnutie status baru
      interval_of_view: 8000, // Dĺžka zobrazenia info v status bare
    }
  },
  methods: {
    skry_g() {
      this.$store.commit('SET_STATUS_BAR', { txt:"", type: ""})
    },
    start_g() {
      if (!this.timer) {
        this.timer = setInterval( () => { // https://codepen.io/edscode/pen/QXXowy
          this.$store.commit('SET_STATUS_BAR', { txt:"", type: ""})
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
      return this.$store.state.status_bar.type == "" ? 'bg-transparent' 
                                                     : (this.$store.state.status_bar.type === "V" ? 'bg-success text-white' 
                                                                                                  : (this.$store.state.status_bar.type === "P" ? 'bg-light' 
                                                                                                                                               : 'bg-transparent'));
    },
  },
  watch: {
    '$store.state.status_bar.txt': function () {         // https://stackoverflow.com/a/56461539
      if (this.$store.state.status_bar.txt.length > 0) {
        this.stop();
        this.start_g();
      }
    },
  },
  template: `
      <div :class="activeClass" @click="skry_g">{{ $store.state.status_bar.txt }}</div>
    `
});