<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;

class Log extends Model
{
  use HasFactory;
  use SoftDeletes;
  //   use InteractsWithMedia;

  protected $table = 'logs';

  protected $casts = [
    'file' => 'array',
  ];

  protected $fillable = [
    'title',
    'color',
    'date',
    'texto',
    'user_id',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
