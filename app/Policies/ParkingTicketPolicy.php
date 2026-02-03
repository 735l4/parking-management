<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ParkingTicket;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ParkingTicketPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ParkingTicket');
    }

    public function view(AuthUser $authUser, ParkingTicket $parkingTicket): bool
    {
        return $authUser->can('View:ParkingTicket');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ParkingTicket');
    }

    public function update(AuthUser $authUser, ParkingTicket $parkingTicket): bool
    {
        return $authUser->can('Update:ParkingTicket');
    }

    public function delete(AuthUser $authUser, ParkingTicket $parkingTicket): bool
    {
        return $authUser->can('Delete:ParkingTicket');
    }

    public function restore(AuthUser $authUser, ParkingTicket $parkingTicket): bool
    {
        return $authUser->can('Restore:ParkingTicket');
    }

    public function forceDelete(AuthUser $authUser, ParkingTicket $parkingTicket): bool
    {
        return $authUser->can('ForceDelete:ParkingTicket');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ParkingTicket');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ParkingTicket');
    }

    public function replicate(AuthUser $authUser, ParkingTicket $parkingTicket): bool
    {
        return $authUser->can('Replicate:ParkingTicket');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ParkingTicket');
    }
}
