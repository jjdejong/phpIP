@php
$titles = $matter->titles->groupBy('type_name');
$classifiers = $matter->classifiers->groupBy('type_name');
$actors = $matter->actors->groupBy('role_name');
$linkedBy = $matter->linkedBy->groupBy('type_code');
@endphp

@extends('layouts.app')

@section('content')
<div class="row card-deck mb-1">
  <div id="refsPanel" class="card border-primary col-3 p-0">
    <div class="card-header bg-primary text-light reveal-hidden p-1">
      <a class="bg-primary text-white lead" href="/matter?Ref={{ $matter->caseref }}" title="{{ _i("See family") }}" {!! $matter->dead ? 'style="text-decoration: line-through;"' : '' !!}>{{ $matter->uid }}</a>
      ({{ $matter->category->category }})
      @canany(['admin', 'readwrite'])
      <a class="bg-primary text-white float-right hidden-action"
         data-toggle="modal" data-target="#ajaxModal" href="/matter/{{ $matter->id }}/edit" title="{{ _i('Advanced matter edition') }}">
        &#9998;
      </a>
      @endcanany
    </div>
    <div class="card-body p-1">
      <dl class="row mb-0">
        @if ($matter->container_id)
        <dt class="col-4 text-right">{{ _i("Container:") }}</dt>
        <dd class="col-8 mb-0">
          <a href="/matter/{{ $matter->container_id }}" title="{{ _i('See container') }}">
            {{ $matter->container->uid }}
          </a>
        </dd>
        @endif
        @if ($matter->parent_id)
        <dt class="col-4 text-right">{{ _i("Parent:") }}</dt>
        <dd class="col-8 mb-0">
          <a href="/matter/{{ $matter->parent_id }}" title="{{ _i('See parent') }}">
            {{ $matter->parent->uid }}
          </a>
        </dd>
        @endif
        @if ($matter->alt_ref)
        <dt class="col-4 text-right">{{ _i("Alt. ref:") }}</dt>
        <dd class="col-8 mb-0">{{ $matter->alt_ref }}</dd>
        @endif
        @if ($matter->expire_date)
        <dt class="col-4 text-right">{{ _i("Expiry:") }}</dt>
        <dd class="col-8">{{ Carbon\Carbon::parse($matter->expire_date)->isoFormat('L') }}</dd>
        @endif
      </dl>
      <div class="alert alert-info text-center py-1 mb-0">
        <b>{{ _i("Responsible:") }}</b>
        {{$matter->responsible}}
      </div>
    </div>
    <div class="card-footer p-1">
      @canany(['admin', 'readwrite'])
      <div class="btn-group btn-block">
        <a class="btn btn-info btn-sm" href="/matter/create?matter_id={{ $matter->id }}&operation=child" data-toggle="modal" data-target="#ajaxModal" data-size="modal-sm" title="{{ _i('Create child') . '  ' . _i($matter->category->category) }}">
          &oplus; {{ _i("New Child") }}
        </a>
        <a class="btn btn-info btn-sm" href="/matter/create?matter_id={{ $matter->id }}&operation=clone" data-toggle="modal" data-target="#ajaxModal" data-size="modal-sm" title="Clone {{ _i($matter->category->category) }}">
          &boxbox; {{ _i("Clone") }}
        </a>
        <a class="btn btn-info btn-sm {{ $matter->countryInfo->goesnational ? '' : 'disabled' }}" href="/matter/{{ $matter->id }}/createN" data-toggle="modal" data-target="#ajaxModal" data-size="modal-sm" title="{{ sprintf('Enter %s in national phase', _i($matter->category->category)) }}">
          &#9872; Nat. Phase
        </a>
      </div>
      @endcanany
    </div>
  </div>

  <div class="card col border-secondary p-1">
    <dl id="titlePanel">
      @foreach ( $titles as $type => $title_group )
        <dt class="mt-2">
          {{ $type }}
        </dt>
        @foreach ( $title_group as $title )
          <dd class="mb-0" data-resource="/classifier/{{ $title->id }}" data-name="value" contenteditable>
            {{ $title->value }}
          </dd>
        @endforeach
      @endforeach
      <div>
        <a class="badge badge-pill badge-primary float-right" role="button" data-toggle="collapse" href="#addTitleCollapse">+</a>
      </div>
      <div id="addTitleCollapse" class="collapse">
        <form id="addTitleForm" autocomplete="off">
          <div class="form-row">
            <input type="hidden" name="matter_id" value="{{ $matter->container_id ?? $matter->id }}">
            <div class="col-2">
              <input type="hidden" name="type_code">
              <input type="text" class="form-control form-control-sm" data-ac="/classifier-type/autocomplete/1" data-actarget="type_code" data-aclength="0" placeholder="Type" autocomplete="off">
            </div>
            <div class="col-10">
              <div class="input-group">
                <input type="text" class="form-control form-control-sm" name="value" placeholder="Value" autocomplete="off">
                <div class="input-group-append">
                  <button type="button" class="btn btn-primary btn-sm" id="addTitleSubmit">&check;</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </dl>
  </div>
  @if ( in_array('Image', $classifiers->keys()->all()) )
    <div class="card col-3 border-dark bg-dark p-1">
      <img src="/classifier/{{ $classifiers['Image'][0]->id }}/img" class="card-img-top" style="max-height: 150px; object-fit: contain;">
    </div>
  @endif
