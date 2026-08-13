# Production Dashboard Design Recommendation

**Date**: August 13, 2026  
**Request**: Create a production page with 4 features  
**Analysis**: Review existing structure + current implementation

---

## Feature Requirements Analysis

### 1. Today's Productions
- Show all production batches created/approved TODAY
- Quick view of daily output
- Status: Draft, Approved, Cancelled

### 2. Total Productions  
- Lifetime total production
- Summary statistics
- Total units produced across all batches

### 3. Custom Date Range Productions
- Filter productions between two dates
- Show quantity, cost, status
- Exportable results

### 4. Item Usage Report
- For a specific item: How many units consumed in productions?
- Within selected date range
- Show which recipes used it

---

## Current System Analysis

### Existing Tables

```
production_batches (main table)
├── id, batch_code, recipe_id, batch_quantity
├── produced_quantity, status (Draft/Approved/Cancelled)
├── created_at, approved_at, created_by, approved_by
└── total_cost, cost_per_unit

recipes
├── id, recipe_name, output_product_id
├── yield_quantity, overhead_cost
└── created_by, created_at

inventory_movements (tracks consumption)
├── item_id, qty, type (PRODUCTION_CONSUME/OUTPUT)
├── reference_id, created_at
└── created_by

recipe_items (ingredients)
├── recipe_id, item_id, quantity_per_batch
└── unit_id
```

### Existing Views/Pages

- ✅ Production List (production/list.php) - Shows all productions
- ✅ Production View (production/view.php) - Individual batch details
- ✅ Production Add/Edit (production/add.php, edit.php) - Create/modify
- ✅ Production Report (production_report.php) - Already has date filtering

### Existing Controller Methods

- Production::index() - List all
- Production::ajax_list() - DataTables integration
- Production::view($id) - Individual batch
- Production::approve($id) - Approve batch

---

## Design Recommendation

### ✅ SINGLE PAGE APPROACH (RECOMMENDED)

**Reason**: All 4 features are closely related and benefit from unified filtering

**Page Name**: `production-dashboard.php`  
**Route**: `production/dashboard`  
**Controller Method**: `Production::dashboard()`

### Structure

```
┌─────────────────────────────────────────────────┐
│      PRODUCTION DASHBOARD                       │
├─────────────────────────────────────────────────┤
│                                                 │
│  QUICK STATS SECTION (Top Cards)               │
│  ┌──────────────┬──────────────┬──────────────┐
│  │ Today's Prod │ Total Prod   │ This Month   │
│  │   Count      │   Count      │   Output     │
│  └──────────────┴──────────────┴──────────────┘
│                                                 │
│  FILTER SECTION (Collapsible)                  │
│  ┌──────────────────────────────────────────┐
│  │ From Date: [___] To Date: [___]          │
│  │ Item: [Select Item] Status: [Select]    │
│  │ [Apply Filter] [Reset] [Export]          │
│  └──────────────────────────────────────────┘
│                                                 │
│  TAB 1: TODAY'S PRODUCTIONS                   │
│  ┌──────────────────────────────────────────┐
│  │ Table: Today's batches with status      │
│  │ Quick view of current day activity       │
│  └──────────────────────────────────────────┘
│                                                 │
│  TAB 2: DATE RANGE PRODUCTIONS               │
│  ┌──────────────────────────────────────────┐
│  │ Table: Batches in selected date range   │
│  │ Detailed info: qty, cost, status        │
│  └──────────────────────────────────────────┘
│                                                 │
│  TAB 3: ITEM USAGE REPORT                     │
│  ┌──────────────────────────────────────────┐
│  │ Item: [Selected Item]                    │
│  │ Date Range: From-To                      │
│  │ Table: Usage in each production batch    │
│  │ Total: X units consumed in Y batches    │
│  └──────────────────────────────────────────┘
│                                                 │
│  TAB 4: TOTALS & SUMMARY                      │
│  ┌──────────────────────────────────────────┐
│  │ Total Batches: X                         │
│  │ Total Output: X units                    │
│  │ Total Cost: X Taka                       │
│  │ Average Cost Per Unit: X Taka           │
│  └──────────────────────────────────────────┘
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## Page Layout Details

### Section 1: Quick Stats (Top of Page)

```html
<div class="row">
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-cubes"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Today's Productions</span>
        <span class="info-box-number">12</span>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Approved Today</span>
        <span class="info-box-number">8</span>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-history"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Batches</span>
        <span class="info-box-number">1,245</span>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-orange"><i class="fa fa-cubes"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Output</span>
        <span class="info-box-number">45,621</span>
      </div>
    </div>
  </div>
