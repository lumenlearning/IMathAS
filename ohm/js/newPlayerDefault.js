$(document).ready(function(){
    let assessVersion = $('input:radio[name=assess-version]:checked').val();
    $('.js-assessmentVersionText').text(assessVersion);

    $('.js-version-inputs').on('change', 'input', function(event){
        $('.js-assessmentVersionText').text(event.target.value);
    });

    $('.js-change-default-link').on('click', function(event){
        console.log('button clicked');
        //enable
        $('#versionNew').removeAttr("disabled");
        //hide
        $('.js-change-default-link').hide();
        //show
        $('#versionOld').show();
    });
});
