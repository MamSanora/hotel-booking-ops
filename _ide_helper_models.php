<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * Admin Model
 *
 * Represents a hotel administrator. Admins authenticate via the 'admin'
 * guard using a username and passwordhash (not email). Two role levels
 * exist: 'superadmin' has unrestricted access; 'admin' has standard access.
 *
 * @property int    $id
 * @property string $full_name
 * @property string $role          'superadmin' | 'admin'
 * @property string $username
 * @property string $passwordhash
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItemsCatalog> $itemsCatalogs
 * @property-read int|null $items_catalogs_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoomManagement> $roomManagements
 * @property-read int|null $room_managements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Staff> $staff
 * @property-read int|null $staff_count
 * @method static \Database\Factories\AdminFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePasswordhash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUsername($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * AuthMethod Model
 *
 * Represents an OAuth / social login provider linked to a guest account.
 * A guest can have multiple providers (e.g. Google and Facebook) linked
 * to the same guest profile.
 *
 * @property int    $id
 * @property int    $guest_id
 * @property string $provider      e.g. 'google', 'facebook', 'github'
 * @property string $provider_key  The OAuth provider's unique user ID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guest $guest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod whereProviderKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthMethod whereUpdatedAt($value)
 */
	class AuthMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * Booking Model
 *
 * Core operational model tracking every room reservation. Supports both
 * self-service (online) bookings by registered guests and proxy bookings
 * created by receptionists for walk-in or phone guests.
 *
 * Status lifecycle:
 *   pending → booked → checked-in → checked-out
 *                    ↘ cancelled / no_show
 *
 * @property int         $id
 * @property int|null    $guest_id
 * @property int|null    $room_id
 * @property int|null    $handled_by_staff_id
 * @property string|null $check_in_date
 * @property string|null $check_out_date
 * @property int         $number_of_stay_extension
 * @property float|null  $total_price
 * @property string      $booking_status
 * @property string|null $guest_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guest|null $guest
 * @property-read \App\Models\Staff|null $handledBy
 * @property-read \App\Models\Room|null $room
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoomService> $roomServices
 * @property-read int|null $room_services_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking arrivingToday()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking booked()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking checkedIn()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking checkedOut()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking departingToday()
 * @method static \Database\Factories\BookingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereBookingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCheckInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCheckOutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereHandledByStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereNumberOfStayExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedAt($value)
 */
	class Booking extends \Eloquent {}
}

namespace App\Models{
/**
 * Contact Model
 *
 * Stores messages submitted via the public contact form on the hotel website.
 * Admins can read and respond to these from the admin message inbox.
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereUpdatedAt($value)
 */
	class Contact extends \Eloquent {}
}

namespace App\Models{
/**
 * Gallery Model
 *
 * Stores hotel gallery images managed by admins and displayed on the
 * public-facing hotel website.
 *
 * @property int    $id
 * @property string $image  Relative path or filename of the uploaded image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereUpdatedAt($value)
 */
	class Gallery extends \Eloquent {}
}

namespace App\Models{
/**
 * Guest Model
 *
 * Represents a hotel guest's profile. This model is intentionally NOT
 * authenticatable — it holds profile data only. Authentication (email +
 * passwordhash) is managed by GuestAuth, and OAuth by AuthMethod.
 *
 * This design means a guest can exist in the system without a login account,
 * which supports walk-in, phone, and proxy bookings by receptionists.
 *
 * @property int         $id
 * @property string      $full_name
 * @property string|null $gender       'male'|'female'|'other'|'prefer_not_to_say'
 * @property string|null $nationality
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $activeBooking
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AuthMethod> $authMethods
 * @property-read int|null $auth_methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \App\Models\GuestAuth|null $guestAuth
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Phone> $phones
 * @property-read int|null $phones_count
 * @method static \Database\Factories\GuestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereUpdatedAt($value)
 */
	class Guest extends \Eloquent {}
}

namespace App\Models{
/**
 * GuestAuth Model
 *
 * The authenticatable model for the 'web' guard. Holds login credentials
 * (email + passwordhash) for guests who register an online account.
 *
 * This model intentionally separates authentication from profile data.
 * The guest's profile (name, gender, nationality) lives in the `guests`
 * table and is accessed via the guest() relationship.
 *
 * Implements MustVerifyEmail to enable the existing email verification flow.
 *
 * @property int         $id
 * @property int         $guest_id
 * @property string      $email
 * @property string      $passwordhash
 * @property string|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guest $guest
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\GuestAuthFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth wherePasswordhash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestAuth whereUpdatedAt($value)
 */
	class GuestAuth extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * ItemsCatalog Model
 *
 * Represents a requestable item available to guests during their stay.
 * Items are categorised (amenity, bedding, beverage) and managed by admins.
 * Guests select from this catalog when submitting a room service request.
 *
 * @property int         $id
 * @property string      $item_name
 * @property string|null $category          'amenity' | 'bedding' | 'beverage'
 * @property int|null    $created_by_admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestedItem> $requestedItems
 * @property-read int|null $requested_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog whereCreatedByAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemsCatalog whereUpdatedAt($value)
 */
	class ItemsCatalog extends \Eloquent {}
}

