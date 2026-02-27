# 🧪 SmallPay KYC System - Testing Guide

## Quick Start Testing

### 1. Verify Installation

```bash
# Navigate to backend
cd SmallPay_backend

# Check routes
php artisan route:list | grep kyc

# Expected output:
# GET|HEAD admin/kyc ...................................... admin.kyc.index
# GET|HEAD admin/kyc/{kyc} ............................... admin.kyc.show
# POST admin/kyc/{kyc}/approve ........................... admin.kyc.approve
# POST admin/kyc/{kyc}/reject ............................ admin.kyc.reject
```

### 2. Verify Database

```bash
# Check migrations
php artisan migrate:status

# Verify table exists
php artisan tinker
> Schema::hasTable('kycs')
true

# Check sample data
> KYC::count()
> Notification::where('type', 'kyc_approved')->count()
```

### 3. Test Web Interface

#### Visit KYC List Page
```
URL: http://localhost:8000/admin/kyc
Expected:
- Tab navigation (Tous, En Attente, En Examen, Approuvés, Rejetés)
- Statistics cards with counts
- Search bar functional
- KYC list table populated
```

#### Visit KYC Detail Page
```
URL: http://localhost:8000/admin/kyc/1
Expected:
- User info card on left
- Full KYC details
- Document viewer
- Approve button visible (if status not final)
- Reject button visible (if status not final)
```

---

## Detailed Testing Scenarios

### Scenario 1: Create and View KYC (Mobile)

#### Setup
```bash
# Make sure API is running
php artisan serve

# Check API endpoint
curl http://localhost:8000/api/kyc/status
```

#### Test Steps
1. Open mobile app
2. Navigate to KYC Form
3. Fill all required fields:
   - Full Name
   - ID Type
   - ID Number
   - Address, City, Country
   - Upload documents
4. Click "Soumettre KYC"

#### Expected Results
```
✅ Form submitted successfully
✅ API returns 201 with KYC ID
✅ KYC status is "pending"
✅ Notification created in DB
✅ Super admin receives email
```

#### Verify in Backend
```bash
php artisan tinker

> KYC::latest()->first()
// Should see:
// status: "pending"
// created_at: now

> Notification::where('type', 'kyc_approved')->latest()->first()
// Should exist
```

---

### Scenario 2: Super Admin Approves KYC

#### Setup
```bash
# Login as super_admin
- Email: admin@example.com
- Password: password
```

#### Test Steps
1. Navigate to Admin → Vérifications (KYC)
2. See list of pending KYCs
3. Click "Consulter" on a KYC
4. Review all details
5. Click "Approuver" button
6. Confirm in modal
7. See success message

#### Expected Results
```
✅ KYC status changes to "approved"
✅ Approval timestamp recorded
✅ approved_by set to current admin
✅ Notification created: type "kyc_approved"
✅ Email sent to user
✅ User receives mobile notification
```

#### Verify in Backend
```bash
php artisan tinker

// Check KYC status
> $kyc = KYC::find(1);
> $kyc->status
"approved"

> $kyc->approved_at
Carbon\Carbon object

> $kyc->approved_by
1

// Check notification
> Notification::where('user_id', $kyc->user_id)->where('type', 'kyc_approved')->first()
// Should exist with full message
```

---

### Scenario 3: Super Admin Rejects KYC

#### Setup
```bash
# Create test KYC with status "pending" if needed
php artisan tinker
> KYC::create([
    'user_id' => 2,
    'full_name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+237691234567',
    'date_of_birth' => '1990-01-01',
    'id_type' => 'national_id',
    'id_number' => 'TEST123456',
    'address' => 'Test Address',
    'city' => 'Douala',
    'postal_code' => '2700',
    'country' => 'Cameroon',
    'status' => 'pending'
]);
```

#### Test Steps
1. Go to `/admin/kyc`
2. Click "Consulter" on the test KYC
3. Click "Rejeter" button
4. Enter rejection reason: "Document d'identité illisible"
5. Click "Rejeter" button
6. Confirm in browser alert
7. See success message

#### Expected Results
```
✅ KYC status changes to "rejected"
✅ Rejection reason stored
✅ Rejection timestamp recorded
✅ Notification created: type "kyc_rejected"
✅ Email sent with rejection reason
✅ Mobile notification shows reason
```

#### Verify in Backend
```bash
php artisan tinker

> $kyc = KYC::find(1);
> $kyc->status
"rejected"

> $kyc->rejection_reason
"Document d'identité illisible"

> $notification = Notification::where('user_id', $kyc->user_id)
    ->where('type', 'kyc_rejected')
    ->first();
> $notification->message
// Should contain the rejection reason
```

