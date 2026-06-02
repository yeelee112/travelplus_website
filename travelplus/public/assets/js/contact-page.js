const form = document.getElementById("contactForm");
const submitButton = document.getElementById("contactSubmitBtn");

if (form && submitButton) {
  let isSubmitting = false;
  const label = submitButton.querySelector("[data-contact-submit-label]");
  const defaultText = submitButton.dataset.defaultText || "Send request";
  const loadingText = submitButton.dataset.loadingText || "Sending...";
  const recaptchaError = form.dataset.recaptchaError || "reCAPTCHA verification failed.";
  const recaptchaSiteKey = form.dataset.recaptchaSiteKey || "";

  const setSubmittingState = (submitting) => {
    isSubmitting = submitting;
    submitButton.disabled = submitting;
    submitButton.setAttribute("aria-disabled", submitting ? "true" : "false");

    if (label) {
      label.textContent = submitting ? loadingText : defaultText;
    }
  };

  form.addEventListener("submit", (event) => {
    if (isSubmitting) {
      event.preventDefault();
      return;
    }

    event.preventDefault();
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
