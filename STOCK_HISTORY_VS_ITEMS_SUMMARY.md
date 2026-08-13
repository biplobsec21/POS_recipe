# Stock History "Current Stock" vs Items Table "stock" - Discrepancy Report

**Date**: August 13, 2026  
**Purpose**: Compare what Stock History page shows vs what Items table has  
**Total Items with Discrepancies**: 173

---

## Understanding the Comparison

### Stock History "Current Stock"
- **Where**: Shown on each item's Stock History page at top
- **How Calculated**: Running balance of all transactions (purchases - sales + returns)
- **Source**: Stock_history_model.get_stock_summary()

### Items Table "stock"
- **Where**: db_items.stock column
- **How Calculated**: Manual entries + purchases - sales (using Pos_model)
- **Source**: db_items table

---

## Top 10 Discrepancies

| Rank | Item Code | Item Name | Items Table | Stock History | Difference |
|------|-----------|-----------|-------------|---|---|
| 1 | IT0091 | Nomal Stekar | 90,516.27 | 0.00 | **+90,516.27** |
| 2 | IT0090 | Special stekar | 24,625.00 | 0.00 | **+24,625.00** |
| 3 | IT0097 | ST peket | 15,950.00 | 0.00 | **+15,950.00** |
| 4 | IT0017 | Cake box 1-6 lbs | 5,532.50 | 0.00 | **+5,532.50** |
| 5 | IT0042 | Dim / egg | -25.91 | 3,500.00 | **-3,525.91** |
| 6 | IT0127 | Saddamban Regular | 2,329.00 | 0.00 | **+2,329.00** |
| 7 | IT0135 | Jhal patis Regular | 1,609.73 | 0.00 | **+1,609.73** |
| 8 | IT0181 | Special Box Dry Cake | 1,259.00 | 0.00 | **+1,259.00** |
| 9 | IT0099 | senduice peket | 1,175.00 | 0.00 | **+1,175.00** |
| 10 | IT0095 | Poster pepar | 1,574.50 | 500.00 | **+1,074.50** |

---

## Pattern Analysis

### Pattern 1: OVERSTATED (Items Table too HIGH)

**Items Table > Stock History Current Stock**

Examples:
```
IT0091: Items=90,516.27  vs  Stock History=0.00  Difference: +90,516.27
IT0090: Items=24,625.00  vs  Stock History=0.00  Difference: +24,625.00
IT0017: Items=5,532.50   vs  Stock History=0.00  Difference: +5,532.50
```

**Meaning**: System thinks we have MORE stock than actual transactions show

**Root Cause**: Items table includes manual entries from db_stockentry that have NO corresponding purchase transactions

### Pattern 2: UNDERSTATED (Items Table too LOW)

**Items Table < Stock History Current Stock**

Examples:
```
IT0042: Items=-25.91     vs  Stock History=3,500.00   Difference: -3,525.91
IT0015: Items=9.50       vs  Stock History=72.00      Difference: -62.50
IT0016: Items=6.50       vs  Stock History=66.00      Difference: -59.50
IT0007: Items=1.04       vs  Stock History=30.00      Difference: -28.96
IT0063: Items=8.49       vs  Stock History=12.00      Difference: -3.51
```

**Meaning**: System thinks we have LESS stock than transactions show

**Root Cause**: Items table wasn't updated properly when purchases were added

### Pattern 3: PERFECT MATCH

**Items Table = Stock History Current Stock**

Approximately 37 items have ZERO discrepancy (within 0.01 units)

---

## Summary Statistics

**Total Items Analyzed**: ~210  
**Items with Discrepancies**: 173 (82.4%)  
**Items Matching**: 37 (17.6%)

**Total Overstated**: 165 items  
**Total Understated**: 8 items

---

## Key Insight

The huge gap between Items Table and Stock History reveals:

1. **Manual entries dominate**: Many items in db_stockentry have NO corresponding purchases
2. **Stock History is more accurate**: It only counts official transactions
3. **Items table is unreliable**: It mixes manual with official data

---

## What This Means

When users view an item's Stock History page:
- **Current Stock**: Shows what TRANSACTIONS say (accurate)
- **Items List**: Shows cached value which may be WRONG (unreliable)

They see **TWO DIFFERENT NUMBERS** for the same item!

---

## SQL File

Run this to regenerate the report:
```bash
mysql < SIMPLE_STOCK_DISCREPANCY.sql
```

---

**Result**: Implement the stock_history-based Pos_model.update_items_quantity() to fix this!

