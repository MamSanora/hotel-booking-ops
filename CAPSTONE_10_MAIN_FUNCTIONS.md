# 10 Main Functions of the Capstone Hotel Booking System

*Project: Dara Meas Hotel — Online Hotel Booking & Operations System*  
*Prepared for: Capstone Defense & System Documentation*

---

## 1. Online Room Discovery & Real-Time Search (Guest)
* **Description:** Allows visitors to browse hotel rooms (`/rooms`) with real-time filtering by room type (Standard, Deluxe, Executive, Suite) and desired check-in/check-out dates.
* **Technical Highlights:** Displays rich room details, amenities, occupancy limits, and pricing per night calculated dynamically.

---

## 2. Self-Service Online Booking & Double-Booking Prevention (Core Capstone Feature)
* **Description:** Registered guests can book available rooms online (`POST /rooms/{room}/book`).
* **Technical Highlights:** Implements strict database date-range overlap checking (`isAvailableForDates`). If another booking overlaps with the selected dates, the system blocks double-booking automatically and calculates the exact duration and total price (`Nights × Price per Night`).

---

## 3. Multi-Gateway QR Payment System (KHQR Bakong & ABA PayWay)
* **Description:** Routes bookings to an interactive payment page (`/payment/{booking}`) supporting both Cambodia’s official **KHQR (Bakong / NBC)** and **ABA PayWay**.
* **Technical Highlights:** Dynamically generates EMVCo-compliant KHQR strings (Tag 29) with CRC verification and official Bakong/KHQR branding.

---

## 4. Automated Payment Verification & Instant E-Receipt Generation
* **Description:** Continuously polls payment status via asynchronous AJAX (`/payment/{booking}/check-status`) without requiring page refreshes.
* **Technical Highlights:** Upon payment confirmation, the system automatically marks the transaction as paid, promotes the booking status to `booked`, and redirects to a digital receipt page (`/payment/success/{booking}`) with a unique reference code (`BK-000XXX`).

---

## 5. Multi-Role Authentication & Role-Based Access Control (RBAC)
* **Description:** Provides three completely isolated authentication guards and portals:
  * **Guest Portal (`web` guard):** Customer self-registration, login, and personal booking history (`/guest/dashboard`).
  * **Reception Portal (`staff` guard):** Front-desk staff operations (`/reception/dashboard`).
  * **Admin Portal (`admin` guard):** Executive management and administrative control (`/admin/dashboard`).

---

## 6. Front-Desk Walk-In Booking System (Reception Staff)
* **Description:** Enables receptionists to rapidly create bookings for walk-in guests directly at the hotel counter (`/reception/walk-in/create`).
* **Technical Highlights:** Bypasses online payment requirements, allowing staff to record cash payments or manual counter transactions instantly.

---

## 7. Guest Check-In & Check-Out Lifecycle Management (Reception Staff)
* **Description:** Manages the physical guest lifecycle from arrival to departure (`/reception/checkin/{booking}` and `/reception/checkout/{booking}`).
* **Technical Highlights:** Automatically updates room occupancy status (`Available` ↔ `Occupied`) in real-time when guests check in or out.

---

## 8. Stay Extensions & Room Service Requests
* **Description:** Allows active guests or reception staff to extend their stay (`/reception/extend-stay/{booking}`) or request room service items.
* **Technical Highlights:** Automatically calculates the cost for extra nights, verifies that no upcoming bookings collide with the extension, and tracks additional charges.

---

## 9. Comprehensive Room Management CRUD (Admin)
* **Description:** Administrators can create, edit, update, and remove hotel rooms (`/admin/rooms`).
* **Technical Highlights:** Controls room numbers, room categories, pricing tiers, maximum occupancy, descriptions, and operational availability status.

---

## 10. Executive Dashboard, Gallery & Staff Management (Admin)
* **Description:** Provides administrators with a centralized control center (`/admin/dashboard`) to oversee all hotel operations.
* **Technical Highlights:** Includes full staff user management (`/admin/staff`), booking approval/cancellation workflows (`/admin/bookings`), website gallery management (`/admin/gallery`), and customer inquiry tracking (`/admin/messages`).
