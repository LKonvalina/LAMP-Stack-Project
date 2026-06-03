// Backend handoff:
// 1. Keep USE_MOCK_API true for the frontend-only demo.
// 2. When the PHP endpoints are ready, set USE_MOCK_API to false.
// 3. Confirm payload/response field names with the backend team in apiRequest() calls below.
const USE_MOCK_API = true;

const API_ENDPOINTS = {
  login: "/LAMPAPI/Login.php",
  register: "/LAMPAPI/Register.php",
  searchContacts: "/LAMPAPI/SearchContacts.php",
  addContact: "/LAMPAPI/AddContact.php",
  updateContact: "/LAMPAPI/UpdateContact.php",
  deleteContact: "/LAMPAPI/DeleteContact.php"
};

const STORAGE_KEYS = {
  users: "contactManagerUsers",
  session: "contactManagerSession",
  contacts: "contactManagerContacts"
};

const state = {
  user: null,
  contacts: [],
  searchTerm: ""
};

let searchTimer;

const elements = {
  landingView: document.querySelector("#landing-view"),
  dashboardView: document.querySelector("#dashboard-view"),
  authTabs: document.querySelectorAll("[data-auth-tab]"),
  loginForm: document.querySelector("#login-form"),
  signupForm: document.querySelector("#signup-form"),
  authMessage: document.querySelector("#auth-message"),
  welcomeMessage: document.querySelector("#welcome-message"),
  logoutButton: document.querySelector("#logout-button"),
  contactForm: document.querySelector("#contact-form"),
  contactFormTitle: document.querySelector("#contact-form-title"),
  contactId: document.querySelector("#contact-id"),
  contactFirstName: document.querySelector("#contact-first-name"),
  contactLastName: document.querySelector("#contact-last-name"),
  contactEmail: document.querySelector("#contact-email"),
  contactPhone: document.querySelector("#contact-phone"),
  saveContactButton: document.querySelector("#save-contact-button"),
  cancelEditButton: document.querySelector("#cancel-edit-button"),
  contactSearch: document.querySelector("#contact-search"),
  contactsList: document.querySelector("#contacts-list"),
  contactCount: document.querySelector("#contact-count"),
  emptyState: document.querySelector("#empty-state")
};

init();

function init() {
  wireAuthTabs();
  wireAuthForms();
  wireDashboardActions();
  restoreSession();
}

function wireAuthTabs() {
  elements.authTabs.forEach((tab) => {
    tab.addEventListener("click", () => setAuthTab(tab.dataset.authTab));
  });
}

function wireAuthForms() {
  elements.loginForm.addEventListener("submit", handleLogin);
  elements.signupForm.addEventListener("submit", handleSignup);
}

function wireDashboardActions() {
  elements.logoutButton.addEventListener("click", handleLogout);
  elements.contactForm.addEventListener("submit", handleSaveContact);
  elements.cancelEditButton.addEventListener("click", resetContactForm);
  elements.contactSearch.addEventListener("input", (event) => {
    state.searchTerm = event.target.value.trim().toLowerCase();

    if (USE_MOCK_API) {
      renderContacts();
      return;
    }

    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
      await loadContacts();
      renderContacts();
    }, 250);
  });
}

async function restoreSession() {
  const session = readStorage(STORAGE_KEYS.session, null);

  if (session) {
    state.user = session;
    await loadContacts();
    showDashboard();
    return;
  }

  showLanding();
}

function setAuthTab(tabName) {
  const isLogin = tabName === "login";

  elements.authTabs.forEach((tab) => {
    const isActive = tab.dataset.authTab === tabName;
    tab.classList.toggle("active", isActive);
    tab.setAttribute("aria-selected", String(isActive));
  });

  elements.loginForm.classList.toggle("active", isLogin);
  elements.signupForm.classList.toggle("active", !isLogin);
  setAuthMessage("");
}

async function handleLogin(event) {
  event.preventDefault();

  const formData = new FormData(elements.loginForm);
  const credentials = {
    login: formData.get("username").trim(),
    password: formData.get("password")
  };

  try {
    const user = USE_MOCK_API
      ? mockLogin(credentials)
      : await apiRequest(API_ENDPOINTS.login, credentials);

    state.user = normalizeUser(user, credentials.login);
    writeStorage(STORAGE_KEYS.session, state.user);
    await loadContacts();
    showDashboard();
    elements.loginForm.reset();
  } catch (error) {
    setAuthMessage(error.message);
  }
}

