<?php

namespace App\Models\HtwDresden\Pillnitz\Legacy;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $description;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
