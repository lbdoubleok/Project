<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Camera;
use App\Models\Cat;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        try {
            $ok = Auth::guard('admin')->attempt([
                'ad_username' => $data['username'],
                'password'    => $data['password'],
            ]);
        } catch (RuntimeException $e) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'รหัสผ่านในฐานข้อมูลยังไม่ถูกเข้ารหัส — รัน php artisan admin:hash-passwords ก่อน',
            ]);
        }

        if (! $ok) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboards'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboards()
    {
        $today = today()->toDateString();

        $stats = [
            ['Total Bookings',    Booking::count(), '📋'],
            ["Today's Check-ins", Booking::whereDate('check_in', $today)->whereIn('status', ['confirmed', 'checked_in'])->count(), '📅'],
            ['Current Stays',     Booking::where('status', 'checked_in')->count(), '🏠'],
            ['Available Rooms',   Room::where('status', 'available')->count(), '✅'],
            ['Pending Payments',  Payment::where('status', 'pending')->count(), '💳'],
        ];

        $recent = Booking::with(['user', 'room.roomType', 'cats', 'payment'])
            ->latest('booking_id')
            ->limit(5)
            ->get();

        return view('admin.dashboards', compact('stats', 'recent'));
    }

    public function roomsIndex()
    {
        return view('admin.rooms.index', [
            'rooms' => Room::with('roomType')->orderBy('room_number')->get(),
            'types' => RoomType::withCount('rooms')->orderBy('room_type_id')->get(),
        ]);
    }

    public function roomCreate()
    {
        return view('admin.rooms.form', [
            'room'  => new Room(['status' => 'available']),
            'types' => RoomType::orderBy('room_type_id')->get(),
        ]);
    }

    public function roomStore(Request $request)
    {
        Room::create($this->validateRoom($request));

        return redirect()->route('admin.rooms.index')->with('success', 'เพิ่มห้องเรียบร้อย');
    }

    public function roomEdit(Room $room)
    {
        return view('admin.rooms.form', [
            'room'  => $room,
            'types' => RoomType::orderBy('room_type_id')->get(),
        ]);
    }

    public function roomUpdate(Request $request, Room $room)
    {
        $room->update($this->validateRoom($request, $room));

        return redirect()->route('admin.rooms.index')->with('success', 'บันทึกข้อมูลห้องแล้ว');
    }

    public function roomToggle(Room $room)
    {
        if ($room->status === 'occupied') {
            return back()->with('error', "ห้อง {$room->room_number} มีแมวพักอยู่ จึงปิดใช้งานไม่ได้");
        }

        $room->update(['status' => $room->status === 'maintenance' ? 'available' : 'maintenance']);

        return back()->with('success', "อัปเดตสถานะห้อง {$room->room_number} แล้ว");
    }

    public function roomTypeUpdate(Request $request, RoomType $roomType)
    {
        $roomType->update($request->validate([
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:10'],
            'description'     => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('success', "อัปเดตประเภทห้อง {$roomType->label} แล้ว");
    }

    private function validateRoom(Request $request, ?Room $room = null): array
    {
        $unique = Rule::unique('rooms', 'room_number');
        if ($room) {
            $unique->ignore($room->room_id, 'room_id');
        }

        return $request->validate([
            'room_number'  => ['required', 'string', 'max:10', $unique],
            'room_type_id' => ['required', 'exists:room_types,room_type_id'],
            'status'       => ['required', Rule::in(Room::STATUSES)],
        ]);
    }

    public function bookingsIndex(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $bookings = Booking::with(['user', 'room.roomType', 'cats', 'payment'])
            ->when(isset(Booking::STATUS_LABELS[$status]), fn ($qb) => $qb->where('status', $status))
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->whereHas('user', fn ($u) => $u->where('full_name', 'like', "%{$q}%"))
                      ->orWhereHas('room', fn ($r) => $r->where('room_number', 'like', "%{$q}%"))
                      ->orWhereHas('cats', fn ($c) => $c->where('name', 'like', "%{$q}%"));

                    if (preg_match('/^bk-?\d{4}(\d+)$/i', $q, $m)) {
                        $w->orWhere('booking_id', (int) $m[1]);
                    }
                });
            })
            ->latest('booking_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'q'        => $q,
            'status'   => $status,
            'statuses' => Booking::STATUS_LABELS,
        ]);
    }

    public function bookingShow(Booking $booking)
    {
        $booking->load(['user', 'room.roomType', 'cats', 'payments' => fn ($p) => $p->latest('payment_id')]);

        return view('admin.bookings.show', [
            'booking'  => $booking,
            'statuses' => Booking::STATUS_LABELS,
        ]);
    }

    public function bookingUpdateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::STATUS_LABELS))],
        ]);

        $booking->changeStatus($data['status']);

        return back()->with('success', 'อัปเดตสถานะเป็น "' . Booking::STATUS_LABELS[$data['status']] . '" แล้ว');
    }

    public function bookingCancel(Request $request, Booking $booking)
    {
        $data = $request->validate(['cancel_reason' => ['nullable', 'string', 'max:255']]);

        $booking->changeStatus('cancelled', $data['cancel_reason'] ?? null);

        return back()->with('success', 'ยกเลิกการจองแล้ว');
    }

    public function paymentsIndex(Request $request)
    {
        $status = (string) $request->query('status', 'all');

        $payments = Payment::with(['booking.user', 'booking.room.roomType'])
            ->when(in_array($status, Payment::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest('payment_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function paymentVerify(Payment $payment)
    {
        $payment->update([
            'status'        => 'verified',
            'verified_by'   => auth('admin')->id(),
            'verified_at'   => now(),
            'reject_reason' => null,
        ]);

        $booking = $payment->booking;
        if ($booking && $booking->status === 'pending') {
            $booking->changeStatus('confirmed');
        }

        return back()->with('success', 'ยืนยันการชำระเงินแล้ว');
    }

    public function paymentReject(Request $request, Payment $payment)
    {
        $data = $request->validate(['reject_reason' => ['required', 'string', 'max:255']]);

        $payment->update([
            'status'        => 'rejected',
            'verified_by'   => auth('admin')->id(),
            'verified_at'   => now(),
            'reject_reason' => $data['reject_reason'],
        ]);

        return back()->with('success', 'ปฏิเสธสลิปแล้ว');
    }

    public function customersIndex()
    {
        $customers = Users::withCount(['cats', 'bookings'])->orderBy('full_name')->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    public function customerShow(Users $customer)
    {
        $customer->load([
            'cats',
            'bookings' => fn ($b) => $b->with(['room.roomType', 'cats', 'payment'])->latest('booking_id'),
        ]);

        return view('admin.customers.show', compact('customer'));
    }

    public function catsIndex()
    {
        $cats = Cat::with(['user', 'bookings'])->orderBy('name')->paginate(12);

        return view('admin.cats.index', compact('cats'));
    }

    public function camerasIndex()
    {
        $cameras = Camera::with([
            'room.roomType',
            'room.bookings' => fn ($b) => $b->where('status', 'checked_in')->with('cats'),
        ])->orderBy('camera_code')->get();

        $rooms = Room::with('roomType')->orderBy('room_number')->get();

        return view('admin.cctv.index', compact('cameras', 'rooms'));
    }

    public function cameraStore(Request $request)
    {
        Camera::create($request->validate([
            'room_id'     => ['required', 'exists:rooms,room_id'],
            'camera_code' => ['required', 'string', 'max:50', 'unique:cameras,camera_code'],
        ]));

        return back()->with('success', 'เพิ่มกล้องแล้ว');
    }

    public function cameraDestroy(Camera $camera)
    {
        $camera->delete();

        return back()->with('success', 'ลบกล้องแล้ว');
    }
}