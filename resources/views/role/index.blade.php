@extends('layouts.app')

@section('content')
<legend class="text-primary">
  {{ _i('Actor Roles') }}
  <a href="role/create" class="btn btn-primary float-right" data-toggle="modal" data-target="#ajaxModal" title="Role" data-resource="/role/">{{ _i('Create a new Role') }}</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary overflow-auto" style="max-height: 640px;">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr id="filter" class="bg-primary text-light">
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/role" name="Code" placeholder="{{ _i('Code') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/role" name="Name" placeholder="{{ _i('Name') }}"></th>
            <th class="align-middle text-center border-top-0" colspan="2">{{ _i('Notes') }}</th>
          </tr>
        </thead>
        <tbody id="tableList">
          @foreach ($roles as $role)
          <tr class="reveal-hidden" data-id="{{ $role->code }}">
            <td>
              <a href="/role/{{ $role->code }}" data-panel="ajaxPanel" title="{{ _i('Role info') }}">
                {{ $role->code }}
              </a>
            </td>
            <td>{{ $role->name }}</td>
            <td>{{ $role->notes }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-5">
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i('Role information') }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i('Click on role to view and edit details') }}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')

@include('tables.table-js')

@endsection
