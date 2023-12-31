<?php

namespace Greenreader9\MofhClient\Message;

class UnsuspendResponse extends AbstractResponse
{
    protected $status;

    /**
     * Parse the additional parameters present in the response string.
     */
    protected function parseResponse()
    {
        parent::parseResponse();

        if (! $this->isSuccessful()) {
            if (preg_match('/account is NOT currently suspended \(status : (\w*) \)/', $this->getMessage(), $matches)) {
                if (trim($matches[1]) == '') {
                    $this->status = 'd';
                } else {
                    $this->status = trim($matches[1]);
                }
            }
        }
    }

    /**
     * Get the status of the account if it's not suspended.
     *
     * Is one of the following chars:
     * - a: active
     * - r: reactivating
     * - c: closing
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
}
