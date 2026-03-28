<?php

namespace App\Services;

use Illuminate\Support\Str;

class BaseService
{
    protected function generateUniqueSlug(string $name, $model, int $id = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while ($model::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
