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
        // Insert the contents from the input into the document
        this.desmosjson = JSON.stringify(this.calculator.getState());
        console.log(desmosjson);
        if (this.isnew) {
            this.addDesmos(this.desmosjson);
        } else {
            var ed = top.tinymce.activeEditor;
            el = ed.selection.getNode();
            ed.dom.setAttrib(el,"data-json",this.desmosjson);

        }
        top.tinymce.activeEditor.windowManager.close();
    },

    import : function() {
        var theResponse = false;
        $.ajax({
            type: "GET",
            url: document.getElementById("import").value,
            success: function (data) {
                console.log(JSON.stringify(data.state).replace(/'/g, "&#8217;"));
                theResponse = JSON.stringify(data.state).replace(/'/g, "&#8217;");
            },
            error: function () {
                alert('Unable to import Desmos Graph. Please Try Again');
            },
            async: false,
            dataType: "json"
        });
        if (theResponse != false) {
            this.addDesmos(theResponse);
            top.tinymce.activeEditor.windowManager.close();
        }
    },

    addDesmos : function(json) {
        var ed = top.tinymce.activeEditor;
        ed.execCommand(
            'mceInsertContent',
            false,
            '<figure class="js-desmos desmos-fig" data-json=\''+json+'\'></figure>'
        );
        elt = ed.dom.doc.getElementsByClassName("js-desmos");
        if (elt.length>0) {
            for (i = 0; i < elt.length; i++) {
                elt[i].setAttribute("onClick", "top.tinymce.activeEditor.execCommand('mceDesmos')");
            }
        }
    },

    loadDesmos : function() {
        //loop through editdesmos class items
        var elt = document.getElementById("editdesmos");

        desmosjson = elt.getAttribute("data-json");
        var options = {
            administerSecretFolders: true,
            imageUploadCallback: function (file, cb) {
                // https://www.desmos.com/api/v1.4/docs/index.html#document-image-uploads
                Desmos.imageFileToDataURL(file, function (err, dataURL) {
                    $.ajax({
                        type: "POST",
                        url: '/desmos/upload-image.php',
                        dataType: 'json',
                        data: {
                            imageData: dataURL
                        },
                        success: function (data, textStatus, jqXHR) {
                            cb(null, data.imageUrl);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            cb(true);
                        },
                    });
                });
            }
        };
        this.calculator = Desmos.GraphingCalculator(elt, options);
        this.calculator.setState(desmosjson);
    }
};
function importToggle() {
    if(document.getElementById("import").value==="") {
        document.getElementById('desmos_form_submit_button').disabled = true;
    } else {
        document.getElementById('desmos_form_submit_button').disabled = false;
    }
}