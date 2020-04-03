<template>
    <svg
        xmlns = "http://www.w3.org/2000/svg"
        aria-hidden = "true"
        viewBox="0 0 24 24"
    >
        <template v-if="calcType==='scientific'">
            <g fill="none" fill-rule="evenodd"><rect fill="#212B36" x="6" y="3" width="13" height="18" rx="1"/><path fill="#DDE3E9" d="M7 9h2v2H7z"/><path fill="#F49342" d="M13 9h2v2h-2zM16 9h2v2h-2z"/><path fill="#DDE3E9" d="M10 9h2v2h-2zM7 12h2v2H7zM7 15h2v2H7z"/><path fill="#F49342" d="M13 12h2v2h-2zM16 12h2v2h-2z"/><path fill="#DDE3E9" d="M10 12h2v2h-2z"/><path fill="#2DCF77" d="M13 15h2v2h-2zM13 18h2v2h-2z"/><path fill="#DDE3E9" d="M10 15h2v2h-2zM10 18h2v2h-2zM7 18h2v2H8a1 1 0 01-1-1v-1z"/><path d="M16 15h2v4a1 1 0 01-1 1h-1v-5z" fill="#2DCF77"/><path d="M8 4h9a1 1 0 011 1v3H7V5a1 1 0 011-1z" fill="#FFF"/></g>
        </template>
        <template v-else-if="calcType==='graphing'">
            {{calcType}}
        </template>
        <template v-else-if="calcType==='basic'">
            <p>basic calculator</p>
        </template>
        <template v-else>
            <p>{{calcType}}</p>
        </template>
    </svg>
</template>

<script>
export default {
  name: 'CalcIcons',
  props: ['calcType', 'alt']
};
</script>

<style>

.sronly {
  position: absolute;
  left: -10000px;
  width: 1px;
  height: 1px;
  overflow: hidden;
}
</style>
