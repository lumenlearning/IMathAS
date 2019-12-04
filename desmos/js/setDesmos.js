function loadDesmos(){
    //loop through js-desmos class items
    var elt = document.getElementsByClassName("js-desmos");
    if (elt.length>0) {
        for (i = 0; i < elt.length; i++) {
            json = elt[i].getAttribute("data-json");
            var calculator = Desmos.GraphingCalculator(elt[i]);
            calculator.setState(json);
        }
    }
}
loadDesmos();

function showSteps(num){
    var stepItems = document.querySelectorAll(".step-item");
    var listItems = document.querySelectorAll('.step-li');
    for (i=0; i<listItems.length; i++) {
        if (num == i) {
            listItems[i].className = "step-li selected";
            stepItems[i].style.display = "block";
        } else {
            listItems[i].className = "step-li";
            stepItems[i].style.display = "none";
        }
    }
}

function addStep(){
    var num = document.querySelectorAll('.step-li').length;

    // Create a <li> node
    var step = document.createElement("li");
    step.className = "step-li selected";
    step.setAttribute("onclick", "showSteps("+num+")");

    // Create an <input> element, set its type and name attributes
    var input = document.createElement("input");
    input.type = "text";
    input.name = "step_title["+num+"]";

    //Create a <button> element
    var button = document.createElement("button");
    button.type = "button";
    button.setAttribute("onclick", "removeStep("+num+")");
    button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 16 16"><defs><path d="M9.885 8l5.724-5.724a1.332 1.332 0 000-1.885 1.332 1.332 0 00-1.885 0L8 6.115 2.276.39a1.332 1.332 0 00-1.885 0 1.332 1.332 0 000 1.885L6.115 8 .39 13.724A1.332 1.332 0 001.334 16c.34 0 .682-.13.942-.39L8 9.884l5.724 5.724a1.33 1.33 0 001.885 0 1.332 1.332 0 000-1.885L9.885 8z" id="a"/></defs><use fill="#637381" xlink:href="#a" fill-rule="evenodd"/></svg>';
    
    // Append the text to <li>

    step.appendChild(input);
    step.appendChild(button);
    
    document.getElementById("step_list").appendChild(step);

    var textarea = document.createElement("textarea");
    textarea.name = "step_text["+num+"]";
    textarea.className = "step-item editor";
    document.getElementById("step_items").appendChild(textarea);

    showSteps(num);
}

function removeStep(num){
    document.querySelectorAll('.step-li')[num].remove();
    document.querySelectorAll(".step-item")[num].remove();
    document.getElementsByName("step[" + num + "]").remove();
    showSteps(0);
}

