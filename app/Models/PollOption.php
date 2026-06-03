<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PollOption extends Model
{
    use HasTranslations;

    public $translatable = ['text'];

    protected $fillable = ['poll_id', 'text', 'color', 'image', 'votes_count', 'order'];
    protected $casts = ['text' => 'array'];

    public function poll() { return $this->belongsTo(Poll::class); }
    public function votes() { return $this->hasMany(PollVote::class); }

    public function getPercentageAttribute()
    {
        return $this->poll->total_votes > 0
            ? round(($this->votes_count / $this->poll->total_votes) * 100, 1)
            : 0;
    }
}
