<?php

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * Store which fields are boolean in the model
     *
     * Note that any boolean fields not in the fillable
     * array will not be automatically set in the repo
     *
     * @var array
     */
    protected $booleanFields = [];

    /**
     * Return all boolean fields for the model
     *
     * @return array
     */
    public function getBooleanFields()
    {
        return $this->booleanFields;
    }
}
