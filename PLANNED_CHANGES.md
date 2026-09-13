# Planned Changes for Defense Panel Requirements

## Summary of Changes

Based on the defense panel feedback, I will implement the following changes:

### 1. Priority level (Autoset) based on the issue category/type
- **File**: `app/Models/IssueReport.php`
- **Change**: Add model boot method to automatically set priority based on category
- **Logic**: Map categories to priority levels (e.g., Safety → Critical, Infrastructure → High, etc.)

### 2. Details (auto) and resident can add details including picture uploads
- **File**: `app/Http/Controllers/IssueController.php` (store method)
- **Change**: Enhance validation to allow additional detail fields and improve image handling
- **Note**: Current implementation already supports image uploads via Media model

### 3. Map Pin (Accurate) - add latitude/longitude mapping capabilities
- **Files**: 
  - `resources/views/layouts/mapping.blade.php` - Enhance map display
  - `app/Http/Controllers/IssueController.php` - Ensure lat/lng are properly handled
- **Change**: Improve map visualization with accurate pin placement

### 4. Check Delinquent (SUBDIVISION POLICY) - add backend validation
- **Files**:
  - `app/Http/Controllers/FinancialController.php` (create/store methods)
  - `app/Models/Delinquent.php` - Add validation method
- **Change**: Add subdivision policy validation before flagging household as delinquent

### 5. Change the term "Delinquent" to a more appropriate term throughout the system
- **Files to rename**:
  - `app/Models/Delinquent.php` → `app/Models/PaymentStatus.php`
  - `database/migrations/2026_05_08_015544_deliquent.php` → migration for payment_statuses
  - `database/migrations/2026_05_18_052008_create_delinquents_table.php` → create_payment_statuses_table.php
  - `app/Http/Controllers/FinancialController.php` - references to Delinquent
  - `resources/views/layouts/deliquents.blade.php` → payment_status.blade.php
  - Routes referencing delinquents
- **Change**: Replace all instances of "delinquent"/"delinquents" with "payment status"

### 6. Update Excel reports to reflect Gcash Payment integration tracking
- **Files**:
  - `app/Models/FinancialRecord.php` - Add gcash transaction fields
  - `database/migrations/2026_05_19_000022_create_financial_reports_table.php` - Update if needed
  - Reporting/export functionality (to be located)
- **Change**: Add gcash_transaction_id, gcash_reference, and payment_gateway fields

### 7. Set up Templated formats for Notices and Announcements
- **Files**:
  - `app/Models/Announcement.php` - Add template functionality
  - `app/Http/Controllers/AnnouncementController.php` - Handle templates
  - `database/migrations` - Add template_id or template_type to announcements table
  - New: `app/Models/AnnouncementTemplate.php`
- **Change**: Allow announcements to be based on predefined templates

## Detailed File Changes

### 1. IssueReport Model Auto-Priority
```php
// In app/Models/IssueReport.php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($issue) {
        if (!$issue->priority) {
            $issue->priority = $issue->autoSetPriority();
        }
    });
}

public function autoSetPriority()
{
    $categoryPriorityMap = [
        'Safety' => 'Critical',
        'Security' => 'Critical',
        'Infrastructure' => 'High',
        'Utilities' => 'High',
        'Sanitation' => 'Medium',
        'Landscaping' => 'Low',
        'Maintenance' => 'Medium',
        'Noise' => 'Medium',
        'Pet Issues' => 'Low',
        'Parking' => 'Medium',
        // Default
        'default' => 'Low'
    ];
    
    return $categoryPriorityMap[$this->category] ?? $categoryPriorityMap['default'];
}
```

### 2. Payment Status Term Change
- Rename model and update all references
- Update migration timestamps
- Update controller method names and variables
- Update Blade template file and references

### 3. Gcash Payment Integration
```php
// In app/Models/FinancialRecord.php
protected $fillable = [
    'resident_id', 'admin_id', 'record_type', 'description', 'amount', 'record_date',
    'gcash_transaction_id', 'gcash_reference', 'payment_gateway'
];

protected $casts = [
    'amount' => 'decimal:2',
    'record_date' => 'date',
    // gcash fields as strings
];
```

### 4. Announcement Templates
```php
// New model: app/Models/AnnouncementTemplate.php
class AnnouncementTemplate extends Model
{
    protected $fillable = ['name', 'type', 'subject_template', 'body_template', 'variables'];
    
    public function render(array $variables = [])
    {
        $subject = str_replace(
            array_keys($variables), 
            array_values($variables), 
            $this->subject_template
        );
        
        $body = str_replace(
            array_keys($variables), 
            array_values($variables), 
            $this->body_template
        );
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}
```

## Implementation Order
1. Priority autoset for IssueReport
2. Payment status terminology change
3. Gcash payment integration
4. Announcement templates
5. Map enhancements
6. Subdivision policy validation

Each change will be implemented with appropriate migrations, model updates, controller modifications, and view updates as needed.