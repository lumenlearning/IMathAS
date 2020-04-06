<template>
    <svg
        aria-hidden="true"
        role="img"
        viewBox="0 0 24 24"
        xmlns = "http://www.w3.org/2000/svg"
    >
        <template v-if="calcType==='scientific'">
            <g fill="none" fill-rule="evenodd">
                <rect fill="#212B36" x="6" y="3" width="13" height="18" rx="1"/>
                <path fill="#DDE3E9" d="M7 9h2v2H7z"/><path fill="#F49342" d="M13 9h2v2h-2zM16 9h2v2h-2z"/><path fill="#DDE3E9" d="M10 9h2v2h-2zM7 12h2v2H7zM7 15h2v2H7z"/><path fill="#F49342" d="M13 12h2v2h-2zM16 12h2v2h-2z"/><path fill="#DDE3E9" d="M10 12h2v2h-2z"/><path fill="#2DCF77" d="M13 15h2v2h-2zM13 18h2v2h-2z"/><path fill="#DDE3E9" d="M10 15h2v2h-2zM10 18h2v2h-2zM7 18h2v2H8a1 1 0 01-1-1v-1z"/><path d="M16 15h2v4a1 1 0 01-1 1h-1v-5z" fill="#2DCF77"/><path d="M8 4h9a1 1 0 011 1v3H7V5a1 1 0 011-1z" fill="#FFF"/>
            </g>
        </template>
        <template v-else-if="calcType==='graphing'">
            <g fill="none" fill-rule="evenodd">
                <rect fill="#212B36" x="4" y="3" width="17" height="18" rx="1"/>
                <rect fill="#FFF" x="5" y="4" width="15" height="16" rx="1"/>
                <path fill="#D8D8D8" d="M12 4h1v16h-1z"/><path fill="#D8D8D8" d="M5 12v-1h15v1z"/><path stroke="#50B83C" stroke-linecap="round" d="M6.5 18.5l12-13"/><path stroke="#F49342" stroke-linecap="round" d="M6.5 18.5l12-5"/>
             </g>
        </template>
        <template v-else-if="calcType==='basic'">
            <g fill="none" fill-rule="evenodd">
                <rect fill="#212B36" x="4" y="3" width="17" height="18" rx="1"/>
                <path fill="#DDE3E9" d="M5 9h3v3H5zM9 9h3v3H9zM5 13h3v3H5zM9 13h3v3H9zM9 17h3v3H9zM13 9h3v3h-3z"/><path fill="#F49342" d="M17 9h3v3h-3z"/><path fill="#DDE3E9" d="M13 13h3v3h-3zM13 17h3v3h-3zM5 17h3v3H6a1 1 0 01-1-1v-2z"/><path d="M17 13h3v6a1 1 0 01-1 1h-2v-7z" fill="#2DCF77"/><path d="M6 4h13a1 1 0 011 1v3H5V5a1 1 0 011-1z" fill="#FFF"/>
            </g>
        </template>   
    </svg>
</template>

<script>
export default {
  name: 'CalcIcons',
  props: ['calcType']
};
</script>
