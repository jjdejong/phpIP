<td colspan="8">
<form id="sendDocumentForm">
  <input type='hidden' value="{{ $matter->id }}" name="matter_id">
  @if (isset($event))
  <input type='hidden' value="{{ $event->id }}" name="event_id">
  @endif
  @if (isset($task))
  <input type='hidden' value="{{ $task->id }}" name="task_id">
  @endif
    <div class='container'>
    <div class='row font-weight-bold'>
      <div class="col-3 bg-light">
        Contact
      </div>
      <div class="col-1 bg-light">
        {{ _i("Send to:") }}
      </div>
      <div class="col-1 bg-light">
        {{ _i('CC:') }}
      </div>
      <div class="col-7">
      </div>
    </div>
    @foreach ($contacts as $contact)
      <div class='row' >
        <div class="col-3">
          {{ $contact->first_name}} {{ is_null($contact->name) ? $contact->display_name : $contact->name  }}
        </div>
        <div class="col-1">
          <input id="" class="contact" name="sendto-{{ $contact->actor_id }}" type="checkbox">
        </div>
        <div class="col-1">
          <input id="" class="contact" name="ccto-{{ $contact->actor_id }}" type="checkbox">
        </div>
        <div class="col-7">
        </div>
      </div>
    @endforeach
  </div>
  <div class='container' data-resource="/document/select/{{ $matter->id }}">
      <div class="row bg-light font-weight-bold">
          <div class="col-lg-4">
            {{ _i("Summary") }}
          </div>
          <div class="col-lg-2">
            {{ _i("Language") }}
          </div>
          <div class="col-lg-2">
            {{ _i("Category") }}
          </div>
          <div class="col-lg-3">
            {{ _i("Style") }}
          </div>
          <div class="col-lg-1">
          </div>
      </div>
      @foreach ($members as $member)
        <div class="row reveal-hidden" data-resource="/document/mailto/{{ $member->id }}">
          <div class = "col-lg-4">
            {{ $member->summary }}
          </div>
          <div class = "col-lg-2">
            {{ $member->language }}
          </div>
          <div class = "col-lg-2">
            {{ $member->category }}
          </div>

          <div class = "co-lgl-3">
            {{ $member->style }}
          </div>
          <div class = "col-lg-1">
            <a class="sendDocument btn btn-info px-1 py-0">{{ _i('Prepare') }}</a>
          </div>
        </div>
      @endforeach
  </div>
</form>
</td>
