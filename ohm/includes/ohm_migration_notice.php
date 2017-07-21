<?php

$fall2017GroupIds = array(
	10 => 'Mercy College',
	277 => 'Fulton Montgomery CC',
	485 => 'Tallahassee CC',
	571 => 'Northern Virginia Community College',
);

$ohmGroupIds = array(
	3 => 'Santiago Canyon College',
	4 => 'Tompkins Cortland CC',
	9 => 'Santa Ana College',
	29 => 'El Paso CC',
	77 => 'Erie CC',
	170 => 'San Jacinto College',
	220 => 'Cerritos CC',
	238 => 'Tidewater CC',
	338 => 'Broward College',
	361 => 'Salt Lake Community College',
	376 => 'Utah Valley University',
	378 => 'Wytheville Community College',
	421 => 'Wiley College',
	658 => 'Oakwood University',
	684 => 'Virginia Highlands CC',
	730 => 'New River Community College',
	796 => 'Florida Memorial Univ',
	990 => 'Ivy Tech Community College',
	995 => 'Reynolds Community College',
	1012 => 'John Tyler Community College',
	1063 => 'Piedmont Virginia Community College',
	1121 => 'Schoolcraft College',
	2258 => 'The University of Mississippi',
	1450 => 'Herkimer College',
	1820 => 'Austin Community College',
);

// Notice for teachers who should be using OHM now.
// Rights at 20 == teacher
$showingohmnotice = false;
if (key_exists($groupid, $ohmGroupIds) && $myrights >= 20 && empty($_COOKIE['ohmmigrationnoticehide'])) {
	$showingohmnotice = true;
	$spaceIdx = strrpos($userfullname, " ");
	$firstname = $spaceIdx ? substr($userfullname, 0, $spaceIdx) : $userfullname;
	?>
    <div style="border: 1px solid #ff0018; border-radius: 5px; padding: 5px 10px 5px 10px; margin: 5px 0px 5px 0px; background: #ffe5ec">
    	<div style="float: right"><a href="#" onclick="dismissohmnotice(this); return false;" class="small">[Dismiss]</a></div>
        <p>
            ATTENTION:
        </p>
        <p>
			<?php echo $ohmGroupIds[$groupid]; ?>
            has migrated to Lumen OHM, the Lumen supported version of MyOpenMath. To access your
            course(s), you should log in at
            <a href="https://ohm.lumenlearning.com/">https://ohm.lumenlearning.com/</a> using your
            MyOpenMath username and password. If you or your students continue logging in at MyOpenMath.com, your
            work and your students' data will not be saved in the proper system.
        </p>
    </div>
	<?php
}

// Notice for teachers who should be using OHM for fall of 2017.
// Rights at 20 == teacher
if (key_exists($groupid, $fall2017GroupIds) && $myrights >= 20 && empty($_COOKIE['ohmmigrationnoticehide'])) {
	$showingohmnotice = true;
	$spaceIdx = strrpos($userfullname, " ");
	$firstname = $spaceIdx ? substr($userfullname, 0, $spaceIdx) : $userfullname;
	?>
    <div style="border: 1px solid #ff0018; border-radius: 5px; padding: 5px 10px 5px 10px; margin: 5px 0px 5px 0px; background: #ffe5ec">
    	<div style="float: right"><a href="#" onclick="dismissohmnotice(this); return false;" class="small">[Dismiss]</a></div>
        <p>
            ATTENTION:
        </p>
        <p>
			<?php echo $fall2017GroupIds[$groupid]; ?>
            will be migrating to Lumen OHM, the Lumen supported version of MyOpenMath, for
            <b>Fall 2017 courses</b>. If you are building courses for the fall, log in at
            <a href="https://ohm.lumenlearning.com/">https://ohm.lumenlearning.com/</a>
            using your MyOpenMath username and password. If you continue logging in at
            MyOpenMath.com, your work and your students’ data will not be saved in the
            proper system.
        </p>
        <p>
            <b>Summer 2017</b> courses can still be run and accessed in MyOpenMath.com by
            both students and faculty.
        </p>
    </div>
	<?php
}
if ($showingohmnotice) {
	?>
	<script type="text/javascript">
	function dismissohmnotice(el) {
		$(el).parent().parent().slideUp();
		document.cookie = "ohmmigrationnoticehide=1";
	}
	</script>
	<?php
}

