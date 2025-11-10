# User Guide: Billing & Subscription Management

## Getting Started

Welcome to the Vertex billing and subscription system. This guide will walk you through all billing-related features and scenarios you may encounter.

## 📋 Table of Contents

1. [Understanding Your Subscription](#understanding-your-subscription)
2. [Managing Employees and Licenses](#managing-employees-and-licenses)
3. [Billing Dashboard](#billing-dashboard)
4. [Payment Processing](#payment-processing)
5. [Plan Management](#plan-management)
6. [Common Scenarios](#common-scenarios)
7. [Troubleshooting](#troubleshooting)

## Understanding Your Subscription

### Plan Types

| Plan | Monthly Price | Users Included | Implementation Fee | Best For |
|------|---------------|----------------|-------------------|----------|
| **Starter** | ₱1,500 | 5 users | ₱5,000 | Small teams |
| **Basic** | ₱3,000 | 15 users | Free | Growing companies |
| **Pro** | ₱7,500 | 50 users | Free | Medium businesses |
| **Elite** | ₱25,000/year | 100 users | Free | Large enterprises |

### Key Concepts

**🏷️ Active Licenses**: Number of users currently consuming licenses  
**📊 Plan Limit**: Maximum users included in your plan price  
**💰 Overage**: Additional users beyond your plan limit (₱49/user)  
**🔧 Implementation Fee**: One-time setup charge for Starter plan  
**📅 Billing Cycle**: Monthly or yearly payment schedule  

## Managing Employees and Licenses

### Adding New Employees

1. **Navigate** to Employee Management
2. **Click** "Add Employee" button
3. **Fill in** employee details
4. **Click** Submit

#### What Happens Next?

The system automatically checks your current license usage and may show one of these scenarios:

#### ✅ **Scenario 1: Within Limit**
```
Current users: 8/15 (Basic Plan)
Action: Employee added successfully
Cost: Included in subscription
```

#### ⚠️ **Scenario 2: Implementation Fee Required**
```
Plan: Starter (First employee)
Implementation Fee: ₱5,000
Action Required: Pay implementation fee
```

**What to do:**
1. Review implementation fee details
2. Click "Proceed with Payment"
3. Complete payment process
4. Employee will be added after payment

#### ⚠️ **Scenario 3: Plan Upgrade Suggested**
```
Current: Basic Plan (15 users)
Attempting to add: 16th user
Better option: Upgrade to Pro Plan
```

**What to do:**
1. Review upgrade options
2. Compare costs:
   - **Option A**: Pay overage (₱49/month per extra user)
   - **Option B**: Upgrade plan (better value for multiple users)
3. Choose your preferred option

#### ⚠️ **Scenario 4: Overage Fee Applies**
```
Current users: 15/15 (Basic Plan)
Overage cost: ₱49/user/month
Total extra cost: ₱49
```

**What to do:**
1. Confirm overage charge
2. Click "Accept Overage Fee"
3. Employee added, overage invoice generated

#### ❌ **Scenario 5: Contact Sales**
```
Plan: Elite (100+ users)
Action: Contact sales for enterprise solution
```

### Activating/Deactivating Employees

#### Activating Employees
- **Go to** Employee list
- **Find** inactive employee
- **Click** "Activate"
- System checks license limits (same process as adding new employee)

#### Deactivating Employees
- **Go to** Employee list
- **Find** active employee
- **Click** "Deactivate"
- License count automatically reduced
- No additional charges

## Billing Dashboard

### Accessing Your Bills

1. **Navigate** to **Billing & Payment** from main menu
2. **View** your billing overview

### Understanding Your Dashboard

#### 📊 **Usage Summary Card**
```
Current Plan: Basic Monthly
Users: 12 of 15
Next Renewal: January 15, 2025
Monthly Cost: ₱3,000
```

#### 📋 **Current Period Usage** (Detailed View)
Click "View Details" to see:

| Employee | Activation Date | Days Active | Status |
|----------|----------------|-------------|--------|
| John Doe | Jan 1, 2025 | 10 days | Active |
| Jane Smith | Jan 5, 2025 | 6 days | Active |
| Mike Wilson | Jan 8, 2025 | 3 days | Active |

#### 💼 **Invoice History**
View all your invoices with:
- Invoice number
- Type (Subscription/Overage/Upgrade/Implementation)
- Amount due
- Status (Pending/Paid/Failed)
- Due date

### Invoice Types Explained

#### 🔄 **Subscription Invoice**
```
Type: Monthly/Yearly Renewal
Amount: Base plan price
Generated: 7 days before renewal
Includes: May include accumulated overage
```

#### 📈 **Overage Invoice**
```
Type: License Overage
Amount: Extra users × ₱49
Generated: Immediately when limit exceeded
Example: 3 extra users = ₱147
```

#### 🔧 **Implementation Fee Invoice**
```
Type: One-time Setup
Amount: ₱5,000 (Starter plan only)
Generated: When adding first employee
Required: Must pay before user creation
```

#### ⬆️ **Plan Upgrade Invoice**
```
Type: Plan Change
Amount: Prorated difference
Generated: When upgrading plan
Example: ₱750 (upgrade from Basic to Pro mid-cycle)
```

## Payment Processing

### Making a Payment

1. **Locate** pending invoice in billing dashboard
2. **Click** "Pay Now" button
3. **Review** payment details:
   ```
   Invoice #: INV-2025-001
   Amount: ₱3,147.00
   VAT (12%): ₱337.68
   Total: ₱3,484.68
   ```
4. **Click** "Proceed to Payment"
5. **Complete** payment via HitPay gateway:
   - Enter card details
   - Confirm payment
   - Wait for confirmation

### Payment Confirmation

After successful payment:
- ✅ Invoice status updates to "Paid"
- 📧 Payment confirmation email sent
- 📊 Subscription automatically renewed (if renewal invoice)
- 🔄 Service continues uninterrupted

### Payment Methods Supported

- 💳 Credit/Debit Cards (Visa, Mastercard)
- 🏦 Online Banking
- 📱 Digital Wallets (GrabPay, PayLah!)

## Plan Management

### Viewing Available Plans

1. **Navigate** to **Subscriptions**
2. **View** current plan details
3. **Browse** available upgrade options

### Upgrading Your Plan

#### When to Consider Upgrading

- Adding many users (overage fees exceed upgrade cost)
- Need higher user limits
- Want better value for money

#### Upgrade Process

1. **Go to** Subscriptions page
2. **Select** billing cycle (Monthly/Yearly)
3. **Choose** desired plan
4. **Review** upgrade details:
   ```
   Current Plan: Basic Monthly (₱3,000)
   New Plan: Pro Monthly (₱7,500)
   
   Remaining days: 15
   Prorated refund: ₱1,500
   Prorated new cost: ₱3,750
   Amount due: ₱2,250
   ```
5. **Click** "Confirm Upgrade"
6. **Pay** the difference
7. **Enjoy** your new plan limits

### Billing Cycle Considerations

#### Monthly Billing
- Lower monthly commitment
- Flexibility to change plans
- Overage charged immediately

#### Yearly Billing
- Significant cost savings
- Annual commitment
- Monthly overage invoices (separate from yearly renewal)

## Common Scenarios

### Scenario 1: Growing Team (Monthly Plan)

**Situation**: You have Basic Monthly (15 users), need to add 5 more employees

**Options**:
1. **Stay on Basic + Overage**
   - Extra cost: 5 × ₱49 = ₱245/month
   - Total: ₱3,000 + ₱245 = ₱3,245/month

2. **Upgrade to Pro**
   - New cost: ₱7,500/month (supports 50 users)
   - Better value for 20+ users

**Recommendation**: Upgrade to Pro for better long-term value

### Scenario 2: Seasonal Business (Yearly Plan)

**Situation**: Elite Yearly plan, seasonal employees

**How it works**:
```
Base Plan: ₱25,000/year (100 users)

January: 120 users → Overage: 20 × ₱49 = ₱980
February: 105 users → Overage: 5 × ₱49 = ₱245
March: 95 users → No overage
April: 110 users → Overage: 10 × ₱49 = ₱490

Each month billed separately
Yearly renewal: December (base subscription only)
```

### Scenario 3: New Business (Starter Plan)

**Situation**: Just starting, first employee addition

**Process**:
1. Add first employee
2. Implementation fee modal appears: ₱5,000
3. Pay implementation fee
4. Employee added successfully
5. Future employees (up to 5) included in monthly fee
6. 6th employee triggers overage or upgrade options

### Scenario 4: Plan Downgrade

**Note**: Plan downgrades are not automatically available through the system.

**Process**:
1. Contact support team
2. Review current usage vs. target plan
3. If usage fits lower plan, manual adjustment possible
4. Prorated refund calculated

## Troubleshooting

### Common Issues

#### ❌ **Employee Addition Blocked**

**Problem**: Can't add employee, system shows error

**Solutions**:
1. **Check** current license usage
2. **Pay** pending invoices
3. **Resolve** overage or upgrade plan
4. **Contact** support if issue persists

#### ❌ **Payment Failed**

**Problem**: Payment doesn't go through

**Solutions**:
1. **Check** card details and limits
2. **Try** different payment method
3. **Contact** bank if card declined
4. **Retry** payment after resolving issue

#### ❌ **Invoice Not Generated**

**Problem**: Expected invoice doesn't appear

**Causes & Solutions**:
- **Renewal invoices**: Generated 7 days before due date
- **Overage invoices**: Created immediately when adding users
- **System delays**: Wait 1-2 hours, refresh page
- **Technical issues**: Contact support

#### ❌ **Incorrect Usage Count**

**Problem**: Dashboard shows wrong user count

**Solutions**:
1. **Refresh** browser page
2. **Check** employee active/inactive status
3. **Verify** recent activations/deactivations
4. **Contact** support for manual reconciliation

### Getting Help

#### Self-Service Options
- 📖 Check this user guide
- 🔍 Review billing dashboard
- 📧 Check email notifications
- 📱 Verify payment methods

#### Contact Support
- 📞 **Phone**: [Support Number]
- 📧 **Email**: support@vertex.com
- 💬 **Chat**: Available in dashboard
- ⏰ **Hours**: Monday-Friday, 9 AM - 6 PM

#### Information to Provide
- Tenant/Company name
- Invoice number (if applicable)
- Error message screenshot
- Steps you've already tried

### Billing Calendar Template

Use this template to track your billing cycle:

```
Subscription: Basic Monthly
Renewal Date: 15th of each month
Current Users: __/15
Expected Overage: ____ users

Monthly Reminders:
□ Day 8: Renewal invoice generated
□ Day 15: Payment due
□ Monitor usage throughout month
□ Plan upgrade if consistently over limit
```

---

## Quick Reference

### Emergency Contacts
- **Payment Issues**: billing@vertex.com
- **Technical Problems**: support@vertex.com
- **Account Changes**: admin@vertex.com

### Key Numbers
- **Overage Rate**: ₱49/user/month
- **Starter Implementation**: ₱5,000 one-time
- **VAT Rate**: 12%
- **Renewal Notice**: 7 days advance

### Important URLs
- **Billing Dashboard**: `/billing`
- **Subscriptions**: `/subscriptions`
- **Employee Management**: `/employees`
- **Payment History**: `/billing` (scroll down)

Last Updated: November 2024  
Version: 1.0
