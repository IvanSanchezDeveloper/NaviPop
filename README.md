# NAVIPOP

A **second-hand shopping web application** where users can:

- Post products for sale.
- Browse other users' products.
- Chat with sellers to negotiate (via Firebase — planned feature).
- (Planned) Make purchases through a **fake** payment platform.

This project was built mainly to **practice frontend & infrastructure skills** and experiment with tools like **Firebase** (for chats) and a payment process simulator.

---

## 📦 Project Structure

The project consists of **two main services**:

1. **Backend** – Built with **Symfony** and **PHP**, includes its own JWT-based authentication via cookies.
2. **Frontend** – Built with **React** and **Tailwind CSS**.

Data is stored in a **PostgreSQL** database.

---

## 🛠️ Tech Stack

| Layer          | Technology                                                                                                                                                                                                                                                            |
|----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Backend        | <img src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg" width="25"/> PHP, <img src="https://symfony.com/logos/symfony_black_03.png" width="25"/> Symfony, <img src="https://img.icons8.com/?size=512&id=rHpveptSuwDz&format=png" width="25"/> JWT |
| Frontend       | <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" width="25"/> React, <img src="https://upload.wikimedia.org/wikipedia/commons/d/d5/Tailwind_CSS_Logo.svg" width="25"/> Tailwind CSS                                                      |
| Database       | <img src="https://upload.wikimedia.org/wikipedia/commons/2/29/Postgresql_elephant.svg" width="25"/> PostgreSQL                                                                                                                                                        |
| Infrastructure | <img src="https://www.docker.com/wp-content/uploads/2022/03/vertical-logo-monochromatic.png" width="25"/> Docker, <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Octicons-mark-github.svg" width="25"/> GitHub Actions                                 |
| Testing        | <img src="https://images.seeklogo.com/logo-png/44/1/phpunit-logo-png_seeklogo-440702.png" width="25"/> PHPUnit, <img src="https://cdn.brandfetch.io/idIq_kF0rb/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B" width="25"/> Cypress (planned for e2e)       |
| Other          | <img src="https://icon2.cleanpng.com/20180426/rwq/avt9jszgj.webp" width="25"/> Firebase                                                                                                                                                                               |

---

## 🏗️ Infrastructure

- **3 Docker containers**:
    - `backend` – Symfony + PHP
    - `frontend` – React + Tailwind CSS
    - `db` – PostgreSQL
- **GitHub Actions** workflow to run tests before merging into `master`.
- (Planned) Cypress end-to-end tests.

---

## 🚀 Running the Project

1. **Start the containers**
   ```bash
   docker compose up --build
   ```

2. **Initialize the backend**
   ```bash
   docker exec -it backend ./build-env.sh
   ```

3. **Initialize the frontend**
   ```bash
   docker exec -it frontend ./build-env.sh
   ```

4. **Access the services**:
    - Backend → [http://localhost:8000](http://localhost:8000)
    - Frontend → [http://localhost:3000](http://localhost:3000)

---

## 🧪 Testing

- **Backend** → PHPUnit
- **Frontend** → Cypress *(planned)*

---

## 📌 Notes

- Next steps can be seen here: [Kanban](https://tree.taiga.io/project/navitechno-navipop/kanban)

---
