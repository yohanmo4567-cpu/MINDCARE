const navToggle = document.querySelector(".nav-toggle");
const navMenu = document.querySelector("#nav-menu");

if (navToggle && navMenu) {
  navToggle.addEventListener("click", () => {
    const isOpen = navToggle.getAttribute("aria-expanded") === "true";
    navToggle.setAttribute("aria-expanded", String(!isOpen));
    navMenu.classList.toggle("is-open", !isOpen);
    document.body.classList.toggle("nav-open", !isOpen);
  });

  navMenu.addEventListener("click", (event) => {
    if (event.target instanceof HTMLAnchorElement) {
      navToggle.setAttribute("aria-expanded", "false");
      navMenu.classList.remove("is-open");
      document.body.classList.remove("nav-open");
    }
  });
}

const year = document.querySelector("#year");
if (year) {
  year.textContent = String(new Date().getFullYear());
}

// ── Redes sociales: entrada progresiva al hacer scroll ──
const socialList = document.querySelector("[data-social-list]");

if (socialList) {
  const socialItems = Array.from(socialList.querySelectorAll(".social-item"));
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (prefersReducedMotion || !("IntersectionObserver" in window)) {
    socialItems.forEach((item) => item.classList.add("is-visible"));
  } else {
    socialList.classList.add("is-reveal");

    const socialObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const position = Math.max(socialItems.indexOf(entry.target), 0);
        entry.target.style.setProperty("--reveal-delay", `${position * 110}ms`);
        entry.target.classList.add("is-visible");
        socialObserver.unobserve(entry.target);
      });
    }, { threshold: 0.25 });

    socialItems.forEach((item) => socialObserver.observe(item));
  }
}

document.querySelectorAll(".auth-form").forEach((form) => {
  const message = form.querySelector(".form-message");

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const fields = Array.from(form.querySelectorAll("input"));
    let firstInvalid = null;

    fields.forEach((field) => {
      const isValid = field.checkValidity();
      field.setAttribute("aria-invalid", String(!isValid));
      if (!isValid && !firstInvalid) {
        firstInvalid = field;
      }
    });

    if (firstInvalid) {
      message.textContent = "Revisa los campos marcados antes de continuar.";
      message.classList.add("is-error");
      firstInvalid.focus();
      return;
    }

    message.textContent = "Formulario validado correctamente. Listo para conectar con PHP y MySQL.";
    message.classList.remove("is-error");
    form.reset();
    fields.forEach((field) => field.removeAttribute("aria-invalid"));
  });
});
