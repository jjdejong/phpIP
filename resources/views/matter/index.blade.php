@php
  if (Request::get('tab') == 1) {
    $hideTab0 = 'd-none';
    $hideTab1 = '';
    $tab = 1;
  } else {
    $hideTab0 = '';
    $hideTab1 = 'd-none';
    $tab = 0;
  }
@endphp

@extends('layouts.app')

@section('style')
<style>
  input:not(:placeholder-shown) {
    border-color: rgb(0, 190, 190);
    font-weight: bold;
  }
</style>
@endsection

@section('content')
<div class="card border-primary mb-0">
  <div id="filterButtons" class="card-header bg-primary p-1">
    <form class="btn-toolbar" role="toolbar">
      <div class="btn-group-toggle mr-3" data-toggle="buttons">
        <label id="showContainers" class="btn btn-info {{ Request::get('Ctnr') ? 'active' : '' }}">
          <input type="checkbox" name="Ctnr" {{ Request::get('Ctnr') ? 'checked' : '' }}> Show Containers
        </label>
      </div>
      <div class="btn-group btn-group-toggle mr-3" data-toggle="buttons" id="actorStatus">
        <label id="showActors" class="btn btn-info {{ $tab == 1 ? '' : 'active' }}">
          <input type="radio" name="tab" {{ $tab == 1 ? '' : 'checked' }}> Actor View
        </label>
        <label id="showStatus" class="btn btn-info {{ $tab == 1 ? 'active' : '' }}">
          <input type="radio" name="tab" {{ $tab == 1 ? 'checked' : '' }}> Status View
        </label>
      </div>
      <div class="btn-group-toggle mr-3" id="mineAll" data-toggle="buttons">
        <label id="showResponsible" class="btn btn-info {{ Request::has('responsible') ? 'active' : '' }}" data-responsible="{{ Auth::user()->login }}">
          <input type="checkbox" name="responsible" {{ Request::has('responsible') ? 'checked' : '' }}> Show Mine
        </label>
      </div>
      <div class="btn-group-toggle mr-3" data-toggle="buttons">
        <label id="includeDead" class="btn btn-info {{ Request::get('include_dead') ? 'active' : '' }}">
          <input type="checkbox" name="include_dead" {{ Request::get('include_dead') ? 'checked' : '' }}> Include Dead
        </label>
      </div>
      <input type="hidden" name="display_with" value="{{ Request::get('display_with') }}">
      <div class="btn-group mr-3">
        <button id="exportList" type="button" class="btn btn-secondary"> &DownArrowBar; Export</button>
      </div>
      <div class="button-group">
        <button id="clearFilters" type="button" class="btn btn-dark">&larrpl; Clear filters</button>
      </div>
    </form>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover table-sm mb-1">
      <thead>
        <tr id="filterFields">
          <td>
            <div class="input-group input-group-sm">
              <input class="form-control" name="Ref" placeholder="Ref" value="{{ Request::get('Ref') }}">
              <div class="input-group-append">
              <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'caseref' ? 'active' : '' }}" type="button" data-sortkey="caseref" data-sortdir="desc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td><input class="form-control form-control-sm px-0" size="3" name="Cat" placeholder="Cat" value="{{ Request::get('Cat') }}"></td>
          <td>
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Status" placeholder="Status" value="{{ Request::get('Status') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'event_name.name' ? 'active' : '' }}" type="button" data-sortkey="event_name.name" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          @cannot('client')
          <td class="tab0 {{ $hideTab0 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Client" placeholder="Client" value="{{ Request::get('Client') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'cli.name' ? 'active' : '' }}" type="button" data-sortkey="cli.name" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          @endcannot
          <td class="tab0 {{ $hideTab0 }}"><input class="form-control form-control-sm" size="8" name="ClRef" placeholder="Cl. Ref" value="{{ Request::get('ClRef') }}"></td>
          @can('client')
          <td class="tab0 {{ $hideTab0 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Applicant" placeholder="Applicant" value="{{ Request::get('Applicant') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'app.name' ? 'active' : '' }}" type="button" data-sortkey="app.name" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          @endcan
          <td class="tab0 {{ $hideTab0 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Agent" placeholder="Agent" value="{{ Request::get('Agent') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'agt.name' ? 'active' : '' }}" type="button" data-sortkey="agt.name" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td class="tab0 {{ $hideTab0 }}"><input class="form-control form-control-sm" size="16" name="AgtRef" placeholder="Agt. Ref" value="{{ Request::get('AgtRef') }}"></td>
          <td class="tab0 {{ $hideTab0 }}"><input class="form-control form-control-sm" name="Title" placeholder="Title" value="{{ Request::get('Title') }}"></td>
          <td class="tab0 {{ $hideTab0 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Inventor1" placeholder="Inventor" value="{{ Request::get('Inventor1') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'inv.name' ? 'active' : '' }}" type="button" data-sortkey="inv.name" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td class="tab1 {{ $hideTab1 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Status_date" placeholder="Date" value="{{ Request::get('Status_date') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'status.event_date' ? 'active' : '' }}" type="button" data-sortkey="status.event_date" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td class="tab1 {{ $hideTab1 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Filed" placeholder="Filed" value="{{ Request::get('Filed') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'fil.event_date' ? 'active' : '' }}" type="button" data-sortkey="fil.event_date" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td class="tab1 {{ $hideTab1 }}"><input class="form-control form-control-sm" name="FilNo" placeholder="Number" value="{{ Request::get('FilNo') }}"></td>
          <td class="tab1 {{ $hideTab1 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Published" placeholder="Published" value="{{ Request::get('Published') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'pub.event_date' ? 'active' : '' }}" type="button" data-sortkey="pub.event_date" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td class="tab1 {{ $hideTab1 }}"><input class="form-control form-control-sm" name="PubNo" placeholder="Number" value="{{ Request::get('PubNo') }}"></td>
          <td class="tab1 {{ $hideTab1 }}">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-sm" name="Granted" placeholder="Granted" value="{{ Request::get('Granted') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary sortable {{ Request::get('sortkey') == 'grt.event_date' ? 'active' : '' }}" type="button" data-sortkey="grt.event_date" data-sortdir="asc">&UpDownArrow;</button>
              </div>
            </div>
          </td>
          <td class="tab1 {{ $hideTab1 }}"><input class="form-control form-control-sm" name="GrtNo" placeholder="Number" value="{{ Request::get('GrtNo') }}"></td>
        </tr>
      </thead>
      <tbody id="matterList">
        @foreach ($matters as $matter)
        @php // Format the publication number for searching on Espacenet
        $published = 0;
        if ( $matter->PubNo || $matter->GrtNo) {
          $published = 1;
          if ( $matter->origin == 'EP' )
            $CC = 'EP';
          else
            $CC = $matter->country;
          $removethese = [ "/^$matter->country/", '/ /', '/,/', '/-/', '/\//' ];
          $pubno = preg_replace ( $removethese, '', $matter->PubNo );
          if ( $CC == 'US' ) {
            if ( $matter->GrtNo )
              $pubno = preg_replace ( $removethese, '', $matter->GrtNo );
            else
              $pubno = substr ( $pubno, 0, 4 ) . substr ( $pubno, - 6 );
          }
        }
        @endphp
        @if ( $matter->container_id )
        <tr>
          @else
        <tr class="table-info">
          @endif
          <td {!! $matter->dead ? 'style="text-decoration: line-through;"' : '' !!}><a href="/matter/{{ $matter->id }}" target="_blank">{{ $matter->Ref }}</a></td>
          <td>{{ $matter->Cat }}</td>
          <td>
            @if ( $published )
            <a href="http://worldwide.espacenet.com/publicationDetails/biblio?DB=EPODOC&CC={{ $CC }}&NR={{ $pubno }}" target="_blank" title="Open in Espacenet">{{ $matter->Status }}</a>
            @else
            {{ $matter->Status }}
            @endif
          </td>
          @cannot('client')
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->Client }}</td>
          @endcannot
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->ClRef }}</td>
          @can('client')
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->Applicant }}</td>
          @endcan
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->Agent }}</td>
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->AgtRef }}</td>
          @if ( $matter->container_id && $matter->Title2 )
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->Title2 }}</td>
          @else
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->Title }}</td>
          @endif
          <td class="tab0 {{ $hideTab0 }}">{{ $matter->Inventor1 }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->Status_date }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->Filed }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->FilNo }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->Published }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->PubNo }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->Granted }}</td>
          <td class="tab1 {{ $hideTab1 }}">{{ $matter->GrtNo }}</td>
        </tr>
        @endforeach
        <tr>
          <td colspan="9">{{ $matters->links() }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('js/matter-index.js') }}"></script>
@endsection
