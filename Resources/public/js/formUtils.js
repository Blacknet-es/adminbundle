function action_addFormElementCollection($action_button)
{
    var isValid = true;
    var widgetContainer = false;
    if (($action_button.data('widget-container') != undefined)) {
        widgetContainer = true;
    }
    var prototype_name = '__name__';
    if ($action_button.data('prototype-name') != undefined) {

        prototype_name = $action_button.data('prototype-name');
    }
    var reg = new RegExp(prototype_name, "g");

    // Si está definida la propiedad data-validate se ejecuta su funcion referenciada
    // Devolverá obligatoriamente un boleano
    if ($action_button.data('validate') != undefined) {

        var validate = $action_button.data('validate');
        isValid = window[validate]();
    }

    if (!isValid) {
        return false;
    }

    if (widgetContainer) {
        var formWidgetcontainer = $action_button.closest('.formWidgetContainer');
        var prototype = formWidgetcontainer.find('#' + $action_button.data('prototype')).html();
        var container = formWidgetcontainer.find('#' + $action_button.data('container'));
    } else {
        var prototype = $('#' + $action_button.data('prototype')).html();
        var container = $('#' + $action_button.data('container'));
    }

    //Reasignamos index y numero.
    recalcular_index(container, $action_button);

    var index = container.data('index');
    index++;
    container.data('index', index);
    //recorremos los subforms para cambiar y readactar su index.

    // Creamos el nuevo subform
    var newForm = prototype.replace(reg, index);
    container.append(newForm);
    $('#' + $(newForm).attr('id')).css('display', 'none').show(400);

    if ($action_button.data('callback') != undefined) {

        var id_element = $(newForm).attr('id');
        var callback = $action_button.data('callback');
        setTimeout(function() {
            window[callback](id_element);
        }, 1);
    }
}

function action_removeFormElementCollection($action_button)
{
    var container = $('#' + $action_button.data('container'));

    //Para la clase mas cercana
    if ($action_button.data('widget-container') != undefined) {
        var element = $action_button.closest('.'+$action_button.data('widget-container') );
    //Para atacar directamente a un id
    } else {
        var element = $('#'+ $action_button.data('id'));
    }

    element.hide(400, function() {
        $(this).remove();
        if ($action_button.data('callback') != undefined) {
            var id_element = $action_button.data('id');
            var callback = $action_button.data('callback');
            setTimeout(function() {
                window[callback](id_element);
            }, 1);
        }

        var index = container.data('index');
        index--;
        container.data('index', index);

        //Reasignamos index y numero.
        recalcular_index(container, $action_button);
    });
}

function recalcular_index(container, $action_button){
    //Reasignamos index y numero.
    cont = 0;
    container.children().each(function(){
        console.log($(this));
        $(this).data('index', cont);

        if($action_button.data('number')) {
            $(this).find($action_button.data('number')).html(cont +1);
        }
        cont++;
    });
}