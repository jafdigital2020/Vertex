# Subscription Expiration Implementation

## Overview
This document describes the implementation of subscription expiration checks that prevent employees from clocking in when their branch subscription has expired (0 days left).

## Implementation Date
November 21, 2025

---

## Components Implemented

### 1. CheckSubscription Middleware
**File:** `app/Http/Middleware/CheckSubscription.php`

**Purpose:** Validates if the user's branch subscription is active before allowing access to protected routes.

**Key Features:**
- Bypasses global admins (super admins)
- Checks branch subscription status via user's employment detail
- Validates subscription end date (blocks access when 0 days left or negative)
- Checks subscription status (blocks if expired/cancelled/inactive)
- Handles both API/AJAX and web requests differently
- Provides detailed logging for troubleshooting

**Logic Flow:**
1. Check if user is global admin → allow access
2. Get user's branch ID from employment detail
3. Fetch latest branch subscription
4. Calculate days left until expiration
5. If ≤ 0 days or status is expired/cancelled/inactive → block access
6. Otherwise → allow access

**Response Handling:**
- **API/JSON requests:** Returns 403 JSON response with error message
- **Web requests:** Redirects to employee dashboard with session flash message

---

### 2. Subscription Expired Modal
**File:** `resources/views/errors/subscriptionexpired.blade.php`

**Purpose:** Full-page error view for subscription expiration (standalone page).

**Design:**
- Beautiful gradient background with floating shapes
- Clear warning icon and status badge
- Displays custom message from session
- Action buttons: Return to Dashboard, Sign Out
- Responsive design for mobile devices

**Features:**
- Professional Timora branding
- Animated elements
- Clear call-to-action
- User-friendly messaging

---

### 3. Modal Popup Script
**File:** `resources/views/layout/partials/footer-scripts.blade.php`

**Purpose:** Displays an inline modal popup when subscription expires (appears on any page).

**Implementation:**
- Checks for `subscription_expired` session flash
- Creates backdrop overlay with blur effect
- Displays modal after 1.5 seconds with smooth animation
- Shows custom expiration message
- Includes Sign Out functionality
- Auto-creates logout form if needed

**Visual Features:**
- Gradient background matching Timora theme
- Pulse animation on status indicator
- Smooth fade-in transitions
- Mobile-responsive
- High z-index to overlay all content

---

### 4. Route Protection
**Files:** `routes/api.php`

**Protected Routes:**
```php
// Clock in/out endpoints
Route::post('/attendance/clock-in', [...])
    ->middleware('check.subscription');

Route::post('/attendance/clock-out', [...])
    ->middleware('check.subscription');

// Break in/out endpoints
Route::post('/attendance/break-in', [...])
    ->middleware('check.subscription');

Route::post('/attendance/break-out', [...])
    ->middleware('check.subscription');
```

**Why These Routes:**
- These are the primary time-tracking actions employees perform
- Blocking these prevents attendance manipulation after subscription expires
- Other read-only routes (viewing attendance) are not blocked to allow access to historical data

---

## Middleware Registration
**File:** `bootstrap/app.php`

The middleware was already registered with alias `check.subscription`:
```php
$middleware->alias([
    'check.subscription' => CheckSubscription::class,
    // ... other middleware
]);
```

---

## Database Structure

### BranchSubscription Model
**Table:** `branch_subscriptions`

**Key Fields Used:**
- `branch_id` - Links to branch
- `subscription_start` - Start date (Carbon instance)
- `subscription_end` - End date (Carbon instance)
- `status` - active/expired/cancelled/inactive
- `is_trial` - Boolean flag for trial subscriptions
- `payment_status` - Payment tracking

**Relationships:**
- `belongsTo(Branch::class)` - Each subscription belongs to a branch
- `hasMany(Payment::class)` - Subscription payment history

---

## User Flow Examples

### Scenario 1: Expired Subscription (API Request)
**User Action:** Employee tries to clock in via mobile app

**Flow:**
1. POST request to `/api/attendance/clock-in`
2. Middleware checks subscription → expired (0 days left)
3. Returns JSON response:
```json
{
    "success": false,
    "message": "Your subscription has expired. Please contact your administrator to renew.",
    "subscription_expired": true
}
```
4. Mobile app shows error alert

---

### Scenario 2: Expired Trial (Web Request)
**User Action:** Employee tries to clock in via web interface

**Flow:**
1. POST request to `/attendance/clock-in`
2. Middleware checks subscription → trial ended
3. Session flash: `subscription_expired` = "Your trial period has ended..."
4. Redirects to employee dashboard
5. After 1.5 seconds, modal pops up with message
6. Employee can return to dashboard or sign out

---

### Scenario 3: Active Subscription
**User Action:** Employee clocks in with valid subscription

**Flow:**
1. POST request to `/api/attendance/clock-in`
2. Middleware checks subscription → 15 days left, status: active
3. Allows request to proceed
4. Clock-in processed normally

---

## Expiration Check Logic

### Days Left Calculation
```php
$now = Carbon::now();
$subscriptionEnd = Carbon::parse($subscription->subscription_end);
$daysLeft = $now->diffInDays($subscriptionEnd, false);

if ($daysLeft <= 0) {
    // Block access - subscription expired
}
```

**Important Notes:**
- `diffInDays(..., false)` returns negative number if end date is in the past
- `$daysLeft = 0` means subscription ends today → blocked
- `$daysLeft < 0` means subscription already ended → blocked
- `$daysLeft > 0` means subscription still active → allowed

---

## Status Checks
The middleware blocks access if subscription status is:
- `expired`
- `cancelled`
- `inactive`

