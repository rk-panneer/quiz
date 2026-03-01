# 🧠 QuizLabs Professional - Marketing Engagement Ecosystem

A high-performance, production-ready Quiz System built with the latest **Laravel 12** and **Filament 4** stack. Designed for deep user engagement, robust data collection, and scalable management.

---

## 🛠️ Technical Architecture

This system follows modern engineering patterns to ensure maintainability and performance:

- **Unified Frontend (Livewire 3 + Alpine.js)**: A single-page-like experience for quiz takers with real-time state synchronization.
- **Premium Admin Panel (Filament 4)**: A state-of-the-art management suite for quizzes, questions, and analytics.
- **Encapsulated Scoring Engine**: A dedicated `QuizScoringService` handles complex arithmetic server-side, ensuring integrity.
- **Atomic Migrations**: Scalable database schema designed for high-concurrency attempts and complex question relations.
- **Security-First**: Integrated ownership checks, session-bound attempts, and strict input validation.

---

## ✨ Features

### 👤 Public Experience
- **Fluid UI**: Glassmorphic, highly responsive interface with subtle micro-animations.
- **Real-time Navigation**: Instant question switching with state persistence.
- **Premium Results**: Dynamic score visualization with achievement rankings and social propagation (X, WhatsApp).
- **Validation**: Strict email and required-field validation for lead quality.

### 🛡️ Admin Suite
- **Interactive Dashboard**: Real-time stats on participation and global performance.
- **Complex CRUD**: Full management of Quizzes and Question Banks.
- **Smart Relations**: Automated question ordering and choice management.
- **Advanced Export**: Streamed CSV export module with IP masking and data sanitization.

---

## 🚀 Quick Start

### Prerequisites
- **PHP**: 8.2+
- **Composer**
- **Node.js**: 18+ (with npm)
- **Database**: MySQL 8.0 / MariaDB 10.3+

### Installation Steps
1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd quiz
   ```

2. **Backend Setup**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**:
   Update your `.env` with your DB credentials, then run:
   ```bash
   php artisan migrate --seed
   ```

4. **Frontend Asset Compiling**:
   ```bash
   npm install
   npm run build
   ```

5. **Serve the Application**:
   ```bash
   php artisan serve
   ```

---

## 🧑‍💻 Access Credentials

### Admin Dashboard
Access the panel at: `http://127.0.0.1:8000/admin`

| Property | Value |
| :--- | :--- |
| **Email** | `admin@admin.com` |
| **Password** | `12345678` |

---

## 🧪 Supported Question Logic

| Question Type | Description |
| :--- | :--- |
| **MCQ Single** | Classical single-choice validation with weighted scoring. |
| **MCQ Multiple** | Complex selection with summed option points. |
| **Boolean** | High-conversion True/False logic. |
| **Number Range** | Intelligent scoring based on proximity (Min/Max bands). |
| **Text Keywords** | NLP-lite scoring based on keyword density in prose input. |

---

## 🛡️ Security & Performance Standards

- **Ownership Locking**: Quiz attempts are cryptographically bound to browser sessions to prevent tampering.
- **Logic Hiding**: Answer keys and point weights are never exposed via the API or Frontend scripts.
- **CSV Streaming**: Exports utilize `league/csv` streaming to handle thousands of records with minimal memory footprint.
- **Optimizer Ready**: Pre-tuned for `artisan optimize` with high-performance routing.

---

*Developed by Panneer R*
