$(document).ready(function(){
    let assessVersion = $('input:radio[name=assess-version]:checked').val();
    $('.js-assessmentVersionText').text(assessVersion);

    $('.js-version-inputs').on('change', 'input', function(event){
        $('.js-assessmentVersionText').text(event.target.value);
    });
});
