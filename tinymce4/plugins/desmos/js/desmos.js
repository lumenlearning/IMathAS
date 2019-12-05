function drawDesmos() {
    //loop through js-desmos class items
    var elt = document.getElementsByClassName("js-desmos");

    if (elt.length > 0) {
        for (i = 0; i < elt.length; i++) {
            alert(elt[i]);
            json = elt[i].getAttribute("data-json");
            var calculator = Desmos.GraphingCalculator(elt[i]);
            calculator.setState(json);
        }
    }
}