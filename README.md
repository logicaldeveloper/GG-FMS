# Personal Finance Tracker

A simple, mobile-friendly personal finance tracker that helps you log daily expenses, categorize them, and visualize spending patterns.

---

## 📋 Features

- ✅ Expense Entry Form (Web)
- ✅ Dashboard with Interactive Filters
- ✅ Pie Chart Visualization (Expense Category + Credit/Savings)
- ✅ JSON-based Data Storage
- ✅ Mobile Responsive Design
- ✅ Supports Filtering by:
  - Expense Category
  - Payment Mode
  - Source (Savings vs Credit)

---

## 🧩 Technologies Used

| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP (for form handling) |
| Data Storage | JSON file (`expenses.json`) |
| Charting | Chart.js |
| Hosting | Local or Shared Hosting (e.g., XAMPP, WAMP, cPanel)

---

## 📁 File Structure
personal-finance-tracker/
│
├── index.html # Expense Entry Form
├── dashboard.php # Dashboard with filters and charts
├── expenses.json # Expense data storage
├── api.php # PHP backend to add expense
└── README.md # This file


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

## 📊 Dashboard Features

### Filters
- **Expense Category** (e.g., Food & Beverages, Groceries)
- **Payment Mode** (e.g., UPI, Credit Card)
- **Source** (`cr_dr_category`):
  - Expense from Savings
  - Expense from Credit

### Visualizations
- **Pie Chart 1**: Spending by Expense Category
- **Pie Chart 2**: Spending by Source (Savings vs Credit)

### Dynamic Total
- Total amount updates based on applied filters

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

## 📦 Want Me to Package Everything?

Would you like me to:
- Package all files into a **downloadable ZIP**?
- Help you **deploy this online**?
- Add a **setup guide** for Raspberry Pi or Android Termux?

Let me know and I’ll help you take this to the next level! 😊

---

## 🙌 Final Words

You've built something powerful and flexible — great job sticking with it and making it your own!

Have questions or need help?  
Reach out anytime — I'm happy to assist. 😊
