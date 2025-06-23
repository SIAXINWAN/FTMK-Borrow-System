<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <style>
    .ftmk-logo {
      display: block;
      width: 200px;
    }

    input {
      border: black solid 1px;
      height: 30px;
      width: 220px;
      padding: bottom 10px;
    }

    .input-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      justify-content: center;
      place-items: center;
      margin-bottom: 10px;
      padding: 30px 0;
    }

    @media (min-width: 600px) {
      .input-grid {
        grid-template-columns: repeat(2, 270px);
        gap: 20px 60px;
        place-items: unset;
      }
    }

    .input-grid input {
      width: 280px;
      box-sizing: border-box;
      height: 40px;
      font-size: 16px;
      padding-left: 10px;
    }

    .radio-row {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-bottom: 20px;
      font-size: 18px;
    }

    .radio-row label {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .radio-row input[type="radio"] {
      width: 20px;
      height: 20px;
      accent-color: #e6b800;
      margin-right: 5px;
    }

    .password-grid {
      display: flex;
      gap: 20px 60px;
      justify-content: center;
      margin-bottom: 5px;
    }

    .password-grid input {
      height: 40px;
      width: 270px;
      font-size: 16px;
      padding-left: 10px;
      box-sizing: border-box;
    }

    .password-note {
      text-align: center;
      color: #888;
      font-size: 13px;
      margin-bottom: 15px;
    }

    .submit-btn {
      display: block;
      margin: 20px auto 0 auto;
      width: 200px;
      height: 50px;
      background-color: #e6b800;
      color: #222;
      border: none;
      border-radius: 8px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
      transition: background 0.2s, transform 0.2s;
    }

    .submit-btn:hover {
      background-color: #cca300;
      transform: translateY(-2px) scale(1.04);
    }

    .link {
      text-align: center;
      margin-top: 18px;
      font-size: 15px;
      color: #444;
    }

    .link a {
      color: #e6b800;
      text-decoration: none;
      transition: color 0.2s, font-size 0.2s;
    }

    .link a:hover {
      color: #b38f00;
      text-decoration: underline;
      font-size: 1.1em;
    }

    .error-text {
      color: red;
      font-size: 13px;
      margin-top: 5px;
      height: 16px;
    }
  </style>
</head>

<body style="display: none;">
  <div id="header">
    <img src="../0images/ftmk-logo.png" alt="ftmk" class="ftmk-logo" />
  </div>
  <h2>
    <center>Create your FTMK Borrow System Account</center>
  </h2>
  <form action="users.php" method="post" class="register-form" onsubmit="return validatePasswords()">
    <div class="input-grid">
      <div style="display: flex; flex-direction: column">
        <input
          id="name"
          type="text"
          placeholder="Name:"
          name="name"
          required
          readonly />
      </div>
      <div style="display: flex; flex-direction: column">
        <input
          id="email"
          type="text"
          placeholder="Email:"
          name="email"
          required
          readonly />
        <div class="error-text" id="emailError"></div>
      </div>
      <input
        id="matricNo"
        type="text"
        placeholder="ID:"
        name="matricNo"
        required
        readonly />
      <input
        id="phone"
        type="text"
        placeholder="Phone:"
        name="phone"
        required
        readonly />
    </div>

    <div class="password-grid">
      <div style="display: flex; flex-direction: column">
        <input
          type="password"
          placeholder="Password:"
          name="password"
          required />
        <div class="error-text" id="passwordError"></div>
      </div>
    </div>

    <div class="password-grid">
      <div style="display: flex; flex-direction: column">
        <input
          type="password"
          placeholder="Confirm:"
          name="confirm"
          required />
        <div class="error-text" id="confirmError"></div>
      </div>
    </div>
    <input type="hidden" id="role" name="role" />
    <button type="submit" class="submit-btn">Create Account</button>
  </form>
  <p class="link">
    Already have account? <a href="../index.php">Sign In </a>
  </p>
  <script>
    window.onload = function() {
      const userID = prompt("Enter your User ID:");
      const ic = prompt("Enter your IC Number:");

      if (userID && ic) {
        fetch("checkDummy.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `userID=${encodeURIComponent(userID)}&ic=${encodeURIComponent(
              ic
            )}`,
          })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              document.getElementById("name").value = data.name;
              document.getElementById("email").value = data.email;
              document.getElementById("matricNo").value = data.userID;
              document.getElementById("phone").value = data.phone;
              document.getElementById("role").value = data.role;
              document.body.style.display = "block";
            } else {
              let message = "Registration is not allowed.";
              let image = "../0images/sad.png";

              if (data.message === "already_registered") {
                message = "You have already registered. Please login.";
                image = "../0images/happy.webp";
              } else if (data.message === "not_found") {
                message = "You are not recognized as an FTMK student or lecturer.";
                image = "../0images/sad.png";
              }

              document.body.innerHTML = `
      <div style="text-align: center; padding-top: 100px;">
        <img src="${image}" alt="status" width="150" />
        <h2>${message}</h2>
        <p>Redirecting to login page...</p>
      </div>
    `;
              document.body.style.display = "block";
              setTimeout(() => {
                window.location.href = "../index.php";
              }, 3000);
            }
          })


          .catch((err) => {
            alert("Server error. Please try again later.");
            window.location.href = "../index.php";
          });
      } else {
        alert("You must enter your Matric Number and IC.");
        window.location.href = "../index.php";
      }
    };

    function validatePasswords() {
      const pw = document.querySelector('input[name="password"]').value;
      const confirm = document.querySelector('input[name="confirm"]').value;
      if (pw !== confirm) {
        document.getElementById("confirmError").innerText = "Passwords do not match.";
        return false;
      }
      return true;
    }
  </script>
</body>

</html>