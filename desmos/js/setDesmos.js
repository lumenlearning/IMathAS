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
	step.setAttribute("onkeydown", "javascript: if(event.keyCode == 9) showSteps('"+parent+"', this)");
    step.setAttribute("draggable", false);

    // Create a <span> wrapper for the drag button
    var buttonDragWrapper = document.createElement("span");
    buttonDragWrapper.classList.add("js-drag-trigger", "move-trigger");
	buttonDragWrapper.setAttribute("aria-discribedby", 'step-directions');

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
	
	var draggableList = document.getElementById("step_list");
	var listDescription = draggableList.dataset.description;
	addDnDAttributes(step, listDescription);
    
	draggableList.appendChild(step);

	var textareaWrapper = document.createElement("div");
	textareaWrapper.id = "step_text_" + numsteps;
	textareaWrapper.className = 'step-item-display-'+numsteps;

	var textarea = document.createElement("textarea");
	textarea.name = "step_text["+numsteps+"]";
	textarea.className = "step-item editor";

	textareaWrapper.appendChild(textarea);

	document.getElementById("step_items").appendChild(textareaWrapper);

	numsteps++;
	initeditor("selector","textarea");
	showSteps(parent, step);
	setupDnD();
}

function confirmDelete(event){
	event.preventDefault();
	var itemNum = $(this).parent().attr("data-num");
	
	$.get("../desmos/views/ConfirmDesmosDelete.php", function(data){
		ohmModal.open({
			content: data,
			height: "auto",
			width: "50%"
		});
		
		$(".js-ohm-modal").focus(); 
		
		//add event listeners once modal is on page
		$(".js-ohm-modal").on("click", ".js-confirm-delete", removeStep);
		$(".js-ohm-modal").on("click", ".js-cancel-modal", ohmModal.close);

		// pass id of target element to delete button 
		$(".js-confirm-delete").data("num", itemNum);
	});	
}

function removeStep(event){
	var itemNum = $(".js-confirm-delete").data("num");
	var desmosItem = $(".step-item-display-" + itemNum);
	var listItem  = $(".js-step-list").find("[data-num='" + itemNum + "']"); 

	desmosItem.remove(); 
	listItem.remove(); 
	ohmModal.close();

	if($("#step_list li").length === 0){
		addStep();
	} else if($("#step_list li").length === 1){
		var trigger = document.querySelector(".js-drag-trigger");
		reorderList.init(trigger);
	}

	showSteps('desmos_edit_container', document.getElementById("step_list").children[0]);
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
	lastTarget: null,
	currentTarget: null,
	originalPosition: null,
	currentPosition: null,
	objTrigger: null,
	init: function(objNode) {
		var trigger = objNode.querySelector("button");
		reorderList.listItems = document.querySelectorAll("#step_list [draggable]");
		var listLength = reorderList.listItems.length;
		if (listLength > 1) {
			reorderList.setListeners(objNode);
			for (var i = 0; i < listLength; i++) {
				trigger.removeAttribute("disabled");
			}
		} else {
			trigger.setAttribute("disabled", true);
			reorderList.removeListeners(objNode);
		}
	},
	setListeners: function(objNode) {
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
	removeListeners: function(objNode) {
		var trigger = objNode.querySelector("button");
		objNode.onmousedown = null;
		objNode.parentNode.ondragstart = null;
		objNode.parentNode.ondragover = null;
		objNode.parentNode.ondragleave = null;
		objNode.parentNode.ondragend = null;
		objNode.onmouseup = null;
		objNode.parentNode.ondrop = null;
		objNode.onkeydown = null;
		trigger.onfocus = null;
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
		reorderList.currentTarget = objEvent.target.closest(".step-li");
		objEvent.preventDefault(); // prevent default to allow drop
		reorderList.currentTarget.classList.add("is-target");
		if (index(reorderList.currentTarget) == 1) {
			// this class only ever gets applied to the first item to create a
			// visible indicator for the top of the list
			reorderList.currentTarget.classList.add("is-over");
		}
		if (reorderList.originalPosition > index(reorderList.currentTarget)) {
			target = index(reorderList.currentTarget) + 1;
		} else {
			target = index(reorderList.currentTarget);
		}
		reorderList.update("You have moved the item to position " + target + ".");
	},
	dragLeave: function(objEvent) {
		reorderList.currentTarget.classList.remove("is-target");
		if (index(reorderList.currentTarget) !== 1 && reorderList.lastTarget !== null) {
			// we need the conditional b/c dragging to the top of the list often triggers
			// the dragLeave, so this class would never get applied in the first place
			reorderList.lastTarget.classList.remove("is-over");
		}
		reorderList.lastTarget = reorderList.currentTarget;
		reorderList.currentTarget = null;
		if ((index(reorderList.currentTarget) == -1) && index(reorderList.lastTarget) == 1) {
			reorderList.update("You have moved the item to position 1.");
		}
	},
	dragEnd: function(objEvent) {
		reorderList.objCurrent.setAttribute("aria-grabbed", false);
		reorderList.objCurrent.setAttribute("aria-selected", false);
		var num = reorderList.objCurrent.getAttribute("data-num");
		var relatedContent = document.getElementById("step_text_" + num);
		// remove selected styles from an element if its content isn't currently showing
		if (relatedContent.style.display === 'none') {
			reorderList.objCurrent.classList.remove("is-selected");
		}
		// handle attempts to move an item to the top of the list
		if ((index(reorderList.currentTarget) == -1) && index(reorderList.lastTarget) == 1) {
			reorderList.objParent.removeChild(reorderList.objCurrent);
			reorderList.objParent.insertBefore(
				reorderList.objCurrent,
				reorderList.lastTarget
			);
			reorderList.drop();
		}
	},
	dragDrop: function(objEvent) {
		objEvent.preventDefault(); // prevent default action (open as link for some elements)
		reorderList.currentTarget.classList.remove("is-target", "is-over");
		reorderList.objCurrent.classList.remove("is-selected");
		if (
			reorderList.currentTarget.parentNode.id == "step_list" ||
			reorderList.currentTarget.id == "step_list"
		) {
			// move dragged elem to the selected drop target
			reorderList.objParent.removeChild(reorderList.objCurrent);
			reorderList.objParent.insertBefore(
				reorderList.objCurrent,
				reorderList.currentTarget.nextSibling
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
		showSteps("desmos_edit_container", reorderList.objCurrent);
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
			listItems[i].classList.remove("is-target", "is-over");
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
$(".js-add").on("click", addStep);
$(".js-step-list").on("click", ".js-delete", confirmDelete);


