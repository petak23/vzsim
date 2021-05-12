/**
 * Vue komponenta pre ui v simulácii.
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
Vue.component('ui_run', {
  props: {
    prvky: String,
    cesty: String,
    vlaky: String,
    urob: Object,           // $emit z casovaca
    was_clicked: Object,    // $emit z canvas-u
    context_menu: Object,   // $emit z kontextového menu canvas-u
  },
  data: function () {
    return {
      cesta_z: null,        // Začiatok cesty
      cesta_k: null,        // Koniec cesty
    }
  },
  /*computed: {},*/
  methods: {
    doWithClicked(data) {     // Spracovanie $emitu wasClicked z canvas-u
      var pr = data !== null ? data.prvok : null;   // Prvok, na ktorý sa kliklo
      var m = data !== null ? data.mod : "";        // Mód cesty
      if (pr !== null && this.cesta_z === null) {   // Našiel som prvok a nemám začiatok cesty
        if (pr.id_prvky_kluc === 6 && (m === "V" || m === "P" && (pr.sm & 8) === 0)) { // Zaciatok vlakovej alebo posunovej cesty pre NH )
          this.zaciatok_cesty(pr, m);
        } else if (pr.id_prvky_kluc === 8) { // Zaciatok posunovej cesty pre NE
          this.zaciatok_cesty(pr, 'P');
        }
      } else if (pr !== null && this.cesta_z !== null && (pr.id_prvky_kluc === 3 || pr.id_prvky_kluc === 14 || pr.id_prvky_kluc === 22)) { // Koniec vlakovej alebo posunovej cesty
        this.cesta_k = (pr.id_prvky_kluc !== 22) ? pr : this.$store.state.prvky[pr.c[0]]; // Pre prípad kliku na KO
        var cesta = this.test_cesta(this.cesta_z, this.cesta_k);
        if (cesta !== null) {
          if (cesta !== 0) { // Mám cestu a nie je obsadená
            var ozn = (typeof this.$store.state.prvky[this.cesta_k.c[2]] !== 'undefined') ? this.$store.state.prvky[this.cesta_k.c[2]].oznacenie : "";
            this.$store.commit('SET_STATUS_BAR', 
                                { txt: "Staviam cestu: ŽST: " + ozn + " [ " + this.cesta_z.oznacenie + " -> " + this.cesta_k.oznacenie + " ]",
                                  type: (cesta.typ === 2 ? "V" : "P")
                                })
            this.postav_cestu(cesta);
          } else {           // Mám cestu ale je obsadená
            this.$store.commit('SET_STATUS_BAR', {txt: "", type:""});
            this.write_error("Cesta nie je voľná!");
            this.zrus_volbu();
          }
        } else {             // Cestu som nenašiel
          this.$store.commit('SET_STATUS_BAR', {txt: "", type:""});
          this.write_error("Cestu som nenašiel!!!");
          this.zrus_volbu();
        }
      } else {  // Zrušenie voľby začiatku
        this.zrus_volbu();
        this.$store.commit('SET_STATUS_BAR', {txt: "", type:""});
      }
      cesta = null;
    },
    zaciatok_cesty(cesta_z, typ_cesty) {
      this.$store.commit('SET_STATUS_BAR', {txt: "Začiatok cesty: " + cesta_z.oznacenie, type: typ_cesty});
      cesta_z.stav += (16 * (typ_cesty == 'V' ? 2 : 1));     // Označ návestidlo podľa typu cesty
      cesta_z.n[0] = (typ_cesty == 'V' ? 2 : 1);             // Ulož typ cesty
      this.$store.commit('UPDATE_PRVOK', cesta_z);           // Aktualizuj údaje prvku
      this.$emit('kresli', {prvok: cesta_z});                // Vykresli prvok
      this.cesta_z = cesta_z;                                // Ulož začiatok cesty
    },
    zrus_volbu() {
      let xs = this.cesta_z.xs;
      this.$store.commit('UPDATE_PRVOK_STAV', {xs: xs, stav: (this.$store.state.prvky[xs].state & 15)}); // Aktualizuj údaje prvku
      this.$emit('kresli', {prvok: this.$store.state.prvky[xs]});
      this.cesta_z = null;
      this.cesta_k = null;
    },
    write_error(txt) {
      this.$store.commit('SET_ERROR_INFO', {txt: "Cestu som nenašiel!!!"});
    },
    get_cislo_cesty(cesta_z, cesta_k) {
      var cislo_cesty = 0;    // Číslo použitej cesty 
      Object.entries(this.$store.state.cesty).forEach(([key, ce]) => { // Nájdenie cesty
        if ((ce.zc === cesta_z.xs) && (ce.kc === cesta_k.xs) && (ce.typ === cesta_z.n[0])) {
          cislo_cesty = key;                      // Zapíš číslo nájdenej cesty
        }
      });
      return cislo_cesty;
    },
    test_cesta(cesta_z, cesta_k) {
      var cislo_cesty = this.get_cislo_cesty(cesta_z, cesta_k);  // Nájdi číslo cesty
      if (cislo_cesty != 0 && this.$store.state.cesty[cislo_cesty].prvky_cesty.length > 0) { // Cesta existuje a mám prvky cesty
        cesta_z.c[3] = cesta_k.xs;                              // Zapíš do návestidla koniec cesty
        this.$store.commit('UPDATE_PRVOK', cesta_z);            // OK
        var volnost = true;
        this.$store.state.cesty[cislo_cesty].prvky_cesty.forEach(x => {       // Zisti volnost cesty
          var pro = x.split("|");
          if ((this.$store.state.prvky[pro[0]].stav & 15) != 0) {             // Je prvok obsadený? Dôležité sú stavy od 1..15 po zmazaní vyšších bitov
            volnost = false;
          }
        });
        if (this.$store.state.cesty[cislo_cesty].prvky_odvrat.length > 0) {  // Test volnosti odvratných úsekov
          this.$store.state.cesty[cislo_cesty].prvky_odvrat.forEach(x => {
            var pro = x.split("|");
            var pr = this.$store.state.prvky[pro[0]];
            if ((pr.stav & 15) > 0) {                           // Je prvok obsadený? Dôležité sú stavy od 1..15 po zmazaní vyšších bitov
              if (pr.id_prvky_kluc == 16 && pr.sm != pro[1]) {  // Odvratná výhybka nie je v správnej polohe
                volnost = false;
              }
            }
          });
        }
        if (cesta_k.ts > 0 && this.$store.state.cesty[cislo_cesty].typ == 2 && (this.$store.state.prvky[cesta_k.ts].stav != 0 || this.$store.state.prvky[cesta_k.ts].c[2] != 0)) { // Blokovaný TS
          volnost = false;
        }
        return (volnost) ? this.$store.state.cesty[cislo_cesty] : 0; // Cesta je volná tak vrat cestu inak 0
      } else {                                          // Cesta neexzistuje alebo nemá prvky
        return null;
      }
    },
    postav_cestu(cesta) {      
      var vyh = 0;                                    // Počet momentálne nesprávne prestavených výhybiek
      var for_emit = [];                              // Pole prvkov pre $emit
      var dvojica = [];                               // Pole pre dvojicu výhybiek
      var out = [];                                   // 
      cesta.prvky_cesty.forEach(x => {                // Ošetri polohu výhybiek
        var pro = x.split("|");                       // Rozlož prvok cesty na xs (a polohu výhybky)
        var pr = this.$store.state.prvky[pro[0]];     // Pre skrátenie zápisov
        if (pr.id_prvky_kluc == 16) {                 // Prvok je VN?
          if (pr.sm != pro[1]) {                      // Nie je správne prestavená
            if (vyh < 4 && this.$store.state.prvky[pro[0]].sm < 64) {  // Max sa prestavuje najviac 3 výhybky a neprestavuje sa
              this.$store.commit('UPDATE_PRVOK_SM', {xs: pro[0], sm: parseInt(pro[1]) + 64}); // Príznak prestavovania
              pr = this.$store.state.prvky[pro[0]];
              out.push(pr);
              if (pr.c[2] > 0 && dvojica.length == 0) { // Výhybka má dvojicu a je prvá
                dvojica.push(pr.xs);
                if (cesta.prvky_odvrat.includes(pr.c[2]+"|"+pro[1])) {  // Mám dvojicu ako odvrat
                  this.$store.commit('UPDATE_PRVOK_SM', {xs: pr.c[2], sm: parseInt(pro[1]) + 64});// Príznak prestavovania
                  out.push(this.$store.state.prvky[pr.c[2]]);
                  dvojica.push(pr.c[2]);
                  for_emit.push({cas: 50, xs: dvojica, sm: pro[1]}); // Priprav pre $emit
                  dvojica = [];                       // Vymazanie poľa
                  vyh++;                              // Dvojica v stave prestavovania
                } 
              } else if (pr.c[2] > 0 && dvojica.length > 0) {  // Výhybka má dvojicu a je druhá
                dvojica.push(pr.xs);
                for_emit.push({cas: 50, xs: dvojica, sm: pro[1]}); // Priprav pre $emit
                dvojica = [];                         // Vymazanie poľa
                vyh++;
              } else {
                for_emit.push({cas: 25, xs: pro[0], sm: pro[1]});  // Priprav pre $emit
                vyh++;
              }
            } else if (this.$store.state.prvky[pro[0]].sm >= 64) { // Ak sa prestavuje tak započítaj
              vyh++;
            }
          }
        }
      });
      
      var ts = this.$store.state.prvky[cesta.kc].ts;
      var _tsp = this.$store.state.prvky[ts];
      if (ts > 0 && cesta.typ == 2) {
        if (_tsp.sm != this.$store.state.prvky[cesta.zc].sm) { // TS je nesprávne
          this.$store.commit('UPDATE_PRVOK_STAV', {xs: ts, stav: 4}); // Aktualizuj údaje prvku
          this.$emit('udalost', {cas: 20, xs: ts, cesta: cesta, do: 'zmenTS'});
          vyh++;
        } else if (vyh == 0) {
          _tsp.c[2] |= 1;
          this.$store.commit('UPDATE_PRVOK', _tsp);// OK
        }
        out.push(_tsp);
      }
      if (vyh == 0) { // Všetky výhybky sú správne prestavené
        cesta.prvky_cesty.forEach(xs => {
          var pro = xs.split("|");                        // Rozlož prvok cesty na xs (a polohu výhybky)
          if (pro[0] == cesta.zc) {                       // Pre počiatočné návestidlo na rozsvietenie o 1s
            out.push(this.$store.state.prvky[pro[0]]);
            this.$emit('udalost', {cas: 10, xs: pro[0], nst: cesta.typ, do: 'zmenPrvok'});
          } else {
            //Zapíš do prvkov záver cesty okrem návestidiel
            if (this.$store.state.prvky[pro[0]].id_prvky_kluc !== 6 && this.$store.state.prvky[pro[0]].id_prvky_kluc !== 8) {
              this.$store.commit('UPDATE_PRVOK_STAV', {xs: pro[0], stav: cesta.typ}); // Aktualizuj údaje prvku
            }
            out.push(this.$store.state.prvky[pro[0]]);
          }
        });
        this.$emit('kresli', {prvok: out}); // Vykresli cestu
        this.cesta_z = null;
        this.cesta_k = null;  
      } else {
        this.cesta_z.stav &= 15;
        out.push(this.cesta_z);
        this.$emit('kresli', {prvok: out});
        if (for_emit.length > 0) {
          this.$emit('udalost', {cas: 25, prvky: for_emit, cesta: cesta, do: 'prestavVN'});
        }
      }
    },
    prestavVN(xs, sm) {                                   // Slúži na grafické ukončenie prestavovania VN
      if (!!xs && xs.constructor === Array) { // Je to pole? https://stackoverflow.com/questions/4775722/how-to-check-if-an-object-is-an-array
        var out = []
        xs.forEach(x => {
          if (this.$store.state.prvky[x].id_prvky_kluc == 16 && sm > 0) { // Je to VN a prestavuje sa
            this.$store.commit('UPDATE_PRVOK_SM', {xs: x, sm: sm});       // Zmeň polohu výhybky
            out.push(this.$store.state.prvky[x]);
          }
        });
        this.$emit('kresli', {prvok: out});                    // Vykresli VN
      } else if (this.$store.state.prvky[xs].id_prvky_kluc == 16 && sm > 0) { // Je to VN a prestavuje sa
        this.$store.commit('UPDATE_PRVOK_SM', {xs: xs, sm: sm});       // Zmeň polohu výhybky
        this.$emit('kresli', {prvok: this.$store.state.prvky[xs]});    // Vykresli VN
      }
    },
    rus_cestu(cesta_z, cesta_k) {
      var cislo_cesty = this.get_cislo_cesty(this.$store.state.prvky[cesta_z], this.$store.state.prvky[cesta_k]);
      this.$store.commit('UPDATE_PRVOK_STAV', {xs: cesta_z, stav: 0}); // Aktualizuj údaje prvku
      var out = [];
      out.push(this.$store.state.prvky[cesta_z]);
      this.$store.state.cesty[cislo_cesty].prvky_cesty.forEach(xs => {
        var pro = xs.split("|");                        // Rozlož prvok cesty na xs (a polohu výhybky)
        if (this.$store.state.prvky[pro[0]].id_prvky_kluc !== 6 && this.$store.state.prvky[pro[0]].id_prvky_kluc !== 8) {
          this.$store.commit('UPDATE_PRVOK_STAV', {xs: pro[0], stav: 0}); // Aktualizuj údaje prvku
        }
        out.push(this.$store.state.prvky[pro[0]]); 
      });
      var ts = this.$store.state.prvky[cesta_k].ts;
      if (ts > 0 && this.$store.state.cesty[cislo_cesty].typ == 2) {
        var _tsp = this.$store.state.prvky[ts];
        _tsp.c[2] = (_tsp.c[2] >> 1) << 1 ;  // Nastavím bit 0 na 0 
        this.$store.commit('UPDATE_PRVOK', _tsp);
        out.push(this.$store.state.prvky[ts]); 
      }
      this.$emit('kresli', {prvok: out});
    },
  },
  watch: {
    was_clicked: function (newWas_clicked) {
      this.doWithClicked(newWas_clicked);
    },
    context_menu: function (newContext_menu) {
      var pr = newContext_menu.prvok;
      this.$store.commit('SET_STATUS_BAR', {txt: "Prijatý príkaz:" + newContext_menu.pol + " pre prvok:" + pr.xs, type: "P"});
      switch (newContext_menu.pol) {
        case 'STOJ':  
          this.$store.commit('UPDATE_PRVOK_STAV', {xs: pr.xs, stav: 0});
          this.$emit('kresli', {prvok: this.$store.state.prvky[pr.xs]});
          break;
        case 'RC':  
          this.$store.commit('UPDATE_PRVOK_STAV', {xs: pr.xs, stav: 5});
          this.$emit('kresli', {prvok: this.$store.state.prvky[pr.xs]});
          this.$emit('udalost', {cas: 50, xs: pr.xs, nst: pr.c[3], do: 'rusCestu'});
          break;
        case 'DN':  
          if (pr.c[3] > 0) {                                          // Je v návestidle zapísaný koniec cesty
            this.$store.commit('UPDATE_PRVOK_STAV', {xs: pr.xs, stav: (this.$store.state.prvky[pr.c[0]].stav)}); // Stav nastav podľa prvku za návestidlom
            this.$emit('kresli', {prvok: this.$store.state.prvky[pr.xs]});
          }
          break;
        case 'VC': this.zaciatok_cesty(pr, 'V');
          break;
        case 'PC': this.zaciatok_cesty(pr, 'P');
          break;
      }
    },
    urob: function (newUrob) {
      switch (newUrob.do) {
        case "rusCestu": this.rus_cestu(newUrob.xs, newUrob.nst); // Spracovanie pre rušenie cesty
          break;
        case "zmenTS": 
          this.$store.commit('UPDATE_PRVOK_STAV', {xs: newUrob.xs, stav: 0}); // Zmeň TS
          this.$store.commit('UPDATE_PRVOK_SM', {xs: newUrob.xs, sm: this.$store.state.prvky[newUrob.cesta.zc].sm}); // Zmeň smer TS
          this.$emit('kresli', {prvok: this.$store.state.prvky[newUrob.xs]});  // Vykresli TS
          this.postav_cestu(newUrob.cesta);
          break;
        case 'zmenPrvok':
          this.$store.commit('UPDATE_PRVOK_STAV', {xs: newUrob.xs, stav: newUrob.nst}); // Zmeň stav prvku
              this.$emit('kresli', {prvok: this.$store.state.prvky[newUrob.xs]});  // Vykresli ho
          break;
        case 'prestavVN':
              this.prestavVN(newUrob.xs, newUrob.sm);
              this.$emit('udalost', {cas: 10, cesta: newUrob.cesta, do: 'postavCestu'});
          break;
        case 'postavCestu':
              this.postav_cestu(newUrob.cesta);
          break;
        default: console.log(newUrob.do);
      }
    }
  },
  created: function () {
    this.$store.commit('SET_INIT_PRVKY', JSON.parse(this.prvky))
    this.$store.commit('SET_INIT_CESTY', JSON.parse(this.cesty))
    this.$store.commit('SET_INIT_VLAKY', JSON.parse(this.vlaky))
  },
  template: ` `
});