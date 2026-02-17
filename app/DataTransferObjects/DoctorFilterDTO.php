<?php

namespace App\DataTransferObjects;

readonly class DoctorFilterDTO
{
    public function __construct(
        public ?string $specialization = null,
        public ?string $search = null,
        public string  $sort_by = 'name',
        public string  $sort_dir = 'asc',
        public int     $per_page = 15,
    ) {
         if ($this->search) {
            $this->search = strip_tags($this->search);
            $this->search = preg_replace('/[^\p{L}\p{N}\s]/u', '', $this->search);
        }
    }
}
