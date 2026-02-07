<?php

namespace App\Models\Eloquent;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    // protected $primaryKey = 'id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'relationship_manager_id',
        'customer_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_alt',
        'date_of_birth',
        'gender',
        'nationality',
        'id_type',
        'id_number',
        'id_expiry_date',
        'id_issuing_country',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'occupation',
        'employer_name',
        'employer_address',
        'monthly_income',
        'source_of_income',
        'net_worth',
        'risk_profile',
        'kyc_status',
        'verified_by',
        'kyc_rejection_reason',
        'kyc_rejected_at',
        'kyc_rejected_by',
        'profile_photo_path',
        'id_front_image_path',
        'id_back_image_path',
        'signature_image_path',
        'marital_status',
        'dependents',
        'education_level',
        'emergency_contacts',
        'next_of_kin',
        'additional_documents',
        'status',
        'customer_type',
        'customer_tier',
        'registered_at',
        'verified_at',
        'last_reviewed_at',
        'notes',
        'metadata',
        'company_name',
        'organization_type', // e.g., 'corporation', 'llc', 'partnership', 'ngo'
        'registration_number',
        'tax_identification_number',
        'industry',
        'business_nature',
        'contact_person',
        'authorized_signatories',
        'is_blacklisted',
        'blacklist_reason',
        'customer_type',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'relationship_manager_id' => 'integer',
        'date_of_birth' => 'date',
        'id_expiry_date' => 'date',
        'monthly_income' => 'decimal:4',
        'net_worth' => 'decimal:4',
        'emergency_contacts' => 'array',
        'next_of_kin'=>'array',
        'additional_documents' => 'array',
        'authorized_signatories' => 'array',
        'metadata' => 'array',
        'registered_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
        'kyc_rejected_at' => 'datetime',
    ];

    protected $appends = [
        'full_name',
        'profile_photo_url',
        'id_front_image_url',
        'id_back_image_url',
        'signature_image_url',
        'age',
        'is_kyc_verified',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = Uuid::uuid4()->toString();
    //         }
    //         if (empty($model->customer_number)) {
    //             $model->customer_number = self::generateCustomerNumber();
    //         }
    //         if (empty($model->registered_at)) {
    //             $model->registered_at = now();
    //         }
    //     });
    // }

    public static function generateCustomerNumber(): string
    {
        $prefix = 'CUST';
        $year = date('Y');
        $month = date('m');
        $sequence = str_pad(self::count() + 1, 6, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $sequence;
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function relationshipManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relationship_manager_id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'customer_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        if ($this->customer_type === 'organization' && $this->company_name) {
            return $this->company_name;
        }
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return $this->defaultProfilePhoto();
        }
        return asset('storage/' . $this->profile_photo_path);
    }

    public function getIdFrontImageUrlAttribute(): ?string
    {
        return $this->id_front_image_path ? asset('storage/' . $this->id_front_image_path) : null;
    }

    public function getIdBackImageUrlAttribute(): ?string
    {
        return $this->id_back_image_path ? asset('storage/' . $this->id_back_image_path) : null;
    }

    public function getSignatureImageUrlAttribute(): ?string
    {
        return $this->signature_image_path ? asset('storage/' . $this->signature_image_path) : null;
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return now()->diffInYears($this->date_of_birth);
    }

    public function getIsKycVerifiedAttribute(): bool
    {
        return $this->kyc_status === 'verified';
    }

    protected function defaultProfilePhoto(): string
    {
        $name = trim(collect(explode(' ', $this->full_name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('kyc_status', 'verified');
    }

    public function verifyKyc()
    {
        $this->update([
            'kyc_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => Auth::user()->id,
        ]);

        // Log activity
        activity()
            ->causedBy(Auth::user())
            ->performedOn($this)
            ->withProperties([
                'customer_number' => $this->customer_number,
                'old_status' => 'pending',
                'new_status' => 'verified'
            ])
            ->log('Customer KYC verified');
    }

    public function rejectKyc($reason = null)
    {
        $this->update([
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $reason,
            'kyc_rejected_at' => now(),
            'kyc_rejected_by' => Auth::user()->id,
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($this)
            ->withProperties([
                'customer_number' => $this->customer_number,
                'reason' => $reason
            ])
            ->log('Customer KYC rejected');
    }

    public function pendingKyc()
    {
        $this->update([
            'kyc_status' => 'pending',
            'verified_at' => null,
            'verified_by' => null,
            'kyc_rejection_reason' => null,
            'kyc_rejected_at' => null,
            'kyc_rejected_by' => null,
        ]);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('customer_number', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('id_number', 'like', "%{$search}%");
        });
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canOpenAccount(): bool
    {
        return $this->isActive() && $this->is_kyc_verified;
    }

    public function totalBalance()
    {
        return $this->accounts()->sum('current_balance');
    }

    public function activeAccountsCount()
    {
        return $this->accounts()->where('status', 'active')->count();
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function activeBeneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class)->active();
    }

    /**
     * Get organization name (for organizations) or full name (for individuals)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->customer_type === 'organization' && $this->company_name) {
            return $this->company_name;
        }
        return $this->full_name;
    }

    /**
     * Get contact person for organizations
     */
    public function getContactPersonAttribute(): ?string
    {
        if ($this->customer_type === 'organization') {
            return $this->attributes['contact_person'] ?? null;
        }
        return $this->full_name;
    }

    /**
     * Get formatted annual revenue for organizations
     */
    public function getAnnualRevenueFormattedAttribute(): ?string
    {
        if ($this->customer_type === 'organization' && isset($this->attributes['annual_revenue'])) {
            return number_format($this->attributes['annual_revenue'], 2);
        }
        return null;
    }

    /**
     * Get organization type label
     */
    public function getOrganizationTypeLabelAttribute(): ?string
    {
        if ($this->customer_type === 'organization' && $this->organization_type) {
            $types = [
                'corporation' => 'Corporation',
                'llc' => 'Limited Liability Company',
                'partnership' => 'Partnership',
                'sole_proprietorship' => 'Sole Proprietorship',
                'ngo' => 'Non-Governmental Organization',
                'government' => 'Government Entity',
                'other' => 'Other',
            ];
            return $types[$this->organization_type] ?? ucfirst($this->organization_type);
        }
        return null;
    }

    /**
     * Get industry label
     */
    public function getIndustryLabelAttribute(): ?string
    {
        if ($this->customer_type === 'organization' && $this->industry) {
            $industries = [
                'agriculture' => 'Agriculture',
                'manufacturing' => 'Manufacturing',
                'construction' => 'Construction',
                'retail' => 'Retail & Wholesale',
                'technology' => 'Technology',
                'finance' => 'Finance & Insurance',
                'healthcare' => 'Healthcare',
                'education' => 'Education',
                'real_estate' => 'Real Estate',
                'transportation' => 'Transportation',
                'hospitality' => 'Hospitality & Tourism',
                'other' => 'Other',
            ];
            return $industries[$this->industry] ?? ucfirst($this->industry);
        }
        return null;
    }
}
