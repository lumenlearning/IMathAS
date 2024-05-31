<template>
  <div
    class="calculator"
    :id="'qa-' + calctype + '-' + qn"
    aria-label="Use calculator">
    <button
      type="button"
      @click="openCalc"
      v-show="!showCalculator || calcIsPoppedOut"
      :disabled="calcIsPoppedOut">
        <icon-calc :calc-type="calctype"></icon-calc>
        Calculator
    </button>
    <div :class="{'calc-fixed-container': calcIsPoppedOut, 'graphing': calctype === 'graphing'}">
      <vue-draggable-resizeable
        v-show="showCalculator"
        class-name-active="calculator-active"
        ref="calcResize"
        @resizing="getCalcDimensions"
        :class="{'reset-heightwidth': !calcIsPoppedOut, 'calc-popout': calcIsPoppedOut}"
        :drag-cancel="'.drag-cancel'"
        :style="{position: calcPosition}"
        :draggable="calcIsPoppedOut"
        :resizable="calcIsPoppedOut"
        :handles="['br']"
        :h="500"
        :w="calctype === 'graphing' ? 600 : 500"
        :x="400"
        :y="-32"
        :z="2"
        :min-width="calctype === 'graphing' ? 500 : 400"
        :min-height="400"
      >
        <!-- FIXME: Hiding this widget until resizing is fixed.
        <div slot="br">
          <icon-drag></icon-drag>
        </div>
        -->
        <div class="calc-header">
          <span v-if="!calcIsPoppedOut">
            <icon-calc :calc-type="calctype"></icon-calc> Calculator
          </span>
          <span v-else> Question {{qn + 1}} Calculator</span>
          <div>
            <button
                type="button"
                :aria-label="!calcIsPoppedOut ? 'Pop out calculator' : 'Pop in calculator'"
                class="button popout"
                @click="toggleCalcPopOut">

                <icon-pop-out v-if="!calcIsPoppedOut"></icon-pop-out>
                <icon-pop-in v-else></icon-pop-in>
              </button>
              <button
                type="button"
                aria-label="Close calculator"
                class="button"
                @click="closeCalc"
              >
                <icon-close></icon-close>
              </button>
            </div>
        </div>
        <div class="calc-body">
          <figure
            class="drag-cancel"
            :id="'calc' + qn"
            ref="figure"
            :class="{ 'graphing' : calctype === 'graphing', }"
            :style="{'height': (calcHeight - 84) + 'px'}">
          </figure>
        </div>
      </vue-draggable-resizeable>
    </div>
  </div>
</template>

<script>
import { store } from '../../basicstore';
import IconCalc from '../icons/Calculators.vue';
import IconClose from '../icons/Close.vue';
import IconPopOut from '../icons/PopOut.vue';
import IconPopIn from '../icons/PopIn.vue';
import IconDrag from '../icons/DragHandle.vue';
import VueDraggableResizeable from 'vue-draggable-resizable';

export default {
  name: 'DesmosCalculator',
  props: ['qn', 'calctype'],
  components: {
    IconCalc,
    IconClose,
    IconPopOut,
    IconPopIn,
    IconDrag,
    VueDraggableResizeable
  },
  data: function () {
    return {
      showCalculator: false,
      calcIsPoppedOut: false,
      calcHeight: 500,
      calcObj: null,
      hadFirstOpen: false
    };
  },
  computed: {
    calcPosition () {
      return this.calcIsPoppedOut ? 'absolute' : 'initial';
    }
  },
  methods: {
    openCalc () {
      if (!this.hadFirstOpen) {
        this.initCalc();
        if (!store.assessInfo.can_view_all) {
          window.recclick('desmoscalc', store.aid, this.qn, this.calctype);
        }
      }
      this.hadFirstOpen = true;
      this.showCalculator = true;
    },
    closeCalc () {
      this.showCalculator = false;
      this.calcIsPoppedOut = false;
    },
    toggleCalcPopOut () {
      this.calcIsPoppedOut = !this.calcIsPoppedOut;
    },
    getCalcDimensions (left, top, width, height) {
      this.calcHeight = height;
    },
    initCalc () {
      if (this.calctype === 'basic') {
        this.calcObj = window.Desmos.FourFunctionCalculator(this.$refs.figure);
      } else if (this.calctype === 'scientific') {
        this.calcObj = window.Desmos.ScientificCalculator(this.$refs.figure);
        this.calcObj.updateSettings({degreeMode: true});
      } if (this.calctype === 'graphing') {
        this.calcObj = window.Desmos.GraphingCalculator(this.$refs.figure);
      }
    }
  },
  beforeDestroy () {
    if (this.calcObj) {
      this.calcObj.destroy();
    }
  },
  watch: {
    calctype: function (newVal, oldVal) {
      // if calctype has changed, and we already have one loaded, destroy it
      if (newVal !== oldVal && this.calcObj) {
        this.calcObj.destroy();
        // if calculator is open, load new one. Otherwise reset to wait for first click
        if (this.showCalculator) {
          this.initCalc();
        } else {
          this.hadFirstOpen = false;
        }
      }
    }
  }
};
</script>
<style>
.calculator {
  margin: 0px 3px;
  position: relative;
  width: 500px;
}

