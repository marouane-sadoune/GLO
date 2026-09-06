<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    public function view(User $user, Document $document): bool
    {
        return $user->can('documents.view') && $this->inScope($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->can('documents.upload');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.delete') && $this->inScope($user, $document);
    }

    private function inScope(User $user, Document $document): bool
    {
        return Document::visibleTo($user)->whereKey($document->id)->exists();
    }
}
