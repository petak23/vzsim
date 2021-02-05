Vue.component('mycanvas', { 
//var mycanvas = {  
  props: {
    prvky: String,
    cesty: String,
    vlaky: String,
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
      cesta_z: null,
      cesta_k: null
    };
  },
  template: `
      <canvas id="editPlace" 
              :width="xmax" 
              :height="ymax" 
              style="background-color: #000" 
              @click.left.ctrl.exact="get_mouse('P', $event)"
              @click.middle="get_mouse('P', $event)"
              @click.left.exact="get_mouse('V', $event)"
      />
    `,
  computed: {
    // Parsovanie JSON-u  na array
    myprv() {
      return JSON.parse(this.prvky);
    },
    mycst() {
      return JSON.parse(this.cesty);
    },
    myvlk() {
      return JSON.parse(this.vlaky);
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
        case 21: //MZ
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
      var ss = ((pr.stav & 15) === 0) ? pr.n[0] : pr.c[3]; // Text na koľaji
      var b = (ss !== 0) ? String(ss).length : 0;          // Dĺžka textu na koľaji
      var k_l = this.kr2x*(pr.c[0]>>4)*2; 
      var k_r = this.kr2x*(pr.c[0] & 15)*2;
      ctx.fillStyle = "#000";
      ctx.fillRect(xxs - k_l - (3 * this.kr2x), yys - this.kr2y + 2, (6 * this.kr2x) + k_l + k_r, 2*this.kr2y - 4);
      //nastupiste
      if ((pr.sm & 1) === 1) { this.drawLine(xxs - 3 * this.kr2x, yys + this.kr2y, xxs + 3 * this.kr2x, yys + this.kr2y, 2, '#FF9F03'); }
      if ((pr.sm & 2) === 2) { this.drawLine(xxs - 3 * this.kr2x, yys - this.kr2y, xxs + 3 * this.kr2x, yys - this.kr2y, 2, '#FF9F03'); }
      var fa_u, fa_v;
      switch (pr.y) {
        case 1: fa_v = '#f33'; fa_u = '#fff703'; break;//stoji a nema
        case 2: fa_v = '#f33'; fa_u = '#000'; break;//stoji a ma>3
        case 3: fa_v = '#fff703'; fa_u = '#000'; break;//1 min do odchodu
        default: fa_v = '#000'; fa_u = '#DCDCDC';
      }
      var text_w_pol = ctx.measureText(ss).width / 2 + 4;
      if (pr.y > 0) { 
        ctx.fillStyle = fa_v;
        ctx.fillRect(xxs - k_l - (3 * this.kr2x) + 1, yys - this.kr2y + 2, xxs + (3 * this.kr2x) + k_r - 1, yys + this.kr2y - 2);     
      }
      var col = this.farbaStav(pr.stav & 15);
      if (b === 0) { 
        this.drawLine(xxs - k_l  - (3 * this.kr2x), yys, xxs + k_r + (3 * this.kr2x), yys, 3, col[0]);
      } else {
        this.drawLine(xxs - k_l - (3 * this.kr2x), yys, xxs - text_w_pol, yys, 3, col[0]);
        this.drawLine(xxs + k_r + (3 * this.kr2x), yys, xxs + text_w_pol, yys, 3, col[0]);
        ctx.fillStyle = fa_u;
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
    
    prvok_TS(pr) {
      var xxs = this.krokx*(this.sux(pr.xs) + 0.5);
      var yys = this.kroky*(this.suy(pr.xs) + 0.5);
      var ctx = this.canvas;
      ctx.fillStyle = (pr.n[1] === 1) ? '#000' : '#DCDCDC';
      ctx.fillRect(xxs - this.kr2x + 1, yys - this.kr2y + 1, 2*this.kr2x - 2, 2*this.kr2y - 2);
      ctx.textAlign = 'center';
      ctx.textBaseline = "middle";
      ctx.font = "14px Courier New";
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
      ctx.strokeStyle = '#8888FF';
      ctx.fillStyle = '#000';
      ctx.font = "14px Verdana";
      var b = pr.sm >> 1; //b-pocet riadkov
      switch ((pr.sm & 1)) {
        case 0:
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
          break;
        case 1:
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
          break;
      }
    },
    get_mouse(m, e) {
      var xs = this.getXs(e.offsetX, e.offsetY);
      var pr = this.myprv[xs];
      var cesta = null;
      if (typeof pr !== 'undefined' ) { //https://stackoverflow.com/questions/2281633/javascript-isset-equivalent
        if (this.cesta_z === null && pr.id_prvky_kluc === 6) { // NH
          if (m === "P" && (pr.sm & 8) === 0) { // Posunová
            this.cesta_z = pr;
            this.$emit('text_g', "Začiatok cesty:" + this.cesta_z.key + "("+ this.cesta_z.xs + ")" + "<||>" + m);
            this.cesta_z.stav += 16;
            this.cesta_z.n[0] = 1;
            this.prvok_N(this.cesta_z);
          } else if (m === "V") {  //Vlaková
            this.cesta_z = pr;
            this.$emit('text_g', "Začiatok cesty:" + this.cesta_z.key + "("+ this.cesta_z.xs + ")" + "<||>" + m);
            this.cesta_z.stav += 32;
            this.cesta_z.n[0] = 2;
            this.prvok_N(this.cesta_z);
          }
        } else if (this.cesta_z === null && pr.id_prvky_kluc === 8) { //NE
          this.cesta_z = pr;
          m = 'P';
          this.$emit('text_g', "Začiatok cesty:" + this.cesta_z.key + "("+ this.cesta_z.xs + ")" + "<||>" + m);
          this.cesta_z.stav += 16;
          this.cesta_z.n[0] = 1;
          this.prvok_N(this.cesta_z);
        } else if (this.cesta_z !== null && (pr.id_prvky_kluc === 3 || pr.id_prvky_kluc === 14 || pr.id_prvky_kluc === 22)) {
          this.cesta_k = (pr.id_prvky_kluc !== 22) ? pr : this.myprv[pr.c[0]]; // Pre prípad kliku na KO
          var cesta = this.test_cesta(this.cesta_z, this.cesta_k);
          if (cesta !== null) {
            if (cesta !== 0) { // Mám cestu a nie je obsadená
              this.$emit('text_g', "Staviam cestu:" + cesta.zc + " -> " + cesta.kc + " t:" + cesta.typ + " v:" + cesta.vyh + "<||>" + (cesta.typ === 2 ? "V" : "P"));
              this.postav_cestu(cesta);
            } else {           // Mám cestu ale je obsadená

            }
          } else {             // Cestu som nenašiel
            this.$emit('text_g', "");
            this.$emit('text_r', "Cestu som nenašiel!!!");
          }
          this.cesta_z.stav &= 15;
          this.prvok_N(this.cesta_z);
          this.cesta_z = null;
          this.cesta_k = null;
          cesta = null;
        } else {
          this.cesta_z.stav &= 15;
          this.prvok_N(this.cesta_z);
          this.cesta_z = null;
          this.cesta_k = null;
          cesta = null;
          this.$emit('text_g', "");
        }
      }
    },
    test_cesta(cesta_z, cesta_k) {
      var cisvyh = 0;         // Poradové číslo výhybky vrámci cesty
      var cislo_cesty = 0;    // Číslo použitej cesty 
      Object.keys(this.mycst).forEach(s => {    // Nájdenie cesty
        if ((this.mycst[s].zc === cesta_z.xs) && (this.mycst[s].kc === cesta_k.xs) && (this.mycst[s].typ === cesta_z.n[0])) {
          cislo_cesty = s;                      // Zapíš číslo nájdenej cesty
        }
      });
      if (cislo_cesty === 0) { return null; }   // Cestu som nenašiel skonč
      var pr = this.myprv[cesta_z.xs];          // Aktuálne testovaný prvok cesty
      var sm0 = (this.myprv[cesta_z.xs].sm & 3);// Smer cesty
      var prvky_cesty = [cesta_z.xs];           // Pole prvkov cesty
      var prvky_odvrat = [];                    // Pole odvratov
      var prvky_odkaz = [];                     // Pole odkazov pre danú cestu
      var xs = cesta_z.c[0];                    // Počiatočné xs z položky csn NH alebo NE
      this.myprv[cesta_z.xs].c[3] = cesta_k.xs; // Zapíš do návestidla koniec cesty
      var cisvch = (pr.sm & 3) === 1 ? 4 : 6;   // Nájdenie čísla "vchodu" do nasledujúcej bunky podľa num. klávesnice
      var rychl = 255;                          // Max. rýchlosť cesty
      var final = 50;                           // Max. počet prvkov cesty
      do {
        pr = this.myprv[xs];                    // Do pr daj info o aktuálnom prvku
        if (typeof pr !== 'undefined' && pr.stav === 0) { // Volný úsek
          prvky_cesty.push(pr.xs);              // Ak prvok existuje a mám ho tak vlož do poľa
          if (pr.xs === this.cesta_k.xs) {      // Je koniec cesty?
            final = 0;
            if (pr.odk > 0) {                            // Existujú k prvku prvky UO alebo MO
              prvky_odkaz = prvky_odkaz.concat(this.najdiOdkazy(xs, sm0));
            }
          } else {                              // Najdi nasledujúci
            switch (pr.id_prvky_kluc) {
              case 1: //UB
              case 3: //KB
              case 4: //UO
              case 5: //MO
                var k = pr.c[0];                             // Cesta úseku
                k = (sm0 === 1) ? (k & 15) : (k >> 8);       // Číslo cesty v smere ku koncu cesty
                if (pr.odk > 0) {                            // Existujú k prvku prvky UO alebo MO
                  prvky_odkaz = prvky_odkaz.concat(this.najdiOdkazy(xs, sm0));
                }
                xs += this.dx[k] + this.dy[k] * this.xmax_s; // Nájdi ďaľší prvok
                cisvch = 10 - k;                             // Nájdi číslo vchodu pre nasledujúci úsek
                break;
              case 6: //NH
              case 8: //NE
                switch (cisvch) {
                  case 4: xs = pr.c[(pr.sm & 3)-1]; break;
                  case 6: xs = pr.c[2-(pr.sm & 3)]; break;
                }
                break;
              case 10: //UP
                xs += this.dx[10 - cisvch];
                break;
              case 14: //KS;
                //pr.c[3] = pre_vlak;
                switch (cisvch) {
                  case 4: xs +=(pr.c[0] & 15) + 2; break;
                  case 6: xs -=(pr.c[1] >> 4) - 2; break;
                }
                break;
              case 16: //VN;
                pr.sm = this.mycst[cislo_cesty].vyh[cisvyh];            // Poloha výhybky pre danú cestu
                if (pr.c[2] > 0) {                                      // Existuje odvrat resp. spolupracujúca výh.
                  var psv = this.myprv[pr.c[2]];                        // Prvok spolupracujúcej(odvratnej) výhybky
/*                  if ((psv.sm & 3 !== pr.sm & 3) && psv.stav >= 1 && psv.stav <= 6) { // Je spolup. výh. prestavená inak
                    //obsad;                                                          // a odvratná cesta nie je voľná 
                    final = 0;
                    return null; 
                  } else {
                    //this.myprv[pomv].sm = (v1 > 3) ? (pp.sm & 3) | v1 : (pp.sm & 12) | v1;
                  }*/
                  prvky_odvrat.push(pr.c[2]);
                }
                k = pr.c[this.mycst[cislo_cesty].vyh[cisvyh]-1] & 4095; // Spočítaj číslo odchodu z prvku (num. klávesnica)
                k = ((k >> 8) === cisvch) ? k & 15 : k >> 8;            // ok
                xs += this.dx[k] + this.dy[k] * this.xmax_s;            // Nájdenie xs nasledujúceho prvku
                cisvyh++;
                cisvch = 10 - k;
                break;
            }
          }
          final--;
        } else {
          final = 0;
          cislo_cesty = 0;
        }
      }
      while (final > 0)

      if (cislo_cesty) {                                      // Ak mám cestu
        this.mycst[cislo_cesty].prvky_cesty = prvky_cesty;    // Do cesty vlož jej prvky
        this.mycst[cislo_cesty].prvky_odvrat = prvky_odvrat;  // Do cesty vlož odvratné výhybky
        this.mycst[cislo_cesty].prvky_odkaz = prvky_odkaz;    // Do cesty vlož odkazy na prvky
      }
      return cislo_cesty !== 0 ? this.mycst[cislo_cesty] : 0;
    },
    postav_cestu(cesta) {
      cesta.prvky_cesty.forEach(xs => {
        if (xs === cesta.zc) this.myprv[xs].stav = cesta.typ;
        //Zapíš do prvkov záver cesty okrem návestidiel
        if (this.myprv[xs].id_prvky_kluc !== 6 && this.myprv[xs].id_prvky_kluc !== 8) {
          this.myprv[xs].stav = cesta.typ;
        }
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
          case 21: //MZ
          default:
            break;
        }
      });
      if (cesta.prvky_odkaz.length) {       //Ak mám odkazy
        cesta.prvky_odkaz.forEach(xo => {
          this.myprv[xo].stav = cesta.typ;
          this.prvok_XB(this.myprv[xo]);
        });
      }
    },
    najdiOdkazy(xs, sm) {
      var odkazy = [];
      Object.keys(this.myprv).forEach(xos => {
        var pr = this.myprv[xos];
        if (pr.n[sm-1] === xs && pr.id_prvky_kluc === 4 ) 
          odkazy.push(xos);
      });
      return odkazy;
    }
  }
});

