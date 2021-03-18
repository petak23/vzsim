/**
 * Vue komponenta pre výpis a správu zoznamu vlakov v simulácii.
 * Posledna zmena(last change): 18.03.2021
 *
 *	Modul: RUN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
Vue.component('zoznam', {
  props: {
    text_i: String,
    vlaky: String,
  },
  computed: {
    myvlk() {
      return JSON.parse(this.vlaky);
    },
  },
  filters: {
    cislovlaku: function (value) {
      if (!value) return ''
      value = value.toString()
      var typ_vlaku;
      switch (value.length) {
      case 1:
      case 2: typ_vlaku ='EC';
        break;
      case 3: typ_vlaku = (['1','2','3','4','5'].includes(value[0])) ? 'IC' : 'R';
        break;
      case 4:
              if ((value[0]=='1') && (['0','1','2','3','4','5','6'].includes(value[1]))) { typ_vlaku ='Rp'
              } else {
                typ_vlaku = ((value[0]=='1') && (['7','8','9'].includes(value[1]))) ? 'Zr' : 'Os';
                if ((value[0]=='2')&&(value[1]=='8')&&(value[2]=='8')) typ_vlaku = 'Sv';
              }
        break;
      case 5:
        switch (parseInt(value[0])) {
          case 1: typ_vlaku = (['0','1','2','3','4','5','6'].includes(value[2])) ? 'R' : 'Os';
            break;
          case 2: typ_vlaku ='Os';
          case 3:
          case 4: typ_vlaku='Pn';
            break;
          case 5:
            switch (parseInt(value[3])) {
              case 0: typ_vlaku ='Nex';
                break;
              case 1:
              case 2: typ_vlaku ='Rn';
                break;
              case 3:
              case 4: typ_vlaku='KPS';
                break;
              case 5:
              case 6:
              case 7: typ_vlaku='Sn';
                break;
              case 8:
              case 9: typ_vlaku='Vn';
            }
          case 6: typ_vlaku ='Pn';
            break;
          case 7: typ_vlaku ='Lv';
            break;
          case 8: typ_vlaku = (['0','1','2','3','4','5','6','7'].includes(value[3])) ? 'Mn' : 'Vl';
            break;
          case 9: typ_vlaku = 'Pv';
        }
        break;
      }
      for (var i = value.length; i < 5; i++) {
        value = "\u00A0"+value;
      }
      for (var i = typ_vlaku.length; i < 3; i++) {
        typ_vlaku += "\u00A0";
      }
      return typ_vlaku+value;
    },
    dlzkatextpred: function (v, l) {
      for (var i = v.length; i < l; i++) {
        v = "\u00A0"+v;
      }
      return v;
    }
  },
  template: `
    <div class="col-6 bg-primary zoznam">
      <ul>
        <li v-for="vl in myvlk">
          {{vl.cislo | cislovlaku}}, {{vl.dl | dlzkatextpred(3)}}0m, 
          {{vl.ry | dlzkatextpred(3)}}km/h, {{vl.mz}}<i class="fas fa-long-arrow-alt-right"></i>{{vl.mo}}
        </li>
      </ul>
    </div>
    `
});