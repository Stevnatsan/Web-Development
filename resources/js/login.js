const role = document.getElementsByName("role");
const button = document.getElementsByName("role_button");

button.addEventListener("click", updateButton);

function updateButton(){
    if(button.value === "CUSTOMER"){
        role.value = "CUSTOMER";
    }
}