</div>

<div class="row card-deck">
  <div id="actorPanel" class="card col-3 border-secondary p-0" style="max-height: 600px">
    <div class="card-header reveal-hidden text-white bg-secondary p-1">
      Actors
      @canany(['admin', 'readwrite'])
      <a class="badge badge-pill badge-light hidden-action float-right" data-toggle="popover" href="javascript:void(0)" title="Add Actor">
        &plus;
      </a>
      @endcanany
    </div>
    <div class="card-body bg-light p-1" style="overflow: auto;">
      @foreach ( $actors as $role_name => $role_group )
      <div class="card reveal-hidden border-secondary mb-1">
        <div class="card-header bg-primary text-light p-1">
          {{ $role_name }}
          @canany(['admin', 'readwrite'])
          <a class="hidden-action float-right text-light font-weight-bold ml-3" data-toggle="popover" title="{{ sprintf(_i('Add %s'), $role_name ) }}"
             data-role_name="{{ $role_name }}"
             data-role_code="{{ $role_group->first()->role_code }}"
             data-shareable="{{ $role_group->first()->shareable }}"
             href="javascript:void(0)">
            &oplus;
          </a>
          <a class="hidden-action float-right text-light font-weight-bold" data-toggle="modal" data-target="#ajaxModal" data-size="modal-lg" title=" {{ sprintf(_i('Edit actors in %s group'), $role_group->first()->role_name) }}" href="/matter/{{ $matter->id }}/roleActors/{{ $role_group->first()->role_code }}">
            &#9998;
          </a>
          @endcanany
        </div>
        <div class="card-body p-1" style="max-height: 80px; overflow: auto;">
          <ul class="list-unstyled mb-0">
            @foreach ( $role_group as $actor )
            <li class="text-truncate {{ $actor->inherited ? 'font-italic' : '' }}">
              @if ( $actor->warn )
              <span title="{{ _i('Special instructions') }}">&#9888;</span>
              @endif
              <a @if ($actor->warn) class="text-danger" @endif
                href="/actor/{{ $actor->actor_id }}"
                data-toggle="modal"
                data-target="#ajaxModal"
                title="{{ _i('Actor data') }}">
              {{ $actor->display_name }}
              </a>
              @if ( $actor->show_ref && $actor->actor_ref )
              ({{ $actor->actor_ref }})
              @endif
              @if ( $actor->show_company && $actor->company )
              &nbsp;- {{ $actor->company }}
              @endif
              @if ( $actor->show_date && $actor->date )
              ({{ Carbon\Carbon::parse($actor->date)->isoFormat('L') }})
              @endif
              @if ( $actor->show_rate && $actor->rate != '100' )
              &nbsp;- {{ $actor->rate }}
              @endif
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  <div id="multiPanel" class="card col-9 p-0" style="background: transparent;">
    <div class="card-deck mb-1">
      <div class="card border-primary reveal-hidden" style="min-height: 138px;">
        <div class="card-header bg-primary p-1">
          <a class="row text-light text-decoration-none" href="/matter/{{ $matter->id }}/events" data-toggle="modal" data-target="#ajaxModal" data-size="modal-lg" title="{{ _i('All events') }}">
            <span class="col-5">{{ _i("Status") }}</span>
            <span class="col-3">{{ _i("Date") }}</span>
            <span class="col-4">
              {{ _i('Number') }}
              <span class="hidden-action float-right">
                &equiv;
              </span>
            </span>
          </a>
        </div>
        <div class="card-body p-1" id="statusPanel" style="overflow: auto;">
          @foreach ( $matter->events->where('info.status_event', 1) as $event )
          <div class="row">
            <span class="col-5">{{ $event->info->name }}</span>
            @if ( $event->alt_matter_id )
            <span class="col-3">{{ ($event->link->event_date ?? $event->event_date)->isoFormat('L') }}</span>
            <span class="col">
              <a href="/matter/{{ $event->alt_matter_id }}" title="{{ $event->altMatter->uid }}" target="_blank">{{ $event->altMatter->country }} {{ $event->link->detail ?? $event->detail }}</a>
            </span>
            @else
            <span class="col-3">{{ $event->event_date->isoFormat('L') }}</span>
            <span class="col">
              @if ( $event->publicUrl() )
              <a href="{{ $event->publicUrl() }}" target="_blank">{{ $event->detail }}</a>
              @else
              {{ $event->detail }}
              @endif
            </span>
            @endif
          </div>
          @endforeach
        </div>
      </div>
      <div class="card col-6 p-0 border-primary reveal-hidden">
        <div class="card-header {{ $matter->tasksPending->count() ? 'text-warning' : 'text-light' }} p-1 bg-primary">
          {{ _i("Open Tasks Due") }}
          <a class="text-warning text-decoration-none hidden-action float-right stretched-link" href="/matter/{{ $matter->id }}/tasks" data-toggle="modal" data-target="#ajaxModal" data-size="modal-lg" title="{{ _i('All tasks') }}"><span class="">
            &equiv;
          </a>
        </div>
        <div class="card-body p-1" id="opentask-panel" style="overflow: auto;">
          @foreach ( $matter->tasksPending as $task )
          <div class="row">
            <span class="col-9">{{ $task->info->name }}: {{ $task->detail }}</span>
            <span class="col-3">{{ $task->due_date->isoFormat('L') }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="row card-deck mb-1">
      <div class="card col-2 p-0 border-primary reveal-hidden" style="min-height: 138px;">
        <div class="card-header {{ $matter->renewalsPending->count() ? 'text-warning' : 'text-light' }} p-1 bg-primary">
          {{ _i("Renewals Due") }}
          <a class="text-warning text-decoration-none hidden-action float-right stretched-link" href="/matter/{{ $matter->id }}/renewals" data-toggle="modal" data-target="#ajaxModal" data-size="modal-lg" title="{{ _i('All renewals') }}">
            &equiv;
          </a>
        </div>
        <div class="card-body p-1" id="renewal-panel" style="overflow: auto;">
          @foreach ( $matter->renewalsPending->take(3) as $task )
          <div class="row">
            <span class="col-4">{{ $task->detail }}</span>
            <span class="col-8">{{ $task->due_date->isoFormat('L') }}</span>
          </div>
          @endforeach
        </div>
      </div>
      <div class="card col-6 p-0 border-primary reveal-hidden">
        <div class="card-header p-1 bg-primary">
          <a class="row text-light text-decoration-none" href="/matter/{{ $matter->id }}/classifiers" data-target="#ajaxModal" data-toggle="modal" title="{{ _i('Classifier detail') }}">
            <div class="col">
              {{ _i("Classifiers") }}
              <span class="hidden-action float-right">
                &equiv;
              </span>
            </div>
          </a>
        </div>
        <div class="card-body p-1" id="classifierPanel" style="overflow: auto;">
          @foreach ( $classifiers as $type => $classifier_group )
            @if ( $type != 'Image' )
            <div class="card">
              <div class="card-body p-1">
                <span class="font-weight-bolder align-middle">{{ $type }}</span>
                @foreach ( $classifier_group as $classifier )
                  @if ( $classifier->url )
                    <a href="{{ $classifier->url }}" class="badge badge-primary align-middle" target="_blank">{{ $classifier->value }}</a>
                  @elseif ( $classifier->lnk_matter_id )
                    <a href="/matter/{{ $classifier->lnk_matter_id }}" class="badge badge-primary align-middle">{{ $classifier->linkedMatter->uid }}</a>
                  @else
                    <div class="badge badge-secondary align-middle">{{ $classifier->value }}</div>
                  @endif
                @endforeach
                @if ( $type == 'Link' )
                  @foreach ( $matter->linkedBy as $linkedBy )
                    <a href="/matter/{{ $linkedBy->id }}" class="badge badge-primary align-middle">{{ $linkedBy->uid }}</a>
                  @endforeach
                @endif
              </div>
            </div>
            @endif
          @endforeach
          @if ( !in_array('Link', $classifiers->keys()->all()) && !$matter->linkedBy->isEmpty() )
          <div class="card">
            <div class="card-body p-1">
              <span class="font-weight-bolder align-middle">{{ _i('Link') }}</span>
                @foreach ( $matter->linkedBy as $linkedBy )
                  <a href="/matter/{{ $linkedBy->id }}" class="badge badge-primary align-middle">{{ $linkedBy->uid }}</a>
                @endforeach
            </div>
          </div>
          @endif
        </div>
      </div>
      <div class="card border-info col-4 p-0">
        <div class="card-header bg-info text-white p-1">
          {{ _i('Related Matters') }}
          <span class="float-right">&#9432;</span>
        </div>
        <div class="card-body p-1" id="relationsPanel" style="overflow: auto;">
          @if ( $matter->family->count() )
          <dl class="mb-1">
            <dt>{{ _i('Fam') }}</dt>
            <dd class="mb-1">
              @foreach ( $matter->family as $member )
              <a class="badge badge-{{ $member->suffix == $matter->suffix ? 'secondary' : 'primary' }}" href="/matter/{{ $member->id }}">{{ $member->suffix }}</a>
              @endforeach
            </dd>
          </dl>
          @endif
          @foreach ( $matter->priorityTo->groupBy('caseref') as $caseref => $family )
          <dl class="mb-1">
            <dt>{{ $caseref }}</dt>
            <dd class="mb-1">
              @foreach ( $family as $rmatter )
              <a class="badge badge-primary" href="/matter/{{ $rmatter->id }}">{{ $rmatter->suffix }}</a>
              @endforeach
            </dd>
          </dl>
          @endforeach
        </div>
      </div>
    </div>
    <div class="row card-deck">
      <div class="card border-secondary col-10 p-0" style="min-height: 100px;">
        <div class="card-header p-1 bg-secondary text-light">
          {{ _i('Notes') }}
        </div>
        <div class="card-body p-1" style="overflow: auto;">
          <textarea id="notes" class="form-control noformat" name="notes" data-resource="/matter/{{ $matter->id }}">{{ $matter->notes }}</textarea>
        </div>
        <div class="card-footer p-1">
          {{ _i('Summaries:') }}
          <a class="badge badge-primary align-middle"
              href="/matter/{{ $matter->id }}/description/en"
              data-toggle="modal"
              data-target="#ajaxModal"
              data-size="modal-lg"
              title="{{ _i('Copy a summary in English') }}">
              &boxbox; EN
          </a>
          <a class="badge badge-primary align-middle"
              href="/matter/{{ $matter->id }}/description/fr"
              data-toggle="modal"
              data-target="#ajaxModal"
              data-size="modal-lg"
              title="{{ _i('Copy a summary in French') }}">
              &boxbox; FR
          </a>
          {{ _i('Email:') }}
          <a class="badge badge-primary align-middle"
              href="/document/select/{{ $matter->id }}?Language=en"
              data-toggle="modal"
              data-target="#ajaxModal"
              data-size="modal-lg"
              title="{{ _i('Prepare an email') }}">
              &#9993; EN
          </a>
          <a class="badge badge-primary align-middle"
              href="/document/select/{{ $matter->id }}?Language=fr"
              data-toggle="modal"
              data-target="#ajaxModal"
              data-size="modal-lg"
              title="{{ _i('Prepare an email') }}">
              &#9993; FR
          </a>
        </div>
      </div>
      <div class="card col-2 border-info p-1">
        <div id="dropZone" class="card-body bg-info text-light text-center align-middle" data-url="/matter/{{ $matter->id }}/mergeFile">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-intersect" viewBox="0 0 16 16">
            <path d="M0 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2H2a2 2 0 0 1-2-2V2zm5 10v2a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2v5a2 2 0 0 1-2 2H5zm6-8V2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2V6a2 2 0 0 1 2-2h5z"/>
          </svg>
          <p>{{ _i("File merge") }}<br>{{ _i("Drop Zone") }}</p>
        </div>
        <div class="bg-info align-bottom text-right"><a class="badge badge-primary" href="https://github.com/jjdejong/phpip/wiki/Templates-(email-and-documents)#document-template-usage" target="_blank">?</a></div>
      </div>
    </div>
  </div>
</div>

<template id="actorPopoverTemplate">
  <form id="addActorForm" autocomplete="off">
     <input type="hidden" name="role">
     <input type="hidden" name="shared">
     <input type="hidden" name="actor_id">
     <div class="ui-front">
       <input type="text" class="form-control form-control-sm" id="roleName" data-actarget="role" placeholder="Role">
       <input type="text" class="form-control form-control-sm" id="actorName" data-actarget="actor_id" placeholder="Name">
       <input type="text" class="form-control form-control-sm" name="actor_ref" placeholder="Reference">
     </div>
     <div class="form-group">
       <div class="form-check my-1">
         <input class="form-check-input mt-0" type="radio" id="actorShared" name="matter_id" value="{{ $matter->container_id ?? $matter->id }}">
         <label class="form-check-label" for="actorShared">{{ _i("Add to container and share") }}</label>
       </div>
       <div class="form-check my-1">
         <input class="form-check-input mt-0" type="radio" id="actorNotShared" name="matter_id" value="{{ $matter->id }}">
         <label class="form-check-label" for="actorNotShared">{{ _i("Add to this matter only (not shared)") }}</label>
       </div>
     </div>
     <div class="btn-group" role="group">
       <button type="button" class="btn btn-info btn-sm" id="addActorSubmit">&check;</button>
       <button type="button" class="btn btn-outline-info btn-sm" id="popoverCancel">&times;</button>
     </div>
     <div class="alert alert-danger d-none" role="alert"></div>
   </form>
</template>

@endsection

@section('script')

@include('matter.show-js')

@endsection
