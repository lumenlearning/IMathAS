<h1><?php echo $action; ?> OHM Banner</h1>

<form method="post" action="?action=save">
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <div>
        <input type="checkbox" id="is_enabled" name="is_enabled" value="1"
            <?php echo ($isEnabled ? 'checked' : ''); ?>/>
        <label for="is_enabled">Enabled? (will be displayed between the specified start/end times)</label>
    </div>
    <div>
        <input type="checkbox" id="is_dismissible" name="is_dismissible" value="1"
               <?php echo ($isDismissible ? 'checked' : ''); ?>/>
        <label for="is_dismissible">Dismissible? (user can permanently dismiss this banner)</label>
    </div>
    <div>
        <input type="checkbox" id="has_start_at" name="has_start_at" value="1"
            <?php echo ($hasStartAt ? 'checked' : ''); ?>/>
        <label for="start_at">Start At</label>
        <span id="start_at">
            <input id="sdate" name="sdate" size="10" value="<?php echo $startDate; ?>"
                   onClick="displayDatePicker('sdate', this); return false"/>
            <input id="stime" name="stime" size="10" value="<?php echo $startTime; ?>"/>
        </span>
    </div>
    <div>
        <input type="checkbox" id="has_end_at" name="has_end_at" value="1"
            <?php echo ($hasEndAt ? 'checked' : ''); ?>/>
        <label for="end_at">End At</label>
        <span id="end_at">
            <input id="edate" name="edate" size="10" value="<?php echo $endDate; ?>"
                   onClick="displayDatePicker('edate', this); return false"/>
            <input id="etime" name="etime" size="10" value="<?php echo $endTime; ?>"/>
        </span>
    </div>
    <br/>
    <div>
        <label for="description">Banner description (for admin use only; this is not user-facing)</label><br/>
        <input id="description" name="description" size="60" value="<?php echo $description; ?>" required/>
    </div>
    <br/>
    <div>
        <input type="checkbox" id="display_teacher" name="display_teacher" value="1"
            <?php echo ($displayTeacher ? 'checked' : ''); ?>/>
        <label for="display_teacher">Display teacher banner?</label>
    </div>
    <div>
        <label for="teacher_title">Teacher banner title</label>
        <input id="teacher_title" name="teacher_title" size="60" value="<?php echo $teacherTitle; ?>"/>
    </div>
    <div>
        <label for="teacher_content">Teacher banner content</label><br/>
        <textarea id="teacher_content" name="teacher_content" rows="10" cols="80"
                  style="resize: both;"><?php echo $teacherContent; ?></textarea>
    </div>
    <br/>
    <div>
        <input type="checkbox" id="display_student" name="display_student" value="1"
            <?php echo ($displayStudent ? 'checked' : ''); ?>/>
        <label for="display_student">Display student banner?</label>
    </div>
    <div>
        <label for="student_title">Student banner title</label>
        <input id="student_title" name="student_title" size="60" value="<?php echo $studentTitle; ?>"/>
    </div>
    <div>
        <label for="student_content">Student banner content</label><br/>
        <textarea id="student_content" name="student_content" rows="10" cols="80"
                  style="resize: both;"><?php echo $studentContent; ?></textarea>
    </div>
    <div>
        <input type="submit" value="<?php echo $action; ?>"/>
    </div>
</form>
