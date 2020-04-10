<link rel="stylesheet" href="views/banner/edit_banner.css" type="text/css"/>
<script src="views/banner/edit_banner.js"></script>


<h1><?php echo $action; ?> OHM Banner</h1>

<div class="lux-component edit-banner-form">
    <form method="post" action="?action=save" class="lux-form">
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>

        <div class="banner-properties">
            <div>
                <input type="checkbox" id="is-enabled" name="is-enabled" value="1"
                    <?php echo ($isEnabled ? 'checked' : ''); ?>/>
                <label for="is-enabled">Enabled? (will be displayed between the specified start/end times)</label>
            </div>
            <div>
                <input type="checkbox" id="is-dismissible" name="is-dismissible" value="1"
                    <?php echo ($isDismissible ? 'checked' : ''); ?>/>
                <label for="is-dismissible">Dismissible? (user can permanently dismiss this banner)</label>
            </div>
        </div>

        <div class="banner-timestamps">
            <div class="u-margin-top">
                <input type="checkbox" id="has-start-at" name="has-start-at" value="1"
                    <?php echo ($hasStartAt ? 'checked' : ''); ?>/>
                <label for="start-at">Start At</label>
                <div class="banner-timestamp-entry">
                    <input id="sdate" class="form-input form-input--fw" name="sdate"
                           value="<?php echo $startDate; ?>"
                           onClick="displayDatePicker('sdate', this); return false;"
                           <?php echo ($hasStartAt) ? 'required' : 'disabled'; ?>/>
                    <input id="stime" class="form-input form-input--fw" name="stime"
                           value="<?php echo $startTime; ?>"
                           <?php echo ($hasStartAt) ? 'required' : 'disabled'; ?>/>
                </div>
            </div>
            <div>
                <input type="checkbox" id="has-end-at" name="has-end-at" value="1"
                    <?php echo ($hasEndAt ? 'checked' : ''); ?>/>
                <label for="end-at">End At</label>
                <div class="banner-timestamp-entry">
                    <input id="edate" class="form-input form-input--fw" name="edate"
                           value="<?php echo $endDate; ?>"
                           onClick="displayDatePicker('edate', this); return false;"
                           <?php echo ($hasEndAt) ? 'required' : 'disabled'; ?>/>
                    <input id="etime" class="form-input form-input--fw" name="etime"
                           value="<?php echo $endTime; ?>"
                           <?php echo ($hasEndAt) ? 'required' : 'disabled'; ?>/>
                </div>
            </div>
        </div>


        <div class="banner-description u-margin-top">
            <label for="description">Short banner description (for admin use only; this is not user-facing)</label>
            <input id="description" class="form-input form-input--fw" name="description" size="60"
                   value="<?php echo $description; ?>" required/>
        </div>


        <div class="teacher-banner">
            <div class="u-margin-top">
                <input type="checkbox" id="display-teacher" name="display-teacher" value="1"
                    <?php echo ($displayTeacher ? 'checked' : ''); ?>/>
                <label for="display-teacher">Display teacher banner?</label>
            </div>
            <div class="u-margin-top-xs">
                <label for="teacher-title">Teacher banner title</label>
                <input id="teacher-title" class="form-input form-input--fw" name="teacher-title"
                       size="60" value="<?php echo $teacherTitle; ?>"/>
            </div>
            <div class="u-margin-top-xs">
                <label for="teacher-content">Teacher banner content</label>
                <textarea id="teacher-content" class="form-input form-input--fw" name="teacher-content"
                          rows="10" cols="80"><?php echo $teacherContent; ?></textarea>
            </div>
        </div>


        <div class="student-banner">
            <div class="u-margin-top">
                <input type="checkbox" id="display-student" name="display-student" value="1"
                    <?php echo ($displayStudent ? 'checked' : ''); ?>/>
                <label for="display-student">Display student banner?</label>
            </div>
            <div class="u-margin-top-xs">
                <label for="student-title">Student banner title</label>
                <input id="student-title" class="form-input form-input--fw" name="student-title"
                       size="60" value="<?php echo $studentTitle; ?>"/>
            </div>
            <div class="u-margin-top-xs">
                <label for="student-content">Student banner content</label>
                <textarea id="student-content" class="form-input form-input--fw" name="student-content"
                          rows="10" cols="80"><?php echo $studentContent; ?></textarea>
            </div>
        </div>


        <button type="submit" class="button u-margin-top"><?php echo $action; ?></button>
    </form>
</div>
