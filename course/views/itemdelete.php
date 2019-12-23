<div class=breadcrumb><?php echo $curBreadcrumb ?></div>
<h2><?php echo $item->name; ?></h2>
Are you SURE you want to delete this link item?
<form method="POST" action="itemdelete.php?type=<?php echo $item->typename; ?>&cid=<?php echo $item->courseid; ?>&block=<?php echo $item->block; ?>&id=<?php echo $item->typeid ?>">
    <p>
        <button type=submit name="remove" value="really">Yes, Delete</button>
        <input type=button value="Nevermind" class="secondarybtn" onClick="window.location='course.php?cid=<?php echo $item->courseid; ?>'">
    </p>
</form>