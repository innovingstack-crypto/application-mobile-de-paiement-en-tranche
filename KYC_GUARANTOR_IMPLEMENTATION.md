# KYC System - Guarantor (Garant) Implementation Update

**Date**: February 6, 2026  
**Status**: ✅ COMPLETE  
**Changes**: Backend updated to support guarantor information and new document fields

---

## 📋 Summary of Changes

The KYC system has been updated to support the new mobile app form which includes guarantor (garant) information and additional document types.

### New Form Fields Added
**Client Section:**
- `phoneNumber` (numéro de téléphone)
- `idFrontImage` (recto de la pièce d'identité)
- `idBackImage` (verso de la pièce d'identité)
- `clientPhoto` (photo du client)
- `signedDocument` (document signé)

**New Guarantor Section:**
- `guarantorName` (nom du garant)
- `guarantorPhone` (téléphone du garant)
- `guarantorIdFront` (recto pièce garant)
- `guarantorIdBack` (verso pièce garant)

---

## 🗄️ Database Changes

### New Migration Created
**File**: `database/migrations/2026_02_06_100000_add_guarantor_fields_to_kycs_table.php`

**New Columns Added**:
```sql
ALTER TABLE kycs ADD (
    client_photo_path VARCHAR(255) NULL,
    signed_document_path VARCHAR(255) NULL,
    guarantor_full_name VARCHAR(255) NULL,
    guarantor_phone VARCHAR(20) NULL,
    guarantor_id_front_path VARCHAR(255) NULL,
    guarantor_id_back_path VARCHAR(255) NULL,
    client_verified BOOLEAN DEFAULT FALSE,
    guarantor_verified BOOLEAN DEFAULT FALSE
);
```

**To Apply Migration**:
```bash
php artisan migrate
```

---

## 📝 Model Changes

### KYC Model Updated
**File**: `app/Models/KYC.php`

**Changes**:
- Added new columns to `$fillable` array
- Client photo path
- Signed document path
- Guarantor fields (name, phone, ID front, ID back)
- Verification flags (client_verified, guarantor_verified)

```php
protected $fillable = [
    // ... existing fields
    'client_photo_path',
    'signed_document_path',
    'guarantor_full_name',
    'guarantor_phone',
    'guarantor_id_front_path',
    'guarantor_id_back_path',
    'client_verified',
    'guarantor_verified',
];
```

---

## 🔌 API Controller Changes

### KYCController Updated
**File**: `app/Http/Controllers/Api/KYCController.php`

**Validation Rules Added**:
```php
'phone_number' => 'nullable|string|max:20',
'id_front_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
'id_back_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
'client_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
'signed_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
'guarantor_full_name' => 'nullable|string|max:255',
'guarantor_phone' => 'nullable|string|max:20',
'guarantor_id_front' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
'guarantor_id_back' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
```

**File Storage Paths**:
- Client photos: `kyc/client_photos/`
- Signed documents: `kyc/signed_documents/`
- Guarantor documents: `kyc/guarantor_documents/`

**Processing Logic**:
- Files are stored with proper validation
- Old files are deleted when updating
- All document paths are preserved in database
- Guarantor data is optional but stored when provided

---

## 🎨 Web Admin View Changes

### KYC Show View Updated
**File**: `resources/views/admin/kyc/show.blade.php`

**Changes**:
- Separated "Documents du Client" section
- Added client photo display
- Added signed document display with download link
- **New**: Full "Informations du Garant" section
  - Guarantor name
  - Guarantor phone
  - Guarantor ID front and back (with zoom)
  - Conditional display (only shows if guarantor data exists)

**New Guarantor Section HTML**:
```blade
<!-- Informations du Garant Section -->
@if ($kyc->guarantor_full_name || $kyc->guarantor_phone || ...)
    <div class="mt-8 pt-6 border-t">
        <h4 class="text-md font-bold text-gray-800 mb-4">
            Informations du Garant
        </h4>
        <!-- Guarantor info display -->
        <!-- Guarantor documents display -->
    </div>
@endif
```

**Document Display Features**:
- Clickable images with zoom modal
- Document download links
- Responsive grid layout
- Clear labeling (Recto/Verso)

---

## 🔄 Data Flow

### Submission Flow
```
Mobile App (KYC Form)
    ↓ Submit with new fields
    ↓
API (KYCController::submit)
    ↓ Validate all fields
    ↓ Store files in proper directories
    ↓ Save data to database
    ↓
Database (kycs table)
    ↓ Fields updated with new columns
    ↓
Web Admin (/admin/kyc/{id})
    ↓ Display all information including guarantor
    ↓ Super admin can approve/reject
```

### File Storage Structure
```
storage/app/public/
└── kyc/
    ├── id_documents/        (ID documents recto)
    ├── client_photos/       (Client photos)
    ├── signed_documents/    (Signed documents)
    ├── guarantor_documents/ (Guarantor ID documents)
    ├── additional_documents/
    └── ...
```

---

## ✅ Compatibility

### With Existing KYCs
- ✅ Backward compatible - all new fields are nullable
- ✅ Existing KYCs without guarantor info still work
- ✅ Guarantor section only displays if data exists

### With Mobile App
- ✅ Supports new KYC form structure
- ✅ Optional fields for phone number variations
- ✅ All new document types supported
- ✅ Handles missing files gracefully

---

## 🧪 Testing Checklist

### Data Validation
- [ ] Test with full guarantor information
- [ ] Test with partial guarantor information
- [ ] Test without guarantor information
- [ ] Test file upload for each document type
- [ ] Test file deletion/update

### Admin Interface
- [ ] Client documents display correctly
- [ ] Guarantor section shows when data exists
- [ ] Guarantor section hidden when no data
- [ ] Image zoom modal works
- [ ] Document download link works
- [ ] Responsive design on mobile

### API Integration
- [ ] New fields accepted in POST request
- [ ] Old field names still accepted
- [ ] Files stored in correct directories
- [ ] Database records created correctly
- [ ] Update preserves existing data

---

## 🔌 API Request Example

### Creating KYC with Guarantor
```javascript
const formData = new FormData();

// Client info
formData.append('full_name', 'Jean Dupont');
formData.append('phone_number', '+237691234567');
formData.append('id_number', '123456789');
formData.append('address', 'Douala, Cameroon');

// Client documents
formData.append('id_front_image', idFrontFile);
formData.append('id_back_image', idBackFile);
formData.append('client_photo', clientPhotoFile);
formData.append('signed_document', signedDocFile);

// Guarantor info
formData.append('guarantor_full_name', 'Marie Dupont');
formData.append('guarantor_phone', '+237691234568');
formData.append('guarantor_id_front', guarantorFrontFile);
formData.append('guarantor_id_back', guarantorBackFile);

// Submit
POST /api/kyc/submit (with formData)
```

---

## 📊 Database Schema

### kycs Table (After Migration)
```
id                          BIGINT PRIMARY KEY
user_id                     BIGINT FOREIGN KEY
full_name                   VARCHAR(255)
email                       VARCHAR(255)
phone                       VARCHAR(20)
date_of_birth              DATE
id_type                     VARCHAR(255)
id_number                   VARCHAR(255) UNIQUE
id_document_path            VARCHAR(255) NULL
client_photo_path           VARCHAR(255) NULL         [NEW]
signed_document_path        VARCHAR(255) NULL         [NEW]
address                     VARCHAR(255)
city                        VARCHAR(255)
postal_code                 VARCHAR(20)
country                     VARCHAR(255)
additional_documents        JSON NULL
status                      ENUM(pending, approved, rejected, under_review)
rejection_reason            TEXT NULL
approved_by                 BIGINT FOREIGN KEY NULL
approved_at                 TIMESTAMP NULL
rejected_at                 TIMESTAMP NULL
guarantor_full_name         VARCHAR(255) NULL         [NEW]
guarantor_phone             VARCHAR(20) NULL          [NEW]
guarantor_id_front_path     VARCHAR(255) NULL         [NEW]
guarantor_id_back_path      VARCHAR(255) NULL         [NEW]
client_verified             BOOLEAN DEFAULT FALSE     [NEW]
guarantor_verified          BOOLEAN DEFAULT FALSE     [NEW]
created_at                  TIMESTAMP
updated_at                  TIMESTAMP
```

---

## 🚀 Deployment Steps

### 1. Apply Migration
```bash
cd SmallPay_backend
php artisan migrate
```

### 2. Clear Caches
```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Test Files
```bash
# Verify storage directory exists
mkdir -p storage/app/public/kyc/{id_documents,client_photos,signed_documents,guarantor_documents}

# Create symlink if not exists
php artisan storage:link
```

### 4. Test in Browser
```
http://localhost:8000/admin/kyc
- View existing KYCs
- Check new sections display correctly
```

### 5. Test API
```bash
# Create test KYC
curl -X POST http://localhost:8000/api/kyc/submit \
  -F "full_name=Test" \
  -F "phone_number=1234567" \
  -F "..." \
  -H "Authorization: Bearer TOKEN"
```

---

## 📝 Changes Summary

| Component | File | Type | Changes |
|-----------|------|------|---------|
| Migration | `database/migrations/2026_02_06_100000_add_guarantor_fields_to_kycs_table.php` | NEW | 8 new columns |
| Model | `app/Models/KYC.php` | UPDATED | 10 new fillable fields |
| API | `app/Http/Controllers/Api/KYCController.php` | UPDATED | Validation + file handling |
| Admin API | `app/Http/Controllers/Api/Admin/KYCController.php` | UPDATED | Format method extended |
| View | `resources/views/admin/kyc/show.blade.php` | UPDATED | Added guarantor section |

---

## ✨ Features Added

### For Users
- ✅ Submit guarantor information
- ✅ Upload guarantor ID documents
- ✅ Submit multiple document types
- ✅ Optional guarantor section

### For Admins
- ✅ View guarantor information
- ✅ Review guarantor documents
- ✅ Separate documents section
- ✅ Clear document categorization

### For System
- ✅ Flexible document storage
- ✅ Verification flags
- ✅ Proper file management
- ✅ Backward compatibility

---

## 🎯 Next Steps

1. **Run Migration**
   ```bash
   php artisan migrate
   ```

2. **Test API Integration**
   - Submit KYC with guarantor info
   - Verify files stored correctly
   - Check database records

3. **Update Mobile App**
   - Integrate new form fields
   - Add guarantor section
   - Test form submission

4. **QA Testing**
   - Test all scenarios
   - Verify admin view
   - Check file downloads

5. **Deploy to Production**
   - Backup database
   - Run migrations
   - Test thoroughly

---

## 📞 Support

### If Migration Fails
```bash
# Rollback
php artisan migrate:rollback

# Check current state
php artisan migrate:status
```

### If Files Don't Upload
```bash
# Check storage permissions
chmod -R 755 storage/app/public

# Verify symlink
php artisan storage:link
```

### If View Doesn't Display
```bash
# Clear view cache
php artisan view:clear

# Verify file exists
ls resources/views/admin/kyc/show.blade.php
```

---

## 📋 Rollback Plan

If needed, to rollback all changes:

```bash
# Rollback migration
php artisan migrate:rollback

# Revert code to previous version
git revert <commit-hash>

# Clear all caches
php artisan cache:clear
```

---

**Status**: ✅ READY FOR PRODUCTION  
**Impact**: Medium (new optional fields)  
**Risk**: Low (backward compatible)  
**Testing**: Required before production

