# Quick Reference: Test User Login Credentials

## 🔐 Test Users Overview

**Total Users Created**: 90  
**Default Password**: `password123`  
**Email Domain**: `@testbilling.com`

---

## 📋 Sample Login Credentials

### Users 1-10 (Base Limit - No Charges)
```
Username: sergiocampos1          | Email: sergio.campos1@testbilling.com
Username: manuelcortez2          | Email: manuel.cortez2@testbilling.com
Username: raulgarcia3            | Email: raul.garcia3@testbilling.com
Username: juancastro4            | Email: juan.castro4@testbilling.com
Username: mariasantos5           | Email: maria.santos5@testbilling.com
Username: fernandavargas6        | Email: fernanda.vargas6@testbilling.com
Username: franciscovalencia7    | Email: francisco.valencia7@testbilling.com
Username: andresmercado8         | Email: andres.mercado8@testbilling.com
Username: marianaaquino9         | Email: mariana.aquino9@testbilling.com
Username: sofiaflores10          | Email: sofia.flores10@testbilling.com
```

### User 11 (Implementation Fee - ₱2,000)
```
Username: jorgeramos11           | Email: jorge.ramos11@testbilling.com
Employee ID: TEST-0011
Status: ⚠️ Implementation fee required
Expected Invoice: ₱2,000.00
```

### Users 12-20 (License Overage - ₱49 each)
```
Username: carloshernandez12      | Email: carlos.hernandez12@testbilling.com
Username: rafaelbautista13       | Email: rafael.bautista13@testbilling.com
Username: raulrivera14           | Email: raul.rivera14@testbilling.com
Username: veronicaabad15         | Email: veronica.abad15@testbilling.com
Username: claudiaromero16        | Email: claudia.romero16@testbilling.com
Username: jorgevaldez17          | Email: jorge.valdez17@testbilling.com
Username: raulfernandez18        | Email: raul.fernandez18@testbilling.com
Username: ricardomolina19        | Email: ricardo.molina19@testbilling.com
Username: jorgeluna20            | Email: jorge.luna20@testbilling.com

Employee IDs: TEST-0012 to TEST-0020
Status: 💰 License overage
Expected Total: ₱441.00 (9 users × ₱49)
```

### Users 21-90 (Plan Upgrade Required)
```
Username: valeriamercado21       | Email: valeria.mercado21@testbilling.com
Username: pedroabad22            | Email: pedro.abad22@testbilling.com
Username: raulmorales23          | Email: raul.morales23@testbilling.com
...
Username: cristinacortez90       | Email: cristina.cortez90@testbilling.com

Employee IDs: TEST-0021 to TEST-0090
Status: 🚀 Plan upgrade needed
Action: Must upgrade from Starter to Core/Pro/Elite
```

---

## 🎯 Username Pattern

All usernames follow this pattern:
```
{firstname}{lastname}{number}
```

Examples:
- `sergiocampos1` = Sergio + Campos + 1
- `manuelcortez2` = Manuel + Cortez + 2
- `raulgarcia3` = Raul + Garcia + 3

**All lowercase, no spaces or special characters**

---

## 📊 User Distribution

| Range | Count | Purpose | Expected Billing |
|-------|-------|---------|------------------|
| 1-10 | 10 | Base limit | No charges |
| 11 | 1 | Implementation | ₱2,000 one-time |
| 12-20 | 9 | Overage | ₱49 per user |
| 21-90 | 70 | Upgrade needed | Plan upgrade cost |
| **Total** | **90** | **All scenarios** | **Mixed** |

---

## 🧪 Quick Test Login

To quickly test, log in with any of these:

**Within Base Limit:**
```
Username: sergiocampos1
Password: password123
```

**Triggers Implementation Fee:**
```
Username: jorgeramos11
Password: password123
```

**Triggers Overage:**
```
Username: carloshernandez12
Password: password123
```

**Triggers Upgrade:**
```
Username: valeriamercado21
Password: password123
```

---

## 📋 Employee ID Lookup

If you need to find a user by Employee ID:

```sql
SELECT 
    u.username,
    u.email,
    ed.employee_id,
    CONCAT(epi.first_name, ' ', epi.last_name) as full_name
FROM users u
JOIN employment_details ed ON u.id = ed.user_id
JOIN employment_personal_informations epi ON u.id = epi.user_id
WHERE ed.employee_id LIKE 'TEST-%'
ORDER BY ed.employee_id;
```

---

## 🔄 Password Reset (If Needed)

If you need to reset passwords for test users:

```bash
php artisan tinker
```

```php
// Reset single user
$user = User::where('username', 'sergiocampos1')->first();
$user->password = Hash::make('password123');
$user->save();

// Reset all test users
User::whereHas('employmentDetail', function($q) {
    $q->where('employee_id', 'LIKE', 'TEST-%');
})->update(['password' => Hash::make('password123')]);
```

---

## 📞 Quick Commands

```bash
# Count test users
php artisan tinker
User::whereHas('employmentDetail', function($q) {
    $q->where('employee_id', 'LIKE', 'TEST-%');
})->count();

# List all test users
User::whereHas('employmentDetail', function($q) {
    $q->where('employee_id', 'LIKE', 'TEST-%');
})->with('employmentDetail')->get()->pluck('employmentDetail.employee_id', 'username');

# Delete all test users
User::whereHas('employmentDetail', function($q) {
    $q->where('employee_id', 'LIKE', 'TEST-%');
})->delete();
```

---

## 🎉 All Set!

You're ready to test! Use these credentials to log in and verify the billing functionality.

**Remember**: All test users use `password123`

---

**Last Updated**: November 9, 2024  
**Created By**: GitHub Copilot
