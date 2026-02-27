# Quick Update - KYC Guarantor Support

**Status**: ✅ DONE  
**Files Updated**: 5  
**New Columns**: 8  
**New Migration**: 1

---

## What Changed

The KYC system backend has been updated to support the new mobile form which includes:

✅ **New Client Fields**
- Phone number (separate field)
- ID front image
- ID back image  
- Client photo
- Signed document

✅ **New Guarantor Section**
- Guarantor name
- Guarantor phone
- Guarantor ID front
- Guarantor ID back

---

## Files Modified

### 1. **NEW: Migration**
`database/migrations/2026_02_06_100000_add_guarantor_fields_to_kycs_table.php`
- Adds 8 columns to kycs table
- Nullable fields (backward compatible)

### 2. **UPDATED: Model**
`app/Models/KYC.php`
- Added 10 new fields to $fillable

### 3. **UPDATED: API Controller**
`app/Http/Controllers/Api/KYCController.php`
- Added file validation for new documents
- Added file storage logic for guarantor docs
- Updated create/update logic

### 4. **UPDATED: Admin API**
`app/Http/Controllers/Api/Admin/KYCController.php`
- Updated formatKYC() to include guarantor fields

### 5. **UPDATED: Web View**
`resources/views/admin/kyc/show.blade.php`
- Separated client documents section
- Added client photo display
- **Added new guarantor section** with documents

---

## How to Deploy

### 1️⃣ Run Migration
```bash
php artisan migrate
```

### 2️⃣ Create Storage Dirs (Optional - Laravel handles it)
```bash
mkdir -p storage/app/public/kyc/{client_photos,signed_documents,guarantor_documents}
```

### 3️⃣ Test
```bash
# Visit admin page
http://localhost:8000/admin/kyc

# Check if new columns exist
php artisan tinker
> Schema::getColumnListing('kycs')
```

---

## Mobile Integration

The mobile app form already has all the fields. Just ensure the API call includes:

```javascript
// Client documents
id_front_image: File
id_back_image: File
client_photo: File
signed_document: File

// Guarantor
guarantor_full_name: String
guarantor_phone: String
guarantor_id_front: File
guarantor_id_back: File
```

---

## What It Looks Like in Admin

When viewing KYC details in `/admin/kyc/{id}`:

```
📄 INFORMATIONS PERSONNELLES
└─ Full name, DOB, ID, Address, etc.

📋 DOCUMENTS DU CLIENT
├─ Pièce d'Identité (front & back)
├─ Photo du Client
└─ Document Signé

👤 INFORMATIONS DU GARANT (NEW)
├─ Nom Complet: [guarantor name]
├─ Téléphone: [guarantor phone]
└─ 📄 Documents du Garant
   ├─ Pièce d'Identité (Recto)
   └─ Pièce d'Identité (Verso)
```

---

## Backward Compatibility

✅ All new fields are NULLABLE  
✅ Existing KYCs still work  
✅ Guarantor section only shows if data exists  
✅ No breaking changes

---

## Testing Quick Checklist

- [ ] Run `php artisan migrate`
- [ ] Check `/admin/kyc` loads
- [ ] View a KYC - should work normally
- [ ] Check guarantor section not showing (if no data)
- [ ] In mobile app, submit KYC with guarantor
- [ ] In admin, verify guarantor section shows
- [ ] Click guarantor ID images - should zoom

---

## Troubleshooting

**Migration failed?**
```bash
php artisan migrate:reset
php artisan migrate
```

**View not showing?**
```bash
php artisan view:clear
```

**Files not uploading?**
```bash
php artisan storage:link
chmod 755 storage/app/public
```

---

## Files Reference

```
📁 Modified Files:
├─ database/migrations/2026_02_06_100000_add_guarantor_fields_to_kycs_table.php [NEW]
├─ app/Models/KYC.php [+10 lines]
├─ app/Http/Controllers/Api/KYCController.php [+50 lines]
├─ app/Http/Controllers/Api/Admin/KYCController.php [+2 lines]
└─ resources/views/admin/kyc/show.blade.php [+70 lines]
```

---

**Total Changes**: ~135 lines of code  
**Breaking Changes**: None  
**Deployment Time**: 2 minutes  
**Testing Time**: 10 minutes

---

For detailed information, see: `KYC_GUARANTOR_IMPLEMENTATION.md`

