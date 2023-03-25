// Get the login modal element
var loginModal = document.getElementById("loginModal");

// Get the close button that closes the login modal
var closeBtn = document.getElementsByClassName("close")[0];

// Get the login button
var loginBtn = document.getElementById("loginBtn");

// When the user clicks on the login button, show the login modal
function showLoginForm() {
  loginModal.style.display = "block";
}

// When the user clicks on the close button, hide the login modal
function closeModal() {
  loginModal.style.display = "none";
}

// When the user clicks outside of the login modal, hide it
window.onclick = function(event) {
  if (event.target == loginModal) {
    loginModal.style.display = "none";
  }
}

// When the user clicks on the login button in the login modal, check the credentials
function login() {
  var username = document.getElementById("username").value;
  var password = document.getElementById("password").value;
  
  // Check if the username and password are correct
  if (username == "admin" && password == "password") {
    alert("Login successful!");
    closeModal();
  } else {
    alert("Incorrect username or password.");
  }
}

function sendLoginDetails(username, password){
    const xmlhttp = new XMLHttpRequest();
  const url = "login.php?username="+username+"&password="+password;
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        if(this.responseText.trim()!=""){
          
          let response = this.responseText;
          alert(response);
    
        }
      }
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}
