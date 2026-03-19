<?php

namespace App\Traits;

use App\Models\SeoMetadata;

trait Seoable
{
    /**
     * Get the entity's SEO metadata universally.
     */
    public function seoMetadata()
    {
        return $this->morphOne(SeoMetadata::class, 'seoable')->withDefault([
            'meta_title' => '',
            'meta_description' => ''
        ]);
    }

    /**
     * Save SEO metadata easily
     */
    public function saveSeoMetadata(?string $title, ?string $description)
    {
        if (empty($title) && empty($description)) {
            $this->seoMetadata()->delete();
            return;
        }

        $this->seoMetadata()->updateOrCreate(
            ['seoable_id' => $this->id, 'seoable_type' => get_class($this)],
            ['meta_title' => $title, 'meta_description' => $description]
        );
    }
}
