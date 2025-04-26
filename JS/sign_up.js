function main(){
    let form = document.querySelector("form");
    validateSignUp(form);
}


const validateSignUp = (form) =>{
    let fullName = document.querySelector("#fullName");
    let phoneNumber = document.querySelector("#phoneNumber");
    let email = document.querySelector("#email");
    let password = document.querySelector("#password");
    let address = document.querySelector("#address");
    let formIsNotValid;
    form.addEventListener("submit", (event) => {
        formIsNotValid = false;
        // validate address
        if(!(/^[0-9]+[A-Za-z0-9\s,.'-]{3,}$/.test(address.value))){
            address.nextElementSibling.classList.remove("warning-hidden");
            formIsNotValid = true;
        }
        else{
            address.nextElementSibling.classList.add("warning-hidden");
        }

        // validate email
        if(!(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email.value))){
            email.nextElementSibling.classList.remove("warning-hidden");
            formIsNotValid = true;
        }
        else{
            email.nextElementSibling.classList.add("warning-hidden");
        }

        // validate full name
        if(!(/^[A-Za-z]+([ '-][A-Za-z]+)*$/.test(fullName.value))){
            fullName.nextElementSibling.classList.remove("warning-hidden");
            formIsNotValid = true;
        }
        else{
            fullName.nextElementSibling.classList.add("warning-hidden");
        }

        //validate phone number
        if(!(/^\d{3}-\d{3}-\d{4}$/.test(phoneNumber.value))){
            phoneNumber.nextElementSibling.classList.remove("warning-hidden");
            formIsNotValid = true;
        }
        else{
            phoneNumber.nextElementSibling.classList.add("warning-hidden");
        }

        //validate password - simplified to just check length
        if(password.value.length < 3){
            password.nextElementSibling.classList.remove("warning-hidden");
            formIsNotValid = true;
        }
        else{
            password.nextElementSibling.classList.add("warning-hidden");
        }

        if(formIsNotValid){
            console.log(formIsNotValid);
            event.preventDefault();
        }
    });
}

window.addEventListener("load", main);
