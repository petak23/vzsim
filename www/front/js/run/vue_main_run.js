/**
 * Hlavná časť Vue v simulácii.
 * Posledna zmena(last change): 12.05.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.4
 */

Vue.use(Vuex)

const store = new Vuex.Store({
  state: {
    text_info: "",
    status_bar: {
      txt: "",
      type: "",
    },
    error_info: {
      txt: "",
    },
    prvky: {},
    cesty: {},
    vlaky: {},
    clock: {
      interval: [200, 130, 60], // rýchlosť behu hodín - dĺžka trvania 200ms
      speed: 0,                 // Číslo rýchlosti od 0 do 2
      day: 0,                   // Deň v týždni 0=pondelok ... 6=nedeľa
      day_shortcut: ["Po", "Ut", "St", "Št", "Pi", "So", "Ne"], //Skratky dní v týždni
      isRunning: false,         // Príznak či hodiny bežia
    },
  },
  mutations: {
    SET_INIT_PRVKY (state, prvky) {
      state.prvky = prvky
    },
    SET_INIT_CESTY (state, cesty) {
      state.cesty = cesty
    },
    SET_INIT_VLAKY (state, vlaky) {
      state.vlaky = vlaky
    },
    SET_TEXT_INFO (state, text) {
      state.text_info = text
    },
    SET_STATUS_BAR (state, status_bar) {
      state.status_bar = status_bar
    },
    SET_ERROR_INFO (state, error_info) {
      state.error_info = error_info
    },
    UPDATE_PRVOK (state, prvok) {
      state.prvky[prvok.xs] = prvok
    },
    UPDATE_PRVOK_STAV (state, info) {
      state.prvky[info.xs].stav = info.stav
    },
    UPDATE_PRVOK_SM (state, info) {
      state.prvky[info.xs].sm = info.sm
    },
    UPDATE_CLOCK_SPEED (state, speed) {
      state.clock.speed = speed >= 0 && speed <= 2 ? speed : state.clock.speed
    },
    UPDATE_CLOCK_DAY (state, day) {
      state.clock.day = day
    },
    UPDATE_CLOCK_RUN (state, run) {
      state.clock.isRunning = run
    },
  },
})

new Vue({
  el: '#app',
  store: store,
  data: {
    // Premenné pre posielané $event-y
    was_clicked: null,  // canvas -> UI
    context_menu: null, // canvas -> UI
    kresli: null,       // UI -> canvas
    udalost: null,      // UI -> casovac
    urob: null,         // casovac -> UI
  },
  /*methods: {}*/
})