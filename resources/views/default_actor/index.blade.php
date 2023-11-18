@extends('layouts.app')

@section('content')
<legend class="alert alert-dark d-flex justify-content-between py-2 mb-1">
  Default Actors
  <a href="default_actor/create" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajaxModal" title="Default actors" data-resource="/default_actor/">Create Default Actor</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary p-1" style="max-height: 640px; overflow: auto;">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr id="filter" class="table-primary align-middle">
            <th><input class="form-control" data-source="/default_actor" name="Actor" placeholder="Actor"></th>
            <th><input class="form-control" data-source="/default_actor" name="Role" placeholder="Role"></th>
            <th><input class="form-control" data-source="/default_actor" name="Country" placeholder="Country"></th>
            <th><input class="form-control" data-source="/default_actor" name="Category" placeholder="Category"></th>
            <th><input class="form-control" data-source="/default_actor" name="Client" placeholder="Client"></th>
          </tr>
        </thead>
        <tbody id="tableList">
          @foreach ($default_actors as $default_actor)
          <tr class="reveal-hidden" data-id="{{ $default_actor->id }}">
            <td>
              <a href="/default_actor/{{ $default_actor->id }}" data-panel="ajaxPanel" title="{{ _i('Actor') }}">
                {{ $default_actor->actor->name }}
              </a>
            </td>
            <td>{{ empty($default_actor->roleInfo) ? '' : $default_actor->roleInfo->name }}</td>
            <td>{{ empty($default_actor->country) ? '' : $default_actor->country->name }}</td>
            <td>{{ empty($default_actor->category) ? '' : $default_actor->category->category }}</td>
            <td>{{ empty($default_actor->client) ? '' : $default_actor->client->name }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-5">
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i('Default actor information') }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i('Click on line to view and edit details') }}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script src="{{ asset('js/tables.js') }}" defer></script>
@endsection
