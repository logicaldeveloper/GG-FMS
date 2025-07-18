# Personal Finance Tracker

A simple, mobile-friendly personal finance tracker that helps you log daily expenses, categorize them, and visualize spending patterns.

---

## 📋 Features

| Feature | Description |
|--------|-------------|
| ✅ Expense Entry | Web form to add expenses with category and payment mode |
| ✅ Dashboard | Shows current month's expenses with filters and chart |
| ✅ Maintenance Screen | Backup, restore, and purge expense data |
| ✅ History Dashboard | View expense data from previous months |
| ✅ JSON Storage | Uses `expenses.json` for current month and monthly backups |
| ✅ Mobile Responsive | Works well on phones and desktops |

---

## 🧩 Technologies Used

| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP (for form handling, backup, restore) |
| Data Storage | JSON files (`expenses.json` and monthly backups) |
| Charting | Chart.js |
| Hosting | Local or Shared Hosting (e.g., XAMPP, WAMP, cPanel) |

---

## 📁 File Structure
personal-finance-tracker/
│
├── index.html # Expense Entry Form
├── dashboard.php # Current month dashboard
├── historyDashboard.php # View old expense data by month
├── expenses.json # Current month's expense data
├── api.php # Add expense logic
├── maintenance.php # Backup, restore, and purge
├── README.md # This file
└── backups/ # Monthly backups stored here
├── Apr-2025.json
├── Mar-2025.json
└── Feb-2025.json

---

## 🚀 How to Run

### Option 1: Local (XAMPP / WAMP)
1. Install XAMPP or WAMP
2. Place all files in `htdocs/your-project-folder/`
3. Start Apache server
4. Open in browser: `http://localhost/your-project-folder/index.html`

### Option 2: Shared Hosting
1. Upload files to your public HTML folder
2. Ensure `expenses.json` is **writable** (chmod 644 or 755)
3. Access via `http://yourdomain.com/your-folder/index.html`

---

## 📝 Expense Entry Form

### Fields:
- **Date**: Date of expense
- **Amount**: Expense amount
- **Payment Mode**:
  - Credit Card
  - Debit Card
  - UPI
  - UPI CC
- **Bank Name**: Optional
- **Expense Category** (Dropdown):
  - Food & Beverages
  - Home Maintenance
  - Groceries
  - Fuel
  - Entertainment
  - Appliance
  - Misc
- **Description**: Short note on the expense

> 💡 The system automatically assigns a `cr_dr_category`:
> - **Debit Card / UPI** → `Expense from Savings`
> - **Credit Card / UPI CC** → `Expense from Credit`

---

## 📊 Dashboard

- Shows only **current month’s expenses**
- Filters by:
  - Expense Category
  - Payment Mode
  - Source (Savings vs Credit)
- Visualizes:
  - Spending by Category
  - Spending by Source (Savings vs Credit)
- Dynamic total updates based on filters

---

## 🔧 Maintenance Screen (`maintenance.php`)

### Features:
- **Backup Expense**:
  - Saves `expenses.json` as `Month-Year.json` (e.g., `May-2025.json`)
  - Dropdown to choose month
- **Purge Expense Data**:
  - Clears current month’s data
  - Only allowed if a backup for the current month exists
  - Confirmation alert before purge
- **Restore Backup**:
  - Dropdown to restore from last 3 months (e.g., `Apr-2025.json`, `Mar-2025.json`)
  - Overwrites `expenses.json` with selected backup

---

## 📜 History Dashboard (`historyDashboard.php`)

### Features:
- Dropdown to select **previous month backups**
- Displays expense data from selected month
- Same filters and charts as dashboard:
  - Expense Category
  - Payment Mode
  - Source (Savings vs Credit)
- Dynamic total and chart updates based on selected month

---

## 🛠️ Future Enhancements

### 🔐 Login System
- Add user authentication using PHP sessions or Firebase
- Store user-specific data in separate JSON files or database

### 💬 WhatsApp Integration (Free Option)
- Use **Twilio WhatsApp API** (trial available)
- Parse incoming messages like:  
  `2025-04-05, 250, UPI, PhonePe, Dinner, Food & Beverages`
- Auto-assign `cr_dr_category` based on payment mode

### 📊 Export to CSV
- Add a button to export filtered data to CSV

### 📱 PWA Support
- Add `manifest.json` and service worker for offline use

---