<template>
  <div class = "questionwrap questionpane">
    <div v-if = "!questionContentLoaded">
      {{ $t('loading') }}
    </div>
    <score-result
      v-if = "showScore"
      :qdata = "questionData"
      :qn = "qn"
    />
    <p
      v-if="questionData.withdrawn !== 0"
      class="noticetext"
    >
      <icons name="alert" color="warn" size="medium" />
      {{ $t('question.withdrawn') }}
    </p>
    <div v-if = "errorsToShow.length > 0" class="small">
      <ul>
        <li v-for = "(error,index) in errorsToShow" :key="index">
          {{ error }}
        </li>
      </ul>
    </div>
    <div
      v-if = "questionContentLoaded"
      v-html="questionData.html"
      class = "question"
      :id="'questionwrap' + qn"
    />
    <question-helps
      v-if = "showHelps"
      :qn = "qn"
    />

    <div v-if="questionHasCalculator" class="calculator">
      <button type="button" @click="openCalc" v-show="!showCalculator">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd"><rect fill="#212B36" x="6" y="3" width="13" height="18" rx="1"/><path fill="#DDE3E9" d="M7 9h2v2H7z"/><path fill="#F49342" d="M13 9h2v2h-2zM16 9h2v2h-2z"/><path fill="#DDE3E9" d="M10 9h2v2h-2zM7 12h2v2H7zM7 15h2v2H7z"/><path fill="#F49342" d="M13 12h2v2h-2zM16 12h2v2h-2z"/><path fill="#DDE3E9" d="M10 12h2v2h-2z"/><path fill="#2DCF77" d="M13 15h2v2h-2zM13 18h2v2h-2z"/><path fill="#DDE3E9" d="M10 15h2v2h-2zM10 18h2v2h-2zM7 18h2v2H8a1 1 0 01-1-1v-1z"/><path d="M16 15h2v4a1 1 0 01-1 1h-1v-5z" fill="#2DCF77"/><path d="M8 4h9a1 1 0 011 1v3H7V5a1 1 0 011-1z" fill="#FFF"/></g></svg>
        Calculator
      </button>
      <div class="calc-header" v-show="showCalculator">
        <span>
          <svg role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd"><rect fill="#212B36" x="6" y="3" width="13" height="18" rx="1"/><path fill="#DDE3E9" d="M7 9h2v2H7z"/><path fill="#F49342" d="M13 9h2v2h-2zM16 9h2v2h-2z"/><path fill="#DDE3E9" d="M10 9h2v2h-2zM7 12h2v2H7zM7 15h2v2H7z"/><path fill="#F49342" d="M13 12h2v2h-2zM16 12h2v2h-2z"/><path fill="#DDE3E9" d="M10 12h2v2h-2z"/><path fill="#2DCF77" d="M13 15h2v2h-2zM13 18h2v2h-2z"/><path fill="#DDE3E9" d="M10 15h2v2h-2zM10 18h2v2h-2zM7 18h2v2H8a1 1 0 01-1-1v-1z"/><path d="M16 15h2v4a1 1 0 01-1 1h-1v-5z" fill="#2DCF77"/><path d="M8 4h9a1 1 0 011 1v3H7V5a1 1 0 011-1z" fill="#FFF"/></g></svg>
          Calculator
        </span>
        <button
          type="button"
          aria-label="Close calculator"
          class="close"
          @click="closeCalc"
        >
          <svg role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24"><defs><path d="M13.414 12l8.293-8.293a.999.999 0 10-1.414-1.414L12 10.586 3.707 2.293a.999.999 0 10-1.414 1.414L10.586 12l-8.293 8.293a.999.999 0 101.414 1.414L12 13.414l8.293 8.293a.997.997 0 001.414 0 .999.999 0 000-1.414L13.414 12z" id="a"/></defs><use fill="#212B36" xlink:href="#a" fill-rule="evenodd"/></svg>
        </button>
      </div>
      <div v-show="showCalculator">
        <figure :id="'calc' + qn" ref="figure"></figure>
      </div>
    </div>

    <div v-if="showSubmit" class="submitbtnwrap">
      <button
        type = "button"
        @click = "submitQuestion"
        :class = "submitClass"
        :disabled = "!canSubmit"
      >
        {{ submitLabel }}
      </button>
      <button
        v-if = "canJumpToAnswer"
        type = "button"
        @click = "jumpToAnswer"
        class = "secondary"
        :disabled = "!canSubmit"
      >
        {{ $t('question.jump_to_answer') }}
      </button>
    </div>
  </div>
</template>

<script>
import { store, actions } from '../../basicstore';
import ScoreResult from '@/components/question/ScoreResult.vue';
import Icons from '@/components/widgets/Icons.vue';
import QuestionHelps from '@/components/question/QuestionHelps.vue';

