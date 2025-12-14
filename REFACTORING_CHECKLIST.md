# REFACTORING CHECKLIST
## Phase-by-Phase Implementation Guide

---

## 📋 PHASE 1: CONSOLIDATE VIEWS & EXTRACT ASSETS (Week 1)

### Step 1.1: Backup Current State
- [ ] Commit current code to git with message "backup: before refactoring"
- [ ] Create branch `refactor/phase-1-consolidate-views`
- [ ] Verify all changes are committed (git status clean)

```bash
git add .
git commit -m "backup: before refactoring phase 1"
git checkout -b refactor/phase-1-consolidate-views
```

### Step 1.2: Create Directory Structure
- [ ] Create `resources/views/invoices/components/` directory
- [ ] Create `resources/css/components/` directory
- [ ] Create `public/js/modules/` directory
- [ ] Create `public/js/utils/` directory

```bash
mkdir -p resources/views/invoices/components
mkdir -p resources/css/components
mkdir -p public/js/modules
mkdir -p public/js/utils
```

### Step 1.3: Extract CSS from Views
- [ ] Extract CSS from `create_optimized.blade.php` → `resources/css/invoice-forms.css`
- [ ] Extract CSS from `edit_gst.blade.php` → `resources/css/invoice-tables.css`
- [ ] Consolidate duplicate keyframes (loading, spin) → `resources/css/components/animations.css`
- [ ] Create `resources/css/components/modals.css`
- [ ] Create `resources/css/components/buttons.css`
- [ ] Update layout to include new CSS files

**CSS Files to Create:**
```
✓ resources/css/components/invoice-forms.css
✓ resources/css/components/invoice-tables.css  
✓ resources/css/components/invoice-modals.css
✓ resources/css/components/animations.css
✓ resources/css/components/buttons.css
```

- [ ] Remove `<style>` blocks from all Blade files
- [ ] Verify styles still work (check in browser)

### Step 1.4: Extract JavaScript from Views
- [ ] Extract JS from view files → `public/js/modules/`
- [ ] Create `public/js/modules/FormValidator.js`
- [ ] Create `public/js/modules/InvoiceCalculator.js`
- [ ] Create `public/js/modules/ApiHandler.js`
- [ ] Create `public/js/modules/UIManager.js`
- [ ] Create `public/js/utils/helpers.js`
- [ ] Create `public/js/utils/constants.js`
- [ ] Create `public/js/app.js` as main entry point

**JavaScript Files to Create:**
```
✓ public/js/modules/FormValidator.js
✓ public/js/modules/InvoiceCalculator.js
✓ public/js/modules/ApiHandler.js
✓ public/js/modules/UIManager.js
✓ public/js/utils/helpers.js
✓ public/js/utils/constants.js
✓ public/js/app.js
```

- [ ] Update layout to include new JS files
- [ ] Remove `<script>` blocks from all Blade files
- [ ] Test JavaScript functionality (check browser console)
- [ ] Verify no JavaScript errors in browser

### Step 1.5: Create Reusable Components
- [ ] Create `resources/views/invoices/components/form-header.blade.php`
- [ ] Create `resources/views/invoices/components/items-section.blade.php`
- [ ] Create `resources/views/invoices/components/gst-section.blade.php`
- [ ] Create `resources/views/invoices/components/totals-section.blade.php`
- [ ] Create `resources/views/invoices/components/payment-section.blade.php`
- [ ] Create `resources/views/invoices/components/modals.blade.php`

**Component Files to Create:**
```
✓ resources/views/invoices/components/form-header.blade.php
✓ resources/views/invoices/components/items-section.blade.php
✓ resources/views/invoices/components/gst-section.blade.php
✓ resources/views/invoices/components/totals-section.blade.php
✓ resources/views/invoices/components/payment-section.blade.php
✓ resources/views/invoices/components/payment-section.blade.php
✓ resources/views/invoices/components/modals.blade.php
```

- [ ] Test each component independently in browser

### Step 1.6: Consolidate Invoice Views
- [ ] Copy `create_optimized.blade.php` → `create.blade.php`
- [ ] Update `create.blade.php` to use components
- [ ] Add conditional logic for invoice types
- [ ] Test GST invoice creation
- [ ] Test non-GST invoice creation
- [ ] Copy `edit_gst.blade.php` → `edit.blade.php`
- [ ] Update `edit.blade.php` to use components
- [ ] Test GST invoice editing
- [ ] Test non-GST invoice editing