async function handleSignup(event) {
  event.preventDefault();

  const formData = new FormData(elements.signupForm);
  const password = formData.get("password");
  const confirmPassword = formData.get("confirmPassword");

  if (password !== confirmPassword) {
    setAuthMessage("Passwords do not match.");
    return;
  }

  const newUser = {
    firstName: formData.get("firstName").trim(),
    lastName: formData.get("lastName").trim(),
    login: formData.get("username").trim(),
    password
  };

  try {
    if (USE_MOCK_API) {
      mockRegister(newUser);
    } else {
      await apiRequest(API_ENDPOINTS.register, newUser);
    }

    elements.signupForm.reset();
    setAuthTab("login");
    setAuthMessage("Account created. You can log in now.", "success");
  } catch (error) {
    setAuthMessage(error.message);
  }
}

function handleLogout() {
  state.user = null;
  state.contacts = [];
  state.searchTerm = "";
  localStorage.removeItem(STORAGE_KEYS.session);
  elements.contactSearch.value = "";
  resetContactForm();
  showLanding();
}

async function handleSaveContact(event) {
  event.preventDefault();

  const contact = {
    id: elements.contactId.value || createId(),
    firstName: elements.contactFirstName.value.trim(),
    lastName: elements.contactLastName.value.trim(),
    email: elements.contactEmail.value.trim(),
    phone: elements.contactPhone.value.trim(),
    userId: state.user.id
  };

  const isEditing = Boolean(elements.contactId.value);

  try {
    if (USE_MOCK_API) {
      isEditing ? mockUpdateContact(contact) : mockAddContact(contact);
    } else if (isEditing) {
      await apiRequest(API_ENDPOINTS.updateContact, contact);
    } else {
      await apiRequest(API_ENDPOINTS.addContact, contact);
    }

    await loadContacts();
    resetContactForm();
  } catch (error) {
    alert(error.message);
  }
}

function showLanding() {
  elements.landingView.classList.remove("hidden");
  elements.dashboardView.classList.add("hidden");
}

function showDashboard() {
  elements.landingView.classList.add("hidden");
  elements.dashboardView.classList.remove("hidden");
  elements.welcomeMessage.textContent = `Welcome, ${state.user.firstName}. Manage your private contact list.`;
  renderContacts();
  window.scrollTo({ top: 0, behavior: "smooth" });
}

async function loadContacts() {
  if (!state.user) {
    state.contacts = [];
    return;
  }

  if (USE_MOCK_API) {
    state.contacts = mockSearchContacts();
    return;
  }

  const data = await apiRequest(API_ENDPOINTS.searchContacts, {
    userId: state.user.id,
    search: state.searchTerm
  });

  state.contacts = normalizeContacts(data.contacts || data.results || data);
}

function renderContacts() {
  const contacts = getFilteredContacts();

  elements.contactsList.innerHTML = "";
  elements.contactCount.textContent = `${contacts.length} ${contacts.length === 1 ? "contact" : "contacts"}`;
  elements.emptyState.classList.toggle("hidden", contacts.length > 0);

  contacts.forEach((contact) => {
    const card = document.createElement("article");
    card.className = "contact-card";

    const initials = `${contact.firstName.charAt(0)}${contact.lastName.charAt(0)}`.toUpperCase();

    card.innerHTML = `
      <div class="avatar">${escapeHtml(initials)}</div>
      <div class="contact-details">
        <h3>${escapeHtml(contact.firstName)} ${escapeHtml(contact.lastName)}</h3>
        <p>${escapeHtml(contact.email || "No email added")}</p>
        <p>${escapeHtml(contact.phone || "No phone added")}</p>
      </div>
      <div class="contact-actions">
        <button class="icon-button" type="button" data-action="edit" data-id="${escapeHtml(contact.id)}">Edit</button>
        <button class="icon-button delete" type="button" data-action="delete" data-id="${escapeHtml(contact.id)}">Delete</button>
      </div>
    `;

    card.querySelector("[data-action='edit']").addEventListener("click", () => startEditContact(contact.id));
    card.querySelector("[data-action='delete']").addEventListener("click", () => deleteContact(contact.id));
    elements.contactsList.append(card);
  });
}

function getFilteredContacts() {
  if (!state.searchTerm) {
    return state.contacts;
  }

  return state.contacts.filter((contact) => {
    const searchable = `${contact.firstName} ${contact.lastName} ${contact.email} ${contact.phone}`.toLowerCase();
    return searchable.includes(state.searchTerm);
  });
}

