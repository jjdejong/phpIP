<div class="card border-primary">
    <div class="card-header text-white bg-primary p-1">
      <form class="row" id="header-{{ $isrenewals }}" autocomplete="off">
        <div class="lead col-2">
          {{ $isrenewals ? 'Open Renewals' : 'Open Tasks' }}
        </div>
        @cannot('client')
        <div class="col-6">
          <div class="input-group">
            <div class="btn-group btn-group-toggle input-group-prepend" data-toggle="buttons">
              <label class="btn btn-info {{ $what_tasks == 0 ? 'active' : '' }}">
                <input type="radio" wire:model="what_tasks" value="0">Everyone
              </label>
              @if(!$user_dashboard)
              <label class="btn btn-info {{ $what_tasks == 1 ? 'active' : '' }}">
                <input type="radio" wire:model="what_tasks" value="1">{{ Auth::user()->login }}
              </label>
              @endif
              <label class="btn btn-info {{ $what_tasks > 1 ? 'active' : '' }}">
                <input type="radio" wire:model="what_tasks" value="{{ $client_id }}">Client
              </label>
            </div>
            @livewire('actor-autocomplete')
          </div>
        </div>
        <div class="col-4">
          <div class="input-group">
            @canany(['admin', 'readwrite'])
            <div class="input-group-prepend">
              <button class="btn btn-light" type="submit" form="clearTasks-{{ $isrenewals }}">Clear selected on</button>
            </div>
            <input type="text" class="form-control mr-2" wire:model.lazy="clear_date">
            @endcanany
          </div>
        </div>
        @endcannot
      </form>
      <div class="row mt-1">
        <div class="col">
        </div>
        <div class="col-2">
          Matter
        </div>
        <div class="col-2">
          Client
        </div>
        <div class="col-3">
          Description
        </div>
        <div class="col-2">
          Due date
        @canany(['admin', 'readwrite'])
          <span class="float-right">Clear</span>
        @endcanany
        </div>
      </div>
    </div>
    <div class="card-body p-1">
      <form wire:submit.prevent="save" id="clearTasks-{{ $isrenewals }}" autocomplete="off">
        <table class="table table-striped table-sm mb-1" style="width: 100%; table-layout: fixed;">
            @foreach ($ptasks as $index => $task)
            <tr class="row">
              <td class="col text-truncate py-0">
                <a href="/matter/{{ $task->matter_id }}/{{ $isrenewals ? 'renewals' : 'tasks' }}" data-toggle="modal" data-target="#ajaxModal" data-size="modal-lg" data-resource="/task/" title="All tasks">
                  {{ $task->name }} {{ $task->detail }}
                </a>
              </td>
              <td class="col-2 py-0">
                <a href="/matter/{{ $task->matter_id }}">
                  {{ $task->uid }}
                </a>
              </td>
              <td class="col-2 text-truncate py-0">
                {{ $task->client }}
              </td>
              <td class="col-3 text-truncate py-0">
                {{ $task->title ?? $task->trademark }}
              </td>
              <td class="col-2 py-0 px-2">
                {{ $task->due_date->isoFormat('L') }}
                @if ($task->due_date < now())
                <div class="badge badge-danger py-0" title="Overdue">&nbsp;</div>
                @elseif ($task->due_date < now()->addWeeks(2))
                <div class="badge badge-warning py-0" title="Urgent">&nbsp;</div>
                @else
                <div class="badge py-0" title="Regular">&nbsp;</div>
                @endif
              @canany(['admin', 'readwrite'])
                <input type="checkbox" class="float-right" wire:model.defer="tasks.{{ $index }}" wire:key="task-{{ $task->id }}" value="{{ $task->id }}">
              @endcanany
              </td>
            </tr>
            @endforeach
        </table>
      </form>
      {{ $ptasks->links() }}
    </div>
</div>