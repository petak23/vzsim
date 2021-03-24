/**
 * Vue komponenta pre ui v simulácii.
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
Vue.component('ui_run', {
  props: {
    prvky: String,
    cesty: String,
    was_clicked: Object,
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
          this.zaciatok_cesty(pr, m);
        } else if (pr.id_prvky_kluc === 8) { // Zaciatok posunovej cesty pre NE
          this.zaciatok_cesty(pr, 'P');
        }
      } else if (pr !== null && this.cesta_z !== null && (pr.id_prvky_kluc === 3 || pr.id_prvky_kluc === 14 || pr.id_prvky_kluc === 22)) { // Koniec vlakovej alebo posunovej cesty
        this.cesta_k = (pr.id_prvky_kluc !== 22) ? pr : this.myprv[pr.c[0]]; // Pre prípad kliku na KO
        var cesta = this.test_cesta(this.cesta_z, this.cesta_k);
        if (cesta !== null) {
          if (cesta !== 0) { // Mám cestu a nie je obsadená
            var sb_ozn = this.myprv[this.cesta_k.c[2]].oznacenie;
            if (this.cesta_k.id_prvky_kluc == 14) {
              var text = this.cesta_k.oznacenie != null ? this.cesta_k.oznacenie : this.cesta_k.n[0];
            } else {
              var text = this.cesta_k.oznacenie;
            }
            this.$emit('status', {txt: "Staviam cestu: ŽST: " + sb_ozn + " [ " + this.cesta_z.oznacenie + " -> " + text + " ]", type: (cesta.typ === 2 ? "V" : "P")});
            this.$emit('kresli', {cesta: cesta});
            //this.postav_cestu(cesta);
          } else {           // Mám cestu ale je obsadená

          }
        } else {             // Cestu som nenašiel
          this.$emit('status', {txt: "", type:"N"});
          this.$emit('error', "Cestu som nenašiel!!!");
        }
        this.zrus_volbu();
        cesta = null;
      } else {  // Zrušenie voľby začiatku
        this.zrus_volbu();
        cesta = null;
        this.$emit('status', {txt: "", type:"N"});
      }
    },
    zaciatok_cesty(cesta_z_pr, typ_cesty) {
      this.cesta_z = cesta_z_pr;                                  // Ulož začiatok cesty
      this.$emit('status', {txt: "Začiatok cesty: " + this.cesta_z.oznacenie, type: typ_cesty});
      this.cesta_z.stav += (16 * (typ_cesty == 'V' ? 2 : 1));     // Označ návestidlo podľa typu cesty
      this.cesta_z.n[0] = (typ_cesty == 'V' ? 2 : 1);             // Ulož typ cesty
      this.$emit('kresli', {prvok: this.cesta_z});
    },
    zrus_volbu() {
      this.cesta_z.stav &= 15;
      this.$emit('kresli', {prvok: this.cesta_z});
      this.cesta_z = null;
      this.cesta_k = null;
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
  },
  watch: {
    was_clicked: function (newWas_clicked) {
      this.doWithClicked(newWas_clicked);
    },
  },
  template: ` `
});