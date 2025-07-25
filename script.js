// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
  // Initialize user tokens
  let userTokens = parseInt(localStorage.getItem("userTokens") || "0");
  updateTokenDisplay();

  // Modern Navbar Functionality
  const navbar = document.querySelector(".navbar-modern");
  const navbarProgress = document.querySelector(".navbar-progress");

  // Navbar scroll effects
  function updateNavbarOnScroll() {
    const scrolled = window.pageYOffset;
    const maxScroll =
      document.documentElement.scrollHeight - window.innerHeight;
    const scrollProgress = (scrolled / maxScroll) * 100;

    // Add scrolled class for styling
    if (scrolled > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }

    // Update progress bar
    if (navbarProgress) {
      navbarProgress.style.transform = `scaleX(${scrollProgress / 100})`;
    }
  }

  // Active nav link highlighting
  function updateActiveNavLink() {
    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll(".nav-link-modern");

    let currentSection = "";

    sections.forEach((section) => {
      const sectionTop = section.offsetTop - 100;
      if (window.pageYOffset >= sectionTop) {
        currentSection = section.getAttribute("id");
      }
    });

    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href") === `#${currentSection}`) {
        link.classList.add("active");
      }
    });
  }

  // Throttled scroll handler for better performance
  let ticking = false;
  function handleScroll() {
    if (!ticking) {
      requestAnimationFrame(() => {
        updateNavbarOnScroll();
        updateActiveNavLink();
        ticking = false;
      });
      ticking = true;
    }
  }

  window.addEventListener("scroll", handleScroll);

  // Smooth scrolling for navigation links
  const navLinks = document.querySelectorAll('a[href^="#"]');
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      const targetSection = document.querySelector(targetId);

      if (targetSection) {
        const offsetTop = targetSection.offsetTop - 80; // Account for fixed navbar
        window.scrollTo({
          top: offsetTop,
          behavior: "smooth",
        });
      }
    });
  });

  // Mobile menu auto-close
  const navbarToggler = document.querySelector(".custom-toggler");
  const navbarCollapse = document.querySelector(".navbar-collapse");

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      if (navbarCollapse.classList.contains("show")) {
        navbarToggler.click();
      }
    });
  });

  // Contact Form Functionality
  const contactForm = document.getElementById("contactForm");
  const submitBtn = document.getElementById("submitBtn");
  const submitText = document.getElementById("submitText");
  const submitLoader = document.getElementById("submitLoader");
  const contactAlert = document.getElementById("contactAlert");
  const alertIcon = document.getElementById("alertIcon");
  const alertTitle = document.getElementById("alertTitle");
  const alertMessage = document.getElementById("alertMessage");
  const messageCounter = document.getElementById("messageCounter");

  // Enhanced Message counter functionality
  const messageTextarea = document.getElementById("message");
  if (messageTextarea && messageCounter) {
    function updateMessageCounter() {
      const length = messageTextarea.value.length;
      messageCounter.textContent = length;

      const counterParent = messageCounter.parentElement;

      // Remove all counter classes first
      counterParent.classList.remove(
        "text-warning",
        "text-danger",
        "text-muted"
      );

      if (length > 1000) {
        counterParent.classList.add("text-danger");
      } else if (length > 900) {
        counterParent.classList.add("text-warning");
      } else {
        counterParent.classList.add("text-muted");
      }
    }

    messageTextarea.addEventListener("input", updateMessageCounter);

    // Initialize counter
    updateMessageCounter();
  }

  // Enhanced Form validation functions
  function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = "";

    // Clear previous validation states
    field.classList.remove("is-invalid", "is-valid");

    // Clear any existing error messages
    const feedbackElement =
      field.parentElement.querySelector(".invalid-feedback");
    if (feedbackElement) {
      feedbackElement.textContent = "";
    }

    // Required field validation
    if (field.hasAttribute("required") && !value) {
      isValid = false;
      errorMessage = getFieldLabel(field) + " is required.";
    }
    // Email validation
    else if (fieldType === "email" && value) {
      const emailRegex =
        /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage =
          "Please enter a valid email address (e.g., user@example.com).";
      }
    }
    // Phone validation (if provided)
    else if (fieldType === "tel" && value) {
      // Remove all non-numeric characters for validation
      const cleanPhone = value.replace(/[^\d]/g, "");
      if (cleanPhone.length < 10) {
        isValid = false;
        errorMessage = "Phone number must be at least 10 digits long.";
      } else if (!/^[\d+\-\s\(\)]+$/.test(value)) {
        isValid = false;
        errorMessage =
          "Please enter a valid phone number (digits, spaces, +, -, (), allowed).";
      }
    }
    // Name validation (letters, spaces, hyphens, apostrophes only)
    else if ((fieldName === "firstName" || fieldName === "lastName") && value) {
      const nameRegex = /^[a-zA-Z\s\-']+$/;
      if (!nameRegex.test(value)) {
        isValid = false;
        errorMessage =
          "Please enter a valid name (letters, spaces, hyphens, and apostrophes only).";
      } else if (value.length < 2) {
        isValid = false;
        errorMessage =
          getFieldLabel(field) + " must be at least 2 characters long.";
      } else if (value.length > 50) {
        isValid = false;
        errorMessage =
          getFieldLabel(field) + " must be less than 50 characters.";
      }
    }
    // Message length validation
    else if (fieldName === "message" && value) {
      if (value.length < 10) {
        isValid = false;
        errorMessage = "Message must be at least 10 characters long.";
      } else if (value.length > 1000) {
        isValid = false;
        errorMessage = "Message must be less than 1000 characters.";
      }
    }
    // Checkbox validation
    else if (field.type === "checkbox" && field.hasAttribute("required")) {
      if (!field.checked) {
        isValid = false;
        errorMessage =
          "You must agree to the Privacy Policy and Terms & Conditions.";
      }
    }

    // Apply validation styling and show error messages
    if (isValid && value) {
      field.classList.add("is-valid");
    } else if (!isValid) {
      field.classList.add("is-invalid");
      if (feedbackElement) {
        feedbackElement.textContent = errorMessage;
      }
    }

    return isValid;
  }

  // Helper function to get field label text
  function getFieldLabel(field) {
    const label = field.parentElement.querySelector("label");
    if (label) {
      return label.textContent.replace(" *", "").trim();
    }
    // Fallback to field name
    return field.name.charAt(0).toUpperCase() + field.name.slice(1);
  }

  // Enhanced real-time validation with debouncing
  const formFields =
    contactForm?.querySelectorAll("input, textarea, select") || [];

  formFields.forEach((field) => {
    let validationTimeout;

    // Validate on blur (when user leaves field)
    field.addEventListener("blur", () => {
      validateField(field);
    });

    // Real-time validation with debouncing (only if field was previously invalid)
    field.addEventListener("input", () => {
      // Clear existing timeout
      if (validationTimeout) {
        clearTimeout(validationTimeout);
      }

      // Only do real-time validation if field was marked as invalid
      if (field.classList.contains("is-invalid")) {
        validationTimeout = setTimeout(() => {
          validateField(field);
        }, 300); // 300ms delay to avoid excessive validation
      }

      // For message field, always update counter
      if (field.name === "message") {
        updateMessageCounter();
      }
    });
  });

  // Show alert function
  function showAlert(type, title, message) {
    contactAlert.className = `alert alert-${type}`;
    alertIcon.className =
      type === "success"
        ? "bi bi-check-circle-fill text-success me-3 fs-4"
        : "bi bi-exclamation-triangle-fill text-danger me-3 fs-4";
    alertTitle.textContent = title;
    alertMessage.textContent = message;
    contactAlert.classList.remove("d-none");

    // Scroll to alert
    contactAlert.scrollIntoView({ behavior: "smooth", block: "center" });

    // Auto-hide after 7 seconds for success messages
    if (type === "success") {
      setTimeout(() => {
        contactAlert.classList.add("d-none");
      }, 7000);
    }
  }

  // Hide alert function
  function hideAlert() {
    contactAlert.classList.add("d-none");
  }

  // Form submission handler
  if (contactForm) {
    contactForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Hide any previous alerts
      hideAlert();

      // Enhanced form validation before submission
      let isFormValid = true;
      const validationErrors = [];

      formFields.forEach((field) => {
        if (!validateField(field)) {
          isFormValid = false;
          const fieldLabel = getFieldLabel(field);
          const errorMsg =
            field.parentElement.querySelector(".invalid-feedback")?.textContent;
          if (errorMsg) {
            validationErrors.push(`${fieldLabel}: ${errorMsg}`);
          }
        }
      });

      if (!isFormValid) {
        const errorList =
          validationErrors.length <= 3
            ? validationErrors.join(", ")
            : `${validationErrors.slice(0, 3).join(", ")} and ${
                validationErrors.length - 3
              } more error(s)`;

        showAlert("danger", "Please Fix These Errors", errorList);

        // Focus on first invalid field
        const firstInvalidField = contactForm.querySelector(".is-invalid");
        if (firstInvalidField) {
          firstInvalidField.focus();
          firstInvalidField.scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
        }
        return;
      }

      // Disable submit button and show loading
      submitBtn.disabled = true;
      submitText.classList.add("d-none");
      submitLoader.classList.remove("d-none");

      // Prepare form data
      const formData = {
        firstName: document.getElementById("firstName").value.trim(),
        lastName: document.getElementById("lastName").value.trim(),
        email: document.getElementById("email").value.trim(),
        phone: document.getElementById("phone").value.trim(),
        message: document.getElementById("message").value.trim(),
        privacy: document.getElementById("privacy").checked,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
      };

      try {
        // Send AJAX request to the contact form handler
        const response = await fetch("contact-handler.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        });

        // Check response status
        if (!response.ok) {
          throw new Error(`Server error: ${response.status}`);
        }

        // Parse JSON response
        const result = await response.json();
        console.log("Form submission result:", result);

        if (result.success === true) {
          // Success - show success message and reset form
          showAlert("success", "Message Sent Successfully!", result.message);
          contactForm.reset();

          // Clear validation classes
          formFields.forEach((field) => {
            field.classList.remove("is-valid", "is-invalid");
          });

          // Reset message counter
          if (messageCounter) {
            messageCounter.textContent = "0";
            messageCounter.parentElement.classList.remove(
              "text-warning",
              "text-danger"
            );
            messageCounter.parentElement.classList.add("text-muted");
          }

          console.log("Contact form submitted successfully");
        } else {
          // Error from server
          showAlert(
            "danger",
            "Submission Failed",
            result.message || "Unknown error occurred"
          );
          console.error("Contact form error:", result);
        }
      } catch (error) {
        // Network or other error
        console.error("Contact form submission error:", error);
        showAlert(
          "danger",
          "Connection Error",
          "There was a problem sending your message. Please try again or contact us directly at support@win80x.com."
        );
      } finally {
        // Re-enable submit button and hide loading
        submitBtn.disabled = false;
        submitText.classList.remove("d-none");
        submitLoader.classList.add("d-none");
      }
    });
  }

  // Download button click handler
  const downloadButtons = document.querySelectorAll('a[href="#"]');
  downloadButtons.forEach((button) => {
    if (
      button.textContent.includes("Download APK") ||
      button.textContent.includes("Download")
    ) {
      button.addEventListener("click", function (e) {
        e.preventDefault();

        // Show download modal or redirect to actual download
        showDownloadModal();
      });
    }
  });

  // Download modal function
  function showDownloadModal() {
    // Create modal HTML
    const modalHTML = `
            <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold" id="downloadModalLabel">
                                <i class="bi bi-download me-2 text-primary"></i>Download Win80x
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-4">
                                <i class="bi bi-phone text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h6 class="mb-3">Ready to start gaming?</h6>
                            <p class="text-muted mb-4">Click the button below to download the latest version of Win80x APK.</p>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary btn-lg" onclick="startDownload()">
                                    <i class="bi bi-download me-2"></i>Download APK (v2.1.0)
                                </button>
                                <small class="text-muted">File size: ~25 MB</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>Safe & Virus-free
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;

    // Add modal to body if it doesn't exist
    if (!document.getElementById("downloadModal")) {
      document.body.insertAdjacentHTML("beforeend", modalHTML);
    }

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById("downloadModal"));
    modal.show();
  }

  // Add loading animation to buttons
  function addLoadingState(button) {
    const originalText = button.innerHTML;
    button.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...';
    button.disabled = true;

    // Remove loading state after 2 seconds (simulate download)
    setTimeout(() => {
      button.innerHTML = originalText;
      button.disabled = false;
    }, 2000);
  }

  // Contact form handling (if added later)
  const contactForms = document.querySelectorAll("form");
  contactForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      const submitButton = form.querySelector('button[type="submit"]');
      if (submitButton) {
        addLoadingState(submitButton);
      }
    });
  });

  // FAQ accordion auto-scroll
  const accordionButtons = document.querySelectorAll(".accordion-button");
  accordionButtons.forEach((button) => {
    button.addEventListener("click", function () {
      setTimeout(() => {
        if (!this.classList.contains("collapsed")) {
          this.scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
        }
      }, 300);
    });
  });

  // Parallax effect removed to fix bugs

  // Add hover effects to cards
  const cards = document.querySelectorAll(".card");
  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
    });
  });

  // Lazy loading for images
  const images = document.querySelectorAll('img[src*="placeholder"]');
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        // In a real scenario, you would replace placeholder with actual image
        img.classList.add("loaded");
        observer.unobserve(img);
      }
    });
  });

  images.forEach((img) => {
    imageObserver.observe(img);
  });

  // Add copy to clipboard functionality for email
  const emailLinks = document.querySelectorAll('a[href^="mailto:"]');
  emailLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const email = this.getAttribute("href").replace("mailto:", "");

      if (navigator.clipboard) {
        navigator.clipboard.writeText(email).then(() => {
          showToast("Email copied to clipboard!");
        });
      }
    });
  });

  // Toast notification function
  function showToast(message) {
    const toastHTML = `
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div class="toast show" role="alert">
                    <div class="toast-header">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            </div>
        `;

    document.body.insertAdjacentHTML("beforeend", toastHTML);

    // Auto remove toast after 3 seconds
    setTimeout(() => {
      const toast = document.querySelector(".toast");
      if (toast) {
        toast.remove();
      }
    }, 3000);
  }

  // Performance optimization: debounce scroll events
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Apply debouncing to scroll events
  const debouncedScrollHandler = debounce(() => {
    // Scroll-related operations
    const scrolled = window.pageYOffset;

    // Update navbar
    if (scrolled > 50) {
      navbar.classList.add("navbar-scrolled");
    } else {
      navbar.classList.remove("navbar-scrolled");
    }
  }, 10);

  window.addEventListener("scroll", debouncedScrollHandler);
});

// Global function for download simulation
function startDownload() {
  const button = event.target;
  const originalText = button.innerHTML;

  button.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Preparing download...';
  button.disabled = true;

  // Simulate download process
  setTimeout(() => {
    button.innerHTML =
      '<i class="bi bi-check-circle me-2"></i>Download started!';

    setTimeout(() => {
      button.innerHTML = originalText;
      button.disabled = false;

      // Close modal
      const modal = bootstrap.Modal.getInstance(
        document.getElementById("downloadModal")
      );
      modal.hide();

      // Show success message
      showToast("Download will begin shortly. Check your downloads folder.");
    }, 1500);
  }, 2000);
}

// Add CSS for navbar scrolled state
const style = document.createElement("style");
style.textContent = `
    .navbar-scrolled {
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    }
    
    .loaded {
        filter: none;
        transition: filter 0.3s ease;
    }
    
    .game-section-card {
        transition: all 0.3s ease;
    }
    
    .game-section-card:hover {
        transform: translateY(-5px);
    }
    
    .price-badge {
        border: 2px solid #e9ecef;
    }
    
    .step-number {
        position: relative;
        z-index: 1;
    }
`;
document.head.appendChild(style);

// Token Management Functions
function updateTokenDisplay() {
  const tokenElements = document.querySelectorAll(".user-tokens");
  tokenElements.forEach((el) => {
    el.textContent = userTokens;
  });
}

function increaseTokens() {
  const input = document.getElementById("tokenQuantity");
  let value = parseInt(input.value);
  if (value < 50) {
    input.value = value + 1;
    updateTotalAmount();
  }
}

function decreaseTokens() {
  const input = document.getElementById("tokenQuantity");
  let value = parseInt(input.value);
  if (value > 1) {
    input.value = value - 1;
    updateTotalAmount();
  }
}

function updateTotalAmount() {
  const quantity = parseInt(document.getElementById("tokenQuantity").value);
  const total = quantity * 100;
  document.getElementById("totalAmount").textContent = total;
}

function purchaseTokens() {
  const quantity = parseInt(document.getElementById("tokenQuantity").value);
  const total = quantity * 100;

  // Show purchase modal
  const modalHTML = `
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold" id="purchaseModalLabel">
              <i class="bi bi-credit-card me-2 text-primary"></i>Purchase Tokens
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div class="mb-4">
              <i class="bi bi-coins text-primary" style="font-size: 4rem;"></i>
            </div>
            <h6 class="mb-3">Purchase ${quantity} token${
    quantity > 1 ? "s" : ""
  }</h6>
            <p class="text-muted mb-4">Total Amount: <strong>₹${total}</strong></p>
            <div class="d-grid gap-2">
              <button type="button" class="btn btn-primary btn-lg" onclick="processPurchase(${quantity}, ${total})">
                <i class="bi bi-credit-card me-2"></i>Pay ₹${total}
              </button>
            </div>
          </div>
          <div class="modal-footer border-0 justify-content-center">
            <small class="text-muted">
              <i class="bi bi-shield-check me-1"></i>Secure Payment Gateway
            </small>
          </div>
        </div>
      </div>
    </div>
  `;

  // Add modal to body if it doesn't exist
  if (!document.getElementById("purchaseModal")) {
    document.body.insertAdjacentHTML("beforeend", modalHTML);
  }

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById("purchaseModal"));
  modal.show();
}

function processPurchase(quantity, total) {
  const button = event.target;
  const originalText = button.innerHTML;

  button.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
  button.disabled = true;

  // Simulate payment process
  setTimeout(() => {
    userTokens += quantity;
    localStorage.setItem("userTokens", userTokens.toString());
    updateTokenDisplay();

    button.innerHTML =
      '<i class="bi bi-check-circle me-2"></i>Payment Successful!';

    setTimeout(() => {
      const modal = bootstrap.Modal.getInstance(
        document.getElementById("purchaseModal")
      );
      modal.hide();

      // Show success message
      showToast(
        `Successfully purchased ${quantity} token${
          quantity > 1 ? "s" : ""
        }! You now have ${userTokens} tokens.`
      );

      // Reset form
      document.getElementById("tokenQuantity").value = 1;
      updateTotalAmount();
    }, 1500);
  }, 2000);
}

function selectGameSection(section) {
  if (userTokens < 1) {
    showToast(
      "You need at least 1 token to play! Please purchase tokens first.",
      "warning"
    );
    document
      .querySelector("#buy-tokens")
      .scrollIntoView({ behavior: "smooth" });
    return;
  }

  const sectionName =
    section === "A" ? "Section A (Beginner)" : "Section B (Advanced)";
  const questions = section === "A" ? "10-15" : "15-20";
  const rewards = section === "A" ? "₹50 - ₹500" : "₹100 - ₹2000";

  const modalHTML = `
    <div class="modal fade" id="gameSectionModal" tabindex="-1" aria-labelledby="gameSectionModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold" id="gameSectionModalLabel">
              <i class="bi bi-play-circle me-2 text-primary"></i>Start Game
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div class="mb-4">
              <i class="bi bi-${
                section === "A" ? "patch-check" : "award"
              } text-${
    section === "A" ? "primary" : "success"
  }" style="font-size: 4rem;"></i>
            </div>
            <h6 class="mb-3">${sectionName}</h6>
            <div class="text-start mb-4">
              <p><strong>Questions:</strong> ${questions}</p>
              <p><strong>Token Cost:</strong> 1 token</p>
              <p><strong>Potential Rewards:</strong> ${rewards}</p>
              <p><strong>Your Tokens:</strong> ${userTokens}</p>
            </div>
            <div class="d-grid gap-2">
              <button type="button" class="btn btn-${
                section === "A" ? "primary" : "success"
              } btn-lg" onclick="startGame('${section}')">
                <i class="bi bi-play me-2"></i>Start Quiz Game
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  // Add modal to body if it doesn't exist
  if (!document.getElementById("gameSectionModal")) {
    document.body.insertAdjacentHTML("beforeend", modalHTML);
  } else {
    document.getElementById("gameSectionModal").remove();
    document.body.insertAdjacentHTML("beforeend", modalHTML);
  }

  // Show modal
  const modal = new bootstrap.Modal(
    document.getElementById("gameSectionModal")
  );
  modal.show();
}