---

### Scenario 4: Filter and Search

#### Test Filters
```bash
# URL with status filter
/admin/kyc?status=pending
Expected: Only pending KYCs shown

/admin/kyc?status=approved
Expected: Only approved KYCs shown

/admin/kyc?status=rejected
Expected: Only rejected KYCs shown
```

#### Test Search
```bash
# By name
/admin/kyc?q=Jean
Expected: KYCs matching "Jean" in full_name

# By email
/admin/kyc?q=test@example.com
Expected: KYC with matching email

# By phone
/admin/kyc?q=691234567
Expected: KYCs with matching phone

# By ID number
/admin/kyc?q=TEST123456
Expected: KYC with matching ID number
```

---

### Scenario 5: Mobile Notification Polling

#### Setup
```bash
# Create NotificationService integration test
// In your mobile test file:
import NotificationService from '@/services/NotificationService';
```

#### Test Steps
1. Approve a KYC from web admin
2. Open mobile app Notifications screen
3. Wait for polling (30 seconds max)
4. See KYC approved notification appear
5. Click notification
6. Verify message and styling

#### Expected Results
```
✅ Notification appears within 30 seconds
✅ Type is "kyc_approved"
✅ Message is correct
✅ Icon is CheckCircle (green)
✅ Background is green (#d1fae5)
✅ Timestamp displays correctly
```

#### Manual API Test
```bash
# Get notifications
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/notifications

# Expected:
{
  "data": [
    {
      "id": 1,
      "type": "kyc_approved",
      "title": "Vérification approuvée",
      "message": "Votre vérification d'identité a été approuvée...",
      "is_read": false,
      "created_at": "2026-02-06T10:35:00"
    }
  ],
  "pagination": {...}
}
```

---

## Automated Testing

### Unit Tests (Laravel)

#### Test KYC Model
```php
// tests/Unit/Models/KYCTest.php

test('can approve kyc', function () {
    $kyc = KYC::factory()->create(['status' => 'pending']);
    $admin = User::factory()->create(['role' => 'super_admin']);
    
    $kyc->approve($admin->id);
    
    expect($kyc->status)->toBe('approved');
    expect($kyc->approved_by)->toBe($admin->id);
    expect($kyc->approved_at)->not->toBeNull();
});

test('can reject kyc', function () {
    $kyc = KYC::factory()->create(['status' => 'pending']);
    $admin = User::factory()->create(['role' => 'super_admin']);
    
    $kyc->reject($admin->id, 'Test reason');
    
    expect($kyc->status)->toBe('rejected');
    expect($kyc->rejection_reason)->toBe('Test reason');
});
```

#### Test Controller
```php
// tests/Feature/KYCControllerTest.php

test('super admin can view kyc list', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $kyc = KYC::factory()->create();
    
    $response = $this->actingAs($admin)
        ->get(route('admin.kyc.index'));
    
    expect($response->status())->toBe(200);
    expect($response->viewData('kycs'))->toContain($kyc);
});

test('non admin cannot approve kyc', function () {
    $user = User::factory()->create(['role' => 'user']);
    $kyc = KYC::factory()->create();
    
    $response = $this->actingAs($user)
        ->post(route('admin.kyc.approve', $kyc));
    
    expect($response->status())->toBe(403);
});
```

### Run Tests
```bash
# Run all KYC tests
./vendor/bin/pest tests/Feature/KYCControllerTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Watch mode
./vendor/bin/pest --watch
```

---

## Performance Testing

### Load Test
```bash
# Using Apache Bench
ab -n 1000 -c 10 http://localhost:8000/admin/kyc

# Expected:
# Requests per second: > 100
# Average response time: < 100ms
```

### Database Query Optimization
```bash
# Check query count
php artisan debugbar

# In controller, verify:
// ✅ Good - uses eager loading
KYC::with(['user', 'approvedBy'])->get()

// ❌ Bad - N+1 queries
KYC::all()->each(fn($kyc) => $kyc->user)
```

---

## Security Testing

### Authorization
```bash
# Test 1: Non-super-admin cannot access
curl -H "Authorization: Bearer USER_TOKEN" \
  http://localhost:8000/admin/kyc
# Expected: 403 Forbidden

# Test 2: Super-admin can access
curl -H "Authorization: Bearer ADMIN_TOKEN" \
  http://localhost:8000/admin/kyc
# Expected: 200 OK
```

