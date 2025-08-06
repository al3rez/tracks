<?php

namespace App\Models;

use Tracks\ActiveRecord\Base;
use Tracks\ActiveRecord\Query;

class Post extends Base
{
    protected static array $validations = [
        'title' => ['presence' => true, 'length' => ['minimum' => 3, 'maximum' => 255]],
        'content' => ['presence' => true],
        'author' => ['presence' => true],
    ];
    
    protected static array $callbacks = [
        'beforeCreate' => ['setDefaults'],
        'beforeSave' => ['generateSlug'],
    ];
    
    protected function setDefaults(): void
    {
        if (!isset($this->published)) {
            $this->published = false;
        }
        if (!isset($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
    }
    
    protected function generateSlug(): void
    {
        if (!empty($this->title)) {
            $this->slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->title), '-'));
        }
    }
    
    public function comments(): array
    {
        return Comment::where(['post_id' => $this->id])->orderBy('created_at', 'DESC')->get();
    }
    
    public function isPublished(): bool
    {
        return (bool) $this->published;
    }
    
    public static function published(): Query
    {
        return static::where(['published' => true]);
    }
    
    public static function recent(int $limit = 10): array
    {
        return static::where(['published' => true])
                     ->orderBy('created_at', 'DESC')
                     ->limit($limit)
                     ->get();
    }
}