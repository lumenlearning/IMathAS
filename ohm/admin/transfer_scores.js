$(document).ready(function () {
    if (0 !== totalConflicts) {
        console.log(totalConflicts + ' grade conflict(s).')
        $('#conflictCount').text(totalConflicts);
        $('#noConflicts').hide();
        $('#hasConflicts').show();
    }

    if (0 !== totalExceptions) {
        console.log(totalExceptions + ' exception(s) for source user.')
        $('#exceptionCount').text(totalExceptions);
        $('#noExceptions').hide();
        $('#hasExceptions').show();
    }
});