### CSRF Protection
```bash
# Test: POST without CSRF token
curl -X POST \
  -d "status=approved" \
  http://localhost:8000/admin/kyc/1/approve
# Expected: 419 Token Mismatch
```

### Input Validation
```bash
# Test: Invalid rejection reason (too short)
curl -X POST \
  -d "rejection_reason=Bad" \
  http://localhost:8000/admin/kyc/1/reject
# Expected: 422 Validation Failed

# Test: Missing fields
curl -X POST \
  -F "full_name=Test" \
  http://localhost:8000/api/kyc/submit
# Expected: 422 with validation errors
```

---

## Debugging

### Enable Query Logging
```php
// In AppServiceProvider.php
use Illuminate\Support\Facades\DB;

DB::listen(function ($query) {
    \Log::info($query->sql);
    \Log::info($query->bindings);
    \Log::info($query->time);
});
```

### Check Request/Response
```bash
# In controller
dd(request()->all()); // Dump request data
dd(response());        // Dump response
```

### Laravel Debugbar
```php
// Install if not present
composer require barryvdh/laravel-debugbar --dev

// Access at: /debugbar (bottom of page)
// View:
// - Queries executed
// - Time taken
// - Memory usage
// - Route info
```

---

## Common Issues & Solutions

### Issue: "Route not found"
```bash
# Clear cache
php artisan route:clear
php artisan view:clear

# Verify routes exist
php artisan route:list | grep kyc
```

### Issue: "View not found"
```bash
# Check file exists
ls -la resources/views/admin/kyc/

# Clear view cache
php artisan view:clear
```

### Issue: "Cannot access storage"
```bash
# Create storage link
php artisan storage:link

# Verify symlink
ls -la public/storage
```

### Issue: "Notifications not sending"
```bash
# Check mail config in .env
MAIL_MAILER=log  # Use 'log' for testing

# Check sent emails
tail -f storage/logs/laravel.log | grep -i mail
```

### Issue: "CSRF token mismatch"
```blade
<!-- Add CSRF token to forms -->
<form method="POST">
    @csrf
    ...
</form>
```

---

## Test Data Generation

### Create Test KYCs
```bash
php artisan tinker

// Create 10 test KYCs
> KYC::factory(10)->create();

// Create with specific status
> KYC::factory(5)->create(['status' => 'approved']);
> KYC::factory(3)->create(['status' => 'rejected']);

// Create with specific user
> $user = User::find(1);
> KYC::factory()->create(['user_id' => $user->id]);
```

### Seed Database
```bash
# Create seeder
php artisan make:seeder KYCSeeder

# Run seeder
php artisan db:seed --class=KYCSeeder

# Fresh database with seeding
php artisan migrate:fresh --seed
```

---

## Checklist Before Production

- [ ] All routes working (manual + API tests)
- [ ] All views rendering correctly
- [ ] Authorization working (super admin only)
- [ ] Notifications sending via email
- [ ] Notifications stored in database
- [ ] Mobile app can fetch notifications
- [ ] Mobile app displays notifications correctly
- [ ] Documents display correctly
- [ ] Status transitions working
- [ ] Filters and search functional
- [ ] Pagination working
- [ ] No database errors in logs
- [ ] No JavaScript errors in console
- [ ] CSRF protection enabled
- [ ] Email templates configured
- [ ] Storage accessible
- [ ] Load testing passed
- [ ] Security testing passed
- [ ] Browser compatibility tested

---

## Rollback Plan

If issues occur:

```bash
# Rollback specific migration
php artisan migrate:rollback --step=1

# Remove views
rm -rf resources/views/admin/kyc/

# Remove controller
rm app/Http/Controllers/Web/KYCController.php

# Revert route changes
git checkout routes/web.php
```

---

## Success Criteria

✅ **Web Admin Interface**
- [ ] Can see list of KYCs
- [ ] Can filter by status
- [ ] Can search by name/email
- [ ] Can view full details
- [ ] Can approve with modal
- [ ] Can reject with reason
- [ ] Sees success messages

✅ **Mobile App**
- [ ] Can submit KYC form
- [ ] Receives approval notification
- [ ] Receives rejection notification
- [ ] Can see notification reason
- [ ] Can resubmit if rejected

✅ **Database**
- [ ] KYC records created correctly
- [ ] Status updated correctly
- [ ] Notifications stored
- [ ] Email logs recorded

✅ **Security**
- [ ] Only super admin can approve/reject
- [ ] CSRF protection working
- [ ] Input validation working
- [ ] No SQL injection possible

---

**Testing Status**: 🟡 Ready for manual testing with real data

