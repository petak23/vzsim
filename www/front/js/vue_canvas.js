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
    this.mriezka(this.xmax_s, this.ymax_s)
    
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
        case 10:
        case 11: this.prvok_XP(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, '#999');
          break;
        case 12: this.prvok_MA(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, '#999', (e.c0 & 4095));
          break;
        case 13: this.prvok_MR(this.krokx*(x + 0.5), this.kroky*(y + 0.5), e.sm, '#999', (e.c0 & 4095));
          break;  
        case 15: this.prvok_UU(this.krokx*(x + 0.5), this.kroky*(y + 0.5), (e.c0 & 4095));
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
      /*int b = #6EF038;   // zakl. stav zelena na AB
      if (stav == 3) {
        b = #FF0000;
      } else {
        b = (c[1] == 0) ? #6EF038 : (c[1] > 0 & c[1] <= 100 ? #FEFF31 : #6EF038);
      }
      int pom = sm & 4;
      if (pom == 4) { b = #000000; }
      stroke(b);
      fill(b);
      pom = sm & 3;
      //println(obsah);
      //println("sm(0,1)=" + pom + " ; c[0]=" + c[0]);
      switch (pom) { //navestidlo AB
      case 1:if (c[0] == 2) { rect(xxs-5,yys-3, 1, 1);} else { rect(xxs-5,yys+2, 1, 1);}
      case 2:if (c[0] == 1) { rect(xxs+4,yys-3, 1, 1);} else { rect(xxs+4,yys+2, 1, 1);}
      }*/
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
      //fill(#000000); noStroke();
      //rect(xxs - kr2x + 1, yys - kr2y + 1, krok_x - 2, krok_y - 2);
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
    }
    
  }
})


new Vue({
  el: '#app',
  //created: function () { 
  //  console.log("Component created!");
  //},
  //components: {
  //  'mycanvas': mycanvas
  //}
  /*methods: {
    mydraw: mydraw
  }*/
})