export default {
  name: 'Question',
  props: ['qn', 'active', 'state', 'seed', 'disabled'],
  components: {
    ScoreResult,
    QuestionHelps,
    Icons
  },
  data: function () {
    return {
      timeActivated: null,
      timeActive: 0,
      showCalculator: false,
      uniqueId: 'test-calc' + this.qn
    };
  },
  computed: {
    questionData () {
      return store.assessInfo.questions[this.qn];
    },
    canSubmit () {
      return (!store.inTransit);
    },
    canJumpToAnswer () {
      return (this.questionData.jump_to_answer);
    },
    questionContentLoaded () {
      return (this.questionData.html !== null);
    },
    questionHasCalculator () {
      return this.questionData.showcalculator;
    },
    showSubmit () {
      return (store.inProgress &&
        this.questionContentLoaded &&
        !store.inPrintView &&
        this.questionData.withdrawn === 0 &&
        this.questionData.canretry && (
        store.assessInfo.submitby === 'by_question' ||
          this.questionData.tries_max > 1
      ) && (
      // if livepoll, only show if state is 2
        store.assessInfo.displaymethod !== 'livepoll' ||
          this.state === 2
      )
      );
    },
    submitClass () {
      return (store.assessInfo.submitby === 'by_assessment')
        ? 'secondary' : 'primary';
    },
    showScore () {
      return (store.inProgress &&
        !store.inPrintView &&
        (this.questionData.hasOwnProperty('score') ||
         this.questionData.status === 'attempted'
        ) &&
        store.assessInfo.show_results &&
        (this.questionData.try > 0 ||
          this.questionData.hasOwnProperty('tries_remaining_range')) &&
        this.questionData.withdrawn === 0
      );
    },
    submitLabel () {
      if (store.assessInfo.submitby === 'by_question') {
        // by question submission
        return this.$t('question.submit');
      } else if (this.questionData.tries_max === 1) {
        // by assessment, with one try
        return this.$t('question.saveans');
      } else {
        // by assessment, can retry
        return this.$t('question.checkans');
      }
    },
    showHelps () {
      return ((store.assessInfo.hasOwnProperty('help_features') && (
        store.assessInfo.help_features.message === true ||
        store.assessInfo.help_features.forum > 0)) ||
        (this.questionData.jsparams && this.questionData.jsparams.helps.length > 0));
    },
    errorsToShow () {
      let errors = [];
      if (store.assessInfo.hasOwnProperty('scoreerrors') &&
        store.assessInfo.scoreerrors.hasOwnProperty(this.qn)
      ) {
        errors = errors.concat(store.assessInfo.scoreerrors[this.qn]);
      }
      if (this.questionData.hasOwnProperty('errors')) {
        errors = errors.concat(this.questionData.errors);
      }
      return errors;
    }
  },
  methods: {
    openCalc(){
      this.showCalculator = true;
    },
    closeCalc() {
      this.showCalculator = false;
    },
    loadQuestionIfNeeded (skiprender) {
      if (!this.questionContentLoaded && this.active && store.errorMsg === null) {
        actions.loadQuestion(this.qn, false, false);
      } else if (this.questionContentLoaded && this.active &&
        !this.questionData.rendered && skiprender !== true) {
        this.renderAndTrack();
      }
    },
    submitQuestion () {
      this.updateTime(false);
      actions.submitQuestion(this.qn, false, this.timeActive);
    },
    jumpToAnswer () {
      store.confirmObj = {
        body: 'question.jump_warn',
        action: () => actions.loadQuestion(this.qn, false, true)
      };
    },
    updateTime (goingActive) {
      if (this.timeActivated === null || goingActive) {
        this.timeActivated = new Date();
      } else if (this.timeActivated !== null) {
        let now = new Date();
        this.timeActive += (now - this.timeActivated);
      }
    },
    addDirtyTrackers () {
      var self = this;
      window.$('#questionwrap' + this.qn).find('input[name],select[name],textarea[name]')
        .off('focus.dirtytrack').off('change.dirtytrack').off('input.dirtytrack')
        .on('focus.dirtytrack', function () {
          if (this.type === 'radio' || this.type === 'checkbox') {
            // focus doesn't make sense here
          } else {
            window.$(this).attr('data-lastval', window.$(this).val());
          }
          actions.clearAutosaveTimer();
        })
        .on('input.dirtytrack', function () {
          store.somethingDirty = true;
        })
        .on('change.dirtytrack', function () {
          let val = window.$(this).val().trim();
          let changed = false;
          if (this.type === 'radio' || this.type === 'checkbox') {
            changed = true;
          } else if (val !== window.$(this).attr('data-lastval') && val !== '') {
            changed = true;
          }
          if (changed) {
            store.somethingDirty = true;
            let name = window.$(this).attr('name');
            let m = name.match(/^(qs|qn|tc)(\d+)/);
            if (m !== null) {
              var qn = m[2] * 1;
              var pn = 0;
              if (qn >= 1000) {
                pn = qn % 1000;
                qn = Math.floor(qn / 1000 + 0.001) - 1;
              }

              // autosave value
              let now = new Date();
              let timeactive = self.timeActive + (now - self.timeActivated);
              actions.doAutosave(qn, pn, timeactive);
            }
          }
        });
    },
    disableOutOfTries () {
      let trymax = this.questionData.tries_max;
      for (let pn in this.questionData.parts) {
        var regex;
        if (this.questionData.parts[pn].try >= trymax) {
          // out of tries - disable inputs
          if (Object.keys(this.questionData.parts).length === 1 && Object.keys(this.questionData.jsparams).length > 1) {
            // Only one "part" listed, but multiple input boxes.
            // Probably conditional. Disable all boxes
            regex = new RegExp('^(qn|tc|qs)(' + (this.qn) + '\\b|' + (this.qn + 1) + '\\d{3}\\b)');
          } else if (pn === 0) {
            regex = new RegExp('^(qn|tc|qs)(' + (this.qn) + '\\b|' + ((this.qn + 1) * 1000 + pn * 1) + '\\b)');
          } else {
            regex = new RegExp('^(qn|tc|qs)' + ((this.qn + 1) * 1000 + pn * 1) + '\\b');
          }
          window.$('#questionwrap' + this.qn).find('input,select,textarea').each(function (i, el) {
            if (el.name.match(regex)) {
              el.disabled = true;
            }
          });
        }
      }
    },
    renderAndTrack () {
      if (this.questionData.rendered || !this.active) {
        return;
      }
      setTimeout(window.drawPics, 100);
      window.rendermathnode(document.getElementById('questionwrap' + this.qn));
      window.initSageCell(document.getElementById('questionwrap' + this.qn));
      this.updateTime(true);
      this.setInitValues();
      // add in timeactive from autosave, if exists
      this.timeActive += actions.getInitTimeactive(this.qn);
      this.addDirtyTrackers();

      let svgchk = '<svg class="scoremarker" viewBox="0 0 24 24" width="16" height="16" stroke="green" stroke-width="3" fill="none" role="img" aria-label="' + this.$t('icons.correct') + '">';
      svgchk += '<polyline points="20 6 9 17 4 12"></polyline></svg>';
      let svgychk = '<svg class="scoremarker" viewBox="0 0 24 24" width="16" height="16" stroke="rgb(255,187,0)" stroke-width="3" fill="none" role="img" aria-label="' + this.$t('icons.partial') + '">';
      svgychk += '<path d="M 5.3,10.6 9,14.2 18.5,4.6 21.4,7.4 9,19.8 2.7,13.5 z" /></svg>';
      let svgx = '<svg class="scoremarker" viewBox="0 0 24 24" width="16" height="16" stroke="rgb(153,0,0)" stroke-width="3" fill="none" role="img" aria-label="' + this.$t('icons.incorrect') + '">';
      svgx += '<path d="M18 6 L6 18 M6 6 L18 18" /></svg>';
      window.$('#questionwrap' + this.qn).find('.scoremarker').remove();
      window.$('#questionwrap' + this.qn).find('div.ansgrn,table.ansgrn').append(svgchk);
      window.$('#questionwrap' + this.qn).find('div.ansyel,table.ansyel').append(svgychk);
      window.$('#questionwrap' + this.qn).find('div.ansred,table.ansred').append(svgx);

      if (this.disabled) {
        window.$('#questionwrap' + this.qn).find('input,select,textarea').each(function (i, el) {
          if (el.name.match(/^(qn|tc|qs)\d/)) {
            el.disabled = true;
          }
        });
      };

      window.imathasAssess.init(this.questionData.jsparams, store.enableMQ);

      window.$('#questionwrap' + this.qn).find('select.ansgrn').after(svgchk);
      window.$('#questionwrap' + this.qn).find('select.ansyel').after(svgychk);
      window.$('#questionwrap' + this.qn).find('select.ansred').after(svgx);

      actions.setRendered(this.qn);
    },
    setInitValues () {
      var regex = new RegExp('^(qn|tc|qs)\\d');
      var thisqn = this.qn;
      window.$('#questionwrap' + this.qn).find('input,select,textarea')
        .each(function (index, el) {
          if (el.name.match(regex)) {
            if (el.type === 'radio' || el.type === 'checkbox') {
              if (el.checked) {
                actions.setInitValue(thisqn, el.name, el.value);
              }
            } else {
              actions.setInitValue(thisqn, el.name, window.$(el).val());
            }
          }
        });
    }
  },
  updated () {
    if (this.questionContentLoaded) {
      this.disableOutOfTries();
      this.renderAndTrack();
      if (this.questionHasCalculator === 'basic') {
        Desmos.FourFunctionCalculator(this.$refs.figure);
      } else if (this.questionHasCalculator === 'graphing') {
        Desmos.GraphingCalculator(this.$refs.figure);
      } else if (this.questionHasCalculator) {
        Desmos.ScientificCalculator(this.$refs.figure);
      }
    } else {
      this.loadQuestionIfNeeded();
    }
  },
  created () {
    this.loadQuestionIfNeeded(true);
  },
  mounted () {
    if (this.questionContentLoaded) {
      this.disableOutOfTries();
      this.renderAndTrack();
    }
  },
  watch: {
    active: function (newVal, oldVal) {
      this.loadQuestionIfNeeded();
      this.updateTime(newVal);
    },
    state: function (newVal, oldVal) {
      if ((newVal > 1 && oldVal <= 1) ||
          (newVal === 4 && oldVal < 4) ||
          (newVal === 3 && oldVal === 4)
      ) {
        // force reload
        actions.loadQuestion(this.qn, false, false);
      }
    },
    seed: function (newVal, oldVal) {
      actions.loadQuestion(this.qn, false, false);
    }
  }
};
</script>
<style>
input[type=text] {
  height: 20px;
}
.haseqneditor {
  margin-right: 0;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  height: 20px;
}
.eqneditortrigger {
  margin: 0;
  border-left: 0;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  height: 30px;
  padding: 4px;
  vertical-align: bottom;
}
input.green {
  margin-left: 0;
  border-color: #090;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}
input.red {
  margin-left: 0;
  border-color: #900;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}
.scoremark {
  display: inline-block;
  height: 20px;
  padding: 4px;
  margin-right: 0;
  border: 1px solid;
  border-right: 0;
  border-radius: 4px 0 0 4px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  height: 20px;
}
.scoremark.red {
  border-color: #900;
  color: #900;
}
.scoremark.green {
  border-color: #090;
  color: #090;
}
.questionwrap .question {
  border: 0;
  background-color: #fff;
  margin: 12px 0;
}
.submitbtnwrap {
  margin: 16px 0;
}
.ansgrn {
  border: 1px solid #090 !important;
}
.ansred {
  border: 1px solid #900 !important;
}
.ansyel {
  border: 1px solid #fb0 !important;
}
div.ansgrn, div.ansred, div.ansyel {
  margin: -1px;
}
input[type=text].ansgrn, .mathquill-math-field.ansgrn {
  padding-right: 17px;
  background: right no-repeat url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjE2IiBoZWlnaHQ9IjE2IiBzdHJva2U9ImdyZWVuIiBzdHJva2Utd2lkdGg9IjMiIGZpbGw9Im5vbmUiPjxwb2x5bGluZSBwb2ludHM9IjIwIDYgOSAxNyA0IDEyIj48L3BvbHlsaW5lPjwvc3ZnPg==");
}
input[type=text].ansred, .mathquill-math-field.ansred {
  padding-right: 17px;
  background: right no-repeat url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjE2IiBoZWlnaHQ9IjE2IiBzdHJva2U9InJnYigxNTMsMCwwKSIgc3Ryb2tlLXdpZHRoPSIzIiBmaWxsPSJub25lIj48cGF0aCBkPSJNMTggNiBMNiAxOCBNNiA2IEwxOCAxOCIgLz48L3N2Zz4=");
}
input[type=text].ansyel, .mathquill-math-field.ansyel {
  padding-right: 17px;
  background: right no-repeat url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjE2IiBoZWlnaHQ9IjE2IiBzdHJva2U9InJnYigyNTUsMTg3LDApIiBzdHJva2Utd2lkdGg9IjMiIGZpbGw9Im5vbmUiPjxwYXRoIGQ9Ik0gNS4zLDEwLjYgOSwxNC4yIDE4LjUsNC42IDIxLjQsNy40IDksMTkuOCAyLjcsMTMuNSB6IiAvPjwvc3ZnPg==");
}

.calculator {
  margin: 0px 3px;
  width: 50%;
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
  height: 400px;
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
.calc-header .close {
  border: none;
  border-radius: 0 3px 0 0;
  margin: 0;
  /* Bring focus border forward so bottom isn't clipped  */
  position: relative;
  z-index: 1;
}

.calc-header .close:hover {
  background: linear-gradient(180deg, white 0%, #e9edf1 100%);
  border-left: 1px solid #ccc;
}

.calc-header .close svg {
  height: 14px;
  vertical-align: middle;
  width: 14px;
}

.calc-header span {
  border: none;
  display: inline-block;
  margin-left: 8px;
  padding: 4px 0;
}

</style>
