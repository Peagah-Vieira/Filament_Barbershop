<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Category;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class MainController extends Controller
{
    public function show()
    {
        $category = Category::all();
        $barber = User::all();
        $weekdayHours = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
        ];

        if (session('success_message')) {
            Alert::success('Horário agendado!', session('success_message'));
        }

        if (session('error_message')) {
            Alert::error('Horário indisponível!', session('error_message'));
        }

        return view('index', [
            'categories' => $category,
            'barbers' => $barber,
            'weekdayHours' => $weekdayHours,
        ]);
    }

    public function store(Request $request)
    {
        $event = new Event();
        $requestStart = Carbon::parse($request->start);
        $requestStartTime = Carbon::parse($request->startTime);
        $databaseEventStart = Event::select('start')->where('start', $requestStart)->first();
        $databaseEventStartTime = Event::select('startTime')->where('startTime', $requestStartTime)->first();

        if ($databaseEventStart && $databaseEventStartTime) {
            $eventStart = Carbon::parse($databaseEventStart->start);
            $eventStartTime = Carbon::parse($databaseEventStartTime->startTime);

            if ($requestStart == $eventStart && $requestStartTime == $eventStartTime) {
                return back()->with('error_message', 'Horário já agendado, agende outro horário, por gentileza.')->withInput();
            }

            $event->subject = $request->subject;
            $event->body = $request->body;
            $event->number = $request->number;
            $event->category = $request->category;
            $event->participants = [1];
            $event->organizer = 1;
            $event->start = $request->start;
            $event->startTime = $request->startTime;
            $event->end = Carbon::create($day = $request->start, $tz = 'GMT');
            $event->endTime = Carbon::create($hour = $request->startTime, $tz = 'GMT')->addHour(1);
            $event->created_at = now();
            $event->updated_at = now();
            $event->save();

            $payment = new Payment();
            $payment->fullname = $request->subject;
            $payment->category_id = $request->category;
            $payment->paid = 0;
            $payment->save();

            return back()->with('success_message', 'Seu agendamento foi marcado no dia' . ' ' . Carbon::parse($request->start)->format('d/m') . ' ' . 'no horário de' . ' ' . $request->startTime. '.');
        } else {
            $event->subject = $request->subject;
            $event->body = $request->body;
            $event->number = $request->number;
            $event->category = $request->category;
            $event->participants = [1];
            $event->organizer = 1;
            $event->start = $request->start;
            $event->startTime = $request->startTime;
            $event->end = Carbon::create($day = $request->start, $tz = 'GMT');
            $event->endTime = Carbon::create($hour = $request->startTime, $tz = 'GMT')->addHour(1);
            $event->created_at = now();
            $event->updated_at = now();
            $event->save();

            $payment = new Payment();
            $payment->fullname = $request->subject;
            $payment->category_id = $request->category;
            $payment->paid = 0;
            $payment->save();

            return back()->with('success_message', 'Seu agendamento foi marcado no dia' . ' ' . Carbon::parse($request->start)->format('d/m') . ' ' . 'no horário de' . ' ' . $request->startTime . '.');
        }
    }
}
