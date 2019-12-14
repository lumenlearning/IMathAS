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

function showSteps(parent, el){
	//showThis(el);
	var listItems = document.getElementById(parent).getElementsByClassName('step-li');
	for (var i = 0; i < listItems.length; i++) {
		var num = listItems[i].getAttribute("data-num");
		var stepItem = document.getElementById(parent).getElementsByClassName("step-item-display-" + num)[0];
		if (!(listItems[i] == el)) {
			listItems[i].classList.remove("is-selected");
			listItems[i].setAttribute("aria-selected", false);
			stepItem.style.display = "none";
		} else {
			listItems[i].classList.add("is-selected");
			listItems[i].setAttribute("aria-selected", true);
			stepItem.style.display = "block";
		}
	}
}

function addStep(){
	var parent = "desmos_edit_container";
    // Create a <li> node
    var step = document.createElement("li");
    step.className = "step-li";
    step.dataset.num = numsteps;
    step.setAttribute("onclick", "showSteps('"+parent+"', this)");
    step.setAttribute("draggable", false);

    // Create a <span> wrapper for the drag button
    var buttonDragWrapper = document.createElement("span");
    buttonDragWrapper.classList.add("js-drag-trigger", "move-trigger");

    // Create a drag <button> element
    var buttonDrag = document.createElement("button");
    buttonDrag.type = "button";
    buttonDrag.classList.add("u-button-reset");
    buttonDrag.setAttribute("aria-label", "Move this item.");
    buttonDrag.innerHTML = '<svg aria-hidden="true"><use xlink:href="#lux-icon-drag"></use></svg>';

    // Create a <label> and <input> set
    var label = document.createElement("label");
    label.setAttribute("for", "step_title["+numsteps+"]");
    label.classList.add("u-sr-only");
    var input = document.createElement("input");
    input.type = "text";
	input.name = "step_title["+numsteps+"]";
	input.setAttribute("maxlength", "100");

    //Create a delete <button> element
    var buttonDelete = document.createElement("button");
    buttonDelete.type = "button";
    buttonDelete.classList.add("js-delete", "delete-trigger");
    buttonDelete.setAttribute("aria-label", "Delete this item.");
    buttonDelete.innerHTML = '<svg aria-hidden="true"><use xlink:href="#lux-icon-x"></use></svg>';


    // Wrap the drag <button> in the <span> wrapper;
    buttonDragWrapper.appendChild(buttonDrag);
    // Append the new elements to <li>
    step.appendChild(buttonDragWrapper);
    step.appendChild(label);
    step.appendChild(input);
    step.appendChild(buttonDelete);
    
	document.getElementById("step_list").appendChild(step);

	// Add textarea
	// echo  "<div id=\"step_text_$i\">";
	// echo "<textarea name=\"step_text[$i]\" class=\"step-item\" editor";

	var textareaWrapper = document.createElement("div");
	textareaWrapper.id = "step_text_" + numsteps;
	textareaWrapper.className = 'step-item-display-'+numsteps;

	var textarea = document.createElement("textarea");
	textarea.name = "step_text["+numsteps+"]";
	textarea.className = "step-item editor";


	textareaWrapper.appendChild(textarea);

	//document.getElementById(parent).getElementsByClassName("step-items")[0];
	document.getElementById("step_items").appendChild(textareaWrapper);

	//var newItem = document.querySelectorAll("[data-num='"+num+"']")[0];

	//var draggableList = document.getElementById("step_list");
	//var listDescription = draggableList.dataset.description;


	//var num = document.getElementById('desmos_edit_container').getElementsByClassName('step-li').length;

	numsteps++;
	initeditor("selector","textarea");
	showSteps(parent, step);
}

