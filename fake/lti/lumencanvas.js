$(function() {
    var isAdmin = false;
    var isTeacher = false;   
    for (var i = ENV.current_user_roles.length - 1; i > -1; i--) {
        switch (ENV.current_user_roles[i]) {
            case 'admin':
                isAdmin = true;

                break;
            case 'teacher':
                isTeacher = true;               
                break;
        }
        if (isTeacher || isAdmin) break;  // Exit the loop early, to avoid checking the remaining roles
    }
    if (!isAdmin && !isTeacher) {
	    var btnlen = INST.editorButtons.length;
	    for (var i=btnlen-1;i>-1;i--) {
		    if (INST.editorButtons[i].name=="CC License Generator" || INST.editorButtons[i].name=="Select an outcome") {
			    INST.editorButtons.splice(i,1);
		    }
	    }
    }
    if (!isAdmin && !isTeacher) $('a[title="CC License Generator"]').parent().hide();
    if (!isAdmin && !isTeacher) $('a[title="Select an outcome"]').parent().hide();
    
});    
