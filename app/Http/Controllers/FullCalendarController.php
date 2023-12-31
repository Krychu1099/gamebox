<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class FullcalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $groups = $user->groups;
        $events = Event::where(function ($query) use ($user) {
            // Pobierz wydarzenia, które stworzył użytkownik
            $query->where('user_id', $user->id);

            // Pobierz wydarzenia, które są w grupach, do których użytkownik należy
            $groupIds = $user->groups->pluck('id');
            $query->orWhereIn('group_id', $groupIds);
        })->get();

        $groups = Group::all(); // Pobierz wszystkie grupy
        $selectedGroupId = Session::get('selected_group_id'); // Pobierz identyfikator grupy z sesji

        return view('calendar.fullcalendar', compact('groups', 'selectedGroupId', 'user', 'events',));

    }


    public function events(Request $request)
    {
        $user = Auth::user();
        $groupIdFromSession = Session::get('selected_group_id'); // Odczytaj identyfikator grupy z sesji

        // Sprawdź, czy identyfikator grupy został zapisany w sesji
        if ($groupIdFromSession) {
            $group = $user->groups()->find($groupIdFromSession);
            $eventData = [];

            // Jeśli użytkownik nie należy do grupy o podanym identyfikatorze z sesji,
            // zwracamy pustą kolekcję wydarzeń
            if (!$group) {
                return response()->json([]);
            }
        } else {
            // Jeśli identyfikator grupy nie jest zapisany w sesji, wykorzystaj identyfikator grupy użytkownika
            $group = $user->groups()->find($request->group);

            // Jeśli użytkownik nie należy do grupy o podanym identyfikatorze,
            // zwracamy pustą kolekcję wydarzeń
            if (!$group) {
                return response()->json([]);
            }
        }
        $groups = $user->groups;
        foreach ($groups as $group) {
            $events = $group->events;
            foreach ($events as $event) {
                $eventData[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start,
                    'end' => $event->end,
                ];
            }
        }

        return response()->json($eventData);
    }



    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'group' => 'required|exists:groups,id',
        ]);

        $group = Group::findOrFail($request->group);
        $users = $group->users;

        $event = new Event();
        $event->title = $request->title;
        $event->start = $request->start;
        $event->end = $request->end;
        $event->user_id = Auth::id();
        $event->group_id = $request->group;

        // Zapisz wydarzenie
        $event->save();

        // Zapisz identyfikator grupy w sesji użytkownika
        Session::put('selected_group_id', $request->group);

        return response()->json($event);
    }




    public function destroy(Request $request, $event)
    {
        $user = Auth::user();
        $event = Event::where('user_id', $user->id)->find($event);

        if (!$event) {
            return response()->json(['message' => 'Nie znaleziono spotkania.'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Spotkanie usunięte.']);
    }


    public function saveGroupId(Request $request)
    {
        $request->validate([
            'group' => 'required|exists:groups,id',
        ]);

        // Zapisz identyfikator grupy w sesji
        Session::put('selected_group_id', $request->group);

        return response()->json(['message' => 'Identyfikator grupy został zapisany w sesji.']);
    }

}
