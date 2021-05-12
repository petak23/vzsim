/**
 * Vue komponenta pre časovač a časovú frontu v simulácii.
 * Posledna zmena(last change): 12.05.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */
Vue.component('casovac', {
  props: {  
    udalost: Object, 
    day: {              // Deň v týždni 1=pondelok ... 7=nedeľa
      type: String,
      default: 1,        
      validator: function (value) {
        return parseInt(value) >= 1 && parseInt(value) <=7;
      }
    },
    hour: {             // Počiatočný čas v desatinách sekúnd
      type: String,
      default: 288000,  // 08:00:00
      validator: function (value) {
        return parseInt(value) >= 0 && parseInt(value) < 864000;
      }
    },
    initfronta: String  // Json objekt pre iniciáciu casovej fronty
  },
  data: function () {
    return {
      time: 288000,             // Počiatočný čas v desatinách sekúnd 8:00 = 8 * 60 * 60 * 10
      timer:null,               // Timer
      casova_fronta: [],        // Časová fronta pre udalosti
      first_run: false          // Prvé spustenie hodín
    };
  },
  computed: {
    time_u: function () {
      let mytime = parseInt(this.time /10);
      let time = mytime / 60;
      let secondes = parseInt((mytime - parseInt((mytime / 60)) * 60));
      let minutes = parseInt(parseInt((mytime / 60)) % 60);
      let hours = parseInt(time / 60);
      if (hours < 10) {
        hours = "0"+hours;
      }
      if (minutes < 10) {
        minutes = "0"+minutes;
      }
      if (secondes < 10) {
        secondes = "0"+secondes;
      }
      return hours+":"+minutes+":"+secondes;
    },
    speedClass: function () {
      switch (this.$store.state.clock.speed) {
        case 0: return "bg-success"; break;
        case 1: return "bg-warning"; break;
        case 2: return "bg-danger"; break;
      }
    },
    speedWidth: function () {
      return ((this.$store.state.clock.speed + 1)/3 * 100)+"%";
    }
  },
  methods: {
    casovacStart () {
      this.$store.commit('UPDATE_CLOCK_RUN', true)
      this.first_run = true
      if (!this.timer) {
        this.timer = setInterval( () => {   // Funkcia spúšťaná pravidelne pri behu časovača
          this.time += 2;
          if (this.time >= 863999) {        // Prešiel som cez polnoc (24*60*60*10)=864000
            this.$store.commit('UPDATE_CLOCK_DAY', this.$store.state.clock.day + (this.$store.state.clock.day < 6 ? 1 : -6)) // Pripočítaj deň ak je Ne -> Po 
            Object.keys(this.casova_fronta).forEach(cs => { // Prepočítaj časovú frontu 
              this.casova_fronta[cs].cas -= this.casova_fronta[cs].cas >= 864000 ? 864000 : 0;
            });
            this.time -= 864000;            // Zmeň čas
          }
          this.test_fronta();
        }, this.$store.state.clock.interval[this.$store.state.clock.speed]);
      }
    },
    casovacStop () {
      this.$store.commit('UPDATE_CLOCK_RUN', false);
      clearInterval(this.timer);
			this.timer = null;
    },
    casovacUp() {
      this.$store.commit('UPDATE_CLOCK_SPEED', this.$store.state.clock.speed + (this.$store.state.clock.speed < 2 ? 1 : 0))
      if (this.$store.state.clock.isRunning) {
        this.casovacStop();
        this.casovacStart();
      }
    },
    casovacDown() {
      this.$store.commit('UPDATE_CLOCK_SPEED', this.$store.state.clock.speed - (this.$store.state.clock.speed > 0 ? 1 : 0))
      if (this.$store.state.clock.isRunning) {
        this.casovacStop();
        this.casovacStart();
      }
    },
    test_fronta() {
      if (this.casova_fronta.length && this.casova_fronta[0].cas <= this.time) { // Zisti či sa má udiať prvý prvok fronty
        var first = this.casova_fronta.shift();     // Vyber prvý prvok z poľa
        this.$emit("urob", first);                  // Odošli na spracovanie
      }
    },
    initCasovac() { // Počiatočné naplnenie časovača a dňa
      this.$store.commit('UPDATE_CLOCK_DAY', parseInt(this.day) - 1)
      this.time = parseInt(this.hour);

      if (typeof this.initfronta  !== "undefined") {
        var to_tf = JSON.parse(this.initfronta);
        to_tf.forEach(i => {
          this.casova_fronta.push(i);
        });
      }
    },
  },
  mounted () {
    this.initCasovac();
  },
  created() {
    this.timer = setInterval( () => {
      this.casovacStop();
      this.casovacStart();
    }, 1000);
  },
  watch: {
    udalost: function (newUdalost) {
      if (!!newUdalost.prvky && newUdalost.prvky.constructor === Array) { // Je to pole? https://stackoverflow.com/questions/4775722/how-to-check-if-an-object-is-an-array
        newUdalost.prvky.forEach(pr => {
          pr.cas += this.time;
          this.casova_fronta.push({cas: pr.cas, xs: pr.xs, cesta: newUdalost.cesta, sm: pr.sm, do: newUdalost.do});
        });
      } else {
        newUdalost.cas += this.time;
        this.casova_fronta.push(newUdalost);  
      }
      this.casova_fronta.sort(function(a, b){return a.cas - b.cas;});
    }
  },
  template: `
    <div class="col-2 bg-dark text-white">
      <div class="btn-group btn-group-sm" role="group" aria-label="hodiny">
        <button class="btn btn-outline-info btn-sm disabled">{{$store.state.clock.day_shortcut[$store.state.clock.day]}}</button>
        <button @click="casovacDown" class="btn btn-outline-info btn-sm" :class="$store.state.clock.speed == 0 ? 'disabled' : ''">
          <i class="fas fa-level-down-alt"></i>
        </button>
        <button class="btn btn-outline-info btn-sm disabled" >{{time_u}}</button>
        <button @click="casovacUp" class="btn btn-outline-info btn-sm" :class="$store.state.clock.speed == 2 ? 'disabled' : ''">
          <i class="fas fa-level-up-alt"></i>
        </button>
        <button @click="casovacStart" v-if="!$store.state.clock.isRunning" class="btn btn-outline-danger btn-sm"><i class="fas fa-power-off"></i></button>
        <button @click="casovacStop" v-if="$store.state.clock.isRunning" class="btn btn-outline-success btn-sm"><i class="fas fa-power-off"></i></button>                                                                                    
      </div>
      <div class="progress" style="height: 5px">
        <div class="progress-bar" role="progressbar"
              :class="speedClass" 
              v-bind:style="{width: speedWidth}" 
              aria-valuemin="0" aria-valuemax="2"></div>
      </div>
    </div>
    `
});