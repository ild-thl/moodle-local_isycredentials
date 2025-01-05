<?php

namespace local_isycredentials\credential;

class issuer extends organisation {
    /**
     * Overrides the parent method to only return the original set id as is.
     * 
     * @override
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
}
