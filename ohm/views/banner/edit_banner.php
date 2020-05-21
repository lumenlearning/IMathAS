<link rel="stylesheet" href="views/banner/edit_banner.css" type="text/css"/>
<script src="views/banner/edit_banner.js"></script>


<h1><?php echo $action; ?> Notification</h1>

<div class="lux-component edit-banner-form">
    <form method="post" action="?action=save" class="lux-form">
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>


        <label for="description">Description</label>
        <input id="description" class="form-input form-input--fw" name="description"
               value="<?php echo $description; ?>" required/>


        <div class="banner-timestamps u-margin-top-sm">
            <label for="start-at">Start date and time</label>
            <div class="banner-timestamp-entry" id="start-at">
                <input id="sdate" class="form-input has-icon icon--suffix icon--calendar date-input"
                       name="sdate" value="<?php echo $startDateTime; ?>"
                       <?php echo ($startImmediately) ? 'disabled' : 'required'; ?>/>
                <input type="checkbox" id="start-immediately"
                       name="start-immediately" value="1"
                    <?php echo ($startImmediately ? 'checked' : ''); ?>/>
                <label class="start-immediately" for="start-immediately">
                    Start immediately
                </label>
            </div>
        </div>
        <div class="banner-timestamps u-margin-top-sm">
            <label for="end-at">End date and time</label>
            <div class="banner-timestamp-entry">
                <input id="edate" class="form-input has-icon icon--suffix icon--calendar date-input"
                       name="edate" value="<?php echo $endDateTime; ?>"
                       <?php echo ($neverEnding) ? 'disabled' : 'required'; ?>/>
                <input type="checkbox" id="never-ending" name="never-ending" value="1"
                    <?php echo ($neverEnding ? 'checked' : ''); ?>/>
                <label class="never-ending" for="never-ending">None</label>
            </div>
        </div>


        <div class="teacher-banner u-margin-top-sm">
            <div class="u-margin-top-xs">
                <label for="teacher-title">Teacher Title</label>
                <input id="teacher-title" class="form-input form-input--fw" name="teacher-title"
                       value="<?php echo $teacherTitle; ?>"/>
            </div>
            <div class="u-margin-top-xs">
                <label for="teacher-content">Teacher Message</label>
                <textarea id="teacher-content" class="form-input form-input--fw" name="teacher-content"
                          rows="10"><?php echo $teacherContent; ?></textarea>
            </div>
        </div>


        <div class="student-banner u-margin-top-sm">
            <div class="u-margin-top-xs">
                <label for="student-title">Student Title</label>
                <input id="student-title" class="form-input form-input--fw" name="student-title"
                       value="<?php echo $studentTitle; ?>"/>
            </div>
            <div class="u-margin-top-xs">
                <label for="student-content">Student Message</label>
                <textarea id="student-content" class="form-input form-input--fw" name="student-content"
                          rows="10"><?php echo $studentContent; ?></textarea>
            </div>
        </div>


        <div class="u-margin-top-sm">
            <p class="banner-settings-title">Settings</p>
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
            <div class="u-margin-top-xs">
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


        <div class="form-action-buttons u-margin-vertical">
            <button type="submit" class="button button--primary"><?php
                echo ('modify' == strtolower($action)) ? 'Save' : $action; ?></button>
            <button id="js-cancel-button" class="button">Cancel</button>
        </div>
    </form>
</div>
