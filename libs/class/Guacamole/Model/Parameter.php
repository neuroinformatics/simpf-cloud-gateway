<?php

namespace SimPF\Guacamole\Model;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    /**
     * table name.
     *
     * @var string
     */
    protected $table = 'parameter';

    /**
     * primary key.
     *
     * @var array
     */
    protected $primaryKey = ['sid', 'name'];

    /**
     * disable incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * disable timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * garded property.
     *
     * @var array
     */
    protected $guarded = [];
}