Even if the end date hasn't been reached, these statuses will block access.

---

## Logging

All subscription blocks are logged with:
```php
Log::info('CheckSubscription: Subscription expired', [
    'user_id' => $user->id,
    'branch_id' => $branchId,
    'subscription_end' => $subscription->subscription_end,
    'days_left' => $daysLeft,
    'route' => $request->path()
]);
```

**Log Locations:**
- Check `storage/logs/laravel.log` for subscription events
- Search for "CheckSubscription" to find related entries

---

## Error Messages

### Trial Expired
> "Your trial period has ended. Please contact your administrator to activate your subscription."

### Subscription Expired
> "Your subscription has expired. Please contact your administrator to renew."

### No Subscription Found
> "No active subscription found. Please contact your administrator."

### Inactive Status
> "Your subscription is no longer active. Please contact your administrator."

---

## Testing Checklist

### Test Cases:

1. **Active Subscription**
   - ✓ Employee can clock in/out normally
   - ✓ No modal or error messages shown

2. **Subscription Ends Today (0 days left)**
   - ✓ Clock-in blocked
   - ✓ Modal shows expiration message
   - ✓ API returns 403 error

3. **Subscription Ended Yesterday (-1 days)**
   - ✓ Clock-in blocked
   - ✓ Appropriate error message displayed

4. **Trial Period Ended**
   - ✓ Clock-in blocked
   - ✓ Shows trial-specific message

5. **Subscription Status = Cancelled**
   - ✓ Access blocked even if end date not reached
   - ✓ Shows inactive subscription message

6. **Global Admin Access**
   - ✓ Bypasses all subscription checks
   - ✓ Can access all features regardless of subscription

7. **No Employment Detail**
   - ✓ Handles gracefully (allows access as fallback)

---

## Frontend Integration

### Modal Display Trigger
The modal automatically displays when:
- Session contains `subscription_expired` flash data
- Page loads completely
- After 1.5 second delay for UX smoothness

### API Error Handling
Frontend should check for:
```javascript
if (response.subscription_expired) {
    // Show error to user
    alert(response.message);
    // Optionally redirect or disable features
}
```

---

## Configuration

### Middleware Alias
```php
'check.subscription' => CheckSubscription::class
```

### Usage in Routes
```php
Route::post('/path', [Controller::class, 'method'])
    ->middleware('check.subscription');
```

### Multiple Middleware
```php
Route::post('/path', [Controller::class, 'method'])
    ->middleware(['auth', 'check.subscription', 'verified']);
```

---

## Future Enhancements

### Recommended Improvements:

1. **Grace Period**
   - Allow 1-3 days grace period after expiration
   - Show warning messages during grace period

2. **Subscription Renewal Reminders**
   - Email notifications 7 days before expiration
   - Dashboard banner warnings

3. **Partial Access**
   - Allow viewing historical data even when expired
   - Block only creation/modification actions

4. **Admin Override**
   - Branch admins can temporarily extend access
   - Emergency access codes

5. **Payment Integration**
   - Direct payment link in modal
   - In-app subscription renewal

6. **Subscription Metrics Dashboard**
   - Show days remaining prominently
   - Subscription health indicators

---

## Troubleshooting

### Issue: Modal Not Showing

**Possible Causes:**
- Session flash not being set
- JavaScript error preventing modal creation
- Footer scripts not included in layout

**Solution:**
- Check `session()->has('subscription_expired')` returns true
- Inspect browser console for errors
- Verify layout includes `@include('layout.partials.footer-scripts')`

---

### Issue: Middleware Not Blocking

**Possible Causes:**
- Middleware not applied to route
- User is global admin (bypasses check)
- Employment detail missing
- Subscription end date incorrectly formatted

**Solution:**
- Verify route has `->middleware('check.subscription')`
- Check `Auth::guard('global')->check()` returns false
- Ensure user has employment_detail with branch_id
- Check subscription_end is valid Carbon date

---

### Issue: Wrong Message Displayed

**Possible Causes:**
- Session flash contains incorrect message
- Trial flag not set correctly
- Status not properly evaluated

**Solution:**
- Check `$subscription->is_trial` value
- Verify `$subscription->status` matches expected values
- Review middleware logic for message selection

---

## Related Files

### Core Implementation
- `app/Http/Middleware/CheckSubscription.php`
- `resources/views/errors/subscriptionexpired.blade.php`
- `resources/views/layout/partials/footer-scripts.blade.php`
- `routes/api.php`
- `bootstrap/app.php`

### Models
- `app/Models/BranchSubscription.php`
- `app/Models/Branch.php`
- `app/Models/User.php`
- `app/Models/EmploymentDetail.php`

### Controllers
- `app/Http/Controllers/Tenant/Attendance/AttendanceEmployeeController.php`

---

## Security Considerations

1. **Authorization:** Middleware checks user authentication first
2. **Data Access:** Only checks user's own branch subscription
3. **Logging:** Sensitive subscription data not logged (only IDs and dates)
4. **XSS Prevention:** Messages properly escaped in Blade templates
5. **CSRF Protection:** Logout form includes @csrf token

---

## Performance Notes

- Middleware executes on every protected route request
- Database query: 1 SELECT to fetch branch subscription
- Cached results could be implemented for high-traffic systems
- Consider Redis caching for subscription status

---

## Support Contact

For questions or issues with this implementation:
- Review logs in `storage/logs/laravel.log`
- Check session data with `session()->all()`
- Verify database records in `branch_subscriptions` table
- Test with different subscription scenarios

---

**Implementation Status:** ✅ Complete
**Last Updated:** November 21, 2025
**Version:** 1.0.0