**Views to Consolidate:**
```
create_optimized.blade.php → create.blade.php ✓
create_non_gst.blade.php → CONSOLIDATE INTO create.blade.php ✓
edit_gst.blade.php → edit.blade.php ✓
edit_non_gst.blade.php → CONSOLIDATE INTO edit.blade.php ✓
```

### Step 1.7: Delete Duplicate Files
- [ ] Delete `resources/views/invoices/create_non_gst.blade.php`
- [ ] Delete `resources/views/invoices/create_optimized.blade.php`
- [ ] Delete `resources/views/invoices/edit_non_gst.blade.php`
- [ ] Delete `resources/views/invoices/index_non_gst.blade.php`
- [ ] Delete `resources/views/invoices/show_non_gst.blade.php`
- [ ] Delete `resources/views/invoices/pdf_non_gst.blade.php`

**Files to Delete:**
```
✗ create_optimized.blade.php
✗ create_non_gst.blade.php
✗ edit_non_gst.blade.php
✗ index_non_gst.blade.php
✗ show_non_gst.blade.php
✗ pdf_non_gst.blade.php
```

### Step 1.8: Testing Phase 1
- [ ] Run Laravel tests: `php artisan test`
- [ ] Check all invoice create/edit pages work
- [ ] Verify GST calculations correct
- [ ] Verify non-GST calculations correct
- [ ] Check CSS loads properly
- [ ] Check JavaScript loads properly
- [ ] Test form validation
- [ ] Test adding/removing invoice items
- [ ] Check no JavaScript errors in console
- [ ] Test on mobile (responsive design)

**Browser Testing:**
```
✓ Create GST Invoice - Full form
✓ Create Non-GST Invoice - Full form
✓ Edit GST Invoice - All fields editable
✓ Edit Non-GST Invoice - All fields editable
✓ Add invoice item - Row appears, calculation updates
✓ Remove invoice item - Row removed, calculation updates
✓ Form validation - Shows errors for required fields
✓ Tax calculation - CGST/SGST correct for GST type
✓ No tax calculation - Non-GST type has no tax
✓ Responsive - Mobile layout works
```

### Step 1.9: Commit Phase 1
- [ ] Review changes: `git diff`
- [ ] Stage files: `git add .`
- [ ] Commit with message: `git commit -m "refactor(phase-1): consolidate views and extract assets"`
- [ ] Push branch: `git push origin refactor/phase-1-consolidate-views`
- [ ] Create Pull Request for team review

---

## 🛠️ PHASE 2: CREATE SERVICE LAYER (Week 2)

### Step 2.1: Create Service Classes
- [ ] Create `app/Services/InvoiceCalculationService.php`
- [ ] Create `app/Services/InvoiceValidationService.php`
- [ ] Create `app/Services/InvoiceService.php`
- [ ] Create `app/Enums/InvoiceType.php`
- [ ] Create `app/Enums/InvoiceStatus.php`

**Service Files to Create:**
```
✓ app/Services/InvoiceCalculationService.php
✓ app/Services/InvoiceValidationService.php
✓ app/Services/InvoiceService.php
✓ app/Enums/InvoiceType.php
✓ app/Enums/InvoiceStatus.php
```

### Step 2.2: Refactor InvoiceController
- [ ] Split duplicate methods (use single method with type parameter)
- [ ] Inject services via constructor
- [ ] Remove business logic (move to services)
- [ ] Simplify controller methods
- [ ] Update method names to be cleaner

**Controller Refactoring:**
```
OLD: indexGst(), indexNonGst()
NEW: index(Request $request)

OLD: createGst(), createNonGst()
NEW: create(Request $request)

OLD: storeGst(), storeNonGst()
NEW: store(InvoiceStoreRequest $request)

OLD: editGst(), editNonGst()
NEW: edit(Invoice $invoice)

OLD: updateGst(), updateNonGst()
NEW: update(Invoice $invoice, InvoiceStoreRequest $request)
```

### Step 2.3: Create Request Classes
- [ ] Update/Create `app/Http/Requests/InvoiceStoreRequest.php`
- [ ] Move validation rules from controller to request class
- [ ] Add type safety with method type hints

### Step 2.4: Create Exception Classes
- [ ] Create `app/Exceptions/InvoiceException.php`
- [ ] Create `app/Exceptions/StockException.php`
- [ ] Use exceptions instead of error returns

### Step 2.5: Testing Phase 2
- [ ] Run Laravel tests: `php artisan test`
- [ ] Test invoice creation via API
- [ ] Test validation works correctly
- [ ] Test service calculations
- [ ] Verify no functionality regression

### Step 2.6: Commit Phase 2
- [ ] Create new branch: `refactor/phase-2-service-layer`
- [ ] Commit changes
- [ ] Push and create PR

