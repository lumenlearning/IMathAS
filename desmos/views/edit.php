<script type="text/javascript">
    var curlibs = '<?php Sanitize::encodeStringForJavascript($item->libs); ?>';
</script>

<style>
    .form-main {
        display: flex;
    }

    .form-left {
        padding-right: 3em;
    }
    .form .button {
        padding: .5em 1em; 
    }

    .form input {
        padding: .25em .5em; 
    }

    .pill {
        padding: 4px 24px; 
        background-color: #dfe3e8;
        border-radius: 10px;
    }

    .datepicker {
        align-items: center;
        width: 8em;
    }

    .datepicker > span {
        display: flex;
    }

    .datepicker input {
        border-top-right-radius: 0; 
        border-bottom-right-radius: 0;
        margin-top: .25em;
    }
    .datepicker .cal-button {
        border-radius: 0;
        margin-top: .25em;
        padding: 0 .2rem;
        padding-top: .2rem;
        border: 1px solid #c4cdd5;
        border-top-right-radius: .25rem;
        border-bottom-right-radius: .25rem;
        border-left: none;
    }
</style>

<div class=breadcrumb><?php echo $curBreadcrumb  ?></div>

<h1 class="-small-type">
    <img src="../ohm/img/desmos.png" alt=""/> 
    <?php echo $pagetitle ?>    
</h1>

<form class="form" enctype="multipart/form-data" method=post action="<?php echo $page_formActionTag ?>">
    <div class="form-main">
        <div class="form-left">
            <div class="controls">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo str_replace('"','&quot;',$item->title);?>" required />
            </div>
            <div class="controls">
                <label for="summary">Summary:</label>
                <input type="text" id="summary" name="summary" value="<?php echo \Sanitize::encodeStringForDisplay($item->summary, true);?>" />
            </div>
        </div>
        <div class="form-right">
            <div class="controls">
                <div class="datepicker" onClick="displayDatePicker('sdate', this); return false">
                    <label for="sdate">Start Date:</label>
                    <span>
                        <input id="sdate" type="text" name="sdate" value="<?php echo $sdate;?>"/>
                        <button type="button" class="cal-button">
                            <svg 
                                width="20" 
                                height="20" 
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                aria-hidden="true"
                            >
                                <g fill-rule="nonzero" fill="none">
                                    <path fill="#FFF" d="M0 3h20v17H0z"/>
                                    <path d="M5 0v1.83h10.048V0h1.113v1.83H20V20H0V1.83h3.887V0H5zm13.81 7.037H1.19v11.945h17.62V7.037zM5 15v2H3v-2h2zm4 0v2H7v-2h2zm4 0v2h-2v-2h2zm-8-3v2H3v-2h2zm4 0v2H7v-2h2zm4 0v2h-2v-2h2zm4 0v2h-2v-2h2zM5 9v2H3V9h2zm4 0v2H7V9h2zm4 0v2h-2V9h2zm4 0v2h-2V9h2zM3.839 3.006H1.19V5.86h17.62V3.006h-2.65v1.11h-1.113v-1.11H4.952v1.11H3.839v-1.11z" fill="#212B36"/>
                                </g>
                            </svg>
                        </button>
                    </span>
                </div>
            </div>
            <div class="controls">
                <div class="datepicker">
                    <label for="edate">End Date:</label>
                    <span>
                        <input id="edate" type="text" name="edate" value="<?php echo $edate;?>"/>
                        <button type="button" class="cal-button" onClick="displayDatePicker('edate', this, 'sdate', 'start date'); return false">
                            <svg 
                                width="20" 
                                height="20" 
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                aria-hidden="true"
                            >
                                <g fill-rule="nonzero" fill="none">
                                    <path fill="#FFF" d="M0 3h20v17H0z"/>
                                    <path d="M5 0v1.83h10.048V0h1.113v1.83H20V20H0V1.83h3.887V0H5zm13.81 7.037H1.19v11.945h17.62V7.037zM5 15v2H3v-2h2zm4 0v2H7v-2h2zm4 0v2h-2v-2h2zm-8-3v2H3v-2h2zm4 0v2H7v-2h2zm4 0v2h-2v-2h2zm4 0v2h-2v-2h2zM5 9v2H3V9h2zm4 0v2H7V9h2zm4 0v2h-2V9h2zm4 0v2h-2V9h2zM3.839 3.006H1.19V5.86h17.62V3.006h-2.65v1.11h-1.113v-1.11H4.952v1.11H3.839v-1.11z" fill="#212B36"/>
                                </g>
                            </svg>
                        </button> 
                    <span>
                </div>
            </div>
        </div>
    </div>
    <div class="libraries">
        In Libraries:
        <span class="pill" id="libnames"><?php echo implode(', ', $item->lnames); ?></span>
        <input type="hidden" name="libs" id="libs"  value="<?php echo Sanitize::encodeStringForDisplay($item->libs) ?>"/>
        <button class="button" type="button" onClick="GB_show('Library Select','libtree2.php?libtree=popup&libs='+curlibs,500,500)" >Select Libraries</button>
        <?php
        if (count($outcomes)>0) {
            echo '<span class="form pill">Associate Outcomes:</span></span class="formright">';
            writeHtmlMultiSelect('outcomes',$outcomes,$outcomenames,$gradeoutcomes,'Select an outcome...');
            echo '</span><br class="form"/>';
        }
        ?>
        <button class="button" type="submit" name="submitbtn" value="Submit"><?php echo $savetitle; ?></button>
    </div>
</form>