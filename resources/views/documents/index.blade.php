@extends('layouts.app')

@section('content')
<legend class="text-primary">
  {{ _i("Document Classes") }}
  <a href="document/create" class="btn btn-primary float-right" data-toggle="modal" data-target="#ajaxModal" title="Document class" data-source="/document" data-resource="/document/create/">{{ _i("Create a new class of templates") }}</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr id="filter" class="bg-primary text-light">
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/document" name="Name" placeholder="{{ _i('Name') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/document" name="Notes" placeholder="{{ _i('Notes') }}"></th>
          </tr>
        </thead>
        <tbody id="tableList">
          @foreach ($template_classes as $class)
          <tr data-id="{{ $class->id }}" class="reveal-hidden">
            <td>
              <a href="/document/{{ $class->id }}" data-panel="ajaxPanel" title="{{ _i('Class data') }}">
                {{ $class->name }}
              </a>
            </td>
            <td>{{ $class->notes }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5">
              {{ $template_classes->links() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i("Class information") }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i("Click on class to view and edit details") }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')

@include('tables.table-js')

@endsection