Vue.component('zoznam', {
  props: {
    text_v: String,
    text_i: String
  },
  data: function () {
    return {
      time: 288000, // v desatinách sekund 8:00 * 60 * 60 * 10
      button_txt: "Spusť",
      timer:null,
      isRunning: false,
      interval: [200, 130, 60],
      speed: 0
    };
  },
  computed: {
    time_u: function () {
      let mytime = Math.round(this.time /10);
      let time = mytime / 60;
      let secondes = Math.round((mytime - parseInt((mytime / 60)) * 60));
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
      switch (this.speed) {
        case 0: return "btn-outline-success"; break;
        case 1: return "btn-outline-warning"; break;
        case 2: return "btn-outline-danger"; break;
      }
    }
  },
  methods: {
    casovac_start () {
      this.isRunning = true;
      if (!this.timer) {
        this.timer = setInterval( () => {
            this.time += 2;
        }, this.interval[this.speed] );
      }
    },
    casovac_stop () {
      this.isRunning = false;
      clearInterval(this.timer);
			this.timer = null;
    },
    casovac_up() {
      this.speed += this.speed < 2 ? 1 : 0;
      if (this.isRunning) {
        this.casovac_stop();
        this.casovac_start();
      }
    },
    casovac_dwn() {
      this.speed -= this.speed > 0 ? 1 : 0;
      if (this.isRunning) {
        this.casovac_stop();
        this.casovac_start();
      }
    }
  },
  template: `
    <div class="row">
      <div class="col-2 bg-dark text-white">
        <div class="btn-group btn-group-sm" role="group" aria-label="...">                                                                                    
          <button @click="casovac_dwn" class="btn btn-outline-info btn-sm" :class="speed == 0 ? 'disabled' : ''"><</button>
          <button class="btn btn-outline-info btn-sm disabled" >{{time_u}}</button>
          <button @click="casovac_up" class="btn btn-outline-info btn-sm" :class="speed == 2 ? 'disabled' : ''">></button>
          <button class="btn btn-sm disabled" :class="speedClass">{{speed}}</button>                                                                                    
        </div><br />
        <button @click="casovac_start" v-if="!isRunning" class="btn btn-outline-success btn-sm">Spusť</button>
        <button @click="casovac_stop" v-if="isRunning" class="btn btn-outline-danger btn-sm">Stop</button>
      </div>
      <div class="col-4 bg-info h-3">{{text_v}}</div>
      <div class="col-6 bg-primary h-3">{{text_i}}</div>
    </div>
    `
});

