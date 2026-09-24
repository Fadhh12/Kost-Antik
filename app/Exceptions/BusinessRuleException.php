<?php

namespace App\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Pelanggaran aturan bisnis (BR-xx). Dirender sebagai redirect kembali
 * dengan toast error, bukan halaman 500.
 */
class BusinessRuleException extends RuntimeException
{
    public function render(Request $request): RedirectResponse
    {
        return back()->withInput()->with('error', $this->getMessage());
    }
}
