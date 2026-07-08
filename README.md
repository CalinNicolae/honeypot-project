# Honeypot Project

A group project focused on building a web server honeypot, designed to attract, log, and analyze malicious activity by simulating a vulnerable web application.

## Overview

A honeypot is a decoy system set up to look like a legitimate, and often vulnerable, target in order to attract attackers, log their behavior, and gather information about common attack patterns and techniques. This project implements such a system as a web application, using PHP for the server side logic, along with JavaScript and CSS for the client facing interface.

## Tech Stack

- **Backend:** PHP
- **Frontend:** JavaScript, CSS
- **Architecture:** Server rendered pages with client side scripting for interactive elements

## Features

- Simulated web application designed to look like a legitimate, potentially vulnerable target
- Logging of incoming requests and interactions for later analysis
- Decoy pages and forms intended to attract and capture attacker behavior
- Client side scripting to enhance the interactivity and realism of the simulated application

## Prerequisites

- A local or remote PHP capable web server environment (such as XAMPP, MAMP, WAMP, or a native PHP installation)
- PHP, version compatible with the project code
- A web browser to access and test the application

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/CalinNicolae/honeypot-project.git
cd honeypot-project
```

### 2. Choose an implementation to run

Navigate to either the `group` directory for the shared group implementation, or `individual/cali-nicolae` for the individual submission.

```bash
cd group
```

### 3. Start a local PHP server

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000` in your browser.

Alternatively, place the project files inside the web root of a local server stack such as XAMPP or WAMP and access it through the corresponding local address.

## Usage

Once running, the application presents itself as a standard web server or web application. Any interaction with it, such as visiting pages, submitting forms, or attempting to access restricted areas, is intended to be logged for review and analysis. Review the code within the `group` and `individual/cali-nicolae` directories for the specific logging mechanism and storage location used.

## Disclaimer

This project was built strictly for educational purposes as part of a course assignment. It is intended to be run in a controlled, isolated environment such as a local machine or a private lab network. It should not be deployed on a production system or exposed to the public internet without a full understanding of the associated security implications.

## Team

This was a group project completed with the following teammates:

- Yasmine Ben Chouat
- Szymon Dudek

## Acknowledgments

This project was developed as a group assignment exploring web security concepts through the design and implementation of a functional honeypot system.