function startEditContact(contactId) {
  const contact = state.contacts.find((item) => item.id === contactId);

  if (!contact) {
    return;
  }

  elements.contactId.value = contact.id;
  elements.contactFirstName.value = contact.firstName;
  elements.contactLastName.value = contact.lastName;
  elements.contactEmail.value = contact.email;
  elements.contactPhone.value = contact.phone;
  elements.contactFormTitle.textContent = "Edit contact";
  elements.saveContactButton.textContent = "Save changes";
  elements.cancelEditButton.classList.remove("hidden");
  elements.contactFirstName.focus();
}

async function deleteContact(contactId) {
  const contact = state.contacts.find((item) => item.id === contactId);
  const contactName = contact ? `${contact.firstName} ${contact.lastName}` : "this contact";

  if (!confirm(`Delete ${contactName}?`)) {
    return;
  }

  try {
    if (USE_MOCK_API) {
      mockDeleteContact(contactId);
    } else {
      await apiRequest(API_ENDPOINTS.deleteContact, {
        id: contactId,
        userId: state.user.id
      });
    }

    await loadContacts();
    resetContactForm();
  } catch (error) {
    alert(error.message);
  }
}

function resetContactForm() {
  elements.contactForm.reset();
  elements.contactId.value = "";
  elements.contactFormTitle.textContent = "Add contact";
  elements.saveContactButton.textContent = "Add contact";
  elements.cancelEditButton.classList.add("hidden");
}

async function apiRequest(url, payload) {
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  });

  const data = await response.json();

  if (!response.ok || data.error) {
    throw new Error(data.error || "Something went wrong. Please try again.");
  }

  return data;
}

function mockRegister(newUser) {
  const users = readStorage(STORAGE_KEYS.users, []);
  const usernameTaken = users.some((user) => user.login.toLowerCase() === newUser.login.toLowerCase());

  if (usernameTaken) {
    throw new Error("That username is already registered.");
  }

  users.push({
    id: createId(),
    firstName: newUser.firstName,
    lastName: newUser.lastName,
    login: newUser.login,
    password: newUser.password
  });

  writeStorage(STORAGE_KEYS.users, users);
}

function mockLogin(credentials) {
  const users = readStorage(STORAGE_KEYS.users, []);
  const user = users.find((item) => item.login === credentials.login && item.password === credentials.password);

  if (!user) {
    throw new Error("Invalid username or password. Create a demo account first.");
  }

  return user;
}

function mockSearchContacts() {
  const allContacts = readStorage(STORAGE_KEYS.contacts, []);
  return allContacts.filter((contact) => contact.userId === state.user.id);
}

function mockAddContact(contact) {
  const contacts = readStorage(STORAGE_KEYS.contacts, []);
  contacts.push(contact);
  writeStorage(STORAGE_KEYS.contacts, contacts);
}

function mockUpdateContact(updatedContact) {
  const contacts = readStorage(STORAGE_KEYS.contacts, []);
  const nextContacts = contacts.map((contact) => (
    contact.id === updatedContact.id ? updatedContact : contact
  ));
  writeStorage(STORAGE_KEYS.contacts, nextContacts);
}

function mockDeleteContact(contactId) {
  const contacts = readStorage(STORAGE_KEYS.contacts, []);
  const nextContacts = contacts.filter((contact) => contact.id !== contactId);
  writeStorage(STORAGE_KEYS.contacts, nextContacts);
}

function normalizeUser(user, fallbackLogin) {
  return {
    id: user.id || user.userId || user.ID || fallbackLogin,
    firstName: user.firstName || user.first || user.FirstName || fallbackLogin,
    lastName: user.lastName || user.last || user.LastName || "",
    login: user.login || user.username || fallbackLogin
  };
}

function normalizeContacts(value) {
  if (!Array.isArray(value)) {
    return [];
  }

  return value.map((contact) => ({
    id: String(contact.id || contact.ID || contact.contactId || createId()),
    firstName: contact.firstName || contact.FirstName || contact.first || "",
    lastName: contact.lastName || contact.LastName || contact.last || "",
    email: contact.email || contact.Email || "",
    phone: contact.phone || contact.Phone || "",
    userId: contact.userId || contact.UserID || state.user.id
  }));
}

function setAuthMessage(message, type = "error") {
  elements.authMessage.textContent = message;
  elements.authMessage.classList.toggle("success", type === "success");
}

function readStorage(key, fallback) {
  const rawValue = localStorage.getItem(key);

  if (!rawValue) {
    return fallback;
  }

  try {
    return JSON.parse(rawValue);
  } catch {
    return fallback;
  }
}

function writeStorage(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
}

function createId() {
  return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}