function removeStep(event){
    if(confirm("Permanently delete this item?")){
        var parent = this.parentElement;
        var itemNum = parent.dataset.num;
        var relatedItems = document.getElementById('desmos_edit_container').getElementsByClassName("step-item-display-" + itemNum);
        parent.remove();
        for (let i = 0; i < relatedItems.length; i++) {
            relatedItems[i].remove();
		}
        showSteps('desmos_edit_container', document.getElementById("step_list").children[0]);
    }
}

// function handleStudentViewNav(event){
//     var listItems = document.querySelectorAll('.step-li');
//     var listItem;
//     var stepIndex; 

//     document.querySelector('.prev').disabled = false;
//     document.querySelector('.next').disabled = false;

//     function handleNext(){
//         for (let i = 0; i < listItems.length; i++) {
//             if (listItems[i].classList.contains('is-selected')) {
//                 listItem = listItems[i];
//                 stepIndex = i+1;
//             }
//         }
        
//         if(stepIndex > listItems.length - 2){
//             event.target.disabled = true;
//             document.querySelector('.prev').disabled = false;
//         } 
    
//         listItem.classList.remove('is-selected');
//         listItem.nextSibling.classList.add('is-selected');
//     }

//     function handlePrev(){
//         for (let i = 0; i < listItems.length; i++) {
//             if (listItems[i].classList.contains('is-selected')) {
//                 listItem = listItems[i];
//                 stepIndex = i-1;
//             }
//         }
    
//         if(stepIndex === 0){
//             event.target.disabled = true;
//             document.querySelector('.next').disabled = false;
//         }
//         listItem.classList.remove('select');
//         listItem.previousSibling.classList.add('is-selected');
//     }

//     event.target.classList.contains("next") ? 
//     handleNext() : handlePrev();

//     showSteps();
// }

// Disable "Previous" and "Next" buttons when first and last list items selected with spacebar 
// function syncNavButtons(event){
//     var listItems = document.querySelectorAll('.step-li');

//     $('.prev').prop('disabled', false);
//     $('.next').prop('disabled', false);

//     if(event.code === "Space" || event.code === "Tab"){
//         if($(this).index() === 0){
//             $('.prev').prop('disabled', true);
//         } else if($(this).index() === listItems.length - 1){
//             $('.next').prop('disabled', true);
//         }
//     }
// }

function index(el) {
	if (!el) return -1;
	var i = 0;
	do {
		i++;
	} while ((el = el.previousElementSibling));
	return i;
}

