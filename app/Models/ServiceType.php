<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceCategory;

class ServiceType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['service_category_id', 'name'];

    public static function labCategoryIds(): array
    {
        return ServiceCategory::where('name', 'like', '%aborator%')->pluck('id')->toArray();
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }
}
