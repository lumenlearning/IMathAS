function loadDesmos(){
    //loop through js-desmos class items
    var elt = document.getElementsByClassName("js-desmos");
    if (elt.length>0) {
        for (i = 0; i < elt.length; i++) {
            var calculator = Desmos.GraphingCalculator(elt[i]);
            json = elt[i].getAttribute("data-json");
            if (json!="") {
                calculator.setState(json);
            }
        }
    }
}
loadDesmos();

function showSteps(parent, num){
    var listItems = document.querySelectorAll(parent + ' .step-li');
    for (i=0; i<listItems.length; i++) {
        if (num == i) {
            listItems[i].className = "step-li is-selected";
            listItems[i].setAttribute("aria-selected", true);
            stepItems[i].style.display = "block";
        } else {
            listItems[i].className = "step-li";
            listItems[i].setAttribute("aria-selected", false);
            stepItems[i].style.display = "none";
        }
    }
}

function showThis(el) {
    var itemNum = el.dataset.num;
    var stepItem = document.querySelectorAll('[name=step_text_' + itemNum + ']')[0];
    el.className = "step-li is-selected";
    el.setAttribute("aria-selected", true);
    stepItem.style.display = "block";
}

function addStep(){
    var num = document.querySelectorAll('.step-li').length;

    // Create a <li> node
    var step = document.createElement("li");
    step.className = "step-li selected";
    step.dataset.num = num;
    step.setAttribute("onclick", "showSteps('#desmos_edit_container', "+num+")");

    // Create an <input> element, set its type and name attributes
    var input = document.createElement("input");
    input.type = "text";
    input.name = "step_title["+num+"]";

    //Create a <button> element
    var button = document.createElement("button");
    button.type = "button";
    button.classList.add("js-delete");
    button.setAttribute("aria-label", "Delete this item.");
    button.innerHTML = '<svg aria-hidden="true"><use xlink:href="#lux-icon-x"></use></svg>';
    
    // Append the text to <li>
    step.appendChild(input);
    step.appendChild(button);
    
    document.getElementById("step_list").appendChild(step);

    var textarea = document.createElement("textarea");
    textarea.name = "step_text_"+num;
    textarea.className = "step-item editor";
    document.getElementById("step_items").appendChild(textarea);

    showSteps("#desmos_edit_container", num);
}

function removeStep(event){
    if(confirm("Permanently delete this item?")){
        var parent = this.parentElement;
        var itemNum = parent.dataset.num;
        var relatedItems = document.querySelectorAll('[name=step' + itemNum + '], [name=step_text_' + itemNum + ']');
        parent.remove();
        for (let i = 0; i < relatedItems.length; i++) {
            relatedItems[i].remove();
        }
        showThis(document.getElementById("step_list").children[0]);
    }
}

function handleStudentViewNav(event){
    var listItems = document.querySelectorAll('.step-li');
    var listItem;
    var stepIndex; 

    document.querySelector('.prev').disabled = false;
    document.querySelector('.next').disabled = false;

    function handleNext(){
        for (let i = 0; i < listItems.length; i++) {
            if (listItems[i].classList.contains('is-selected')) {
                listItem = listItems[i];
                stepIndex = i+1;
            }
        }
        
        if(stepIndex > listItems.length - 2){
            event.target.disabled = true;
            document.querySelector('.prev').disabled = false;
        } 
    
        listItem.classList.remove('is-selected');
        listItem.nextSibling.classList.add('is-selected');
    }

    function handlePrev(){
        for (let i = 0; i < listItems.length; i++) {
            if (listItems[i].classList.contains('is-selected')) {
                listItem = listItems[i];
                stepIndex = i-1;
            }
        }
    
        if(stepIndex === 0){
            event.target.disabled = true;
            document.querySelector('.next').disabled = false;
        }
        listItem.classList.remove('select');
        listItem.previousSibling.classList.add('is-selected');
    }

    event.target.classList.contains("next") ? 
    handleNext() : handlePrev();

    showSteps(stepIndex);
}

// Disable "Previous" and "Next" buttons when first and last list items selected with spacebar 
function syncNavButtons(event){
    var listItems = document.querySelectorAll('.step-li');

    $('.prev').prop('disabled', false);
    $('.next').prop('disabled', false);

    if(event.code === "Space" || event.code === "Tab"){
        if($(this).index() === 0){
            $('.prev').prop('disabled', true);
        } else if($(this).index() === listItems.length - 1){
            $('.next').prop('disabled', true);
        }
    }
}

$('.js-desmos-nav').on("click", "button", handleStudentViewNav);
$('.js-step-list li').on("keydown", syncNavButtons);
$('.js-add').on("click", addStep);
$('.js-step-list').on("click", ".js-delete", removeStep);