var reorderList = {
	listItems: null,
	objCurrent: null,
	objParent: null,
	originalPosition: null,
	currentPosition: null,
	objTrigger: null,
	init: function(objNode) {
		var trigger = objNode.querySelector("button");
		objNode.onmousedown = reorderList.mouseStart;
		objNode.parentNode.ondragstart = reorderList.dragStart;
		objNode.parentNode.ondragover = reorderList.dragOver;
		objNode.parentNode.ondragleave = reorderList.dragLeave;
		objNode.parentNode.ondragend = reorderList.dragEnd;
		objNode.onmouseup = reorderList.dragDrop;
		objNode.parentNode.ondrop = reorderList.dragDrop;
		objNode.onkeydown = reorderList.keyboardNav;
		trigger.onfocus = reorderList.focus;
	},
	keyboardNav: function(objEvent) {
		var key = objEvent.code;
		switch (key) {
			case "Space":
				if (!reorderList.objCurrent) {
					reorderList.objCurrent = this.parentNode;
					reorderList.objParent = reorderList.objCurrent.parentNode;
					reorderList.objTrigger = reorderList.objCurrent.querySelector(
						"button"
					);
				}
				reorderList.toggleSelect();
				break;
			case "ArrowUp":
			case "ArrowDown":
				event.preventDefault();
				reorderList.move(key);
				break;
			case "Escape":
				reorderList.cancel();
				break;
		}
	},
	focus: function() {
		if (!reorderList.objCurrent) {
			// we only want the focus action and update to happen if there isn't a currently grabbed item
			// otherwise, this would constantly override our drag-and-drop instructions
			var objFocused = this.parentNode.parentNode;
			var focusedVal = objFocused.querySelector("input[type=text]").value;
			var focusedTitle = focusedVal || "Item " + index(objFocused);
			reorderList.update(
				focusedTitle + ", draggable item. Press spacebar to lift and reorder."
			);
		}
	},
	mouseStart: function(objEvent) {
		reorderList.reset();
		reorderList.objCurrent = this.parentNode;
		reorderList.objParent = reorderList.objCurrent.parentNode;
		reorderList.objTrigger = reorderList.objCurrent.querySelector("button");
		reorderList.objParent.setAttribute("aria-dropeffect", "move");
		reorderList.objCurrent.classList.add("is-selected");
		reorderList.objCurrent.setAttribute("draggable", true);
		var dataTransfer = new DataTransfer;
		dataTransfer.setData("text", "");
		reorderList.objCurrent.dispatchEvent(new DragEvent("dragstart", { dataTransfer: dataTransfer }));
	},
	dragStart: function(objEvent) {
		objEvent.dataTransfer.setData("text", "");  // drag and drop fails on moz w/o this
		reorderList.select();
	},
	dragOver: function(objEvent) {
		var target;
		objEvent.preventDefault(); // prevent default to allow drop
		objEvent.target.classList.add("is-over");
		if (reorderList.originalPosition > index(objEvent.target)) {
			target = index(objEvent.target) + 1;
		} else {
			target = index(objEvent.target);
		}
		reorderList.update("You have moved the item to position " + target + ".");
	},
	dragLeave: function(objEvent) {
		event.target.classList.remove("is-over");
	},
	// dragEnd: function() {
	// },
	dragDrop: function(objEvent) {
		objEvent.preventDefault(); // prevent default action (open as link for some elements)
		objEvent.target.classList.remove("is-over");
		reorderList.objCurrent.classList.remove("is-selected");
		if (
			objEvent.target.parentNode.id == "step_list" ||
			objEvent.target.id == "step_list"
		) {
			// move dragged elem to the selected drop target
			reorderList.objParent.removeChild(reorderList.objCurrent);
			reorderList.objParent.insertBefore(
				reorderList.objCurrent,
				objEvent.target.nextSibling
			);
			reorderList.drop();
		}
		// ignore; item doesn't move
	},
	toggleSelect: function() {
		var grabbed = reorderList.objCurrent.getAttribute("aria-grabbed");
		if (grabbed === "false") {
			reorderList.select();
		} else {
			reorderList.drop();
		}
	},
	select: function() {
		reorderList.listItems = reorderList.objParent.children.length;
		reorderList.originalPosition = index(reorderList.objCurrent);
		reorderList.currentPosition = reorderList.originalPosition;
		reorderList.objCurrent.setAttribute("draggable", true);
		reorderList.objCurrent.setAttribute("aria-grabbed", true);
		reorderList.objCurrent.setAttribute("aria-selected", true);
		reorderList.objParent.setAttribute("aria-dropeffect", "move");
		reorderList.objTrigger.focus();
		reorderList.update(
			"You have lifted an item. It is in position " +
				reorderList.originalPosition +
				" of " +
				reorderList.listItems +
				" in the list. Use the arrow keys to move, spacebar to drop, and escape key to cancel."
		);
	},
	move: function(key) {
		if (
			reorderList.objCurrent == null ||
			reorderList.objCurrent.getAttribute("aria-grabbed") === "false"
		) {
			// ignore; this item is not currently grabbed
		} else {
			if (key === "ArrowUp") {
				if (reorderList.currentPosition > 1) {
					reorderList.currentPosition = reorderList.currentPosition - 1;
					reorderList.objParent.insertBefore(
						reorderList.objCurrent,
						reorderList.objCurrent.previousElementSibling
					);
				}
				// ignore; this item is already at the top of the list
			} else if (key === "ArrowDown") {
				if (reorderList.currentPosition < reorderList.listItems) {
					var next = reorderList.objCurrent.nextElementSibling;
					reorderList.currentPosition = reorderList.currentPosition + 1;
					reorderList.objParent.insertBefore(
						reorderList.objCurrent,
						next.nextElementSibling
					);
				}
				// ignore; this item is already at the bottom of the list
			}
			reorderList.update(
				"You have moved the item to position " + reorderList.currentPosition + "."
			);
			reorderList.objTrigger.focus();
		}
	},
	drop: function(objEvent) {
		if (reorderList.objCurrent) {
			reorderList.currentPosition = index(reorderList.objCurrent);
			if (reorderList.currentPosition === reorderList.originalPosition) {
				// nothing moved!
				reorderList.update(
					"You have dropped the item. It is in its original position."
				);
			} else {
				reorderList.update(
					"You have dropped the item. It has moved from position " +
						reorderList.originalPosition +
						" to " +
						reorderList.currentPosition +
						"."
                );
			}
			setTimeout("reorderList.reset()", 350); // this is not my fave thing, but will do in a pinch
		}
		showSteps("#desmos_edit_container", reorderList.objCurrent);
		// ignore; no item currently grabbed
	},
	cancel: function(objEvent) {
		if (reorderList.objCurrent) {
			if (reorderList.originalPosition === reorderList.currentPosition) {
				// nothing moved!
			} else if (reorderList.originalPosition < reorderList.currentPosition) {
				var targetElement = reorderList.originalPosition - 1;
				reorderList.objParent.insertBefore(
					reorderList.objCurrent,
					reorderList.objParent.children[targetElement]
				);
			} else if (reorderList.originalPosition > reorderList.currentPosition) {
				reorderList.objParent.insertBefore(
					reorderList.objCurrent,
					reorderList.objParent.children[reorderList.originalPosition]
				);
			}
			reorderList.objTrigger.focus();
			reorderList.update(
				"Movement cancelled. The item has returned to its starting position of " +
					reorderList.originalPosition +
					"."
			);
			setTimeout("reorderList.reset()", 350);
		}
	},
	update: function(message) {
		var draggableList = document.getElementById("step_list");
		var liveRegion = document.getElementById(draggableList.dataset.liveregion);
		liveRegion.innerHTML = message;
	},
	reset: function() {
		if (reorderList.objParent) {
			reorderList.objParent.removeAttribute("aria-dropeffect");
		}
		reorderList.listItems = null;
		reorderList.objCurrent = null;
		reorderList.objTrigger = null;
		reorderList.objParent = null;
		reorderList.originalPosition = null;
		reorderList.currentPosition = null;

		var listItems = document.querySelectorAll("#step_list [draggable]");
		for (var i = 0; i < listItems.length; i++) {
			listItems[i].setAttribute("aria-grabbed", false);
			listItems[i].setAttribute("aria-selected", false);
			listItems[i].setAttribute("draggable", false);
			// listItems[i].classList.remove("is-selected");
		}
	}
};

function addDnDAttributes(el, listDescription) {
	var trigger = el.querySelector(".js-drag-trigger");
	trigger.setAttribute("aria-describedby", listDescription);
	el.setAttribute("aria-grabbed", false);
	el.setAttribute("aria-selected", false);
	reorderList.init(trigger);
}

function setupDnD() {
	var draggableList = document.getElementById("step_list");
	var listDescription = draggableList.dataset.description;
	var listItems = document.querySelectorAll("#step_list [draggable]");

	for (var i = 0; i < listItems.length; i++) {
		addDnDAttributes(listItems[i], listDescription);
	}
}

setupDnD();

// $('.js-desmos-nav').on("click", "button", handleStudentViewNav);
// $('.js-step-list li').on("keydown", syncNavButtons);
$('.js-add').on("click", addStep);
$('.js-step-list').on("click", ".js-delete", removeStep);
