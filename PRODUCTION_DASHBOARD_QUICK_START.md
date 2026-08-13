# Production Dashboard - Quick Start Guide

## 🚀 How to Access

### Via Sidebar
1. Log in to Eva POS System
2. Left sidebar → Recipe Management
3. Click **"Production Dashboard"**

### Via Direct URL
```
http://localhost:8888/production_dashboard
```

---

## 📊 What Each Tab Does

### 1️⃣ Today's Productions
**Shows**: All batches created TODAY

**Use when**: 
- Checking daily output
- Reviewing today's work
- Quick status check

**Columns**: Batch Code, Recipe, Product, Qty, Cost, Status, Creator, Time

---

### 2️⃣ Date Range Productions
**Shows**: Productions between selected dates

**How to use**:
1. Select **From Date** and **To Date** in filters
2. (Optional) Select Status (Approved/Draft/Cancelled)
3. (Optional) Select Recipe
4. Click **"Apply Filters"**

**Default**: Last 30 days

**Use when**:
- Analyzing production trends
- Monthly/quarterly reports
- Comparing time periods

---

### 3️⃣ Item Usage Report
**Shows**: How many units of a specific item were used in productions

**How to use**:
1. Select **Item** from filter dropdown
2. Select **From Date** and **To Date**
3. Click **"Apply Filters"**

**Example Output**:
```
Item: Flour (IT0001)
Total Used: 500 kg in 25 batches
Used in batches: PROD-2026080901, PROD-2026080902, etc.
```

**Use when**:
- Tracking ingredient consumption
- Planning material purchases
- Checking item usage patterns

---

### 4️⃣ Summary & Totals
**Shows**: Lifetime production statistics

**Displays**:
- Total Batches: 1,245
- Approved: 1,200
- Draft: 35
- Cancelled: 10
- Total Output: 45,621 units
- Total Cost: 2,456,890 Tk
- Average Cost Per Unit: 53.75 Tk

**Use when**:
- KPI tracking
- Historical analysis
- Reporting to management

---

## 🔧 Using Filters

### Available Filters
| Filter | Options | Default |
|--------|---------|---------|
| From Date | Any date | 30 days ago |
| To Date | Any date | Today |
| Status | All, Approved, Draft, Cancelled | All |
| Recipe | All recipes in system | All |
| Item | All items used in recipes | (for Tab 3 only) |

### How to Apply
1. Expand **"Filters & Search"** section (click + button)
2. Select your filters
3. Click **"Apply Filters"** button
4. Wait for data to update (shows in selected tab)

### How to Reset
1. Click **"Reset"** button
2. Page reloads with default filters

---

## 💾 Exporting Data

### To Export Productions
1. Set filters (date range, status, recipe)
2. Click **"Export CSV"** button
3. File downloads: `productions_YYYY-MM-DD_HH-MM-SS.csv`
4. Open in Excel/Google Sheets

### To Export Item Usage
1. Select an item from dropdown
2. Set date range
3. Click **"Export CSV"** button
4. File downloads: `item_usage_YYYY-MM-DD_HH-MM-SS.csv`

---

## 📈 Understanding the Data

### Status Explained

| Status | Color | Meaning |
|--------|-------|---------|
| **Approved** | Green | Batch is approved and ingredients deducted |
| **Draft** | Yellow | Batch created but not yet approved |
| **Cancelled** | Red | Batch was cancelled |

### Quantities Explained

| Field | Meaning | Example |
|-------|---------|---------|
| Batch Qty | Number of "batches" produced | 10 (batches) |
| Total Output | Total units produced | 1,000 (units) |
| Cost Per Unit | Average cost to produce 1 unit | 5.50 Tk |

---

## ❓ Common Questions

### Q: Why doesn't today show any productions?
**A**: Check if any batches were created/approved today. If not, select the "Date Range" tab and adjust the date range.

### Q: How do I see a specific recipe's production?
**A**: Go to "Date Range" tab, select the recipe in dropdown, apply filters.

### Q: Can I see which item is running low based on usage?
**A**: Yes! Use "Item Usage Report" tab to see consumption patterns. High consumption = item running low.

### Q: What does "Cost Per Unit" mean?
**A**: Total production cost ÷ Total units produced. Lower is better!

### Q: Can I see individual batch details?
**A**: Click the batch code to go to Production List and view details.

---

## ⚙️ Settings & Permissions

### Who Can Access?
Users with **"production_view"** permission

### How to Grant Access
1. Admin → User Management
2. Select user
3. Check **"Production View"** permission
4. Save

---

## 🎯 Common Use Cases

### Use Case 1: Daily Standup
→ **Today's Productions** tab
Shows: What was produced, by whom, status, cost

### Use Case 2: Monthly Report
→ **Date Range** tab
Set dates to current month, export CSV

### Use Case 3: Ingredient Planning
→ **Item Usage Report** tab
Select ingredient, see consumption trends

### Use Case 4: Cost Analysis
→ **Summary & Totals** tab
Check average cost per unit, identify expensive batches

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Dashboard not showing | Check permission, try refreshing |
| Filters not working | Ensure dates are in YYYY-MM-DD format |
| CSV export fails | Check browser download settings |
| No data in tabs | Check date range, may need to adjust |
| Numbers seem wrong | Verify batches are "Approved" status |

---

## 📞 Need Help?

1. **Check the filters** - Adjust date range
2. **Check permission** - Admin needs to grant "production_view"
3. **Check database** - Ensure productions exist in date range
4. **Ask IT/Development** - They can check server logs

---

**Happy tracking!** 📊✨

