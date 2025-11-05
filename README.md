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


   # Customer Ledger Management System

A comprehensive customer account management system built with CodeIgniter that provides detailed financial tracking, transaction history, and advanced reporting capabilities.

## 🌟 Features

### 🎯 Customer Management
- **Global Customer Selection** - Always-visible dropdown for quick customer switching
- **Complete Customer Profiles** - Name, mobile, code, address, and contact details
- **Quick Search & Filter** - Find customers instantly with search functionality

### 💰 Financial Tracking
- **Complete Ledger System** - Track all customer transactions with running balances
- **DR/CR Calculations** - Automatic debit/credit balance tracking
- **Account Summary** - Real-time financial overview including:
  - Opening Balance
  - Total Sales
  - Total Received Payments
  - Balance Due

### 📊 Transaction Types
- **Sales Invoices** - Complete sales transaction tracking
- **Customer Payments** - Payment receipt management
- **Sales Returns** - Return transaction handling
- **Opening Balances** - Initial account setup
- **Return Payments** - Refund processing

### 🎨 User Interface
- **Responsive Design** - Works seamlessly on desktop and mobile
- **Interactive Tables** - Click to expand/collapse transaction details
- **Color-coded Labels** - Visual indicators for different transaction types
- **Clean Black & White Design** - Professional and easy to read

### 📈 Advanced Features
- **DataTables Integration** - Advanced sorting, filtering, and pagination
- **Export Capabilities** - Multiple format support:
  - 📄 PDF Export
  - 📊 Excel Export
  - 📋 CSV Export
  - 🖨️ Print Functionality
  - 📑 Copy to Clipboard
  - 👁️ Column Visibility Toggle
- **Date Range Filtering** - Custom date period selection with 12-month default
- **Keyboard Shortcuts** - `Ctrl+Shift+C` to quickly access customer dropdown

- **File change**: - application/models/Customer_ledger_model.php
                   - application/views/customer_ledger.php
                   - application/controllers/Customers.php