Vue.component('info', {
  props: 
    ['text_g', 'text_r']
  ,
  data: function () {
    return {
      textr: '',
      textg: '',
      textgv: false,
      textrv: false,
      timer:null
    }
  },
  methods: {
    skry_g() {
      this.textgv = false;
      this.$emit('text_g_clr', true);
    },
    skry_r() {
      this.textrv = false;
      this.$emit('text_r_clr', true);
    },
    start_r() {
      if (!this.timer) {
        this.timer = setInterval( () => { // https://codepen.io/edscode/pen/QXXowy
          this.textr = "";
          this.textrv = false;
          this.$emit('text_r_clr', true);
          clearInterval(this.timer);
          this.timer = null;
          this.stop();
        }, 3000 );
			}
    },
    stop () {
      clearInterval(this.timer);
      this.timer = null;
    }
  },
  computed: {
    activeClass: function () {
      var sp = this.text_g.split("<||>");
      return sp[1] === 'V' ? 'bg-success' : (sp[1] === 'P' ? 'bg-light' : 'bg-transparent');
    },
    redClass: function () {
      return this.text_r.length > 0 ? "bg-danger" : "bg-transparent";
    }
  },
  watch: {
    text_r: function (newText_r, oldText_r) {
      this.textrv = this.text_r.length > 0 ? true : false;
      this.textr = newText_r;
      this.start_r();
    },
    text_g: function(newText_g, oldText_g) {
      this.textgv = this.text_g.length > 0 ? true : false;
      var sp = newText_g.split("<||>");
      this.textg = sp[0];
    }
  },

  template: `
    <div class="row">
      <div  class="col-6 mt-1 min-h-my" 
            :class="activeClass" @click="skry_g">{{textg}}</div>
                                          
      <div class="col-6 mt-1 min-h-my text-white"
            :class="redClass" @click="skry_r">{{textr}}</div>
    </div>
    `
});

new Vue({
  el: '#app',
  data: {
    text_g: "",
    text_r: ""
  },
  methods: {
    text_r_clear() {
      this.text_r = "";
    },
    text_g_clear() {
      this.text_g = "";
    }
  }
});