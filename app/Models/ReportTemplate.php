<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $subject_template
 * @property string $body_template
 * @property string|null $variables
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate query()
 * @mixin \Eloquent
 */
class ReportTemplate extends Model
{
    protected $fillable = [
        'name', 'type', 'subject_template', 'body_template', 'variables',
    ];

    /**
     * Render the template with provided variables
     */
    public function render(array $variables = [])
    {
        $subject = $this->subject_template;
        $body = $this->body_template;

        // Replace variable placeholders
        foreach ($variables as $key => $value) {
            $placeholder = "{{{$key}}}"; // {{{variable_name}}} format
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}
