# Contact Manager

## Project Overview

Contact Manager is a secure web-based personal contact management application developed for COP 4331. Users can create accounts, log in securely, and manage their own personal contact lists. Each user's contacts are private and isolated from other users.

The application allows users to:

* Register for a new account
* Log in securely
* Add contacts
* Search contacts
* Edit contacts
* Delete contacts
* Log out securely

The system is built using a LAMP stack and communicates between the frontend and backend through JSON-based API endpoints.

---

## Live Application

**Application URL:**
https://lamp.myucf.xyz/

## Team Members

| Name            | Role                                      |
| --------------- | ----------------------------------------- |
| John Casavant   | [Backend, Cloud SQL]                      |
| Ethan Harris    | [Responsibilities]                        |
| Jai Khindri     | [Backend, Cloud SQL]                      |
| Evan Phillips   | [Middleware / API]                        |
| Lucas Konvalina | [Frontend / Pm] 			      |

---

## Technology Stack

### Backend

* Linux (DigitalOcean Ubuntu Droplet)
* Apache Web Server
* MySQL Database
* PHP REST-style API

### Frontend

* HTML
* CSS
* JavaScript
* AJAX

### Development Tools

* GitHub
* Git
* DigitalOcean
* Cloudflare DNS
* Postman / Bruno
* Discord

---

## Project Architecture

Frontend pages communicate with PHP API endpoints using AJAX requests.

The PHP API processes requests, communicates with the MySQL database, and returns JSON responses.

```text
Browser
   |
AJAX + JSON
   |
PHP API
   |
MySQL Database
```

---

## Security Features

### Password Protection

Passwords are never stored in plain text.

User passwords are:

* Salted
* Hashed
* Stored securely in the database

### HTTPS

The application is served over HTTPS using SSL certificates.

### User Isolation

Users can only access and manage their own contacts.

Authenticates a user and returns user information.

### Register

```http
POST /LAMPAPI/Register.php
```
Creates a new user account.

### Search Contacts

```http
POST /LAMPAPI/SearchContacts.php
```

Searches contacts belonging to the currently authenticated user.

### Add Contact

```http
POST /LAMPAPI/AddContact.php
```

Creates a new contact.

### Update Contact

```http
POST /LAMPAPI/UpdateContact.php
```

Updates an existing contact.

### Delete Contact

```http
POST /LAMPAPI/DeleteContact.php
```
### Clone Repository

```bash
git clone [REPOSITORY_URL]
```

### Configure Database

Create a MySQL database and import the provided schema.

### Configure API

Update database credentials inside the PHP API files.

### Deploy

Deploy files to:

```text
/var/www/html
```

on the DigitalOcean server.

---

## Features

* User Registration
* User Login
* Password Hashing
* Contact Creation
* Contact Search
* Contact Editing
* Contact Deletion
* JSON API Communication
* AJAX Requests
* HTTPS Support
* Remote Hosting

--
---

COP 4004
University of Central Florida
