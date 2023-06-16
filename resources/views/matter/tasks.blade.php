@php 
  $ncols = 7; 
@endphp
<table class="table table-hover table-sm">
  <thead class="thead-light">
    <tr>
      <th>{{ _i("Tasks by event") }}</th>
      <th>{{ _i("Due date") }}</th>
      <th>{{ _i("OK") }}</th>
      <th>{{ _i("Date") }}</th>
      @cannot('client')
      @if($is_renewals)
      @php
        $ncols += 3;
      @endphp
      <th>{{ _i("Cost") }}</th>
      <th>{{ _i("Fee") }}</th>
      <th>{{ _i("Cur.") }}</th>
      {{-- <th>{{ _i("Time") }}</th> --}}
      @endif
      @endcannot
      <th>{{ _i("By") }}</th>
      <th>{{ _i("Notes") }}</th>
      <th style="width: 24px;">&nbsp;</th>
      @cannot('client')
      <th>{{ _i("Email") }}</th>
      @endcannot
    </tr>
  </thead>
  @foreach ( $events as $event )
  <tbody>
    <tr class="reveal-hidden">
      <td colspan="{{ $ncols }}">
        <ul class="list-inline my-1">
          <li class="list-inline-item">{{ $event->info->name }}</li>
          <li class="list-inline-item">{{ $event->event_date->isoFormat('L') }}</li>
          @canany(['admin', 'readwrite'])
          <span class="hidden-action float-right">
            <li class="list-inline-item">
              <a href="#" class="text-primary" id="addTaskToEvent" data-event_id="{{ $event->id }}" title="{{ _i('Add task to ') . $event->info->name }}">
                &CirclePlus;
              </a>
            </li>
            <li class="list-inline-item">
              <a href="#" class="text-danger" id="deleteEvent" data-event_id="{{ $event->id }}" title="{{ _i('Delete event (with tasks)') }}">
                &CircleTimes;
              </a>
            </li>
            <li class="list-inline-item" style="font-size:1rem">
              <a href="#" class="text-danger" id="regenerateTasks" data-event_id="{{ $event->id }}" title="{{ _i('Regenerate Tasks') }}">
                &#8623;
              </a>
            </li>
          </span>
          @endcanany
        </ul>
      </td>
      @cannot('client')
      <td class="text-center align-middle lead">
        @if (count(App\EventName::where('code', $event->code)->first()->templates) != 0)
          <a href="#" class="chooseTemplate text-info" data-url="/document/select/{{ $matter->id }}?EventName={{ $event->code }}&Event={{ $event->id }}">&#9993;</a>
        @endif
      </td>
      @endcannot
    </tr>
  
    @foreach ($event->tasks as $task)
    <tr class="reveal-hidden {{ $task->done ? 'text-success' : 'text-danger' }}" data-resource="/task/{{ $task->id }}">
      <td nowrap>
        <span class="ml-2">{{ $task->info->name }}</span>
        <span data-name="detail" contenteditable>{{ $task->detail ?? '--' }}</span>
      </td>
      <td><input type="text" class="form-control noformat" name="due_date" value="{{ $task->due_date->isoFormat('L') }}"></td>
      <td><input type="checkbox" class="form-control noformat" name="done" {{ $task->done ? 'checked' : '' }}></td>
      <td><input type="text" class="form-control noformat" name="done_date" value="{{ empty($task->done_date) ? '' : $task->done_date->isoFormat('L') }}"></td>
      @cannot('client')
      @if($is_renewals)
      <td><input type="text" class="form-control noformat" name="cost" value="{{ $task->cost }}"></td>
      <td><input type="text" class="form-control noformat" name="fee" value="{{ $task->fee }}"></td>
      <td><input type="text" class="form-control noformat" name="currency" value="{{ $task->currency }}"></td>
      {{-- <td><input type="text" class="form-control noformat" name="time_spent" value="{{ $task->time_spent }}"></td> --}}
      @endif
      @endcannot
      <td><input type="text" class="form-control noformat" name="assigned_to" data-ac="/user/autocomplete" value="{{ $task->assigned_to }}"></td>
      <td><input type="text" class="form-control noformat" name="notes" value="{{ $task->notes }}"></td>
      <td>
        @canany(['admin', 'readwrite'])
        <a href="#" class="hidden-action text-danger" id="deleteTask" title="{{ _i('Delete task') }}">&CircleTimes;</a>
        @endcanany
      </td>
      @cannot('client')
      <td>
          @if (count(App\EventName::where('code',$task->code)->first()->templates) != 0)
            <a href="#" class="chooseTemplate text-info font-weight-bold" data-url="/document/select/{{ $matter->id }}?EventName={{ $task->code }}&Task={{ $task->id }}">@</a>
          @endif
      </td>
      @endcannot
    </tr>
    @endforeach
  </tbody>
  @endforeach
</table>
<a class="badge badge-primary float-right" href="https://github.com/jjdejong/phpip/wiki/Events,-Deadlines-and-Tasks" target="_blank">?</a>

<template id="addTaskFormTemplate">
  <tr>
    <td colspan="{{ $ncols + 1 }}">
      <form id="addTaskForm">
        <input type="hidden" name="trigger_id">
        <div class="input-group">
          <input type="hidden" name="code">
          <input type="text" class="form-control form-control-sm" placeholder="Task" data-ac="/event-name/autocomplete/1?category={{ $matter->category_code }}" data-actarget="code">
          <input type="text" class="form-control form-control-sm" name="detail" placeholder="{{ _i('Detail') }}">
          <input type="text" class="form-control form-control-sm" placeholder="{{ _i('Due date (xx/xx/yyyy)') }}" name="due_date">
          <input type="hidden" name="assigned_to">
          <input type="text" class="form-control form-control-sm" placeholder="{{ _i('Assigned to') }}" data-ac="/user/autocomplete" data-actarget="assigned_to">
          <input type="text" class="form-control form-control-sm" name="notes" placeholder="{{ _i('Notes') }}">
          <div class="input-group-append">
            <button type="button" class="btn btn-primary btn-sm" id="addTaskSubmit">&check;</button>
            <button type="reset" class="btn btn-outline-primary btn-sm" onClick="$(this).parents('tr').html('')">&times;</button>
          </div>
        </div>
      </form>
    </td>
  </tr>
</template>
