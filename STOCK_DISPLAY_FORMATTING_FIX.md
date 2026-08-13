# Stock Display Formatting Fix

**Issue**: Stock history page shows quantities with floating-point precision errors
- **Before**: `Current Stock: 21.090000000001 PCS`
- **After**: `Current Stock: 21.090 PCS` (3 decimal places)

**Date**: August 2026  
**File Modified**: `application/views/stock_history.php`

---

## Changes Made

### 1. Current Stock Display (Product Information Section)

**Location**: Line ~112-114

```php
// BEFORE
<?= $item_info->stock; ?>

// AFTER
<?= number_format((float)$item_info->stock, 3); ?>
```

Also fixed Alert Quantity display on the same section.

---

### 2. Main "Current Stock" Header Display

**Location**: Line ~197 (in "Current Stock Total" box)

```php
// BEFORE
<?= $stock_summary['current_stock']; ?>

// AFTER
<?= number_format((float)$stock_summary['current_stock'], 3); ?>
```

---

### 3. Quantities In Summary Section

**Location**: Lines ~149-153 (Purchase, Opening Stock, Sell Return, Production Output, Transfers In)

All 5 quantities now formatted with 3 decimal places:

```php
// BEFORE
<p>Total Purchase: <span class="quantity-positive"><?= $stock_summary['total_purchase']; ?></span></p>

// AFTER
<p>Total Purchase: <span class="quantity-positive"><?= number_format((float)$stock_summary['total_purchase'], 3); ?></span></p>
```

Applied to:
- Total Purchase
- Opening Stock
- Total Sell Return
- Production Output
- Stock Transfers (In)

---

### 4. Quantities Out Summary Section

**Location**: Lines ~167-172 (Sold, Adjustments, Purchase Return, Production Consumption, Transfers Out, Damaged)

All 6 quantities now formatted with 3 decimal places:

```php
// BEFORE
<p>Total Sold: <span class="quantity-negative"><?= $stock_summary['total_sold']; ?></span></p>

// AFTER
<p>Total Sold: <span class="quantity-negative"><?= number_format((float)$stock_summary['total_sold'], 3); ?></span></p>
```

Applied to:
- Total Sold
- Total Stock Adjustment
- Total Purchase Return
- Production Consumption
- Stock Transfers (Out)
- Total Damaged

---

### 5. Transaction History Table - Quantity Change Column

**Location**: Line ~356-361 (JavaScript render function)

```javascript
// BEFORE
return '<span class="' + className + '">' + sign + parseFloat(data).toFixed(2) + '</span>';

// AFTER
return '<span class="' + className + '">' + sign + parseFloat(data).toFixed(3) + '</span>';
```

Now displays quantity changes with 3 decimal places in the transaction table.

---

### 6. Transaction History Table - New Quantity Column

**Location**: Line ~365-370 (JavaScript render function)

```javascript
// BEFORE
var displayQty = Math.max(0, parseFloat(data)).toFixed(2);

// AFTER
var displayQty = Math.max(0, parseFloat(data)).toFixed(3);
```

Now displays running balance (new quantity) with 3 decimal places.

---

## Impact

### Before Fix
```
Current Stock: 21.090000000001 PCS
Total Purchase: 100.000000001
Running Balance: 45.999999998
```

### After Fix
```
Current Stock: 21.090 PCS
Total Purchase: 100.000 PCS
Running Balance: 46.000 PCS
```

---

## Why This Happened

**Floating-Point Precision Issue**:
- PHP/MySQL store decimal numbers using floating-point arithmetic
- Floating-point cannot represent all decimal values exactly
- Example: 21.09 + 0.000000000001 (accumulated rounding errors)
- Solution: Use `number_format()` to round to acceptable decimal places

**Why 3 Decimal Places?**
- Consistent with system's DECIMAL(20,4) precision (4 decimals for storage)
- Display uses 3 decimals for readability (shows .000 but not .0000)
- Aligns with production fix guide (Issue #4: Float Precision)

---

## Testing

To verify the fix works:

1. Navigate to `items/stock_history/42` (or any item with decimal quantities)
2. Check "Current Stock" value - should show exactly 3 decimal places
3. Check "Quantities In" and "Quantities Out" sections - all values formatted
4. Check "Transaction History" table - Quantity Change and New Quantity columns formatted
5. No more floating-point display errors (like `.090000000001`)

---

## Files Modified

- ✅ `application/views/stock_history.php` - 6 locations updated

## Decimal Precision Standard

This aligns with **Issue #4: Float Precision & Rounding Inconsistencies** from FIX_GUIDES.md:

**Standard**: `DECIMAL(20,4)` across entire system  
**Display**: 3 decimal places for user-friendly view  
**Storage**: 4 decimal places for maximum precision

---

## Future Improvements

1. **Create helper function** - Create a helper to centralize formatting:
```php
// Helper function
function format_quantity($qty, $decimals = 3) {
    return number_format((float)$qty, $decimals);
}

// Usage
<?= format_quantity($stock_summary['current_stock']); ?>
```

2. **Apply to other views** - Similar fixes needed in:
   - Items list view
   - Purchase orders
   - Sales orders
   - Production batches
   - All quantity displays

3. **Configuration** - Make decimal places configurable:
```php
// config/decimals.php
define('DECIMAL_PLACES_DISPLAY', 3);
define('DECIMAL_PLACES_STORAGE', 4);
```

---

## Related Documentation

- [FIX_GUIDES.md - Issue #4: Float Precision & Rounding Inconsistencies](FIX_GUIDES.md#issue-4-float-precision--rounding-inconsistencies)
- [STOCK_CALCULATION_COMPARISON.md](STOCK_CALCULATION_COMPARISON.md)
- [SALES_STOCK_MISMATCH_ANALYSIS.md](SALES_STOCK_MISMATCH_ANALYSIS.md)
