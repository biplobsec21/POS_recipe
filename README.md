# Stock History & Ledger Module for CodeIgniter

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3.x-orange.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)

A comprehensive stock tracking and transaction history module that provides complete audit trails for inventory movements in CodeIgniter applications.

## ✨ Features

### 📊 Core Functionality
- **Complete Stock Ledger** - Track all inventory movements with running balances
- **Multi-source Integration** - Pulls data from purchases, sales, returns, adjustments, and production
- **Real-time Calculations** - Automatic stock level calculations
- **Visual Stock Alerts** - Color-coded low stock warnings

### 🔄 Transaction Types
- ✅ Purchases (Stock In)
- ✅ Sales (Stock Out) 
- ✅ Sales Returns (Stock In)
- ✅ Purchase Returns (Stock Out)
- ✅ Stock Adjustments
- ✅ Production Output/Consumption
- ✅ Stock Transfers

### 💻 User Interface
- **Responsive Design** - Works on desktop and mobile
- **DataTables Integration** - Advanced sorting, filtering, pagination
- **Export Capabilities** - Excel, PDF, CSV, Print
- **Color-coded Transactions** - Visual indicators for different types

## 🚀 Quick Start

### Installation

1. **Copy Files**
   ```bash
   # Copy to your CodeIgniter application
   application/controllers/Items.php → Add new methods
   application/models/Stock_history_model.php → New file
   application/views/stock_history.php → New file