function startGame(section) {
  // Deduct token
  userTokens -= 1;
  localStorage.setItem("userTokens", userTokens.toString());
  updateTokenDisplay();

  const modal = bootstrap.Modal.getInstance(
    document.getElementById("gameSectionModal")
  );
  modal.hide();

  // Simulate game play and score
  const score = Math.floor(Math.random() * 100) + 1; // Random score between 1-100
  const luckyNumber =
    section === "A"
      ? Math.floor(Math.random() * 50) + 1 // 1-50 for Section A
      : Math.floor(Math.random() * 100) + 1; // 1-100 for Section B

  // Calculate reward based on lucky number
  let reward = 0;
  if (section === "A") {
    reward = Math.floor(luckyNumber * 10) + 50; // ₹50-₹500
  } else {
    reward = Math.floor(luckyNumber * 20) + 100; // ₹100-₹2000
  }

  // Show game result after delay
  setTimeout(() => {
    showGameResult(section, score, luckyNumber, reward);
  }, 3000);

  // Show game in progress
  showToast("Game in progress... Good luck!", "info");
}

function showGameResult(section, score, luckyNumber, reward) {
  const sectionName = section === "A" ? "Section A" : "Section B";

  const modalHTML = `
    <div class="modal fade" id="gameResultModal" tabindex="-1" aria-labelledby="gameResultModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 text-center">
            <h5 class="modal-title fw-bold w-100" id="gameResultModalLabel">
              <i class="bi bi-trophy me-2 text-warning"></i>Game Results
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div class="mb-4">
              <i class="bi bi-star-fill text-warning" style="font-size: 4rem;"></i>
            </div>
            <h6 class="mb-3">${sectionName} Results</h6>
            <div class="row text-center mb-4">
              <div class="col-4">
                <div class="border rounded p-3">
                  <h4 class="text-primary mb-1">${score}</h4>
                  <small class="text-muted">Your Score</small>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-3">
                  <h4 class="text-success mb-1">${luckyNumber}</h4>
                  <small class="text-muted">Lucky Number</small>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-3">
                  <h4 class="text-warning mb-1">₹${reward}</h4>
                  <small class="text-muted">You Won!</small>
                </div>
              </div>
            </div>
            <div class="alert alert-success">
              <i class="bi bi-check-circle me-2"></i>
              Congratulations! ₹${reward} has been credited to your wallet.
            </div>
            <div class="d-grid gap-2">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                <i class="bi bi-check me-2"></i>Claim Reward
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  // Add modal to body
  if (document.getElementById("gameResultModal")) {
    document.getElementById("gameResultModal").remove();
  }
  document.body.insertAdjacentHTML("beforeend", modalHTML);

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById("gameResultModal"));
  modal.show();
}

// Update existing showToast function to handle different types
function showToast(message, type = "success") {
  const iconClass =
    type === "success"
      ? "bi-check-circle text-success"
      : type === "warning"
      ? "bi-exclamation-triangle text-warning"
      : "bi-info-circle text-info";

  const toastHTML = `
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div class="toast show" role="alert">
        <div class="toast-header">
          <i class="${iconClass} me-2"></i>
          <strong class="me-auto">${
            type.charAt(0).toUpperCase() + type.slice(1)
          }</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML("beforeend", toastHTML);

  // Auto remove toast after 4 seconds
  setTimeout(() => {
    const toast = document.querySelector(".toast");
    if (toast) {
      toast.remove();
    }
  }, 4000);
}

