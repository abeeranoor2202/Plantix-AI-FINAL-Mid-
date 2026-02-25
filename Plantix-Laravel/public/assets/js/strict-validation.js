/**
 * Strict Form Validation
 * Enforces validation on all forms and displays proper error messages.
 */

(function () {
  "use strict";

  // Expose validation logic
  window.StrictValidation = {
    validateForm: validateForm,
    validateField: validateField,
  };

  document.addEventListener("DOMContentLoaded", function () {
    // Attach capture listener to handle all form submissions
    document.addEventListener(
      "submit",
      function (event) {
        const form = event.target;
        // Disable default browser validation UI
        form.setAttribute("novalidate", "true");

        if (!validateForm(form)) {
          event.preventDefault();
          event.stopPropagation();
          event.stopImmediatePropagation(); // Stop other handlers
          return false;
        }
      },
      true
    ); // Capture phase to intervene before other handlers

    // Real-time validation on input
    document.addEventListener(
      "input",
      function (event) {
        const input = event.target;
        if (input.classList && input.classList.contains("is-invalid")) {
          validateField(input);
        }
      },
      true
    );

    // Also validate on blur to show errors immediately after leaving field
    document.addEventListener(
      "focusout",
      function (event) {
        const input = event.target;
        // Only validate if it is a form control
        if (
          input &&
          (input.tagName === "INPUT" ||
            input.tagName === "TEXTAREA" ||
            input.tagName === "SELECT")
        ) {
          validateField(input);
        }
      },
      true
    );
  });

  function validateForm(form) {
    let isValid = true;
    // Validate all inputs, selects, and textareas
    const inputs = form.querySelectorAll("input, select, textarea");

    // Focus on the first invalid field
    let firstInvalid = null;
    let errorMessages = [];
    // Keep track of radio groups we've validated so we don't duplicate messages
    const processedRadioGroups = new Set();

    inputs.forEach((input) => {
      // Skip non-control nodes
      if (!input.type) return;

      // If this is a radio input and we already validated its group, skip
      if (
        input.type === "radio" &&
        input.name &&
        processedRadioGroups.has(input.name)
      )
        return;

      const fieldResult = validateField(input);

      // Mark radio group as processed so we validate it once
      if (input.type === "radio" && input.name)
        processedRadioGroups.add(input.name);

      if (!fieldResult.valid) {
        isValid = false;
        if (!firstInvalid) firstInvalid = input;
        if (fieldResult.message) {
          errorMessages.push(fieldResult.message);
        }
      }
    });

    if (!isValid) {
      // Show alert box as requested, matching the style of Terms check
      if (errorMessages.length > 0) {
        // Show the first error only to mimic the singular popup style.
        const msg = errorMessages[0];
        if (window.Dialog && window.Dialog.alert) {
          Dialog.alert({
            title: "Validation Error",
            message: msg,
          });
        } else {
          alert(msg);
        }
      }
      if (firstInvalid) {
        try {
          firstInvalid.focus();
        } catch (e) {}
      }
    }

    // All done, return status
    return isValid;
  }

  function validateField(input) {
    // Skip buttons/hidden inputs
    if (!input.type) return { valid: true };
    if (
      input.type === "hidden" ||
      input.type === "submit" ||
      input.type === "button"
    )
      return { valid: true };

    // Skip disabled fields
    if (input.disabled) return { valid: true };

    let error = null;
    const label = getLabel(input);

    // Determine the canonical value for the field depending on type
    let value = "";
    if (input.type === "file") {
      value = input.files && input.files.length ? "has-file" : "";
    } else if (input.type === "checkbox") {
      // For checkbox we consider whether it's checked
      value = input.checked ? input.value || "on" : "";
    } else if (input.type === "radio") {
      // For radio we consider whether this specific radio is checked (group handling occurs in required checks below)
      value = input.checked ? input.value || "on" : "";
    } else {
      value = (input.value || "").toString().trim();
    }

    // Required check - handle checkbox/radio/file specially
    if (input.hasAttribute("required")) {
      if (input.type === "checkbox" && !input.checked) {
        // support natural language 'Accept terms' style labels
        error = `Please ${label.toLowerCase()} to proceed.`;
      } else if (input.type === "radio") {
        // check the group inside the form (or document) to ensure one is checked
        const root = input.form || document;
        // Use a safe selector for names that might contain special characters
        const radios = root.querySelectorAll(
          'input[type="radio"][name="' + cssEscape(input.name) + '"]'
        );
        const anyChecked = Array.from(radios).some((r) => r.checked);
        if (!anyChecked) {
          error = `Please select ${label.toLowerCase()} to proceed.`;
        }
      } else if (input.type === "file") {
        if (!value) error = `Please provide ${label.toLowerCase()} to proceed.`;
      } else if (!value) {
        error = `Please enter ${label.toLowerCase()} to proceed.`;
      }
    }

    // Email check
    if (!error && input.type === "email" && value && !isValidEmail(value)) {
      error = "Please enter a valid email address to proceed.";
    }

    // Minlength check
    if (
      !error &&
      input.getAttribute &&
      input.getAttribute("minlength") &&
      value.length < parseInt(input.getAttribute("minlength"))
    ) {
      error = `Please enter at least ${input.getAttribute(
        "minlength"
      )} characters for ${label.toLowerCase()} to proceed.`;
    }

    // Pattern check
    if (
      !error &&
      input.getAttribute &&
      input.getAttribute("pattern") &&
      value &&
      !new RegExp("^" + input.getAttribute("pattern") + "$").test(value)
    ) {
      error =
        input.getAttribute("title") ||
        `Please enter a valid ${label.toLowerCase()} to proceed.`;
    }

    // Custom validations
    if (!error && input.id === "signinPassword" && value.length < 8) {
      error = "Please enter a password of at least 8 characters to proceed.";
    }

    if (error) {
      showError(input, error);
      return { valid: false, message: error };
    } else {
      clearError(input);
      return { valid: true };
    }
  }

  // Helper to get a human-readable name for the field
  function getLabel(input) {
    if (!input) return "Field";
    // Prefer an explicit developer-provided friendly label first
    const explicit =
      input.getAttribute &&
      (input.getAttribute("data-label") ||
        input.getAttribute("data-field-name") ||
        input.getAttribute("aria-label"));
    if (explicit) return String(explicit).replace("*", "").trim();
    // Try to find a label tag
    const id = input.id;
    if (id) {
      const labelEl = document.querySelector(`label[for="${id}"]`);
      if (labelEl) return labelEl.textContent.replace("*", "").trim();
    }
    // Fallback to placeholder or name
    return (
      (input.getAttribute &&
        (input.getAttribute("placeholder") || input.name)) ||
      "Field"
    )
      .replace("*", "")
      .trim();
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function showError(input, message) {
    try {
      if (input.classList) input.classList.add("is-invalid");

      // Look for existing error message container
      let feedback =
        input.parentNode && input.parentNode.querySelector(".invalid-feedback");

      // If not found, check next sibling (sometimes error span is outside label wrapper)
      if (!feedback) {
        feedback = input.nextElementSibling;
        if (
          !feedback ||
          !feedback.classList ||
          !feedback.classList.contains("invalid-feedback")
        ) {
          feedback = null;
        }
      }

      // Create if missing
      if (!feedback && input.parentNode) {
        feedback = document.createElement("div");
        feedback.className = "invalid-feedback";
        feedback.style.display = "block"; // Ensure it's visible even without BS classes sometimes
        feedback.style.color = "#dc3545";
        feedback.style.fontSize = "0.875em";
        feedback.style.marginTop = "0.25rem";
        input.parentNode.appendChild(feedback);
      }

      if (feedback) feedback.textContent = message;

      // Force red border with !important to override other styles
      input.style.setProperty("border-color", "#dc3545", "important");
      input.style.setProperty("border-width", "1px", "important");
      input.style.setProperty("border-style", "solid", "important");
    } catch (e) {
      // best-effort UI update — don't break behavior
      console.warn("showError failed", e);
    }
  }

  function clearError(input) {
    try {
      if (input.classList) input.classList.remove("is-invalid");
      input.style.removeProperty("border-color");
      input.style.removeProperty("border-width");
      input.style.removeProperty("border-style");

      const feedback =
        input.parentNode && input.parentNode.querySelector(".invalid-feedback");
      if (feedback) feedback.remove();

      // Also check next sibling just in case
      const next = input.nextElementSibling;
      if (
        next &&
        next.classList &&
        next.classList.contains("invalid-feedback")
      ) {
        next.remove();
      }
    } catch (e) {
      console.warn("clearError failed", e);
    }
  }

  // Very small css escape for quoted selectors to keep querySelector working for odd names
  function cssEscape(str) {
    if (!str) return "";
    return String(str).replace(/"/g, '\\"').replace(/\\/g, "\\\\");
  }
})();
