<link rel="stylesheet" href="views/banner/edit_banner.css" type="text/css"/>
<script src="views/banner/edit_banner.js"></script>


<h1><?php echo $action; ?> Notification</h1>

<div class="lux-component edit-banner-form">
    <form method="post" action="?action=save" class="lux-form">
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>


        <div class="banner-description">
            <label for="description">Description</label>
            <input id="description" class="form-input form-input--fw" name="description" size="60"
                   value="<?php echo $description; ?>" required/>
        </div>


        <div class="banner-timestamps">
            <label for="start-at">Start date and time</label>
            <div class="banner-timestamp-entry" id="start-at">
                <input id="sdate" class="form-input form-input--fw has-icon icon--suffix icon--calendar date-input"
                       name="sdate" value="<?php echo $startDate; ?>"
                       onClick="displayDatePicker('sdate', this); return false;"
                       <?php echo ($startImmediately) ? 'disabled' : 'required'; ?>/>
                <input id="stime" class="form-input form-input--fw time-input"
                       name="stime" value="<?php echo $startTime; ?>"
                       <?php echo ($startImmediately) ? 'disabled' : 'required'; ?>/>
                <input type="checkbox" id="start-immediately" class="start-immediately-checkbox"
                       name="start-immediately" value="1"
                    <?php echo ($startImmediately ? 'checked' : ''); ?>/>
                <label class="start-immediately" for="start-immediately">Start immediately</label>
            </div>
        </div>
        <div class="banner-timestamps">
            <label for="end-at">End date and time</label>
            <div class="banner-timestamp-entry">
                <input id="edate" class="form-input form-input--fw has-icon icon--suffix icon--calendar"
                       name="edate" value="<?php echo $endDate; ?>"
                       onClick="displayDatePicker('edate', this); return false;"
                       <?php echo ($neverEnding) ? 'disabled' : 'required'; ?>/>
                <input id="etime" class="form-input form-input--fw"
                       name="etime" value="<?php echo $endTime; ?>"
                       <?php echo ($neverEnding) ? 'disabled' : 'required'; ?>/>
                <input type="checkbox" id="never-ending" name="never-ending" value="1"
                    <?php echo ($neverEnding ? 'checked' : ''); ?>/>
                <label class="never-ending" for="never-ending">None</label>
            </div>
        </div>


        <div class="teacher-banner">
            <div class="u-margin-top-xs">
                <label for="teacher-title">Teacher Title</label>
                <input id="teacher-title" class="form-input form-input--fw" name="teacher-title"
                       size="60" value="<?php echo $teacherTitle; ?>"/>
            </div>
            <div class="u-margin-top-xs">
                <label for="teacher-content">Teacher Message</label>
                <textarea id="teacher-content" class="form-input form-input--fw" name="teacher-content"
                          rows="10" cols="80"><?php echo $teacherContent; ?></textarea>
            </div>
        </div>


        <div class="student-banner">
            <div class="u-margin-top-xs">
                <label for="student-title">Student Title</label>
                <input id="student-title" class="form-input form-input--fw" name="student-title"
                       size="60" value="<?php echo $studentTitle; ?>"/>
            </div>
            <div class="u-margin-top-xs">
                <label for="student-content">Student Message</label>
                <textarea id="student-content" class="form-input form-input--fw" name="student-content"
                          rows="10" cols="80"><?php echo $studentContent; ?></textarea>
            </div>
        </div>


        <div class="banner-settings" id="banner-settings">
            <label for="banner-settings" class="banner-settings">Settings</label>
            <div>
                <input type="checkbox" id="is-enabled" name="is-enabled" value="1"
                    <?php echo ($isEnabled ? 'checked' : ''); ?>/>
                <label for="is-enabled">Enabled</label>
            </div>
            <div>
                <input type="checkbox" id="is-dismissible" name="is-dismissible" value="1"
                    <?php echo ($isDismissible ? 'checked' : ''); ?>/>
                <label for="is-dismissible">Dismissible by users</label>
            </div>
            <div class="display-toggles">
                <span class="display-toggles-label">Message displays for:</span>
                <span class="display-toggles-checkboxes">
                    <input type="checkbox" id="display-teacher" name="display-teacher" value="1"
                        <?php echo ($displayTeacher ? 'checked' : ''); ?>/>
                    <label for="display-teacher">Teacher</label>
                    <input type="checkbox" id="display-student" name="display-student" value="1"
                        <?php echo ($displayStudent ? 'checked' : ''); ?>/>
                    <label for="display-student">Student</label>
                </span>
            </div>
        </div>


        <div class="form-action-buttons">
            <button type="submit" class="button u-margin-top"><?php
                echo ('modify' == strtolower($action)) ? 'Save' : $action; ?></button>
    </form>
    <form method="GET" action="?">
        <button type="submit" class="button u-martin-top">Cancel</button>
    </form>
        </div>
</div>
