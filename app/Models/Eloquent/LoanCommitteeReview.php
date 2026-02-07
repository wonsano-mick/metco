<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanCommitteeReview extends Model
{
    use HasFactory;

    protected $table = 'loan_committee_reviews';

    protected $fillable = [
        'loan_id',
        'reviewed_by',
        'decision',
        'score',
        'risk_level',
        'recommendation',
        'comments',
        'conditions',
        'reviewed_at',
    ];

    protected $casts = [
        'loan_id' => 'integer',
        'reviewed_by' => 'integer',
        'score' => 'integer',
        'conditions' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
