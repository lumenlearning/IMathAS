<div class="course-banner" data-banner-id="<?php echo $bannerId; ?>">
    <header class="course-banner-header">
        <h2>OHM Updates</h2>
        <button
            aria-label="Close this banner."
            class="u-button-reset course-banner-close-button"
            id="js-dismiss-banner"
            type="button">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12" height="12" role="img" aria-hidden="true">
                <defs>
                    <path d="M7.414 6l4.293-4.293A.999.999 0 1 0 10.293.293L6 4.586 1.707.293A.999.999 0 1 0 .293 1.707L4.586 6 .293 10.293a.999.999 0 1 0 1.414 1.414L6 7.414l4.293 4.293a.997.997 0 0 0 1.414 0 .999.999 0 0 0 0-1.414L7.414 6z" id="icon-close-a"/>
                </defs>
                <g fill="none" fill-rule="evenodd">
                    <mask id="icon-close-b" fill="#fff">
                       <use xlink:href="#icon-close-a"/>
                    </mask>
                    <use fill="#637381" xlink:href="#icon-close-a"/>
                    <g mask="url(#icon-close-b)" fill="#fff">
                       <path d="M0 0h12v12H0z"/>
                    </g>
                </g>
            </svg>
        </button>
    </header>
    <div class="course-banner-content">
        <h3>Hello, student user!</h3>
        <p>Here is an important announcement!</p>
    </div>
</div>
<script src="/ohm/views/banner/banner.js"></script>
