<?php

namespace App;

class Role extends Model
{
    protected $hidden = [
        'system',
    ];

    protected $fillable = [
        'value', 'system', 'slug', 'display',
    ];

    protected $casts = [
        'system' => 'boolean',
    ];

    protected $with = [
        'tenant',
    ];

    /**
     * Returns the SCIM representation
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'meta' => [
                'created' => $this->created_at ? $this->created_at->format('c') : null,
                'lastModified' => $this->updated_at ? $this->updated_at->format('c') : null,
                'resourceType' => 'Role',
            ],
            'value' => $this->id,
            'display' => $this->display,
            'slug' => $this->slug,
            'system' => $this->system,
            'tenant' => $this->relationLoaded('tenant') && $this->tenant ? $this->tenant->subdomain : null,
        ];
    }
}
