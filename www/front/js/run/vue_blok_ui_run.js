/**
 * Vue komponenta pre ui v simulácii.
 * Posledna zmena(last change): 25.03.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */
Vue.component('ui_run', {
  props: {
    prvky: String,
    cesty: String,
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
  computed: {
    // Parsovanie JSON-u  na array
    myprv() {
      return JSON.parse(this.prvky);
    },
    mycst() {
      return JSON.parse(this.cesty);
    },
  },
  methods: {
    doWithClicked(data) {     // Spracovanie $emitu wasClicked z canvas-u
      var pr = data !== null ? data.prvok : null;
      var m = data !== null ? data.mod : "";
      if (pr !== null && this.cesta_z === null) {
        if (pr.id_prvky_kluc === 6 && (m === "V" || m === "P" && (pr.sm & 8) === 0)) { // Zaciatok vlakovej alebo posunovej cesty pre NH )
          this.zaciatok_cesty(pr.xs, m);
        } else if (pr.id_prvky_kluc === 8) { // Zaciatok posunovej cesty pre NE
          this.zaciatok_cesty(pr.xs, 'P');
        }
      } else if (pr !== null && this.cesta_z !== null && (pr.id_prvky_kluc === 3 || pr.id_prvky_kluc === 14 || pr.id_prvky_kluc === 22)) { // Koniec vlakovej alebo posunovej cesty
        this.cesta_k = (pr.id_prvky_kluc !== 22) ? pr : this.myprv[pr.c[0]]; // Pre prípad kliku na KO
        var cesta = this.test_cesta(this.cesta_z, this.cesta_k);
        if (cesta !== null) {
          if (cesta !== 0) { // Mám cestu a nie je obsadená
            var ozn = (typeof this.myprv[this.cesta_k.c[2]] !== 'undefined') ? this.myprv[this.cesta_k.c[2]].oznacenie : "";
            this.$emit('status', 
                        { txt: "Staviam cestu: ŽST: " + ozn + " [ " + this.cesta_z.oznacenie + " -> " + this.cesta_k.oznacenie + " ]",
                          type: (cesta.typ === 2 ? "V" : "P")
                        });
            this.postav_cestu(cesta);
          } else {           // Mám cestu ale je obsadená
            this.$emit('status', {txt: "", type:"N"});
            this.write_error("Cesta nie je voľná!");
            this.zrus_volbu();
          }
        } else {             // Cestu som nenašiel
          this.$emit('status', {txt: "", type:"N"});
          this.write_error("Cestu som nenašiel!!!");
          this.zrus_volbu();
        }
      } else {  // Zrušenie voľby začiatku
        this.zrus_volbu();
        this.$emit('status', {txt: "", type:"N"});
      }
      cesta = null;
    },
    zaciatok_cesty(cesta_z_xs, typ_cesty) {
      this.$emit('status', {txt: "Začiatok cesty: " + this.myprv[cesta_z_xs].oznacenie, type: typ_cesty});
      this.myprv[cesta_z_xs].stav += (16 * (typ_cesty == 'V' ? 2 : 1));     // Označ návestidlo podľa typu cesty
      this.myprv[cesta_z_xs].n[0] = (typ_cesty == 'V' ? 2 : 1);             // Ulož typ cesty
      this.cesta_z = this.myprv[cesta_z_xs];                                // Ulož začiatok cesty
      this.$emit('kresli', {prvok: this.myprv[cesta_z_xs]});
    },
    zrus_volbu() {
      this.myprv[this.cesta_z.xs].stav &= 15;
      this.$emit('kresli', {prvok: this.myprv[this.cesta_z.xs]});
      this.cesta_z = null;
      this.cesta_k = null;
    },
    write_error(txt) {
      this.$emit('error', {txt: "Cestu som nenašiel!!!"});
    },
    get_cislo_cesty(cesta_z, cesta_k) {
      var cislo_cesty = 0;    // Číslo použitej cesty 
      Object.keys(this.mycst).forEach(s => {    // Nájdenie cesty
        if ((this.mycst[s].zc === cesta_z.xs) && (this.mycst[s].kc === cesta_k.xs) && (this.mycst[s].typ === cesta_z.n[0])) {
          cislo_cesty = s;                      // Zapíš číslo nájdenej cesty
        }
      });
      return cislo_cesty;
    },
    test_cesta(cesta_z, cesta_k) {
      var cislo_cesty = this.get_cislo_cesty(cesta_z, cesta_k);  // Nájdi číslo cesty
      if (cislo_cesty != 0 && this.mycst[cislo_cesty].prvky_cesty.length > 0) { // Cesta existuje a mám prvky cesty
        this.myprv[cesta_z.xs].c[3] = cesta_k.xs;                // Zapíš do návestidla koniec cesty
        var volnost = true;
        this.mycst[cislo_cesty].prvky_cesty.forEach(x => {       // Zisti volnost cesty
          var pro = x.split("|");
          if ((this.myprv[pro[0]].stav & 15) != 0) {             // Je prvok obsadený? Dôležité sú stavy od 1..15 po zmazaní vyšších bitov
            volnost = false;
          }
        });
        if (this.mycst[cislo_cesty].prvky_odvrat.length > 0) {  // Test volnosti odvratných úsekov
          this.mycst[cislo_cesty].prvky_odvrat.forEach(x => {
            var pro = x.split("|");
            var pr = this.myprv[pro[0]];
            if ((pr.stav & 15) > 0) {                           // Je prvok obsadený? Dôležité sú stavy od 1..15 po zmazaní vyšších bitov
              if (pr.id_prvky_kluc == 16 && pr.sm != pro[1]) {  // Odvratná výhybka nie je v správnej polohe
                volnost = false;
              }
            }
          });
        }
        if (cesta_k.ts > 0 && this.mycst[cislo_cesty].typ == 2 && (this.myprv[cesta_k.ts].stav != 0 || this.myprv[cesta_k.ts].c[2] != 0)) { // Blokovaný TS
          volnost = false;
        }
        return (volnost) ? this.mycst[cislo_cesty] : 0; // Cesta je volná tak vrat cestu inak 0
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
        var pr = this.myprv[pro[0]];                  // Pre skrátenie zápisov
        if (pr.id_prvky_kluc == 16) {                 // Prvok je VN?
          if (pr.sm != pro[1]) {                      // Nie je správne prestavená
            if (vyh < 4 && this.myprv[pro[0]].sm < 64) {  // Max sa prestavuje najviac 3 výhybky a neprestavuje sa
              this.myprv[pro[0]].sm = parseInt(pro[1]) + 64; // Príznak prestavovania
              out.push(this.myprv[pro[0]]);
              if (pr.c[2] > 0 && dvojica.length == 0) { // Výhybka má dvojicu a je prvá
                dvojica.push(pr.xs);
                if (cesta.prvky_odvrat.includes(pr.c[2]+"|"+pro[1])) {  // Mám dvojicu ako odvrat
                  this.myprv[pr.c[2]].sm = parseInt(pro[1]) + 64;       // Príznak prestavovania
                  out.push(this.myprv[pr.c[2]]);
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
            } else if (this.myprv[pro[0]].sm >= 64) { // Ak sa prestavuje tak započítaj
              vyh++;
            }
          }
        }
      });
      
      var ts = this.myprv[cesta.kc].ts;
      if (ts > 0 && cesta.typ == 2) {
        if (this.myprv[ts].sm != this.myprv[cesta.zc].sm) { // TS je nesprávne
          this.myprv[ts].stav = 4;
          this.$emit('udalost', {cas: 20, xs: ts, cesta: cesta, do: 'zmenTS'});
          vyh++;
        } else if (vyh == 0) {
          this.myprv[ts].c[2] |= 1;
        }
        out.push(this.myprv[ts]);
      }
      if (vyh == 0) { // Všetky výhybky sú správne prestavené
        cesta.prvky_cesty.forEach(xs => {
          var pro = xs.split("|");                        // Rozlož prvok cesty na xs (a polohu výhybky)
          if (pro[0] == cesta.zc) {                       // Pre počiatočné návestidlo na rozsvietenie o 1s
            out.push(this.myprv[pro[0]]);
            this.$emit('udalost', {cas: 10, xs: pro[0], nst: cesta.typ, do: 'zmenPrvok'});
          } else {
            //Zapíš do prvkov záver cesty okrem návestidiel
            if (this.myprv[pro[0]].id_prvky_kluc !== 6 && this.myprv[pro[0]].id_prvky_kluc !== 8) {
              this.myprv[pro[0]].stav = cesta.typ;
            }
            out.push(this.myprv[pro[0]]);
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
          if (this.myprv[x].id_prvky_kluc == 16 && sm > 0) { // Je to VN a prestavuje sa
            this.myprv[x].sm = sm;                           // Zmeň polohu výhybky
            out.push(this.myprv[x]);
          }
        });
        this.$emit('kresli', {prvok: out});                    // Vykresli VN
      } else if (this.myprv[xs].id_prvky_kluc == 16 && sm > 0) { // Je to VN a prestavuje sa
        this.myprv[xs].sm = sm;                           // Zmeň polohu výhybky
        this.$emit('kresli', {prvok: this.myprv[xs]});    // Vykresli VN
      }
    },
    rus_cestu(cesta_z, cesta_k) {
      var cislo_cesty = this.get_cislo_cesty(this.myprv[cesta_z],this.myprv[cesta_k]);
      this.myprv[cesta_z].stav = 0;
      var out = [];
      out.push(this.myprv[cesta_z]);
      this.mycst[cislo_cesty].prvky_cesty.forEach(xs => {
        var pro = xs.split("|");                        // Rozlož prvok cesty na xs (a polohu výhybky)
        if (this.myprv[pro[0]].id_prvky_kluc !== 6 && this.myprv[pro[0]].id_prvky_kluc !== 8) {
          this.myprv[pro[0]].stav = 0;
        }
        out.push(this.myprv[pro[0]]); 
      });
      var ts = this.myprv[cesta_k].ts;
      if (ts > 0 && this.mycst[cislo_cesty].typ == 2) {
        this.myprv[ts].c[2] = (this.myprv[ts].c[2] >> 1) << 1 ;  // Nastavím bit 0 na 0 
        out.push(this.myprv[ts]); 
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
      this.$emit('status', {txt: "Prijatý príkaz:" + newContext_menu.pol + " pre prvok:" + pr.xs, type: "P"});
      switch (newContext_menu.pol) {
        case 'STOJ':  this.myprv[pr.xs].stav = 0;
                      this.$emit('kresli', {prvok: this.myprv[pr.xs]});
          break;
        case 'RC':  this.myprv[pr.xs].stav = 5;
                    this.$emit('kresli', {prvok: this.myprv[pr.xs]});
                    this.$emit('udalost', {cas: 50, xs: pr.xs, nst: pr.c[3], do: 'rusCestu'});
          break;
        case 'DN':  if (pr.c[3] > 0) {                                          // Je v návestidle zapísaný koniec cesty
                      this.myprv[pr.xs].stav = this.myprv[pr.c[0]].stav;        // Stav nastav podľa prvku za návestidlom
                      this.$emit('kresli', {prvok: this.myprv[pr.xs]});
                    }
          break;
        case 'VC': this.zaciatok_cesty(pr.xs, 'V');
          break;
        case 'PC': this.zaciatok_cesty(pr.xs, 'P');
          break;
      }
    },
    urob: function (newUrob) {
      switch (newUrob.do) {
        case "rusCestu": this.rus_cestu(newUrob.xs, newUrob.nst); // Spracovanie pre rušenie cesty
          break;
        case "zmenTS": 
              this.myprv[newUrob.xs].stav = 0; // Zmeň TS
              this.myprv[newUrob.xs].sm = this.myprv[newUrob.cesta.zc].sm; // Zmeň smer TS
              this.$emit('kresli', {prvok: this.myprv[newUrob.xs]});  // Vykresli TS
              this.postav_cestu(newUrob.cesta);
          break;
        case 'zmenPrvok': 
              this.myprv[newUrob.xs].stav = newUrob.nst;  // Zmeň stav prvku
              this.$emit('kresli', {prvok: this.myprv[newUrob.xs]});  // Vykresli ho
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
  template: ` `
});