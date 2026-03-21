/* =====================================================
   NaturasiMIS Login Page JavaScript
   - Fixed role typo
   - Improved validation
   - Better UX
===================================================== */

document.getElementById("loginForm").addEventListener("submit", (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const rememberMe = document.querySelector(".options input[type='checkbox']").checked;
  const feedback = document.getElementById("feedback");

  // Clear previous feedback
  feedback.textContent = "";
  feedback.className = "feedback";

  // Hardcoded users with roles (FIXED TYPO)
  const users = {
    admin: { password: "1234", role: "admin" },
    inventory: { password: "5678", role: "inventory" }, 
    production: { password: "91011", role: "production" },
    manager: { password: "121314", role: "manager" }
  };

  // Validation
  if (!username || !password) {
    feedback.textContent = "⚠️ Please enter both username and password.";
    feedback.className = "feedback error";
    setTimeout(() => {
      feedback.textContent = "";
    }, 3000);
    return;
  }

  // Check credentials
  if (users[username] && users[username].password === password) {
    const role = users[username].role;
    
    // Success feedback
    feedback.textContent = `✅ Login successful as ${role}! Redirecting...`;
    feedback.className = "feedback success";

    // Save remember me preference
    if (rememberMe) {
      localStorage.setItem("rememberedUser", username);
    } else {
      localStorage.removeItem("rememberedUser");
    }

    // Save session info including role
    localStorage.setItem("loggedInUser", username);
    localStorage.setItem("userRole", role);

    // Disable form during redirect
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = "Redirecting...";

    // Redirect to dashboard
    setTimeout(() => {
      window.location.href = "/dashboard.html";
    }, 1500);
  } else {
    // Error feedback
    feedback.textContent = "❌ Invalid username or password. Please try again.";
    feedback.className = "feedback error";

    // Shake animation effect
    const loginCard = document.querySelector(".login-card");
    loginCard.style.animation = "shake 0.5s";
    setTimeout(() => {
      loginCard.style.animation = "";
    }, 500);

    // Clear error after 4 seconds
    setTimeout(() => {
      feedback.textContent = "";
    }, 4000);
  }
});

/* =====================================================
   AUTO-FILL REMEMBERED USER
===================================================== */
window.addEventListener("load", () => {
  const savedUser = localStorage.getItem("rememberedUser");
  if (savedUser) {
    document.getElementById("username").value = savedUser;
    document.querySelector(".options input[type='checkbox']").checked = true;
    // Focus on password field for better UX
    document.getElementById("password").focus();
  } else {
    // Focus on username field
    document.getElementById("username").focus();
  }
});

/* =====================================================
   SHAKE ANIMATION (Add to CSS if not present)
===================================================== */
const style = document.createElement('style');
style.textContent = `
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
  }
`;
document.head.appendChild(style);