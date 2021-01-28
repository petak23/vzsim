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
    //this.mriezka(this.xmax_s, this.ymax_s);
    
    this.myprv.forEach(e => {
      var x = this.sux(e.xs);
      var y = this.suy(e.xs);
      switch (e.id_prvky_kluc) {
        case 1:
        case 2: 
        case 4:
        case 5: 
                this.prvok_XB(this.krokx*(x + 0.5), this.kroky*(y + 0.5), '#999', (e.c0 & 4095));  
          break;
        case 3: this.prvok_XB(this.krokx*(x + 0.5), this.kroky*(y + 0.5), '#339', (e.c0 & 4095)); 
          break;
        case 6:
        case 7:
        case 9: this.prvok_NHD(this.krokx*(x + 0.5), this.kroky*(y + 0.5), '#000', '#999', '#999', e.sm);
          break;
        case 8: this.prvok_NE(this.krokx*(x + 0.5), this.kroky*(y + 0.5), '#000', '#999', e.sm);
          break;
        case 10:
        case 11: this.prvok_XP(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, '#999');
          break;
        case 12: this.prvok_MA(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, '#999', (e.c0 & 4095));
          break;
        case 13: this.prvok_MR(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, '#999', (e.c0 & 4095));
          break;
        case 14: this.prvok_KS(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e, 0);
          break;
        case 15: this.prvok_UU(this.krokx*(x + 0.5), this.kroky*(y + 0.5), (e.c0 & 4095));
          break;
        case 16: this.prvok_VN(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e);
          break;
        case 17: this.prvok_VK(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e);
          break;
        case 18: this.prvok_ZB (this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, 0, [e.c0, e.c1, e.c2, e.c3]); //ZB
          break;
        case 19: this.prvok_TS(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e, e.sm); //TS
          break;
        case 20: this.prvok_SB(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e);
          break;
        case 21: //MZ
        case 22: //KO
          break;
        default:
          break;
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
        this.drawLine((i*this.krokx), 0, (i*this.krokx), (this.ymax_s * this.kroky), 1, "#555555");
      }
      for (i = 0; i <= this.ymax_s; i++) {
        this.drawLine(0, (i*this.kroky), (this.xmax_s * this.krokx), (i*this.kroky), 1, "#555555");
      }
    },
    sux(xs) { // x súradnica
      return xs % this.xmax_s;
    },
    suy(xs) { // y súradnica
      return parseInt(xs / this.xmax_s);
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
    prvok_XB(xxs, yys, stav, cesta) {
      var a = [0, 0, 0];
      a[0] = (cesta >> 8) % 10;
      a[1] = ((cesta >> 4) & 15) % 10;
      a[2] = (cesta & 15) % 10;
      this.drawLine(xxs+this.kr2x*this.dx[a[0]], yys+this.kr2y*this.dy[a[0]], xxs+this.kr2x*this.dx[a[1]], yys+this.kr2y*this.dy[a[1]], 3, stav); 
      this.drawLine(xxs+this.kr2x*this.dx[a[1]], yys+this.kr2y*this.dy[a[1]], xxs+this.kr2x*this.dx[a[2]], yys+this.kr2y*this.dy[a[2]], 3, stav);
    },
    prvok_UU(xxs, yys, cesta) { 
      var a = (cesta & 15) % 10;  
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
    prvok_MA(xxs, yys, sm, stav, cesta) { /* AUTOBLOK */
      var a = [0, 0, 0];
      a[0] = (cesta >> 8) % 10;
      a[1] = ((cesta >> 4) & 15) % 10;
      a[2] = (cesta & 15) % 10;
      this.drawLine(xxs - this.kr2x + 1, yys, xxs + this.kr2x-2, yys, 3, stav);
      this.zastavka(xxs, yys, sm, a);
    },
    prvok_MR(xxs, yys, sm, stav, cesta) { /* PREDZVEST */
      var a = [0, 0, 0];
      a[0] = (cesta >> 8) % 10;
      a[1] = ((cesta >> 4) & 15) % 10;
      a[2] = (cesta & 15) % 10;
      this.drawLine(xxs - this.kr2x + 1, yys, xxs + this.kr2x-2, yys, 3, stav);
      this.zastavka(xxs, yys, sm, a);
    },
    prvok_XP(xxs, yys, sm, stav) { /* PRIECESTIE */
      if ((sm & 24) > 0) { //uzavrete
        this.drawLine(xxs - this.kr2x, yys, xxs + this.kr2x, yys, 3, stav);
        this.drawLine(xxs - 2, yys - this.kr2y + 1, xxs - 2, yys - this.kr2y + 3, 1, '#999');
        this.drawLine(xxs - 2, yys - this.kr2y + 3, xxs + this.kr2x - 3, yys - this.kr2y + 3, 1, '#999');
        this.drawLine(xxs + kr2x - 3, yys - kr2y + 3, xxs + this.kr2x - 3, yys - this.kr2y + 1, 1, '#999');
        this.drawLine(xxs - 2, yys + this.kr2y - 1, xxs - 2, yys + this.kr2y - 3, 1, '#999');
        this.drawLine(xxs - 2, yys + this.kr2y - 3, xxs + this.kr2x - 3, yys + this.kr2y - 3, 1, '#999');
        this.drawLine(xxs + this.kr2x - 3, yys + this.kr2y - 3, xxs + this.kr2x - 3, yys + this.kr2y - 1, 1, '#999');
      } else { //otvorene
        this.drawLine(xxs - this.kr2x, yys, xxs - this.kr2x + 1, yys, 3, stav);
        this.drawLine(xxs + this.kr2x - 1, yys, xxs + this.kr2x, yys, 3, stav);
        this.drawLine(xxs - 2, yys - this.kr2y + 1, xxs - 2, yys + this.kr2y - 1, 1, '#999');
        this.drawLine(xxs + 2, yys - this.kr2y + 1, xxs + 2, yys + this.kr2y - 1, 1, '#999');
      }
    },
    prvok_KS(xxs, yys, e, stav) { /* STANICNA KOLAJ */
      var ctx = this.canvas;
      var ss = ((stav & 15) === 0) ? e.n0 : e.c3;
      var b = (ss !== 0) ? String(ss).length : 0;
      var k_l = this.kr2x*(e.c0>>4)*2; 
      var k_r = this.kr2x*(e.c0 & 15)*2;
      ctx.fillStyle = "#000";
      ctx.fillRect(xxs - k_l - (3 * this.kr2x), yys - this.kr2y + 2, (6 * this.kr2x) + k_l + k_r, 2*this.kr2y - 4);
      //nastupiste
      if ((e.sm & 1) === 1) { this.drawLine(xxs - 3 * this.kr2x, yys + this.kr2y, xxs + 3 * this.kr2x, yys + this.kr2y, 2, '#FF9F03'); }
      if ((e.sm & 2) === 2) { this.drawLine(xxs - 3 * this.kr2x, yys - this.kr2y, xxs + 3 * this.kr2x, yys - this.kr2y, 2, '#FF9F03'); }
      var fa_u, fa_v;
      switch (e.y) {
        case 1: fa_v = '#FF2929'; fa_u = '#FFF703'; break;//stoji a nema
        case 2: fa_v = '#FF2929'; fa_u = '#000000'; break;//stoji a ma>3
        case 3: fa_v = '#FFF703'; fa_u = '#000000'; break;//1 min do odchodu
        default: fa_v = '#000000'; fa_u = '#DCDCDC';
      }
      var text_w_pol = ctx.measureText(ss).width / 2 + 4;
      if (e.y > 0) { 
        ctx.fillStyle = fa_v;
        ctx.fillRect(xxs - k_l - (3 * this.kr2x) + 1, yys - this.kr2y + 2, xxs + (3 * this.kr2x) + k_r - 1, yys + this.kr2y - 2);     
      }
      //farbaStav(stav & 15);
      if (b === 0) { 
        this.drawLine(xxs - k_l  - (3 * this.kr2x), yys, xxs + k_r + (3 * this.kr2x), yys, 3, '#999');
      } else {
        this.drawLine(xxs - k_l - (3 * this.kr2x), yys, xxs - text_w_pol, yys, 3, '#999');
        this.drawLine(xxs + k_r + (3 * this.kr2x), yys, xxs + text_w_pol, yys, 3, '#999');
        ctx.fillStyle = fa_u;
        ctx.font = "14px Verdana";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(ss, xxs, yys); 
      }
    },
    vykresliVetvu(xxs, yys, i, a, farba) { /* Vykreslenie jednej vetvy výhybky */
      this.drawLine(xxs + this.kr2x * this.dx[a[i][0]], yys + this.kr2y * this.dy[a[i][0]], xxs + this.kr2x * this.dx[a[i][1]], yys + this.kr2y * this.dy[a[i][1]], 3, farba);
      this.drawLine(xxs + this.kr2x * this.dx[a[i][1]], yys + this.kr2y * this.dy[a[i][1]], xxs + this.kr2x * this.dx[a[i][2]], yys + this.kr2y * this.dy[a[i][2]], 3, farba);
    },
    initVyhybka(xxs, yys, rezim, sm) {
      var ctx = this.canvas;
      var fa_s, fa_v;
      if (rezim > 127) {
        fa_s = '#B909DE'; fa_v = '#B909DE';
      } else {
        if (sm > 64) { //Vyhybka sa prestavuje 
          fa_s = '#999999'; fa_v = '#777777';
        } else {
          fa_s = '#000000'; fa_v = '#000000';
        }
      }
      ctx.beginPath();
      ctx.lineWidth = "1";
      ctx.strokeStyle = fa_s;
      ctx.fillStyle = fa_v;
      ctx.fillRect(xxs - (this.kr2x / 2), yys - this.kr2y, this.kr2x, 2*this.kr2y);
      ctx.stroke();
    },
    prvok_VN(xxs, yys, e) { /* Obycajna vyhybka */
      var a = [[0, 0, 0], [0, 0, 0]];
      var ppom = e.c0 & 4095;                    
      a[0][0] = (ppom >> 8) % 10;
      a[0][1] = ((ppom >> 4) & 15) % 10;
      a[0][2] = (ppom & 15) % 10;
      ppom = e.c1 & 4095;                    
      a[1][0] = (ppom >> 8) % 10;
      a[1][1] = ((ppom >> 4) & 15) % 10;
      a[1][2] = (ppom & 15) % 10;
      
      this.initVyhybka(xxs, yys, 0, e.sm);
      if (e.sm > 64) { //Vyhybka sa prestavuje
        //farbaStav(3);
        this.vykresliVetvu(xxs, yys, 2 - (e.sm & 63), a, '#999');
        this.vykresliVetvu(xxs, yys, (e.sm & 63) - 1, a, '#999');
      } else {
        this.vykresliVetvu(xxs, yys, 2 - (e.sm & 3), a, '#34454D');
        //farbaStav(stav);
        this.vykresliVetvu(xxs, yys, (e.sm & 3) - 1, a, '#999');
      }
    },
    prvok_VK(xxs, yys, e) {     /* Krizova vyhybka */
      var a = [[0, 0, 0], [0, 0, 0], [0, 0, 0], [0, 0, 0]];
      var ppom = e.c0 & 4095;                    
      a[0][0] = (ppom >> 8) % 10;
      a[0][1] = ((ppom >> 4) & 15) % 10;
      a[0][2] = (ppom & 15) % 10;
      ppom = e.c1 & 4095;                    
      a[1][0] = (ppom >> 8) % 10;
      a[1][1] = ((ppom >> 4) & 15) % 10;
      a[1][2] = (ppom & 15) % 10;
      ppom = e.c2 & 4095;                    
      a[2][0] = (ppom >> 8) % 10;
      a[2][1] = ((ppom >> 4) & 15) % 10;
      a[2][2] = (ppom & 15) % 10;
      ppom = e.c3 & 4095;                    
      a[3][0] = (ppom >> 8) % 10;
      a[3][1] = ((ppom >> 4) & 15) % 10;
      a[3][2] = (ppom & 15) % 10;
      this.initVyhybka(xxs, yys, 0, e.sm);
      var b = ((e.sm & 63) & 3); 
      var c = (((e.sm & 63) >> 2) & 3);
      this.vykresliVetvu(xxs, yys, 2 - b, a, '#34454D');
      this.vykresliVetvu(xxs, yys, 4 - c, a, '#34454D');
      //farbaStav(stav);
      var stav = 0;
      if ((e.sm & 16) == 16 || stav == 0) { 
        this.vykresliVetvu(xxs, yys, b - 1, a, '#999'); }
      if ((e.sm & 32) == 32 || stav == 0) {
        this.vykresliVetvu(xxs, yys, c + 1, a, '#999'); }
    },
    /* Vykreslenie navestidla pre posun
    * @param int pozadie farba pozadia
    * @param int obris farba obrisu symbolu navestidla */
    prvok_NE(xxs, yys, pozadie, obris, sm) {
      var ctx = this.canvas;
      ctx.fillStyle = pozadie;
      ctx.fillRect(xxs - this.kr2x+2, yys - this.kr2y+1, 2*this.kr2x-4, 2*this.kr2y-2);
                           // Vymaž políčko návestidla
      var k = 3 - (2 * (sm & 3));        // sm=1 => k=1; sm=2 => k=-1
      this.drawLine(xxs + (-1 * k * (this.kr2x / 2)), yys - this.kr2y + 2, xxs + (k * this.kr2x / 2), yys, 2, obris); 
      this.drawLine(xxs + (k * this.kr2x / 2), yys, xxs + (-1 * k * (this.kr2x / 2)), yys + this.kr2y - 2, 2, obris);
    },
    prvok_NHD(xxs, yys, pozadie, obris, obsah, sm) {
      var ctx = this.canvas;
      ctx.fillStyle = pozadie;
      ctx.fillRect(xxs - this.kr2x+2, yys - this.kr2y+1, 2*this.kr2x-4, 2*this.kr2y-2);
      
      var k = 3 - (2 * (sm & 3));     // sm=1 => k=1; sm=2 => k=-1
      ctx.beginPath();
      ctx.moveTo(xxs + (-1 * k * (this.kr2x / 2)), yys - this.kr2y + 2);
      ctx.lineTo(xxs + (k * this.kr2x / 2), yys); 
      ctx.lineTo(xxs + (-1 * k * (this.kr2x / 2)), yys + this.kr2y - 2);
      ctx.closePath();
      ctx.lineWidth = 2;
      ctx.strokeStyle = obris;
      ctx.stroke();
      ctx.fillStyle = obsah;
      ctx.fill();
    },
    prvok_SB(xxs, yys, e) { /* STANICNA BUDOVA */
      var ctx = this.canvas;
      ctx.fillStyle = '#FF8400';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 1, 2*this.kr2x - 2, 2*this.kr2y - 2);

      this.kresliText(xxs, yys, e.n1, e.oznacenie, '#ddd');
    },
    /* Vykreslenie textu so smerom
    * @param int smer_txt Smer podľa numerickej klávesnice 
    * @param String text Text na vypísanie 
    * @param color farba Farba textu */
    kresliText(xxs, yys, smer_txt, text, farba) {
      var ctx = this.canvas;
      ctx.fillStyle = farba;
      ctx.font = "14px Verdana";
      var korekcia_y = 0;
      var smer = ['center','middle'];
      var smu = (smer_txt & 15);
      switch (smu) {
        case 1: smer = ['right', 'top']; break;
        case 2: smer = ['center', 'top']; break;
        case 3: smer = ['left', 'top']; break;
        case 4: smer = ['right', 'middle']; korekcia_y = -2; break;
        case 5: smer = ['center', 'middle']; korekcia_y = -2; break;
        case 6: smer = ['left', 'middle']; korekcia_y = -2; break;
        case 7: smer = ['right', 'bottom']; break;
        case 8: smer = ['center', 'bottom']; break;
        case 9: smer = ['left', 'bottom']; break;
      }
      ctx.textAlign = smer[0];
      ctx.textBaseline = smer[1];
      ctx.strokeStyle = farba;
      ctx.font = "14px Verdana";
      ctx.fillText(text, xxs + this.dx[smu] * this.kr2x, yys + this.dy[smu] * this.kr2y + korekcia_y);
    },
    
    prvok_TS(xxs, yys, e, stav) {
      var ctx = this.canvas;
      ctx.fillStyle = (e.n1 == 1) ? '#000' : '#DCDCDC';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 1, 2*this.kr2x - 2, 2*this.kr2y - 2);
      ctx.textAlign = 'center';
      ctx.textBaseline = "middle";
      ctx.font = "14px Courier New";
      if (e.n1 == 1) {  //Ak je viditeľný 
        var p = 3 - 2 * e.sm;
        ctx.fillStyle = '#66DE57'; ctx.strokeStyle = '#66DE57'; //Základný stav voľný - zelená
        if (e.c2 > 0) { ctx.strokeStyle = '#EE3355'; ctx.fillStyle = '#000';}                // Obsadený - červená
        if (e.dl > 0) { ctx.fillStyle = '#333'; ctx.strokeStyle = '#333'; }                  // Blokovany - čierny
        if ((stav & 4) == 4) { ctx.fillStyle = '#8888FF'; ctx.strokeStyle = '#8888FF';}      // Zmena smeru na AB - fialový
        ctx.fillText(((stav & 3) == 1 ? '»':'«'), xxs, yys);
      } else {  
        ctx.fillStyle = '#333'; ctx.strokeStyle = '#333';
        ctx.fillText(((stav & 3) == 1 ? '»':'«'), xxs, yys);
      } 
    },
    prvok_ZB (xxs, yys, sm, stav, c) {
      var ctx = this.canvas;
      ctx.lineWidth = "1";
      ctx.strokeStyle = '#8888FF';
      ctx.fillStyle = '#000';
      ctx.font = "14px Verdana";
      var b = sm >> 1; //b-pocet riadkov
      switch ((sm & 1)) {
        case 0:
          ctx.strokeRect(xxs - this.kr2x, yys - this.kr2y + 1, 6 * this.kr2x, 2*this.kr2y * (b + 1) - 2);
          ctx.textAlign = 'right';
          ctx.textBaseline = 'middle';
          ctx.strokeStyle = '#FFE498';
          if (stav == 0) {
           for (var i = 0; i <= b; i++) { 
             if (c[i] > 0) { 
               ctx.fillText(c[i], xxs + this.kr2x * 5, yys - 2 + i * (2*this.kr2y - 1));
             } 
           }
          } else {
            ctx.fillText(c[3], xxs + this.kr2x * 5, yys - 1);
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText("?", xxs + 2 * this.kr2x, yys + 2*this.kr2y - 2);
            ctx.strokeStyle = '#8888FF'; 
            ctx.fillText("A", xxs, yys + 2*this.kr2y - 2);
            ctx.strokeStyle = '#FF8888'; 
            ctx.fillText("N", xxs + this.kr2x * 4, yys + 2*this.kr2y - 2);
          }
         break;
       case 1:
          ctx.strokeRect(xxs - this.kr2x, yys + this.kr2y - 1, 6 * this.kr2x, 2 - 2*this.kr2y * (b + 1));
          ctx.textAlign = 'right';
          ctx.textBaseline = 'middle';
          ctx.strokeStyle = '#FFE498';
          if (stav == 0) {
           for (var i = 0; i <= b; i++) { 
             if (c[i] > 0) { 
               ctx.fillText(c[i], xxs + this.kr2x * 5, yys - 2 - i * (2*this.kr2y - 1));
             }
           }
          } else {
            ctx.fillText(c[3], xxs + this.kr2x * 5, yys - 2*this.kr2y - 1);
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText("?", xxs + 2 * this.kr2x, yys - 3);
            ctx.strokeStyle = '#8888FF'; 
            ctx.fillText("A", xxs, yys - 3);
            ctx.strokeStyle = '#FF8888'; 
            ctx.fillText("N", xxs + this.kr2x * 4, yys - 3);
          }
         break;
      }
    }
  }
});


new Vue({
  el: '#app'
});