</div>
```

### Section 2: Filters

```html
<div class="box box-primary collapsed">
  <div class="box-header with-border">
    <h3 class="box-title">
      <i class="fa fa-filter"></i> Filters & Search
    </h3>
    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse">
        <i class="fa fa-plus"></i>
      </button>
    </div>
  </div>
  
  <div class="box-body" style="display:none;">
    <form id="filter-form">
      <div class="row">
        <div class="col-md-2">
          <label>From Date</label>
          <input type="text" class="form-control datepicker" id="from_date">
        </div>
        
        <div class="col-md-2">
          <label>To Date</label>
          <input type="text" class="form-control datepicker" id="to_date">
        </div>
        
        <div class="col-md-2">
          <label>Status</label>
          <select class="form-control" id="status">
            <option value="">All</option>
            <option value="Approved">Approved</option>
            <option value="Draft">Draft</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        
        <div class="col-md-2">
          <label>Recipe</label>
          <select class="form-control" id="recipe_id">
            <option value="">All Recipes</option>
            [Dynamic options from DB]
          </select>
        </div>
        
        <div class="col-md-2">
          <label>Item Used</label>
          <select class="form-control" id="item_id">
            <option value="">All Items</option>
            [Dynamic options from DB]
          </select>
        </div>
        
        <div class="col-md-2">
          <label>&nbsp;</label>
          <button type="button" class="btn btn-primary form-control" onclick="applyFilter()">
            <i class="fa fa-search"></i> Apply
          </button>
        </div>
      </div>
      
      <div class="row" style="margin-top: 10px;">
        <div class="col-md-12">
          <button type="button" class="btn btn-default btn-sm" onclick="resetFilter()">
            <i class="fa fa-refresh"></i> Reset
          </button>
          <button type="button" class="btn btn-success btn-sm" onclick="exportData()">
            <i class="fa fa-download"></i> Export Excel
          </button>
          <button type="button" class="btn btn-info btn-sm" onclick="printReport()">
            <i class="fa fa-print"></i> Print
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
```

### Section 3: Tab Navigation

```html
<div class="nav-tabs-custom">
  <ul class="nav nav-tabs">
    <li class="active">
      <a href="#today" data-toggle="tab">
        <i class="fa fa-clock-o"></i> Today's Productions
      </a>
    </li>
    
    <li>
      <a href="#daterange" data-toggle="tab">
        <i class="fa fa-calendar"></i> Date Range
      </a>
    </li>
    
    <li>
      <a href="#itemusage" data-toggle="tab">
        <i class="fa fa-cubes"></i> Item Usage
      </a>
    </li>
    
    <li>
      <a href="#summary" data-toggle="tab">
        <i class="fa fa-bar-chart"></i> Summary
      </a>
    </li>
  </ul>
  
  <div class="tab-content">
    <!-- Tab content here -->
  </div>
</div>
```

---

## Implementation Plan

### Phase 1: Create Base Page Structure
- [ ] Create controller method: `Production::dashboard()`
- [ ] Create view: `production/dashboard.php`
- [ ] Add routes in config

### Phase 2: Add Quick Stats
- [ ] Create methods in Production_model:
  - `get_today_productions_count()`
  - `get_approved_today_count()`
  - `get_total_productions_count()`
  - `get_total_output_quantity()`

### Phase 3: Add Filtering
- [ ] Create methods in Production_model:
  - `get_productions_by_date_range($from, $to)`
  - `get_production_summary($filters)`

### Phase 4: Add Tab Content
- [ ] Tab 1: Today's productions
- [ ] Tab 2: Date range productions
- [ ] Tab 3: Item usage report
- [ ] Tab 4: Totals & summary

### Phase 5: Add Export/Print
- [ ] Export to Excel functionality
- [ ] Print-friendly layout

---

## Database Queries Needed

### Query 1: Today's Productions
```sql
SELECT * FROM production_batches 
WHERE DATE(created_at) = CURDATE()
ORDER BY created_at DESC;
```

### Query 2: Production Count in Date Range
```sql
SELECT COUNT(*) as total 
FROM production_batches 
WHERE DATE(created_at) BETWEEN ? AND ?;
```

### Query 3: Item Usage in Productions
```sql
SELECT 
  pb.batch_code,
  r.recipe_name,
  pb.batch_quantity,
  ri.quantity_per_batch as qty_per_unit,
  (pb.batch_quantity * ri.quantity_per_batch) as total_used,
  pb.created_at,
  pb.status
FROM production_batches pb
JOIN recipes r ON pb.recipe_id = r.id
JOIN recipe_items ri ON r.id = ri.recipe_id
WHERE ri.item_id = ? 
  AND DATE(pb.created_at) BETWEEN ? AND ?
ORDER BY pb.created_at DESC;
```

### Query 4: Total Summary
```sql
SELECT
  COUNT(*) as total_batches,
  SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) as approved,
  SUM(produced_quantity) as total_output,
  SUM(total_cost) as total_cost,
  AVG(cost_per_unit) as avg_cost
FROM production_batches
WHERE DATE(created_at) BETWEEN ? AND ?;
```

---

## Benefits of Single Page Approach

✅ **User Flow**: All features accessible in one place  
✅ **Performance**: Single page load instead of multiple  
✅ **Consistency**: Same filters apply across all tabs  
✅ **Simplicity**: Easier navigation with tabs  
✅ **Maintenance**: One file to update vs multiple  

---

## Alternative: Multi-Page Approach (NOT RECOMMENDED)

Would require 4 separate pages:
- `production/daily.php` - Today's only
- `production/total.php` - Lifetime stats
- `production/daterange.php` - Custom range
- `production/itemusage.php` - Item usage

**Disadvantages**:
- ❌ Duplicated filters on each page
- ❌ Inconsistent date handling
- ❌ Harder to maintain
- ❌ More code duplication

---

## Recommendation

### ✅ USE SINGLE PAGE WITH 4 TABS

**File**: `application/views/production/dashboard.php`  
**Controller**: Add method `Production::dashboard()`  
**Model**: Add data retrieval methods to existing models

---

## Next Steps

1. **Design Phase** (now)
   - ✅ Review existing structure
   - ✅ Create wireframe (above)
   - ✅ Plan queries

2. **Implementation Phase** (next)
   - Create controller method
   - Create model methods
   - Create view with tabs
   - Add JavaScript for filtering

3. **Enhancement Phase** (later)
   - Add export functionality
   - Add charts/graphs
   - Add email reports

---

**Ready to proceed with implementation?** Let me know if you want to:
1. Start with the controller method
2. Create the model methods
3. Build the view HTML
4. Or all of the above

