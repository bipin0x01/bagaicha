// Pops alert when load more button is clicked
function productsloader() {
  alert("We don't have any more products yet, but we are working on it!");
}

const loadmore = document.getElementById("more-products");
loadmore.addEventListener("click", productsloader);

// form validation

function sendContact() {
  // Get the form data
  var name = document.getElementById("name");
  var email = document.getElementById("email");
  var subject = document.getElementById("subject");
  var message = document.getElementById("message");
  // Form validation
  if (
    name.value == "" ||
    subject.value == "" ||
    email.value == "" ||
    message.value == ""
  ) {
    alert("Please Fill All the fields!"); // Display an alert box
    return false;
  } else {
    return true;
  }
}
