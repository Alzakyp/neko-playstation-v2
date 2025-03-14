<?php

namespace App\Traits;

trait AlertMessage
{
    public function successMessage($message)
    {
        return session()->flash('success', $message);
    }

    public function errorMessage($message)
    {
        return session()->flash('error', $message);
    }

    protected function warningMessage($message)
    {
        session()->flash('warning', $message);
    }

    /**
     * Flash info message to session
     *
     * @param string $message
     * @return void
     */
    protected function infoMessage($message)
    {
        session()->flash('info', $message);
    }
}
