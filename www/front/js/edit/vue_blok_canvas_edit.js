/**
 * Vue komponenta pre canvys a vykreslenie plochy v editácii.
 * Posledna zmena(last change): 18.03.2021
 *
 *	Modul: EDIT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
Vue.component('mycanvas', { 
//var mycanvas = {  
  props: {
    prvky: String,
    xmax_s: String,
    ymax_s: String,
  },
  data: function () {
    return {
      canvas: null,
      krokx: 16,
      kroky: 16,
         //0  1  2  3  4 5 6  7  8  9
      dx: [0,-1, 0, 1,-1,0,1,-1, 0, 1], //posun x
      dy: [0, 1, 1, 1, 0,0,0,-1,-1,-1], //posun y
    }
  },
  template: '<canvas id="editPlace" :width="xmax" :height="ymax" style="background-color: #000" />',
  computed: {
    // Parsovanie JSON-u  na array
    myprv() {
      return JSON.parse(this.prvky)
    },
    xmax() {
      return (this.xmax_s * this.krokx)
    },
    ymax() {
      return (this.ymax_s * this.kroky)
    },
    kr2x() {
      return (this.krokx / 2)
    },
    kr2y() {
      return (this.kroky / 2)
    }
  },
  mounted() {
    var c = document.getElementById("editPlace");
    this.canvas = c.getContext('2d');
    this.mriezka(this.xmax_s, this.ymax_s);
    Object.keys(this.myprv).forEach(xs => {
      var pr = this.myprv[xs];
      switch (pr.id_prvky_kluc) {
        case 1:
        case 2:
        case 3:
        case 4:
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
        case 21: this.prvok_MZ(pr);
          break;
//        default:
//          break;
      }
		});
  },
  methods: {
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
        case 72: out = ['#fc1', '#fc1']; break; //Označenie odkazov
        default: out = ['#999', '#999']; break; 
      }
      return out;
    },
    prvok_XB(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var a = [0, 0, 0];
      a[0] = ((pr.c[0] & 4095) >> 8) % 10;
      a[1] = (((pr.c[0] & 4095) >> 4) & 15) % 10;
      a[2] = ((pr.c[0] & 4095) & 15) % 10;
      var ook = pr.id_prvky_kluc === 4 || pr.id_prvky_kluc === 5;
      var col = this.farbaStav(ook ? 72 : pr.stav, pr.id_prvky_kluc === 3 ? 1 : 0);
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
      var ss = pr.oznacenie != null ? pr.oznacenie : pr.n[0]; // Text na koľaji
      var b = (ss !== 0) ? String(ss).length : 0;          // Dĺžka textu na koľaji
      var k_l = this.kr2x*(pr.c[0]>>4)*2; 
      var k_r = this.kr2x*(pr.c[0] & 15)*2;
      ctx.fillStyle = "#000";
      ctx.fillRect(xxs - k_l - (3 * this.kr2x), yys - this.kr2y + 2, (6 * this.kr2x) + k_l + k_r, 2*this.kr2y - 4);
      //nastupiste
      if ((pr.sm & 1) === 1) { this.drawLine(xxs - 3 * this.kr2x, yys + this.kr2y, xxs + 3 * this.kr2x, yys + this.kr2y, 2, '#FF9F03'); }
      if ((pr.sm & 2) === 2) { this.drawLine(xxs - 3 * this.kr2x, yys - this.kr2y, xxs + 3 * this.kr2x, yys - this.kr2y, 2, '#FF9F03'); } 
      var text_w_pol = ctx.measureText(ss).width / 2 + 4;
      /*if (pr.y > 0) { 
        ctx.fillStyle = '#000';
        ctx.fillRect(xxs - k_l - (3 * this.kr2x) + 1, yys - this.kr2y + 2, xxs + (3 * this.kr2x) + k_r - 1, yys + this.kr2y - 2);     
      }*/
      var col = this.farbaStav(pr.stav & 15);
      if (b === 0) { 
        this.drawLine(xxs - k_l  - (3 * this.kr2x), yys, xxs + k_r + (3 * this.kr2x), yys, 3, col[0]);
      } else {
        this.drawLine(xxs - k_l - (3 * this.kr2x), yys, xxs - text_w_pol, yys, 3, col[0]);
        this.drawLine(xxs + k_r + (3 * this.kr2x), yys, xxs + text_w_pol, yys, 3, col[0]);
        ctx.fillStyle = '#ddd';
        ctx.font = "14px sans-serif";
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
      ctx.fillRect(xxs - (this.kr2x / 2), yys - this.kr2y, this.kr2x, 2*this.kr2y);
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

    prvok_MZ(pr) { /* MIESTO ZJAVENIA */
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.fillStyle = '#760';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 1, 2*this.kr2x - 2, 2*this.kr2y - 2);
      this.kresliText(xxs, yys+2, 5, pr.n[0] == 0 ? '◊' : (pr.sm == 1 ? '>' : '<'), '#000');
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
      ctx.fillStyle = (pr.n[1] === 1) ? '#000' : '#DCDCDC';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 1, 2*this.kr2x - 2, 2*this.kr2y - 2);
      ctx.textAlign = 'center';
      ctx.textBaseline = "middle";
      ctx.font = "14px sans-serif";
      if (pr.n[1] === 1) {  //Ak je viditeľný 
        var p = 3 - 2 * pr.sm;
        ctx.fillStyle = '#66DE57'; ctx.strokeStyle = '#66DE57'; //Základný stav voľný - zelená
        if (pr.c[2] > 0) { ctx.strokeStyle = '#EE3355'; ctx.fillStyle = '#000';}                // Obsadený - červená
        if (pr.dl > 0) { ctx.fillStyle = '#333'; ctx.strokeStyle = '#333'; }                  // Blokovany - čierny
        if ((pr.stav & 4) === 4) { ctx.fillStyle = '#8888FF'; ctx.strokeStyle = '#8888FF';}      // Zmena smeru na AB - fialový
        ctx.fillText(((pr.stav & 3) === 1 ? '»':'«'), xxs, yys);
      } else {  
        ctx.fillStyle = '#333'; ctx.strokeStyle = '#333';
        ctx.fillText(((pr.stav & 3) === 1 ? '»':'«'), xxs, yys);
      } 
    },
    prvok_ZB(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.lineWidth = "1";
      ctx.strokeStyle = '#88f';
      ctx.fillStyle = '#000';
      ctx.font = "14px Verdana";
      var b = pr.sm >> 1; //b-pocet riadkov
      if ((pr.sm & 1) === 0) {
        ctx.strokeRect(xxs - this.kr2x, yys - this.kr2y + 1, 6 * this.kr2x, 2*this.kr2y * (b + 1) - 2);
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.strokeStyle = '#fe9';
        for (var i = 0; i <= b; i++) { 
          if (pr.c[i] > 0) { 
            ctx.fillText(pr.c[i], xxs + this.kr2x * 5, yys - 2 + i * (2*this.kr2y - 1));
          } 
        }
      } else {
        ctx.strokeRect(xxs - this.kr2x, yys + this.kr2y - 1, 6 * this.kr2x, 2 - 2*this.kr2y * (b + 1));
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.strokeStyle = '#FFE498';
        for (var i = 0; i <= b; i++) { 
          if (pr.c[i] > 0) { 
            ctx.fillText(pr.c[i], xxs + this.kr2x * 5, yys - 2 - i * (2*this.kr2y - 1));
          }
        }
      }
    }
  }
});