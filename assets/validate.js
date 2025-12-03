$(document).ready(function () {

  function validateField(input) {
    let field = $(input);
    let value = field.val().trim();
    let errorSpan = $("#" + field.attr("name") + "Error");

    let validationType = field.data("validation") || "";
    let minLength = field.data("min") || 0;
    let maxLength = field.data("max") || 9999;
    let filesize = field.data("filesize") || 0;

    let errorMessage = "";

    if (validationType !== "") {

      // REQUIRED
      if (validationType.includes("required") && value === "") {
        if (field.attr("name") === "email") {
          errorMessage = "Email is required.";
        } else if (field.attr("name") === "password") {
          errorMessage = "Password is required.";
        } else {
          errorMessage = "This field is required.";
        }
      }

      // EMAIL
      if (!errorMessage && validationType.includes("email")) {
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(value)) {
          errorMessage = "Please enter a valid email address.";
        }
      }

      // STRONG PASSWORD
      if (!errorMessage &&
        validationType.includes("strongPassword") &&
        !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w]).{8,}$/.test(value)
      ) {
        errorMessage =
          "Password must contain uppercase, lowercase, number, special character & be at least 8 characters.";
      }

      // CONFIRM PASSWORD
      if (!errorMessage && validationType.includes("confirmPassword")) {
        let password = $("#" + field.data("password-id")).val().trim();
        if (value !== password) {
          errorMessage = "Passwords do not match.";
        }
      }

      // TERMS CHECKBOX
      if (!errorMessage &&
        validationType.includes("terms") &&
        !field.is(":checked")
      ) {
        errorMessage = "You must agree to the Terms & Conditions.";
      }

      // ALPHABET ONLY
      if (!errorMessage &&
        validationType.includes("alpha") &&
        !/^[A-Za-z\s]+$/.test(value)
      ) {
        errorMessage = "Only letters are allowed.";
      }

      // NUMERIC
      if (!errorMessage &&
        validationType.includes("numeric") &&
        !/^\d+$/.test(value)
      ) {
        errorMessage = "Only numbers are allowed.";
      }

      // MIN LENGTH
      if (!errorMessage &&
        validationType.includes("min") &&
        value.length < minLength
      ) {
        errorMessage = `Must be at least ${minLength} characters.`;
      }

      // MAX LENGTH
      if (!errorMessage &&
        validationType.includes("max") &&
        value.length > maxLength
      ) {
        errorMessage = `Must be less than ${maxLength} characters.`;
      }

      // FILE VALIDATION
      if (validationType.includes("file")) {
        if (field[0].files.length === 0) {
          if (validationType.includes("required")) {
            errorMessage = "Please upload a file.";
          }
        } else {
          let file = field[0].files[0];
          let fileName = file.name;
          let fileSizeKB = file.size / 1024;

          if (!/\.(jpg|jpeg|png)$/i.test(fileName)) {
            errorMessage = "Only JPG, JPEG, or PNG files are allowed.";
          } else if (
            validationType.includes("filesize") &&
            fileSizeKB > filesize
          ) {
            errorMessage = `File size must be less than ${filesize} KB.`;
          }
        }
      }
    }

    // Select dropdown
    if (
      !errorMessage &&
      field.is("select") &&
      validationType.includes("required") &&
      (value === "" || field.find("option:selected").index() === 0)
    ) {
      errorMessage = "Please select an option.";
    }

    // -------- DISPLAY ERROR --------
    if (errorMessage) {
      errorSpan.text(errorMessage).show();
      field.addClass("is-invalid").removeClass("is-valid");
    } else {
      errorSpan.text("").hide();
      field.removeClass("is-invalid").addClass("is-valid");
    }
  }

  // Live validation
  $("input, textarea, select").on("input change", function () {
    validateField(this);
  });

  // On submit
  $("form").on("submit", function (e) {
    let isValid = true;

    $(this)
      .find("input, textarea, select")
      .each(function () {
        validateField(this);
        let errorSpan = $("#" + $(this).attr("name") + "Error");
        if (errorSpan.text().trim() !== "") {
          isValid = false;
        }
      });

    if (!isValid) {
      e.preventDefault();
    }
  });
});