---

## 📝 PHASE 3: ADD TESTS & DOCUMENTATION (Week 3)

### Step 3.1: Create Unit Tests
- [ ] Create `tests/Unit/Services/InvoiceCalculationServiceTest.php`
- [ ] Test tax calculations
- [ ] Test discount application
- [ ] Test grand total calculation

### Step 3.2: Create Feature Tests
- [ ] Create `tests/Feature/Http/Controllers/InvoiceControllerTest.php`
- [ ] Test invoice creation
- [ ] Test invoice editing
- [ ] Test invoice deletion
- [ ] Test validation errors

### Step 3.3: Add Code Documentation
- [ ] Add PHPDoc to all service methods
- [ ] Add comments to complex logic
- [ ] Update README with new structure
- [ ] Document API endpoints

### Step 3.4: Code Quality Checks
- [ ] Run PHPStan: `./vendor/bin/phpstan analyze app`
- [ ] Run PHP Insights: `php artisan insights`
- [ ] Fix any issues found
- [ ] Verify test coverage >= 60%

### Step 3.5: Commit Phase 3
- [ ] Create new branch: `refactor/phase-3-tests-docs`
- [ ] Commit changes
- [ ] Push and create PR

---

## 🎉 FINAL: MERGE & DEPLOY

### Step 4.1: Code Review
- [ ] Schedule review meetings
- [ ] Address reviewer feedback
- [ ] Make requested changes

### Step 4.2: Merge to Main
- [ ] Approve PRs after review
- [ ] Merge to develop branch
- [ ] Run full test suite
- [ ] Merge to main if stable

### Step 4.3: Deploy
- [ ] Deploy to staging environment
- [ ] Final QA testing on staging
- [ ] Deploy to production (if approved)
- [ ] Monitor for errors/issues

### Step 4.4: Documentation
- [ ] Update project documentation
- [ ] Add new team member onboarding guide
- [ ] Document conventions and patterns used
- [ ] Create architecture decision record (ADR)

---

## 🔍 VALIDATION CHECKLIST

### Functionality Tests
- [ ] All original features still work
- [ ] No regression in existing features
- [ ] Performance is same or better
- [ ] No JavaScript errors in console
- [ ] No CSS styling issues
- [ ] All forms submit correctly
- [ ] All calculations correct
- [ ] All validations work

### Code Quality Tests
- [ ] No code duplication
- [ ] File sizes under 300 lines (except views: <500)
- [ ] All code follows naming standards
- [ ] All code properly documented
- [ ] All tests pass
- [ ] Code coverage >= 60%
- [ ] No PHP warnings/errors
- [ ] No console errors/warnings

### Performance Tests
- [ ] Page load time acceptable
- [ ] No memory leaks
- [ ] CSS/JS properly minified
- [ ] No duplicate asset loading
- [ ] Database queries optimized

---

## 📊 METRICS TRACKING

### Before Refactoring
```
Invoice views total lines: 10,000+
Code duplication: ~50%
Files > 1000 lines: 2
Test coverage: 0%
Average file size: 500 lines
```

### After Phase 1
```
Invoice views total lines: 2,000
Code duplication: ~20% (CSS/JS still inline)
Files > 1000 lines: 1 (InvoiceController)
Test coverage: 0%
Average file size: 200 lines
```

### After Phase 2
```
Invoice views total lines: 2,000
Code duplication: ~10%
Files > 1000 lines: 0
Test coverage: 0%
Average file size: 150 lines
```

### After Phase 3
```
Invoice views total lines: 2,000
Code duplication: ~5%
Files > 1000 lines: 0
Test coverage: 60%+
Average file size: 150 lines
```

---

## ✅ SIGN-OFF

- [ ] All phases completed
- [ ] All tests passing
- [ ] All code reviewed
- [ ] Documentation updated
- [ ] Team trained on new structure
- [ ] Ready for production deployment

**Completed By:** _________________  
**Date:** _________________  
**Reviewed By:** _________________  
**Approved By:** _________________  

---

## 📞 TROUBLESHOOTING

**Issue:** Tests failing after refactoring  
**Solution:** Check route names haven't changed, verify service injection working

**Issue:** JavaScript not working  
**Solution:** Check console for errors, verify module files included in layout, check for missing semicolons

**Issue:** CSS not loading  
**Solution:** Clear browser cache, verify CSS files in public/css, check asset() helper paths

**Issue:** Validation not working  
**Solution:** Verify request class registered in controller, check validation rules migrated correctly

---

**Status:** READY FOR IMPLEMENTATION  
**Last Updated:** December 14, 2025
