<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 'username', 'email', 'password',
        'phone', 'avatar', 'role', 'bio',
        'location', 'website', 'social_links',
        'is_active', 'is_verified', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'social_links' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ============ العلاقات ============
    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function editedNews()
    {
        return $this->hasMany(News::class, 'editor_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'writer_id');
    }

    public function editedArticles()
    {
        return $this->hasMany(Article::class, 'editor_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function citizenReports()
    {
        return $this->hasMany(CitizenReport::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    // ============ Scopes ============
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeWriters($query)
    {
        return $query->where('role', 'writer');
    }

    public function scopeEditors($query)
    {
        return $query->where('role', 'editor');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeReaders($query)
    {
        return $query->where('role', 'reader');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('username', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%")
              ->orWhere('bio', 'LIKE', "%{$term}%");
        });
    }

    // ============ Attributes ============
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    public function getIsWriterAttribute()
    {
        return $this->role === 'writer';
    }

    public function getIsEditorAttribute()
    {
        return $this->role === 'editor';
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    public function getTotalViewsAttribute()
    {
        return $this->news()->sum('views_count');
    }

    public function getTotalArticlesAttribute()
    {
        return $this->articles()->count();
    }
}