namespace App\Models{
/**
 * Phone Model
 *
 * Stores one or more phone numbers per guest. Separated into its own
 * table to support guests with multiple contact numbers.
 *
 * @property int    $id
 * @property int    $guest_id
 * @property string $phone_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guest $guest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Phone whereUpdatedAt($value)
 */
	class Phone extends \Eloquent {}
}

namespace App\Models{
/**
 * RequestedItem Model
 *
 * Junction model linking a room service request to specific catalog items.
 * Records which items were requested and in what quantity.
 *
 * Rows are append-only — no updated_at timestamp is stored.
 *
 * @property int $id
 * @property int $request_id
 * @property int $catalog_id
 * @property int $amount_per_item
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\ItemsCatalog $catalog
 * @property-read \App\Models\RoomService $roomService
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem whereAmountPerItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem whereCatalogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestedItem whereRequestId($value)
 */
	class RequestedItem extends \Eloquent {}
}

namespace App\Models{
/**
 * Room Model
 *
 * Represents a physical hotel room. Replaces the old Room model which had
 * legacy columns (room_title, wifi, bed_type) and a separate RoomType FK.
 * Room type is now stored directly as an enum for simplicity.
 *
 * @property int         $id
 * @property string|null $room_number
 * @property string      $current_status  'available' | 'occupied'
 * @property string|null $room_type
 * @property float|null  $price_per_night
 * @property int|null    $capacity
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $activeBooking
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoomManagement> $roomManagements
 * @property-read int|null $room_managements_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room availableForDates(string $checkIn, string $checkOut, ?int $excludeBookingId = null)
 * @method static \Database\Factories\RoomFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room occupied()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCurrentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room wherePricePerNight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRoomType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereUpdatedAt($value)
 */
	class Room extends \Eloquent {}
}

namespace App\Models{
/**
 * RoomManagement Model
 *
 * Represents a single audit log entry recording an administrative action
 * performed on a room. Rows are append-only — never updated after creation.
 *
 * Actions:
 *   add_room     → Admin added a new room to the system.
 *   update_price → Admin changed the room's price_per_night.
 *
 * @property int    $id
 * @property int    $room_id
 * @property int    $managed_by_admin_id
 * @property string $action              'add_room' | 'update_price'
 * @property string $created_at
 * @property-read \App\Models\Admin $managedBy
 * @property-read \App\Models\Room $room
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement whereManagedByAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomManagement whereRoomId($value)
 */
	class RoomManagement extends \Eloquent {}
}

namespace App\Models{
/**
 * RoomService Model
 *
 * Represents a guest request or complaint submitted during their stay.
 * Corresponds to Process 6.0 (Room Services) in the DFD.
 *
 * Guests submit requests from their room-accessible interface.
 * Receptionists view, claim, and respond to requests via their dashboard.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property int|null    $handled_by_staff_id
 * @property string|null $request_type    'request' | 'complaint'
 * @property string|null $guest_notes
 * @property string      $request_status  'pending'|'confirmed'|'completed'|'cancelled'|'denied'
 * @property string|null $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @property-read \App\Models\Staff|null $handledBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestedItem> $requestedItems
 * @property-read int|null $requested_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereGuestNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereHandledByStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereRequestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereRequestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomService whereUpdatedAt($value)
 */
	class RoomService extends \Eloquent {}
}

namespace App\Models{
/**
 * Staff Model
 *
 * Represents a front-desk receptionist. Replaces the old Receptionist model.
 * Staff authenticate via the 'staff' guard using username and passwordhash.
 *
 * Each staff member is optionally linked to the admin who manages them.
 * Staff can handle bookings (walk-in proxy bookings) and room service requests.
 *
 * @property int    $id
 * @property string $full_name
 * @property string $role             'receptionist'
 * @property int    $managed_by_admin_id
 * @property string $username
 * @property string $passwordhash
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \App\Models\Admin|null $managedBy
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoomService> $roomServices
 * @property-read int|null $room_services_count
 * @method static \Database\Factories\StaffFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereManagedByAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff wherePasswordhash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereUsername($value)
 */
	class Staff extends \Eloquent {}
}

namespace App\Models{
/**
 * Transaction Model
 *
 * Records all payment transactions for bookings. Replaces the old Payment
 * model (which had Stripe-specific and ABA PayWay-specific columns) with a
 * simpler design covering the two accepted payment methods: cash and KHQR.
 *
 * A booking can have multiple transactions — e.g. one for the initial
 * booking payment and one or more for stay extensions (Process 5.0 DFD).
 *
 * The 'half' payment_status supports Process 3.2 ("Confirm Remaining Balance")
 * in the DFD — a guest pays part upfront and the balance on check-in.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property float       $amount_paid
 * @property string|null $payment_for     'booking' | 'stay_extension'
 * @property string|null $payment_method  'cash' | 'khqr'
 * @property string      $payment_status  'pending'|'half'|'full'|'refunded'
 * @property string|null $transaction_id
 * @property string|null $merchant_reference
 * @property string|null $payment_link
 * @property string|null $qr_code_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction successful()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereMerchantReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereQrCodeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 */
	class Transaction extends \Eloquent {}
}

