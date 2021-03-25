/**
 * Vue komponenta pre canvas a vykreslovanie plochy v simulácii.
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
Vue.component('mycanvas', { 
//var mycanvas = {  
  props: {
    prvky: String,
    cesty: String,
    xmax_s: String,
    ymax_s: String,
    urob: Object,

    kresli: Object, // Požiadavka na vykreslenie prvku
  },
  data: function () {
    return {
      canvas: null,         // Kresliaca plocha
      krokx: 16,            // Veľkosť grafického políčka v px - smer x
      kroky: 16,            // Veľkosť grafického políčka v px - smer y
         //0  1  2  3  4 5 6  7  8  9
      dx: [0,-1, 0, 1,-1,0,1,-1, 0, 1], //relatívny posun x podľa numerickej kl. od stredu
      dy: [0, 1, 1, 1, 0,0,0,-1,-1,-1], //relatívny posun y
      cesta_z: null,        // Začiatok cesty
      cesta_k: null,        // Koniec cesty
      // --- Kontextové menu
      viewMenu: false,      // Zobrazenie
      top: '0px',           // Pozícia
      left: '0px',
      menu_nadpis: "",      // Nadpis
      menu_xs: 0,           // Prvok, pre ktorý je
      menu_items: [],       // Položky
      menus: {              // Kontextové menu pre jednotlivé prvky
        6: [
            { id: 1, txt: 'STOJ'},
            { id: 2, txt: 'RC'},
            { id: 3, txt: 'DN'},
            { id: 4, txt: 'PC'},
            { id: 5, txt: 'VC'},
          ],
        8: [
            { id: 1, txt: 'STOJ'},
            { id: 2, txt: 'RC'},
            { id: 3, txt: 'DN'},
            { id: 4, txt: 'PC'},
          ],
      },
      // --- Kontextového menu - Koniec
    };
  },
  template: `
      <div>
        <canvas id="editPlace" 
              :width="xmax" 
              :height="ymax" 
              style="background-color: #000" 
              @click.left.ctrl.exact="get_mouse('P', $event)"
              @click.middle="get_mouse('P', $event)"
              @click.left.exact="get_mouse('V', $event)"
              @contextmenu.prevent="openMenu"
        />
        <ul id="right-click-menu" tabindex="-1"  v-if="viewMenu" 
            @blur="closeMenu"  
            :style="{ top:top, left:left}">
            <li class="nadpis">{{menu_nadpis}}
            <li v-for="it in menu_items" :key="it.id" @click.left="selectMenu(it.id)">
              {{ it.txt }}
            </li>
        </ul>
      </div>
    `,
  computed: {
    // Parsovanie JSON-u  na array
    myprv() {
      return JSON.parse(this.prvky);
    },
    mycst() {
      return JSON.parse(this.cesty);
    },
    xmax() {
      return (this.xmax_s * this.krokx);
    },
    ymax() {
      return (this.ymax_s * this.kroky);
    },
    kr2x() {
      return (this.krokx / 2);
    },
    kr2y() {
      return (this.kroky / 2);
    }
  },
  mounted() {
    var c = document.getElementById("editPlace");
    this.canvas = c.getContext('2d');
    //this.mriezka(this.xmax_s, this.ymax_s);
    Object.keys(this.myprv).forEach(xs => {
      this.kresli_prvok(this.myprv[xs]);
		});
  },
  methods: {
		// --- Kontextové menu - Sčasti prevzaté: https://codepen.io/SimpleSoftwareIO/pen/yNwYJb
    setMenu(top, left) { 
      this.top = top + 'px';
      this.left = left + 'px';
    },
    closeMenu() {
      this.viewMenu = false;
      this.menu_xs = 0;
      this.menu_nadpis = "";
      this.menu_items = [];
    },
    openMenu(e) { 
      var pr = this.myprv[this.getXs(e.offsetX, e.offsetY)];
      if (typeof pr !== 'undefined') {
        if (pr.id_prvky_kluc == 6 || pr.id_prvky_kluc == 8) { // Menu je pre návestidlo
          this.menu_nadpis = pr.oznacenie != null ? pr.oznacenie : pr.xs;
          this.menu_xs = pr.xs;
          this.menu_items = this.menus[pr.id_prvky_kluc];
          this.viewMenu = true;
          this.setMenu(e.y, e.x);
        }
      }
    },
    selectMenu(id) {
      var pr = this.myprv[this.menu_xs];
      this.$emit('context_menu', {prvok: pr, pol: this.menus[pr.id_prvky_kluc][id-1].txt});
      this.closeMenu();
    },
		// --- Kontextové menu - Koniec
    drawLine(x1, y1, x2, y2, w, s) {
      let ctx = this.canvas;
      ctx.beginPath();
      ctx.strokeStyle = s;
      ctx.lineWidth = w;
      ctx.moveTo(x1, y1);
      ctx.lineTo(x2, y2);
      ctx.stroke();
      ctx.closePath();
    },
    mriezka() {
      var i;
      for (i = 0; i <= this.xmax_s; i++) {
        this.drawLine((i*this.krokx), 0, (i*this.krokx), (this.ymax_s * this.kroky), 1, "#555");
      }
      for (i = 0; i <= this.ymax_s; i++) {
        this.drawLine(0, (i*this.kroky), (this.xmax_s * this.krokx), (i*this.kroky), 1, "#555");
      }
    },
    sux(xs) { // x súradnica
      return xs % this.xmax_s;
    },
    suy(xs) { // y súradnica
      return parseInt(xs / this.xmax_s);
    },
    getXs(x, y) { //Zo súradníc spočíta xs
      return parseInt(y / (2*this.kr2y)) * this.xmax_s + parseInt(x / (2*this.kr2x));
    },
    zastavka (xxs, yys, sm, a) {
      var b = (sm & 240) >> 4;
      if (b > 0) {
        var kr2x1 = this.kr2x >> 1; 
        var kr2y1 = this.kr2y >> 1;
        this.drawLine(xxs+kr2x1*(this.dx[a[0]]+this.dx[b-1]), yys+kr2y1*(this.dy[a[0]]+this.dy[b-1]), xxs+kr2x1*(this.dx[a[1]]+this.dx[b-1]), yys+kr2y1*(this.dy[a[1]]+this.dy[b-1]), 3, '#D8A216');
        this.drawLine(xxs+kr2x1*(this.dx[a[1]]+this.dx[b-1]), yys+kr2y1*(this.dy[a[1]]+this.dy[b-1]), xxs+kr2x1*(this.dx[a[2]]+this.dx[b-1]), yys+kr2y1*(this.dy[a[2]]+this.dy[b-1]), 3, '#D8A216');
      }
    },
    farbaStav(_stav, _spec = 0) {
      var out;
      switch (_stav) {
        case  0: out = _spec === 0 ? ['#999', '#999'] : ['#559', '#999']; break; //volny
        case  1: out = ['#fff', '#fff']; break; //posun-zaver
        case  2: out = ['#1f1', '#1f1']; break; //vlak-zaver
        case  3: out = ['#f33', '#f33']; break; //obsadeny
        case  4: out = ['#f3d', '#f3d']; break; //porucha
        default: out = ['#999', '#999']; break; 
      }
      return out;
    },
    prvok_XB(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.beginPath();
      ctx.strokeStyle = "#000";
      ctx.fillStyle = "#000";
      ctx.fillRect(xxs - this.kr2x, yys - this.kr2y, 2*this.kr2x, 2*this.kr2y);
      ctx.stroke();
      var a = [0, 0, 0];
      a[0] = ((pr.c[0] & 4095) >> 8) % 10;
      a[1] = (((pr.c[0] & 4095) >> 4) & 15) % 10;
      a[2] = ((pr.c[0] & 4095) & 15) % 10;
      var col = this.farbaStav(pr.stav, pr.id_prvky_kluc === 3 ? 1 : 0);
      this.drawLine(xxs+this.kr2x*this.dx[a[0]], yys+this.kr2y*this.dy[a[0]], xxs+this.kr2x*this.dx[a[1]], yys+this.kr2y*this.dy[a[1]], 3, col[0]); 
      this.drawLine(xxs+this.kr2x*this.dx[a[1]], yys+this.kr2y*this.dy[a[1]], xxs+this.kr2x*this.dx[a[2]], yys+this.kr2y*this.dy[a[2]], 3, col[0]);
    },
    prvok_UU(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var a = ((pr.c[0] & 4095) & 15) % 10;  
      this.drawLine(xxs, yys, xxs + this.kr2x*this.dx[a], yys + this.kr2y*this.dy[a], 3, '#999');
      switch (a) {
        case 1: this.drawLine(xxs - this.kr2x + 2, yys - this.kr2y + 2, xxs + this.kr2x - 2, yys + this.kr2y + 2, 3, '#999'); break;
        case 9: this.drawLine(xxs - this.kr2x + 2, yys - this.kr2y + 2, xxs + this.kr2x - 2, yys + this.kr2y + 2, 3, '#999'); break;
        case 2: this.drawLine(xxs - this.kr2x + 2, yys, xxs + this.kr2x - 2, yys, 3, '#999'); break;
        case 8: this.drawLine(xxs - this.kr2x + 2, yys, xxs + this.kr2x - 2, yys, 3, '#999'); break;
        case 3: this.drawLine(xxs - this.kr2x + 2, yys + this.kr2y - 2, xxs + this.kr2x - 2, yys - this.kr2y + 2, 3, '#999'); break;
        case 7: this.drawLine(xxs - this.kr2x + 2, yys + this.kr2y - 2, xxs + this.kr2x - 2, yys - this.kr2y + 2, 3, '#999'); break;
        case 4: this.drawLine(xxs, yys - this.kr2y + 2, xxs, yys + this.kr2y - 2, 3, '#999'); break;
        case 6: this.drawLine(xxs, yys - this.kr2y + 2, xxs, yys + this.kr2y - 2, 3, '#999'); break;
      }
    },
    prvok_MA(pr) { /* AUTOBLOK */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var a = [0, 0, 0];
      a[0] = ((pr.c[0] & 4095) >> 8) % 10;
      a[1] = (((pr.c[0] & 4095) >> 4) & 15) % 10;
      a[2] = ((pr.c[0] & 4095) & 15) % 10;
      var col = this.farbaStav(pr.stav);
      this.drawLine(xxs - this.kr2x + 1, yys, xxs + this.kr2x-2, yys, 3, col[0]);
      this.zastavka(xxs, yys, pr.sm, a);
    },
    prvok_MR(pr) { /* PREDZVEST */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var a = [0, 0, 0];
      a[0] = ((pr.c[0] & 4095) >> 8) % 10;
      a[1] = (((pr.c[0] & 4095) >> 4) & 15) % 10;
      a[2] = ((pr.c[0] & 4095) & 15) % 10;
      var col = this.farbaStav(pr.stav);
      this.drawLine(xxs - this.kr2x + 1, yys, xxs + this.kr2x-2, yys, 3, col[0]);
      this.zastavka(xxs, yys, pr.sm, a);
    },
    prvok_XP(pr) { /* PRIECESTIE */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var col = this.farbaStav(pr.stav);
      if ((pr.sm & 24) > 0) { //uzavrete
        this.drawLine(xxs - this.kr2x, yys, xxs + this.kr2x, yys, 3, col[0]);
        this.drawLine(xxs - 2, yys - this.kr2y + 1, xxs - 2, yys - this.kr2y + 3, 1, '#999');
        this.drawLine(xxs - 2, yys - this.kr2y + 3, xxs + this.kr2x - 3, yys - this.kr2y + 3, 1, '#999');
        this.drawLine(xxs + kr2x - 3, yys - kr2y + 3, xxs + this.kr2x - 3, yys - this.kr2y + 1, 1, '#999');
        this.drawLine(xxs - 2, yys + this.kr2y - 1, xxs - 2, yys + this.kr2y - 3, 1, '#999');
        this.drawLine(xxs - 2, yys + this.kr2y - 3, xxs + this.kr2x - 3, yys + this.kr2y - 3, 1, '#999');
        this.drawLine(xxs + this.kr2x - 3, yys + this.kr2y - 3, xxs + this.kr2x - 3, yys + this.kr2y - 1, 1, '#999');
      } else { //otvorene
        this.drawLine(xxs - this.kr2x, yys, xxs - this.kr2x + 1, yys, 3, col[0]);
        this.drawLine(xxs + this.kr2x - 1, yys, xxs + this.kr2x, yys, 3, col[0]);
        this.drawLine(xxs - 2, yys - this.kr2y + 1, xxs - 2, yys + this.kr2y - 1, 1, '#999');
        this.drawLine(xxs + 2, yys - this.kr2y + 1, xxs + 2, yys + this.kr2y - 1, 1, '#999');
      }
    },
    prvok_KS(pr) { /* STANICNA KOLAJ */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      var ss = ((pr.stav & 15) === 0) ? pr.oznacenie : pr.c[3]; // Text na koľaji
      var b = (ss !== 0) ? String(ss).length : 0;          // Dĺžka textu na koľaji
      var k_l = this.kr2x*(pr.c[0]>>4)*2; 
      var k_r = this.kr2x*(pr.c[0] & 15)*2;
      ctx.fillStyle = "#000000";
      ctx.fillRect(xxs - k_l - (3 * this.kr2x), yys - this.kr2y + 1, (6 * this.kr2x) + k_l + k_r, 2*this.kr2y - 2);
      //nastupiste
      if ((pr.sm & 1) === 1) { this.drawLine(xxs - 3 * this.kr2x, yys + this.kr2y, xxs + 3 * this.kr2x, yys + this.kr2y, 2, '#FF9F03'); }
      if ((pr.sm & 2) === 2) { this.drawLine(xxs - 3 * this.kr2x, yys - this.kr2y, xxs + 3 * this.kr2x, yys - this.kr2y, 2, '#FF9F03'); }
      var text_w_pol = ctx.measureText(ss).width / 2 + 4;
      /* pr.y == 1 - stojí a nemá
       * pr.y == 2 - stojí a má > 3 min do odchodu
       * pr.y == 3 - 1 min do odchodu */
      if (pr.y > 0) {
        ctx.fillStyle = (pr.y == 3 ? '#fff703' : (pr.y == 1 || pr.y == 2 ? '#f33' : '#000'));
        ctx.fillRect(xxs - k_l - (3 * this.kr2x), yys - this.kr2y + 1, (6 * this.kr2x) + k_l + k_r, 2*this.kr2y - 2);     
      }
      var col = this.farbaStav(pr.stav & 15);
      if (b === 0) { 
        this.drawLine(xxs - k_l  - (3 * this.kr2x), yys, xxs + k_r + (3 * this.kr2x), yys, 3, col[0]);
      } else {
        this.drawLine(xxs - k_l - (3 * this.kr2x), yys, xxs - text_w_pol, yys, 3, col[0]);
        this.drawLine(xxs + k_r + (3 * this.kr2x), yys, xxs + text_w_pol, yys, 3, col[0]);
        ctx.fillStyle = (pr.y == 1 ? '#fff703' : (pr.y == 3 || pr.y == 2 ? '#000' : '#ddd'));
        ctx.font = "14px Verdana";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(ss, xxs, yys+1); 
      }
    },
    
    /* ----------------------------------------------------- VÝHYBKY ------------------------------------ */
    
    vykresliVetvu(xxs, yys, i, a, farba) { /* Vykreslenie jednej vetvy výhybky */
      this.drawLine(xxs + this.kr2x * this.dx[a[i][0]], yys + this.kr2y * this.dy[a[i][0]], xxs + this.kr2x * this.dx[a[i][1]], yys + this.kr2y * this.dy[a[i][1]], 3, farba);
      this.drawLine(xxs + this.kr2x * this.dx[a[i][1]], yys + this.kr2y * this.dy[a[i][1]], xxs + this.kr2x * this.dx[a[i][2]], yys + this.kr2y * this.dy[a[i][2]], 3, farba);
    },
    initVyhybka(xxs, yys, rezim, pr) {
      var ctx = this.canvas;
      var fa_s, fa_v;
      if (rezim > 127) {
        fa_s = '#B909DE'; fa_v = '#B909DE';
      } else {
        if (pr.sm > 64) { //Vyhybka sa prestavuje 
          fa_s = '#999'; fa_v = '#777';
        } else {
          fa_s = '#000'; fa_v = '#000';
        }
      }
      ctx.beginPath();
      ctx.lineWidth = "1";
      ctx.strokeStyle = fa_s;
      ctx.fillStyle = fa_v;
      ctx.fillRect(xxs - this.kr2x, yys - this.kr2y, 2*this.kr2x, 2*this.kr2y);
      ctx.stroke();
      var a = [[0, 0, 0], [0, 0, 0]];
      for (var i = 0;  i < 2; i++) {
        var ppom = pr.c[i] & 4095;                    
        a[i][0] = (ppom >> 8) % 10;
        a[i][1] = ((ppom >> 4) & 15) % 10;
        a[i][2] = (ppom & 15) % 10;
      }
      return a;
    },
    prvok_VN(pr) { /* Obycajna vyhybka */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var a = this.initVyhybka(xxs, yys, 0, pr);
      if (pr.sm > 64) { //Vyhybka sa prestavuje
        var col = this.farbaStav(3);
        this.vykresliVetvu(xxs, yys, 2 - (pr.sm & 63), a, col[0]);
        this.vykresliVetvu(xxs, yys, (pr.sm & 63) - 1, a, col[0]);
      } else {
        this.vykresliVetvu(xxs, yys, 2 - (pr.sm & 3), a, '#34454D');
        var col = this.farbaStav(pr.stav);
        this.vykresliVetvu(xxs, yys, (pr.sm & 3) - 1, a, col[0]);
      }
    },
    
    /* ----------------------------------------------------- NÁVESTIDLÁ ------------------------------------ **/
    prvok_N(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      switch (pr.stav) {
        case 1: pozadie = '#000'; obris = '#fff'; break;    //Posun dovolený
        case 2: pozadie = '#000'; obris = '#1f1'; break;    //Volno
        case 4: pozadie = '#f2d'; obris = '#000'; break;    //Porucha
        case 5: pozadie = '#00a'; obris = '#999'; break;    //Rušenie cesty
        case 16: pozadie = '#eee'; obris = '#999'; break;   //Označ zač. PC
        case 17: pozadie = '#eee'; obris = '#fff'; break;   //Označ zač. PC + Posun dovolený
        case 18: pozadie = '#eee'; obris = '#1f1'; break;   //Označ zač. PC + volno
        case 32: pozadie = '#1a1'; obris = '#999'; break;   //Označ zač. VC
        case 33: pozadie = '#1a1'; obris = '#fff'; break;   //Označ zač. PC + Posun dovolený
        case 34: pozadie = '#1a1'; obris = '#1f1'; break;   //Označ zač. PC + volno
        default: pozadie = '#000'; obris = '#999'; 
      }
      var ctx = this.canvas;
      ctx.fillStyle = pozadie;
      ctx.fillRect(xxs - this.kr2x+2, yys - this.kr2y+1, 2*this.kr2x-4, 2*this.kr2y-2);
      var k = 3 - (2 * (pr.sm & 3)); // sm=1 => k=1; sm=2 => k=-1
      if (pr.id_prvky_kluc === 6) {  // Hlavné
        ctx.beginPath();
        ctx.moveTo(xxs + (-1 * k * (this.kr2x / 2)), yys - this.kr2y + 2);
        ctx.lineTo(xxs + (k * this.kr2x / 2), yys); 
        ctx.lineTo(xxs + (-1 * k * (this.kr2x / 2)), yys + this.kr2y - 2);
        ctx.closePath();
        ctx.lineWidth = 2;
        ctx.strokeStyle = obris;
        ctx.stroke();
        ctx.fillStyle = obris;
        ctx.fill();
      } else { // Se
        this.drawLine(xxs + (-1 * k * (this.kr2x / 2)), yys - this.kr2y + 2, xxs + (k * this.kr2x / 2), yys, 2, obris); 
        this.drawLine(xxs + (k * this.kr2x / 2), yys, xxs + (-1 * k * (this.kr2x / 2)), yys + this.kr2y - 2, 2, obris);
      }
    },
    
    prvok_SB(pr) { /* STANICNA BUDOVA */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.fillStyle = '#f80';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 3, 2*this.kr2x - 2, 2*this.kr2y - 6);
      this.kresliText(xxs, yys, pr.n[1], pr.oznacenie, '#ddd');
    },
    
    /* Vykreslenie textu so smerom
    * @param int smer_txt Smer podľa numerickej klávesnice 
    * @param String text Text na vypísanie 
    * @param color farba Farba textu */
    kresliText(xxs, yys, smer_txt, text, farba) {
      var smu = (smer_txt & 15);
      var korekcia_y = (smu >= 4 && smu <= 6) ? -2 : 0;
      var align = ['right', 'center', 'left'];
      var valign = ['top', 'middle', 'bottom'];
      var ctx = this.canvas;
      ctx.fillStyle = farba;
      ctx.textAlign = align[(smu % 3)-1];
      ctx.textBaseline = valign[parseInt(smu / 3)];
      ctx.strokeStyle = farba;
      ctx.font = "14px sans-serif";
      ctx.fillText(text, xxs + this.dx[smu] * this.kr2x, yys + this.dy[smu] * this.kr2y + korekcia_y);
    },
    
    prvok_TS(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.fillStyle = (pr.n[1] === 1) ? ((pr.stav & 4) === 4 ? '#ccc' : '#000') : '#ccc';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 1, 2*this.kr2x - 2, 2*this.kr2y - 2);
      ctx.textAlign = 'center';
      ctx.textBaseline = "middle";
      ctx.font = "14px sans-serif";
      if (pr.n[1] === 1) {  //Ak je viditeľný 
        //var p = 3 - 2 * pr.sm;
        ctx.fillStyle = '#66DE57'; ctx.strokeStyle = '#66DE57';               //Základný stav voľný - zelená
        if (pr.c[2] > 0) { ctx.strokeStyle = '#e35'; ctx.fillStyle = '#e35';} // Obsadený - červená
        if (pr.dl > 0) { ctx.fillStyle = '#333'; ctx.strokeStyle = '#333'; }  // Blokovany - čierny
        if ((pr.stav & 4) === 4) {                                            // Zmena smeru - fialový
          ctx.fillStyle = '#88f'; ctx.strokeStyle = '#88f';
          ctx.fillText('◊', xxs, yys);
        } else {    
          ctx.fillText(((pr.sm & 3) === 1 ? '»':'«'), xxs, yys);
        }
      } else {  
        ctx.fillStyle = '#333'; ctx.strokeStyle = '#333';
        ctx.fillText(((pr.sm & 3) === 1 ? '»':'«'), xxs, yys);
      } 
    },
    prvok_ZB(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.lineWidth = "1";
      ctx.strokeStyle = '#88f';
      ctx.fillStyle = '#000';
      ctx.font = "14px sans-serif";
      var b = pr.sm >> 1; //b-pocet riadkov
      if ((pr.sm & 1) === 0) {
        ctx.strokeRect(xxs - this.kr2x, yys - this.kr2y + 1, 6 * this.kr2x, 2*this.kr2y * (b + 1) - 2);
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.strokeStyle = '#FFE498';
        if (pr.stav === 0) {
          for (var i = 0; i <= b; i++) { 
            if (pr.c[i] > 0) { 
              ctx.fillText(pr.c[i], xxs + this.kr2x * 5, yys - 2 + i * (2*this.kr2y - 1));
            } 
          }
        } else {
          ctx.fillText(pr.c[3], xxs + this.kr2x * 5, yys - 1);
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText("?", xxs + 2 * this.kr2x, yys + 2*this.kr2y - 2);
          ctx.strokeStyle = '#8888FF'; 
          ctx.fillText("A", xxs, yys + 2*this.kr2y - 2);
          ctx.strokeStyle = '#FF8888'; 
          ctx.fillText("N", xxs + this.kr2x * 4, yys + 2*this.kr2y - 2);
        }
      } else {
        ctx.strokeRect(xxs - this.kr2x, yys + this.kr2y - 1, 6 * this.kr2x, 2 - 2*this.kr2y * (b + 1));
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.strokeStyle = '#FFE498';
        if (pr.stav === 0) {
          for (var i = 0; i <= b; i++) { 
            if (pr.c[i] > 0) { 
              ctx.fillText(pr.c[i], xxs + this.kr2x * 5, yys - 2 - i * (2*this.kr2y - 1));
            }
          }
        } else {
          ctx.fillText(pr.c[3], xxs + this.kr2x * 5, yys - 2*this.kr2y - 1);
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText("?", xxs + 2 * this.kr2x, yys - 3);
          ctx.strokeStyle = '#8888FF'; 
          ctx.fillText("A", xxs, yys - 3);
          ctx.strokeStyle = '#FF8888'; 
          ctx.fillText("N", xxs + this.kr2x * 4, yys - 3);
        }
      }
    },
    get_mouse(m, e) {
      var xs = this.getXs(e.offsetX, e.offsetY);
      var pr = this.myprv[xs];
      if (typeof pr !== 'undefined' ) { //https://stackoverflow.com/questions/2281633/javascript-isset-equivalent
        this.$emit('was_clicked', {prvok: pr, mod: m});
      } else {
        this.$emit('was_clicked', {prvok: null, mod: ""});
      }
    },
    kresli_prvok(pr) {
      this.myprv[pr.xs] = pr; //Aktualizuj údaje o prvku
      switch (pr.id_prvky_kluc) {
        case 1:
        case 2:
        case 3:
        case 5: this.prvok_XB(pr);  
          break;
        case 6: 
        case 8: this.prvok_N(pr);
          break;
        case 10:
        case 11: this.prvok_XP(pr);
          break;
        case 12: this.prvok_MA(pr);
          break;
        case 13: this.prvok_MR(pr);
          break;
        case 14: this.prvok_KS(pr);
          break;
        case 15: this.prvok_UU(pr);
          break;
        case 16: this.prvok_VN(pr);
          break;
        case 18: this.prvok_ZB(pr);
          break;
        case 19: this.prvok_TS(pr);
          break;
        case 20: this.prvok_SB(pr);
          break;
      }
      if (pr.odk != 0 && pr.id_prvky_kluc < 17) {         // Ak má prvok odkazy
        var odkazy = pr.odk.split("|");
        if (pr.id_prvky_kluc == 16) {                     // Pre výhybky
          var i;
          var cast = 0;
          for (i = 0; i < odkazy.length; i++) {
            if (odkazy[i] == "a") {
              cast = 1;
            } else if (odkazy[i] == "b") {
              cast = 2;
            } else {
              if (cast == 0) {                            // Vykresli pre nultú časť
                this.myprv[odkazy[i]].stav = pr.stav;
                this.prvok_XB(this.myprv[odkazy[i]]);  
              } else if (cast == pr.sm || pr.stav == 0) { // Vykresli ak sa zhoduje alebo pri základnom stave
                this.myprv[odkazy[i]].stav = pr.stav;
                this.prvok_XB(this.myprv[odkazy[i]]);  
              } 
            }
          }
        } else {                                          // Pre ostatné prvky
          odkazy.forEach(x => {
            var po = this.myprv[x];
            po.stav = pr.stav;
            this.prvok_XB(po);
          });
        }
      }
    },
    najdiOdkazy(pr) {
      var odkazy = [];
      Object.keys(this.myprv).forEach(xos => {    // Prejdi všetky prvky
        var pro = this.myprv[xos];                // Len pre skrátenie zápisu
        if (pro.id_prvky_kluc === 4) {            // Najdi odkazy v stanici
          if (pr.id_prvky_kluc === 16) {          // Odkazy na výhybku
            if (pro.n[pro.sm-1] === pr.xs) odkazy.push(xos);// Pre správnu cestu
          } else {                                // Pre ostatné prvky
            if ((pro.n[0] === pr.xs || pro.n[1] === pr.xs)) odkazy.push(xos);
          }
          
        }
      });
      return odkazy;
    },
  },
  watch: {
    kresli: function (newKresli) {
      if (newKresli !== null) {
        if (typeof newKresli.prvok !== 'undefined') {
          if (newKresli.prvok.constructor === Array) { // Je to pole? https://stackoverflow.com/questions/4775722/how-to-check-if-an-object-is-an-array
            newKresli.prvok.forEach(pr => {
              this.kresli_prvok(pr);
            });
          } else {
            this.kresli_prvok(newKresli.prvok);
          }
        }
      }
    },
  }
});