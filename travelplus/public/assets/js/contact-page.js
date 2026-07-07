const form = document.getElementById("contactForm");
const submitButton = document.getElementById("contactSubmitBtn");

if (form && submitButton) {
  let isSubmitting = false;
  const label = submitButton.querySelector("[data-contact-submit-label]");
  const defaultText = submitButton.dataset.defaultText || "Send request";
  const loadingText = submitButton.dataset.loadingText || "Sending...";
  const recaptchaError = form.dataset.recaptchaError || "reCAPTCHA verification failed.";
  const recaptchaSiteKey = form.dataset.recaptchaSiteKey || "";
  const nameError = form.dataset.nameError || "Please enter your full name.";
  const emailRequiredError = form.dataset.emailRequired || form.dataset.emailError || "Please enter your email address.";
  const emailInvalidError = form.dataset.emailInvalid || form.dataset.emailError || "Please enter a valid email address.";
  const phoneRequiredError = form.dataset.phoneRequired || form.dataset.phoneError || "Please enter your phone number.";
  const phoneInvalidError = form.dataset.phoneInvalid || form.dataset.phoneError || "Please enter a valid Vietnamese phone number.";
  const messageError = form.dataset.messageError || "Your message must be at least 10 characters.";
  const messageOptional = form.dataset.messageOptional === "true";
  const privacyError = form.dataset.privacyError || "Please agree to the privacy statement and terms of service.";

  const setSubmittingState = (submitting) => {
    isSubmitting = submitting;
    submitButton.disabled = submitting;
    submitButton.setAttribute("aria-disabled", submitting ? "true" : "false");

    if (label) {
      label.textContent = submitting ? loadingText : defaultText;
    }
  };

  const normalizePhone = (value) => {
    let phone = (value || "").trim().replace(/[^\d+]/g, "");

    if (phone.startsWith("+84")) {
      phone = `0${phone.slice(3)}`;
    } else if (phone.startsWith("84")) {
      phone = `0${phone.slice(2)}`;
    }

    return phone;
  };

  const isValidVietnamPhone = (value) => /^(?:0(?:2\d{8,9}|[35789]\d{8}))$/.test(normalizePhone(value));

  const getFieldContainer = (field) => field.closest("label")
    || field.closest(".form-inner")
    || field.closest(".form-inner2")
    || field.closest(".form-check")
    || field.closest(".travelplus-contact-check")
    || field.closest(".summer-lead__check");

  const clearFieldError = (field) => {
    field.classList.remove("is-invalid");
    field.removeAttribute("aria-invalid");

    const containerEl = getFieldContainer(field);
    const errorEl = containerEl ? containerEl.querySelector(".travelplus-inline-error") : null;
    if (errorEl) {
      errorEl.remove();
    }
  };

  const setFieldError = (field, message) => {
    clearFieldError(field);
    field.classList.add("is-invalid");
    field.setAttribute("aria-invalid", "true");

    const containerEl = getFieldContainer(field);
    if (!containerEl) {
      return;
    }

    const errorEl = document.createElement("small");
    errorEl.className = "travelplus-inline-error";
    errorEl.textContent = message;
    containerEl.appendChild(errorEl);
  };

  const scrollToElement = (element) => {
    if (!element) {
      return;
    }

    element.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  };

  const flashError = form.closest(".travelplus-contact-form-card, .summer-lead__form-card, .visa-lead-card, .mice-page__brief-card, .contact-form-wrap")?.querySelector(".alert-danger");
  if (flashError) {
    setTimeout(() => scrollToElement(flashError), 120);
  }

  const inputs = Array.from(form.querySelectorAll("input, textarea, select"));
  inputs.forEach((field) => {
    field.addEventListener("input", () => clearFieldError(field));
    field.addEventListener("change", () => clearFieldError(field));
  });

  form.addEventListener("submit", (event) => {
    if (isSubmitting) {
      event.preventDefault();
      return;
    }

    event.preventDefault();

    let firstInvalidField = null;

    const rememberInvalid = (field, message) => {
      setFieldError(field, message);
      if (!firstInvalidField) {
        firstInvalidField = field;
      }
    };

    const nameField = form.querySelector('[name="name"]');
    const phoneField = form.querySelector('[name="phone"]');
    const emailField = form.querySelector('[name="email"]');
    const messageField = form.querySelector('[name="message"]');
    const privacyField = form.querySelector('[name="privacy_agree"]');

    [nameField, phoneField, emailField, messageField, privacyField].forEach((field) => {
      if (field) {
        clearFieldError(field);
      }
    });

    if (nameField && !nameField.value.trim()) {
      rememberInvalid(nameField, nameError);
    }

    if (emailField) {
      const email = emailField.value.trim();
      const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      if (!email) {
        rememberInvalid(emailField, emailRequiredError);
      } else if (!isValidEmail) {
        rememberInvalid(emailField, emailInvalidError);
      }
    }

    if (phoneField) {
      const phone = phoneField.value.trim();
      if (!phone) {
        rememberInvalid(phoneField, phoneRequiredError);
      } else if (!isValidVietnamPhone(phone)) {
        rememberInvalid(phoneField, phoneInvalidError);
      } else {
        phoneField.value = normalizePhone(phone);
      }
    }

    if (messageField && (!messageOptional || messageField.value.trim() !== "") && messageField.value.trim().length < 10) {
      rememberInvalid(messageField, messageError);
    }

    if (privacyField && !privacyField.checked) {
      rememberInvalid(privacyField, privacyError);
    }

    if (firstInvalidField) {
      setSubmittingState(false);
      scrollToElement(getFieldContainer(firstInvalidField) || firstInvalidField);
      firstInvalidField.focus({ preventScroll: true });
      return;
    }

    setSubmittingState(true);

    if (!recaptchaSiteKey || typeof window.grecaptcha === "undefined") {
      setSubmittingState(false);
      window.alert(recaptchaError);
      return;
    }

    window.grecaptcha.ready(() => {
      window.grecaptcha
        .execute(recaptchaSiteKey, { action: "contact" })
        .then((token) => {
          const tokenField = document.getElementById("recaptcha_token");
          if (tokenField) {
            tokenField.value = token;
          }
          form.submit();
        })
        .catch(() => {
          setSubmittingState(false);
          window.alert(recaptchaError);
        });
    });
  });
}
