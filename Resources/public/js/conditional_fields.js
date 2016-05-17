$(document).ready(function() {
    var showAndHide = function() {
        var selectedInput = $(this).find("input[type='radio']:checked");
        var isRadio = selectedInput.length > 0;
        var activated = 0;
        if(!isRadio) {
            activated = $(this).is(':checked');
        } else {
            activated = selectedInput.val() == 1;
        }
        var target = $($(this).data("target"));
        target.each(function() {
            var act = activated;
            if($(this).hasClass("inverted")) {
                act = !activated;
            }
            if(act) {
                $(this).removeClass("hidden");
            } else {
                $(this).addClass("hidden");
            }
        });
    };

    var activator = $(".activator");
    activator.each(showAndHide);
    activator.change(showAndHide);
});