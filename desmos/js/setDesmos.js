//loop through js-desmos class items
var elt = document.getElementsByClassName("js-desmos");
if (elt.length>0) {
    for (i=0; i<elt.length; i++) {
        setDesmos(elt[i]);
    }
}
function setDesmos(item)
{
    id = item.getAttribute("data-id");
    $.ajax(
        {
            type: 'GET',
            url: '/../desmos/returnjson.php',
            data: {
                "id": id
            },
            success: function (res) {
                var calculator = Desmos.GraphingCalculator(item);
                calculator.setState(res);
                console.log(res);
            },
            error: function (err) {
                calculator.setState(err.responseText);
                console.log('error:', err);
            }
        }
    );
}
function showSteps(num)
{
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
function addStep()
{
    var num = document.querySelectorAll('.step-li').length;

    // Create a <li> node
    var step = document.createElement("span");
    step.className = "step-li selected";
    step.setAttribute("onclick", "showSteps("+num+")");
    // Create an <input> element, set its type and name attributes
    var input = document.createElement("input");
    input.type = "text";
    input.name = "step_title["+num+"]";
    // Append the text to <li>
    step.appendChild(input);
    document.getElementById("step_list").appendChild(step);

    var textarea = document.createElement("textarea");
    textarea.name = "step_text["+num+"]";
    textarea.className = "step-item editor";
    document.getElementById("step_items").appendChild(textarea);

    showSteps(num);
}
function removeStep()
{
    var listItems = document.querySelectorAll('.step-li');
    for (i=0; i<listItems.length; i++) {
        if (listItems[i].classList.contains('selected')) {
            listItems[i].remove();
            document.querySelectorAll(".step-item")[i].remove();
            if (document.getElementsByName("step["+i+"]").length > 0) {
                document.getElementsByName("step[" + i + "]").remove();
            }
        }
    }

    showSteps(0);
}