// Update token quantity on input change
document.addEventListener("DOMContentLoaded", function () {
  const tokenInput = document.getElementById("tokenQuantity");
  if (tokenInput) {
    tokenInput.addEventListener("input", updateTotalAmount);
  }

  // Contact form handling
  const contactForm = document.getElementById("contactForm");
  if (contactForm) {
    contactForm.addEventListener("submit", handleContactFormSubmission);
  }
});

// Contact form submission handler
function handleContactFormSubmission(event) {
  event.preventDefault();

  const form = event.target;
  const formData = new FormData(form);

  // Get form values
  const firstName = formData.get("firstName");
  const lastName = formData.get("lastName");
  const email = formData.get("email");
  const phone = formData.get("phone");
  const subject = formData.get("subject");
  const message = formData.get("message");
  const privacy = formData.get("privacy");

  // Basic validation
  if (!firstName || !lastName || !email || !subject || !message || !privacy) {
    showToast(
      "Please fill in all required fields and accept the privacy policy.",
      "warning"
    );
    return;
  }

  // Email validation
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    showToast("Please enter a valid email address.", "warning");
    return;
  }

  // Show loading state
  const submitButton = form.querySelector('button[type="submit"]');
  const originalButtonText = submitButton.innerHTML;
  submitButton.innerHTML = '<i class="bi bi-hourglass me-2"></i>Sending...';
  submitButton.disabled = true;

  // Simulate form submission (replace with actual API call)
  setTimeout(() => {
    // Reset button state
    submitButton.innerHTML = originalButtonText;
    submitButton.disabled = false;

    // Show success message
    showToast(
      "Thank you for your message! We'll get back to you within 24 hours.",
      "success"
    );

    // Reset form
    form.reset();

    // Log contact submission (for demo purposes)
    console.log("Contact form submitted:", {
      firstName,
      lastName,
      email,
      phone,
      subject,
      message,
      timestamp: new Date().toISOString(),
    });
  }, 2000); // Simulate 2-second API delay
}
