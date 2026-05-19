<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SoapNote;
use Illuminate\Auth\Access\HandlesAuthorization;

class SoapNotePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SoapNote');
    }

    public function view(AuthUser $authUser, SoapNote $soapNote): bool
    {
        return $authUser->can('View:SoapNote');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SoapNote');
    }

    public function update(AuthUser $authUser, SoapNote $soapNote): bool
    {
        return $authUser->can('Update:SoapNote');
    }

    public function delete(AuthUser $authUser, SoapNote $soapNote): bool
    {
        return $authUser->can('Delete:SoapNote');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SoapNote');
    }

    public function restore(AuthUser $authUser, SoapNote $soapNote): bool
    {
        return $authUser->can('Restore:SoapNote');
    }

    public function forceDelete(AuthUser $authUser, SoapNote $soapNote): bool
    {
        return $authUser->can('ForceDelete:SoapNote');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SoapNote');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SoapNote');
    }

    public function replicate(AuthUser $authUser, SoapNote $soapNote): bool
    {
        return $authUser->can('Replicate:SoapNote');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SoapNote');
    }

}