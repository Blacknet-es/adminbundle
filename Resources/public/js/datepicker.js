$(document).ready(function(){

    $('.datepicker').pickadate({
        formatSubmit: 'yyyy-mm-dd',
        format: 'yyyy-mm-dd',
        selectMonths: true,
        selectYears: 200,
        firstDay: 1,
    });

    $('.timepicker').pickatime({
        formatSubmit: 'H:i',
        format: 'H:i'
    });
})