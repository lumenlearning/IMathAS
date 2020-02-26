const importInput = document.getElementById("import");
importInput.addEventListener("input", toggleSubmit);
function toggleSubmit() {
    const submitButton = document.getElementById("desmos_form_submit_button");
    let inputValue = importInput.value;
    if (inputValue != '') {
        submitButton.removeAttribute("disabled");
    } else {
        submitButton.setAttribute("disabled", true);
    }
}