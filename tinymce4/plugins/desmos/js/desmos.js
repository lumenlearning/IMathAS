var desmosDialog = {
    width: 300,
    height: 200,
    desmosjson: "",
    isnew: null,
    calculator: null,

    init : function() {
        var f = document.forms[0];

        // Get the selected contents as text and place it in the input
        this.width = top.tinymce.activeEditor.windowManager.getParams().width;
        this.height = top.tinymce.activeEditor.windowManager.getParams().height;
        this.isnew = top.tinymce.activeEditor.windowManager.getParams().isnew;
        this.desmosjson = top.tinymce.activeEditor.windowManager.getParams().desmosjson;
        document.getElementById("editdesmos").setAttribute("data-json",this.desmosjson);
        this.loadDesmos();
    },

    insert : function() {
        var ed = top.tinymce.activeEditor;
        // Insert the contents from the input into the document
        this.desmosjson = JSON.stringify(this.calculator.getState());
        console.log(desmosjson);
        if (this.isnew) {
            figure = '<figure class="editdesmos" style="width: 600px; height: 400px;" data-json=\''+desmosjson+'\'></figure>';
            ed.execCommand('mceInsertContent', false, figure);
        } else {
            el = ed.selection.getNode();
            ed.dom.setAttrib(el,"data-json",this.desmosjson);

        }
        top.tinymce.activeEditor.windowManager.close();
    },

    loadDesmos : function() {
        //loop through editdesmos class items
        var elt = document.getElementById("editdesmos");

        desmosjson = elt.getAttribute("data-json");
        this.calculator = Desmos.GraphingCalculator(elt);
        this.calculator.setState(desmosjson);
    }
};