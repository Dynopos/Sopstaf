<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamAssessmentItem extends Model
{
    protected $fillable = ['team_assessment_id', 'criteria_id', 'score', 'note'];

    protected function casts(): array
    {
        return ['score' => 'integer'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(TeamAssessment::class, 'team_assessment_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(KpiCriteria::class, 'criteria_id');
    }
}