.calculator * {
  box-sizing: border-box;
}
.calculator button {
  background: linear-gradient(180deg, white 0%, #f9fafb 100%);
  border: 1px solid #c5cfd6;
  border-radius: 3px;
  box-shadow: 0 1px 0 0 rgba(33,43,54,0.05);
  color: #212b36;
  font-size: 0.9rem;
  line-height: 1;
  text-align: center;
}

.calculator button:hover {
  background: linear-gradient(180deg, white 0%, #e9edf1 100%);
}
.calculator > button {
  padding-left: 8px;
  margin: 0;
}
.calculator figure {
  height: 100%;
  margin: 0;
  width: 100%;
}
.calculator svg {
  height: 20px;
  vertical-align: text-bottom;
  width: 20px;
}
.calc-header {
  background-color: #f2f2f2;
  border: 1px solid #C4CDD5;
  border-bottom: none;
  border-radius: 3px 3px 0 0;
  display: flex;
  justify-content: space-between;
}
.calc-header .button {
  border: none;
  border-left: 1px solid #ccc;
  border-radius: 0 3px 0 0;
  height: 100%;
  margin: 0;
  /* Bring focus border forward so bottom isn't clipped  */
  position: relative;
  z-index: 1;
}

.calc-header .button:hover {
  background: linear-gradient(180deg, white 0%, #e9edf1 100%);
}

.calc-header .button svg {
  height: 12px;
  vertical-align: middle;
  width: 12px;
}

.calc-header span {
  border-radius: 4px;
  display: inline-block;
  margin-left: 8px;
  padding: 3px 0;
}

.calc-popout {
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 31px 41px 0 rgba(33,43,54,0.2), 0 2px 16px 0 rgba(33,43,54,0.08)
}

.calc-popout .calc-header span {
  font-size: 18px;
  padding: 4px 0;
}

.calc-popout .calc-header {
  background-color: #1e74d1;
  border: none;
  color:#fff;
}

.calc-popout .calc-header .button {
  background: transparent;
  border: none;
  border-radius: 3px;
  color: #fff;
  height: calc(100% - 6px);
  margin: 3px 0;
}

.calc-popout .calc-header .button:last-of-type {
  margin-right: 3px;
}

.calc-popout .calc-header .button:hover {
  background: #0059BA;
}

.calc-popout .calc-header svg {
  fill: #fff;
}

.calc-popout .calc-header .button svg {
  vertical-align: bottom;
}

.calc-popout .calc-body {
  margin: 16px;
  margin-bottom: 32px;
}

/* maintain height of question section when calculator is popped out */
.calc-fixed-container {
  height: 530px;
  position: relative;
}

.handle {
  box-sizing: border-box;
  cursor: se-resize;
  position: absolute;
}

.handle-br {
  bottom: 5px;
  right: 5px;
}

.handle svg {
  height: 18px;
  width: 17px;
}

/* reset calc size when popped in -- override vue-draggable-resizeable inline styles */
.reset-heightwidth {
  height: initial !important;
  width: initial !important;
}

@media (max-width: 768px){
  .calculator {
    width: 100%;
  }

  .calc-header .popout {
    display: none;
  }
}
</style>
