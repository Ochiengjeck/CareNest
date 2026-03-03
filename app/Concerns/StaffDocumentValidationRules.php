<?php

namespace App\Concerns;

trait StaffDocumentValidationRules
{
    protected function staffDocumentRules(): array
    {
        return [
            'docTitle' => ['required', 'string', 'max:255'],
            'docDescription' => ['nullable', 'string', 'max:1000'],
            'docYear' => ['nullable', 'integer', 'digits:4', 'min:2000', 'max:2099'],
            'docFile' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'docExpiry' => ['nullable', 'date', 'after:today'],
            'docNotes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function staffDocumentUpdateRules(): array
    {
        return array_merge($this->staffDocumentRules(), [
            'docFile' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);
    }
}
