<?php

namespace SimPF\Guacamole\Model;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    /**
     * table name.
     *
     * @var string
     */
    protected $table = 'connection';

    /**
     * primary key.
     *
     * @var string
     */
    protected $primaryKey = 'sid';

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
