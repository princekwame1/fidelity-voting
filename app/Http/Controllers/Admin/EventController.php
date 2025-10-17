<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Question;
use App\Models\Option;
use App\Services\VotingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private VotingService $votingService
    ) {}

    public function index(): View
    {
        $events = Event::with(['questions', 'votingSessions'])
            ->withCount(['votes', 'votingSessions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'voting_duration_minutes' => 'required|integer|min:5|max:1440',
            'show_results_table' => 'boolean',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string|max:500',
            'questions.*.multiple_choice' => 'boolean',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*' => 'required|string|max:255',
            'questions.*.subtexts' => 'nullable|array',
            'questions.*.subtexts.*' => 'nullable|string|max:255'
        ]);

        $event = Event::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'voting_duration_minutes' => $validated['voting_duration_minutes'],
            'show_results_table' => $validated['show_results_table'] ?? true,
            'created_by' => auth()->id()
        ]);

        foreach ($validated['questions'] as $questionData) {
            $question = Question::create([
                'event_id' => $event->id,
                'question_text' => $questionData['text'],
                'multiple_choice' => $questionData['multiple_choice'] ?? false
            ]);

            // Handle options with subtexts
            $subtexts = $questionData['subtexts'] ?? [];
            foreach ($questionData['options'] as $index => $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'subtext' => $subtexts[$index] ?? null
                ]);
            }
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event created successfully!');
    }

    public function show(Event $event): View
    {
        $event->load(['questions.options', 'votingSessions']);
        $stats = $this->votingService->getEventStats($event);
        $results = $this->votingService->getEventResults($event);

        return view('admin.events.show', compact('event', 'stats', 'results'));
    }

    public function edit(Event $event): View
    {
        $event->load(['questions.options']);
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'voting_duration_minutes' => 'required|integer|min:5|max:1440',
            'show_results_table' => 'boolean'
        ]);

        $validated['show_results_table'] = $validated['show_results_table'] ?? false;

        $event->update($validated);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->votes()->exists()) {
            return redirect()
                ->route('admin.events.index')
                ->with('error', 'Cannot delete event that has votes.');
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event deleted successfully!');
    }

    public function results(Event $event): JsonResponse
    {
        $results = $this->votingService->getEventResults($event);
        return response()->json($results);
    }

    public function stats(Event $event): JsonResponse
    {
        $stats = $this->votingService->getEventStats($event);
        return response()->json($stats);
    }

    public function displayQR(Event $event): View
    {
        return view('admin.events.qr-display', compact('event'));
    }
}
